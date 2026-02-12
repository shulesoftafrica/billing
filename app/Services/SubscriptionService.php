<?php

namespace App\Services;

use App\Models\AdvancePayment;
use App\Models\ControlNumber;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\PricePlan;
use App\Models\ProductPurchase;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create subscriptions for multiple price plans and generate a single invoice
     *
     * @param int $customerId
     * @param array $planIds
     * @return Invoice
     * @throws \Exception
     */
    public function createSubscriptionsWithInvoice(int $customerId, array $planIds): Invoice
    {
        // Validate input
        $this->validateInput($customerId, $planIds);

        return DB::transaction(function () use ($customerId, $planIds) {
            // Get customer and price plans
            $customer = Customer::findOrFail($customerId);
            $pricePlans = PricePlan::whereIn('id', $planIds)
                ->where('active', true)
                ->lockForUpdate() // Lock rows to prevent concurrent issues
                ->get();

            // Ensure all plan IDs are valid and active
            if ($pricePlans->count() !== count($planIds)) {
                throw new \Exception('One or more price plans are invalid or inactive');
            }

            // Check for duplicate subscriptions
            $this->checkDuplicateSubscriptions($customerId, $planIds);

            // Create subscriptions for each plan
            $subscriptions = $this->createSubscriptions($customer, $pricePlans);

            // Create single invoice for all subscriptions
            $invoice = $this->createInvoice($customer, $pricePlans);

            // Create invoice items for each plan (linked to subscriptions)
            $this->createInvoiceItems($invoice, $pricePlans, $subscriptions);

            Log::info('Subscriptions created successfully', [
                'customer_id' => $customerId,
                'invoice_id' => $invoice->id,
                'subscription_count' => count($subscriptions),
            ]);

            // Load relationships for response
            $invoice->load(['invoiceItems.pricePlan', 'customer']);

            return $invoice;
        });
    }

    /**
     * Validate input parameters
     *
     * @param int $customerId
     * @param array $planIds
     * @throws \Exception
     */
    private function validateInput(int $customerId, array $planIds): void
    {
        if (empty($planIds)) {
            throw new \Exception('At least one price plan must be selected');
        }

        if (!is_array($planIds)) {
            throw new \Exception('Plan IDs must be an array');
        }

        // Remove duplicates
        $planIds = array_unique($planIds);

        // Validate all plan IDs are integers
        foreach ($planIds as $planId) {
            if (!is_numeric($planId) || $planId <= 0) {
                throw new \Exception('Invalid price plan ID: ' . $planId);
            }
        }

        // Validate customer exists
        if (!Customer::where('id', $customerId)->exists()) {
            throw new \Exception('Customer not found');
        }
    }

    /**
     * Check for duplicate non-active subscriptions (pending, paused, etc.)
     * Active subscriptions are allowed for renewals
     *
     * @param int $customerId
     * @param array $planIds
     * @throws \Exception
     */
    private function checkDuplicateSubscriptions(int $customerId, array $planIds): void
    {
        $existingSubscriptions = Subscription::where('customer_id', $customerId)
            ->whereIn('price_plan_id', $planIds)
            ->whereIn('status', ['pending', 'paused'])
            ->pluck('price_plan_id')
            ->toArray();

        if (!empty($existingSubscriptions)) {
            throw new \Exception(
                'Customer already has pending/paused subscriptions for plan IDs: ' .
                    implode(', ', $existingSubscriptions)
            );
        }
    }

    /**
     * Create subscription records for each price plan
     * Subscriptions start with 'pending' status and dates are null until payment
     *
     * @param Customer $customer
     * @param \Illuminate\Support\Collection $pricePlans
     * @return array
     */
    private function createSubscriptions(Customer $customer, $pricePlans): array
    {
        $subscriptions = [];

        foreach ($pricePlans as $plan) {
            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'price_plan_id' => $plan->id,
                'status' => 'pending',
                'start_date' => null,
                'end_date' => null,
                'next_billing_date' => null,
            ]);

            $subscriptions[] = $subscription;
        }

        return $subscriptions;
    }

    /**
     * Create invoice for all selected plans
     *
     * @param Customer $customer
     * @param \Illuminate\Support\Collection $pricePlans
     * @return Invoice
     */
    private function createInvoice(Customer $customer, $pricePlans): Invoice
    {
        // Calculate totals
        $subtotal = $pricePlans->sum('amount');
        $taxTotal = 0; // Tax calculation can be added later based on business rules
        $total = $subtotal + $taxTotal;

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => $invoiceNumber,
            'status' => 'issued',
            'description' => 'Subscription invoice for ' . count($pricePlans) . ' plan(s)',
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'due_date' => Carbon::now()->addDays(30)->toDateString(),
            'issued_at' => Carbon::now(),
        ]);

        return $invoice;
    }

    /**
     * Create invoice items for each price plan
     *
     * @param Invoice $invoice
     * @param \Illuminate\Support\Collection $pricePlans
     * @param array $subscriptions
     * @return void
     */
    private function createInvoiceItems(Invoice $invoice, $pricePlans, array $subscriptions): void
    {
        // Create a map of plan_id to subscription for easy lookup
        $subscriptionMap = [];
        foreach ($subscriptions as $subscription) {
            $subscriptionMap[$subscription->price_plan_id] = $subscription;
        }

        foreach ($pricePlans as $plan) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'subscription_id' => $subscriptionMap[$plan->id]->id,
                'price_plan_id' => $plan->id,
                'quantity' => 1,
                'unit_price' => $plan->amount,
                'total' => $plan->amount,
            ]);
        }
    }

    /**
     * Generate unique invoice number
     *
     * @return string
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = Carbon::now()->format('Ymd');

        // Get last invoice number for today
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . $date . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract sequence number and increment
            $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
            $sequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        return $prefix . $date . $sequence;
    }

    /**
     * Calculate subscription end date based on billing interval
     *
     * @param Carbon $startDate
     * @param PricePlan $plan
     * @return string
     */
    private function calculateEndDate(Carbon $startDate, PricePlan $plan): string
    {
        $endDate = clone $startDate;

        switch ($plan->subscription_type) {
            case 'daily':
                $endDate->addDay();
                break;
            case 'weekly':
                $endDate->addWeek();
                break;
            case 'monthly':
                $endDate->addMonth();
                break;
            case 'quarterly':
                $endDate->addMonths(3);
                break;
            case 'yearly':
                $endDate->addYear();
                break;
            default:
                $endDate->addDay(); // Default to daily
        }

        return $endDate->toDateString();
    }

    /**
     * Calculate next billing date based on billing interval
     *
     * @param Carbon $startDate
     * @param PricePlan $plan
     * @return string
     */
    private function calculateNextBillingDate(Carbon $startDate, PricePlan $plan): string
    {
        return $this->calculateEndDate($startDate, $plan);
    }

    /**
     * Get all subscriptions with optional filtering
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSubscriptions(array $filters = [])
    {
        $query = Subscription::with(['customer', 'pricePlan.product']);

        // Apply filters if provided
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Get subscriptions for a specific customer
     *
     * @param int $customerId
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomerSubscriptions(int $customerId, ?string $status = null)
    {
        $query = Subscription::with(['pricePlan.product'])
            ->where('customer_id', $customerId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }

    /**
     * Cancel a subscription (only pending subscriptions can be cancelled)
     *
     * @param int $subscriptionId
     * @return Subscription
     * @throws \Exception
     */
    public function cancelSubscription(int $subscriptionId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId) {
            $subscription = Subscription::lockForUpdate()->find($subscriptionId);

            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            // Only pending subscriptions can be cancelled
            if ($subscription->status !== 'pending') {
                throw new \Exception(
                    'Only pending subscriptions can be cancelled. Current status: ' . $subscription->status
                );
            }

            // Update subscription status to canceled
            $subscription->update(['status' => 'canceled']);

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'customer_id' => $subscription->customer_id,
                'price_plan_id' => $subscription->price_plan_id,
            ]);

            return $subscription->fresh(['customer', 'pricePlan']);
        });
    }
    /**
     * Enable subscription by validating total payments against invoiced amount
     *
     * @param Subscription $subscription
     * @param float $invoicedAmount
     * @param Payment|null $payment
     * @return bool
     * @throws \Exception
     */
    public function enableSubscription($invoice_id, $subscription, $invoicedAmount, $payment = null): bool
    {
        return DB::transaction(function () use ($invoice_id, $subscription, $invoicedAmount, $payment) {
            $customerId = $subscription->customer_id;
            $productId = $subscription->pricePlan->product_id;

            // Step 1: Find all pending payments for this customer
            $pendingPayments = Payment::where('customer_id', $customerId)
                ->where('status', 'pending')
                ->whereIn('payment_reference', ControlNumber::where('customer_id', $customerId)->where('product_id', $productId)->get()->pluck('reference'))
                ->get();

            // Step 2: Find advance payments with reminder > 0
            $advancePayments = AdvancePayment::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->where('reminder', '>', 0)
                ->get();

            // Step 3: Verify current payment is not null
            $hasCurrentPayment = $payment !== null;
            if (!$hasCurrentPayment) {
                $payment =  $pendingPayments->sortByDesc('created_at')->first();
                $hasCurrentPayment = $payment !== null;
            }
            // Step 4: Calculate sum of all payments
            $pendingPaymentsSum = $pendingPayments->sum('amount');
            $advancePaymentsSum = $advancePayments->sum('reminder');
            $totalPayments = $pendingPaymentsSum + $advancePaymentsSum;
            // Step 5: Check the total payments against the invoiced amount
            $totalPaid = InvoicePayment::where('invoice_id', $invoice_id)->sum('amount');
            $balance = $invoicedAmount - $totalPaid;
            // Now use $balance as the invoiced amount
            // Step 6: Compare and execute logic
            if ($totalPayments == $balance) {
                // Equal: Activate subscription and clear all payments
                $startDate = Carbon::now();
                $endDate = $this->calculateEndDate($startDate, $subscription->pricePlan);

                $subscription->update([
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                // Send webhook notification for subscription activation
                $this->sendSubscriptionWebhook($subscription);

                if ($hasCurrentPayment) {
                    $payment->update(['status' => 'cleared']);
                    // Record the payment allocation to invoice
                    InvoicePayment::create([
                        'invoice_id' => $invoice_id,
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                    ]);
                }

                // Record all other pending payments as allocated to invoice
                $pendingPayments->each(function ($p) use ($invoice_id, $payment) {
                    if (!$payment || $p->id !== $payment->id) {
                        $p->update(['status' => 'cleared']);
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $p->id,
                            'amount' => $p->amount,
                        ]);
                    }
                });

                // Record advance payments used to clear the invoice
                $advancePayments->each(function ($ap) use ($invoice_id) {
                    if ($ap->reminder > 0) {
                        // Record advance payment allocation to invoice
                        if ($ap->payment_id) {
                            InvoicePayment::create([
                                'invoice_id' => $invoice_id,
                                'payment_id' => $ap->payment_id,
                                'amount' => $ap->reminder,
                            ]);
                        }
                        // Clear the advance payment after using it
                        $ap->update(['reminder' => 0]);
                    }
                });

                Log::info('Subscription enabled - payments equal invoiced amount', [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $customerId,
                    'total_payments' => $totalPayments,
                    'invoiced_amount' => $invoicedAmount,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                return true;
            } elseif ($totalPayments > $balance) {
                // Greater: Activate subscription and handle excess
                $startDate = Carbon::now();
                $endDate = $this->calculateEndDate($startDate, $subscription->pricePlan);

                $subscription->update([
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                // Send webhook notification for subscription activation
                $this->sendSubscriptionWebhook($subscription);

                // Allocate amount to invoice and track remaining amount for each payment
                $remainingToAllocate = $balance;

                // Allocate advance payments to invoice with remaining amount as reminder
                $advancePayments->each(function ($ap) use ($invoice_id, &$remainingToAllocate) {
                    if ($remainingToAllocate <= 0 || $ap->reminder <= 0) {
                        return; // Stop if we've allocated the full invoice amount or advance payment is empty
                    }

                    $allocationAmount = min($ap->reminder, $remainingToAllocate);
                    $newReminder = $ap->reminder - $allocationAmount;

                    // Record advance payment allocation to invoice
                    if ($ap->payment_id) {
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $ap->payment_id,
                            'amount' => $allocationAmount,
                        ]);
                    }

                    // Update advance payment with remaining reminder
                    $ap->update(['reminder' => $newReminder]);

                    $remainingToAllocate -= $allocationAmount;
                });


                if ($hasCurrentPayment) {
                    $allocationAmount = min($payment->amount, $remainingToAllocate);
                    $payment->update(['status' => 'cleared']);

                    // Record the payment allocation to invoice
                    InvoicePayment::create([
                        'invoice_id' => $invoice_id,
                        'payment_id' => $payment->id,
                        'amount' => $allocationAmount,
                    ]);

                    $remainingToAllocate -= $allocationAmount;
                }

                // Allocate remaining amount from other pending payments
                $pendingPayments->each(function ($p) use ($invoice_id, $customerId, $payment, $productId, &$remainingToAllocate) {
                    if ($remainingToAllocate <= 0) {
                        return; // Stop if we've allocated the full invoice amount
                    }

                    if (!$payment || $p->id !== $payment->id) {
                        $p->update(['status' => 'cleared']);

                        $allocationAmount = min($p->amount, $remainingToAllocate);

                        // Record the payment allocation to invoice
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $p->id,
                            'amount' => $allocationAmount,
                        ]);

                        $remainingToAllocate -= $allocationAmount;
                        if ($remainingToAllocate <= 0) {
                            // Calculate and record excess amount
                            $excessAmount = $p->amount - $allocationAmount;

                            AdvancePayment::create([
                                'payment_id' => $p->id,
                                'customer_id' => $customerId,
                                'product_id' => $productId,
                                'reminder' => $excessAmount,
                                'amount' => $excessAmount,
                            ]);
                        }
                    }
                });

                // Calculate and record excess amount
                if ($hasCurrentPayment && $remainingToAllocate <= 0) {
                    $excessAmount = $totalPayments - $balance;

                    AdvancePayment::create([
                        'payment_id' => $payment->id,
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'reminder' => $excessAmount,
                        'amount' => $excessAmount,
                    ]);
                }
                return true;
            } else {
                // Allocate advance payments to invoice with remaining amount as reminder
                $advancePayments->each(function ($ap) use ($invoice_id) {

                    $allocationAmount = $ap->reminder;

                    // Record advance payment allocation to invoice
                    if ($ap->payment_id) {
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $ap->payment_id,
                            'amount' => $allocationAmount,
                        ]);
                    }

                    // Update advance payment with remaining reminder
                    $ap->update(['reminder' => 0]);
                });
                $pendingPayments->each(function ($p) use ($invoice_id) {


                    $p->update(['status' => 'cleared']);

                    $allocationAmount = $p->amount;

                    // Record the payment allocation to invoice
                    InvoicePayment::create([
                        'invoice_id' => $invoice_id,
                        'payment_id' => $p->id,
                        'amount' => $allocationAmount,
                    ]);
                });
                Log::info('Subscription not cleared - insufficient payments', [
                    'invoice_id' => $invoice_id,
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'total_payments' => $totalPayments,
                    'invoiced_amount' => $balance,
                ]);
            }
            return true;
        });
    }

    /**
     * Find unpaid invoices and process on-time product payments
     * Determines invoice_id from payment_reference and processes pending invoices
     *
     * @param string $payment_reference
     * @param int $customerId
     * @param float $invoicedAmount
     * @param Payment|null $payment
     * @return bool
     * @throws \Exception
     */
    public function getOneTimePendingInvoice(int $productId, int $customerId, $payment = null): bool
    {
        return DB::transaction(function () use ($productId, $customerId, $payment) {

            // Step 2: Get price_plan_id from price_plans where product_id matches
            $pricePlans = PricePlan::where('product_id', $productId)->get();

            if ($pricePlans->isEmpty()) {
                throw new \Exception('No price plans found for product_id: ' . $productId);
            }

            // Step 3: Find all invoice_items where price_plan_id matches
            $priceplanIds = $pricePlans->pluck('id')->toArray();
            $invoiceItems = InvoiceItem::whereIn('price_plan_id', $priceplanIds)->get();

            if ($invoiceItems->isEmpty()) {
                throw new \Exception('No invoice items found for price plans');
            }

            // Step 4: Loop through invoice_items and process
            foreach ($invoiceItems as $invoiceItem) {
                // Get the total amount from invoice_item
                $invoiceItemTotal = $invoiceItem->total;

                // Get sum of paid amounts from invoice_payments for this product
                $paidAmount = InvoicePayment::where('invoice_id', $invoiceItem->invoice_id)
                    ->whereHas('payment.controlNumber', function ($query) use ($productId) {
                        $query->where('product_id', $productId);
                    })
                    ->sum('amount');

                // Compare and decide
                if ($paidAmount == $invoiceItemTotal) {
                    // Amount paid equals invoice item total - skip
                    continue;
                } elseif ($paidAmount < $invoiceItemTotal || $paidAmount == 0) {
                    // Less or zero amount paid - process this invoice
                    $invoiceId = $invoiceItem->invoice_id;
                    $invoicedAmount = ($invoiceItem->total - $paidAmount);

                    // Call clearOnTimeProductPayment with all required parameters
                    $this->clearOnTimeProductPayment($invoiceId, $customerId, $invoicedAmount, $payment);
                }
            }

            return true;
        });
    }

    /**
     * Clear on-time product payment by validating total payments against invoiced amount
     * No subscription logic involved - purely handles payment allocation
     * Processes all one-time products in the invoice
     *
     * @param int $invoice_id
     * @param int $customerId
     * @param float $invoicedAmount
     * @param Payment|null $payment
     * @return bool
     * @throws \Exception
     */
    public function clearOnTimeProductPayment(int $invoice_id, int $customerId, float $invoicedAmount): bool
    {
        return DB::transaction(function () use ($invoice_id, $customerId, $invoicedAmount) {
            // Get invoice with relationships to find one-time products
            $invoice = Invoice::with(['invoiceItems.pricePlan.product'])->findOrFail($invoice_id);

            // Get all unique one-time products (product_type_id = 1) from invoice items
            $oneTimeProducts = $invoice->invoiceItems
                ->map(function ($item) {
                    return $item->pricePlan->product;
                })
                ->filter(function ($product) {
                    return $product->product_type_id == 1;
                })
                ->unique('id')
                ->values();
            if ($oneTimeProducts->isEmpty()) {
                throw new \Exception('No one-time products found in invoice items');
            }

            // Loop through each one-time product and clear payments
            foreach ($oneTimeProducts as $product) {

                $productId = $product->id;

                // Step 1: Find all pending payments for this customer
                $pendingPayments = Payment::where('customer_id', $customerId)
                    ->where('status', 'pending')
                    ->whereIn('payment_reference', ControlNumber::where('customer_id', $customerId)->where('product_id', $productId)->get()->pluck('reference'))
                    ->get();

                // Step 2: Find advance payments with reminder > 0
                $advancePayments = AdvancePayment::where('customer_id', $customerId)
                    ->where('product_id', $productId)
                    ->where('reminder', '>', 0)
                    ->get();

                // Step 4: Calculate sum of all payments
                $pendingPaymentsSum = $pendingPayments->sum('amount');
                $advancePaymentsSum = $advancePayments->sum('reminder');
                $totalPayments = $pendingPaymentsSum + $advancePaymentsSum;

                // Step 5: Compare and execute logic
                if ($totalPayments == $invoicedAmount) {

                    // Record all other pending payments as allocated to invoice
                    $pendingPayments->each(function ($p) use ($invoice_id) {
                        $p->update(['status' => 'cleared']);
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $p->id,
                            'amount' => $p->amount,
                        ]);
                    });

                    // Record advance payments used to clear the invoice
                    $advancePayments->each(function ($ap) use ($invoice_id) {
                        if ($ap->reminder > 0) {
                            // Record advance payment allocation to invoice
                            if ($ap->payment_id) {
                                InvoicePayment::create([
                                    'invoice_id' => $invoice_id,
                                    'payment_id' => $ap->payment_id,
                                    'amount' => $ap->reminder,
                                ]);
                            }
                            // Clear the advance payment after using it
                            $ap->update(['reminder' => 0]);
                        }
                    });

                    Log::info('On-time product payment cleared - payments equal invoiced amount', [
                        'invoice_id' => $invoice_id,
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'total_payments' => $totalPayments,
                        'invoiced_amount' => $invoicedAmount,
                    ]);
                } elseif ($totalPayments > $invoicedAmount) {
                    // Greater: Handle excess
                    // Allocate amount to invoice and track remaining amount for each payment
                    $remainingToAllocate = $invoicedAmount;

                    // Allocate advance payments to invoice with remaining amount as reminder
                    $advancePayments->each(function ($ap) use ($invoice_id, &$remainingToAllocate) {
                        if ($remainingToAllocate <= 0 || $ap->reminder <= 0) {
                            return; // Stop if we've allocated the full invoice amount or advance payment is empty
                        }

                        $allocationAmount = min($ap->reminder, $remainingToAllocate);
                        $newReminder = $ap->reminder - $allocationAmount;

                        // Record advance payment allocation to invoice
                        if ($ap->payment_id) {
                            InvoicePayment::create([
                                'invoice_id' => $invoice_id,
                                'payment_id' => $ap->payment_id,
                                'amount' => $allocationAmount,
                            ]);
                        }

                        // Update advance payment with remaining reminder
                        $ap->update(['reminder' => $newReminder]);

                        $remainingToAllocate -= $allocationAmount;
                    });

                    // Allocate remaining amount from other pending payments
                    $pendingPayments->each(function ($p) use ($invoice_id, &$remainingToAllocate, $customerId, $productId) {
                        if ($remainingToAllocate <= 0) {
                            return; // Stop if we've allocated the full invoice amount
                        }


                        $p->update(['status' => 'cleared']);

                        $allocationAmount = min($p->amount, $remainingToAllocate);

                        // Record the payment allocation to invoice
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $p->id,
                            'amount' => $allocationAmount,
                        ]);

                        $remainingToAllocate -= $allocationAmount;
                        $excessAmount = $p->amount -  $allocationAmount;
                        if ($remainingToAllocate <= 0  &&  $excessAmount > 0) {

                            AdvancePayment::create([
                                'payment_id' => $p->id,
                                'customer_id' => $customerId,
                                'product_id' => $productId,
                                'reminder' =>  $excessAmount,
                                'amount' =>  $excessAmount,
                            ]);
                            $p->update(['status' => 'cleared']);
                        }
                    });
                } else {
                    // Allocate advance payments to invoice with remaining amount as reminder
                    $advancePayments->each(function ($ap) use ($invoice_id) {

                        $allocationAmount = $ap->reminder;

                        // Record advance payment allocation to invoice
                        if ($ap->payment_id) {
                            InvoicePayment::create([
                                'invoice_id' => $invoice_id,
                                'payment_id' => $ap->payment_id,
                                'amount' => $allocationAmount,
                            ]);
                        }

                        // Update advance payment with remaining reminder
                        $ap->update(['reminder' => 0]);
                    });
                    $pendingPayments->each(function ($p) use ($invoice_id) {


                        $p->update(['status' => 'cleared']);

                        $allocationAmount = $p->amount;

                        // Record the payment allocation to invoice
                        InvoicePayment::create([
                            'invoice_id' => $invoice_id,
                            'payment_id' => $p->id,
                            'amount' => $allocationAmount,
                        ]);
                    });
                    Log::info('On-time product payment not cleared - insufficient payments', [
                        'invoice_id' => $invoice_id,
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'total_payments' => $totalPayments,
                        'invoiced_amount' => $invoicedAmount,
                    ]);
                }
            }

            return true;
        });
    }
    /**
     * Create product purchase record and handle excess payments for usage-based products
     * This method processes product usage/purchases similar to enableSubscription but creates
     * product_purchase records instead of just activating subscriptions
     *
     * @param int $productId
     * @param int $customerId
     * @param float $invoicedAmount
     * @param Payment|null $payment
     * @return bool
     * @throws \Exception
     */
    public function createProductPurchase(int $productId, int $customerId, $payment = null): bool
    {
        return DB::transaction(function () use ($productId, $customerId, $payment) {
            // Step 1: Find pending subscription for this customer and product
            $subscription = Subscription::where('customer_id', $customerId)
                ->whereHas('pricePlan.product', function ($query) use ($productId) {
                    $query->where('id', $productId);
                })
                ->whereIn('status', ['pending', 'partial'])
                ->lockForUpdate()
                ->first();

            if (!$subscription && $payment) {
                $this->createAutoSubscription($customerId, $productId, $payment->amount, $payment);
                return true;
            }
            $invoiceItem = InvoiceItem::where('subscription_id', $subscription->id)->first();
            if (!empty($invoiceItem)) {
                $invoicedAmount = $invoiceItem->total;

                // Get price plan details to determine rate
                $pricePlan = $subscription->pricePlan;
                $rate = $pricePlan->rate ?? 1;

                // Step 2: Find all pending payments for this customer except the current payment if not null
                $pendingPayments = Payment::where('customer_id', $customerId)
                    ->where('status', 'pending')
                    ->whereIn('payment_reference', ControlNumber::where('customer_id', $customerId)->where('product_id', $productId)->get()->pluck('reference'));
                if ($payment) {
                    $pendingPayments->where('id', '!=', $payment->id);
                }
                $pendingPayments = $pendingPayments->get();

                // Step 3: Verify current payment is not null
                $hasCurrentPayment = $payment !== null;
                if (!$hasCurrentPayment) {
                    $payment = $pendingPayments->sortByDesc('created_at')->first();
                    $hasCurrentPayment = $payment !== null;
                }

                // Step 4: Calculate sum of pending payments only
                $pendingPaymentsSum = $pendingPayments->sum('amount');
                $currentPayment = $payment ? $payment->amount : 0;
                $totalPayments = $pendingPaymentsSum + $currentPayment;

                // Step 5: Calculate quantity = paid amount / rate
                $quantity = $invoicedAmount / $rate;
                if ($quantity >= 1) {
                    // Step 6: Determine subscription status based on payment vs invoiced amount
                    $subscriptionStatus = ($totalPayments == $invoicedAmount) ? 'active' : 'partial';

                    // Step 7: Update subscription status and dates
                    $startDate = Carbon::now();
                    $endDate = $this->calculateEndDate($startDate, $pricePlan);

                    $subscription->update([
                        'status' => $subscriptionStatus,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);

                    // Step 8: Create product purchase record
                    ProductPurchase::create([
                        'product_id' => $productId,
                        'customer_id' => $customerId,
                        'quantity' => $quantity,
                    ]);

                    // // Step 9: Handle payment status - clear payment only if quantity >= 1
                    // if ($quantity >= 1 && $hasCurrentPayment) {
                    //     $payment->update(['status' => 'cleared']);
                    // }


                    $invoiceId = $invoiceItem->invoice_id;
                    // Allocate current payment
                    if ($payment) {
                        InvoicePayment::create([
                            'invoice_id' => $invoiceId,
                            'payment_id' => $payment->id,
                            'amount' => $payment->amount,
                        ]);
                        $payment->update(['status' => 'cleared']);
                    }

                    // Allocate other pending payments
                    $pendingPayments->each(function ($p) use ($invoiceId) {
                        InvoicePayment::create([
                            'invoice_id' => $invoiceId,
                            'payment_id' => $p->id,
                            'amount' => $p->amount,
                        ]);
                        $p->update(['status' => 'cleared']);
                    });
                }
                // Step 11: Handle excess payment case
                if ($totalPayments > $invoicedAmount && $quantity >= 1) {
                    $excessAmount = $totalPayments - $invoicedAmount;
                    $this->createAutoSubscription($customerId, $productId, $excessAmount, $payment);
                } else {
                    // Equal or less payments - standard product purchase
                    Log::info('Product purchase created successfully', [
                        'product_id' => $productId,
                        'customer_id' => $customerId,
                        'subscription_id' => $subscription->id,
                        'quantity' => $quantity,
                        'status' => $subscriptionStatus,
                    ]);
                }
            }
            return true;
        });
    }
    public function createAutoSubscription($customerId, $productId, $amount, $payment)
    {

        // Find the last subscription for this product
        $lastSubscription = Subscription::where('customer_id', $customerId)
            ->whereHas('pricePlan.product', function ($query) use ($productId) {
                $query->where('id', $productId);
            })
            ->where('status', '=', 'active')
            ->latest('created_at')
            ->first();
        $rate = $lastSubscription->PricePlan->rate;
        // Create product purchase for excess amount
        $quantity = $amount / $rate;
        if ($quantity >= 1) {

            // Create new subscription
            $newSubscription = Subscription::create([
                'customer_id' => $customerId,
                'price_plan_id' => $lastSubscription->PricePlan->id,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
                'next_billing_date' => null,
            ]);

            // Create new invoice with excess amount
            $newInvoice = Invoice::create([
                'customer_id' => $customerId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'status' => 'issued',
                'description' => 'Excess payment invoice for product usage',
                'subtotal' => $amount,
                'tax_total' => 0,
                'total' => $amount,
                'due_date' => Carbon::now()->addDays(30)->toDateString(),
                'issued_at' => Carbon::now(),
            ]);
            // Create invoice item for new subscription and price plan
            InvoiceItem::create([
                'invoice_id' => $newInvoice->id,
                'subscription_id' => $newSubscription->id,
                'price_plan_id' => $lastSubscription->PricePlan->id,
                'quantity' => 1,
                'unit_price' => $amount,
                'total' => $amount,
            ]);

            ProductPurchase::create([
                'product_id' => $productId,
                'customer_id' => $customerId,
                'quantity' => $quantity,
            ]);
            $payment->update(['status' => 'cleared']);

            if ($payment) {
                InvoicePayment::create([
                    'invoice_id' => $newInvoice->id,
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                ]);
            }
        }
    }

    /**
     * Send webhook notification when subscription is activated
     * Sends subscription details to external API for further processing
     *
     * @param Subscription $subscription
     * @return void
     */
    private function sendSubscriptionWebhook(Subscription $subscription): void
    {
        try {
            // Load subscription with relationships
            $subscription->load(['customer', 'pricePlan.product']);

            // Get customer to check organization_id
            $customer = $subscription->customer;

            // Only send webhook if organization_id is 1
            if ($customer->organization_id !== 1) {
                return;
            }
            $base = config('app.webhook_base_url');

            $url = $base ? rtrim($base, '/') : "https://{$customer->username}.shulesoft.africa/api";

            $webhookUrl = $url . '/subscriptionWebhook';


            // Prepare subscription details payload
            $payload = [
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'price_plan_id' => $subscription->price_plan_id,
                'price_plan_name' => $subscription->pricePlan->name,
                'product_id' => $subscription->pricePlan->product_id,
                'product_name' => $subscription->pricePlan->product->name,
                'status' => $subscription->status,
                'start_date' => $subscription->start_date,
                'end_date' => $subscription->end_date,
                'created_at' => $subscription->created_at,
                'organization_id' => $customer->organization_id,
            ];

            // Send HTTP POST request to webhook endpoint
            $response = Http::post($webhookUrl, $payload);

            Log::info('Subscription webhook sent successfully', [
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'webhook_url' => $webhookUrl,
                'response_status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send subscription webhook', [
                'subscription_id' => $subscription->id,
                'customer_id' => $subscription->customer_id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception - webhook failure shouldn't break subscription activation
        }
    }
}
