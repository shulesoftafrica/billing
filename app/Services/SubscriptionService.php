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
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function enableSubscription($invoce_id, $subscription, $invoicedAmount, $payment = null): bool
    {
        return DB::transaction(function () use ($invoce_id, $subscription, $invoicedAmount, $payment) {
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

            // Step 5: Compare and execute logic
            if ($totalPayments == $invoicedAmount) {
                // Equal: Activate subscription and clear all payments
                $startDate = Carbon::now();
                $endDate = $this->calculateEndDate($startDate, $subscription->pricePlan);

                $subscription->update([
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                if ($hasCurrentPayment) {
                    $payment->update(['status' => 'cleared']);
                    // Record the payment allocation to invoice
                    InvoicePayment::create([
                        'invoice_id' => $invoce_id,
                        'payment_id' => $payment->id,
                        'amount' => $payment->amount,
                    ]);
                }

                // Record all other pending payments as allocated to invoice
                $pendingPayments->each(function ($p) use ($invoce_id, $payment) {
                    if (!$payment || $p->id !== $payment->id) {
                        $p->update(['status' => 'cleared']);
                        InvoicePayment::create([
                            'invoice_id' => $invoce_id,
                            'payment_id' => $p->id,
                            'amount' => $p->amount,
                        ]);
                    }
                });

                // Record advance payments used to clear the invoice
                $advancePayments->each(function ($ap) use ($invoce_id) {
                    if ($ap->reminder > 0) {
                        // Record advance payment allocation to invoice
                        if ($ap->payment_id) {
                            InvoicePayment::create([
                                'invoice_id' => $invoce_id,
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
            } elseif ($totalPayments > $invoicedAmount) {
                // Greater: Activate subscription and handle excess
                $startDate = Carbon::now();
                $endDate = $this->calculateEndDate($startDate, $subscription->pricePlan);

                $subscription->update([
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                // Allocate amount to invoice and track remaining amount for each payment
                $remainingToAllocate = $invoicedAmount;

                // Allocate advance payments to invoice with remaining amount as reminder
                $advancePayments->each(function ($ap) use ($invoce_id, &$remainingToAllocate) {
                    if ($remainingToAllocate <= 0 || $ap->reminder <= 0) {
                        return; // Stop if we've allocated the full invoice amount or advance payment is empty
                    }

                    $allocationAmount = min($ap->reminder, $remainingToAllocate);
                    $newReminder = $ap->reminder - $allocationAmount;

                    // Record advance payment allocation to invoice
                    if ($ap->payment_id) {
                        InvoicePayment::create([
                            'invoice_id' => $invoce_id,
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
                        'invoice_id' => $invoce_id,
                        'payment_id' => $payment->id,
                        'amount' => $allocationAmount,
                    ]);

                    $remainingToAllocate -= $allocationAmount;
                }

                // Allocate remaining amount from other pending payments
                $pendingPayments->each(function ($p) use ($invoce_id, $payment, &$remainingToAllocate) {
                    if ($remainingToAllocate <= 0) {
                        return; // Stop if we've allocated the full invoice amount
                    }

                    if (!$payment || $p->id !== $payment->id) {
                        $p->update(['status' => 'cleared']);

                        $allocationAmount = min($p->amount, $remainingToAllocate);

                        // Record the payment allocation to invoice
                        InvoicePayment::create([
                            'invoice_id' => $invoce_id,
                            'payment_id' => $p->id,
                            'amount' => $allocationAmount,
                        ]);

                        $remainingToAllocate -= $allocationAmount;
                    }
                });

                // Calculate and record excess amount
                $excessAmount = $totalPayments - $invoicedAmount;

                AdvancePayment::create([
                    'payment_id' => $payment?->id,
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'reminder' => $excessAmount,
                    'amount' => $excessAmount,
                ]);

                Log::info('Subscription enabled - excess payment recorded', [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $customerId,
                    'total_payments' => $totalPayments,
                    'invoiced_amount' => $invoicedAmount,
                    'excess_amount' => $excessAmount,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                return true;
            } else {
                // Less: Do nothing
                Log::info('Subscription not enabled - insufficient payments', [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $customerId,
                    'total_payments' => $totalPayments,
                    'invoiced_amount' => $invoicedAmount,
                ]);

                return true;
            }
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
}
