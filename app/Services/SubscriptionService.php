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
use App\Services\WebhookDispatchService;
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

            // Check for existing pending subscriptions (idempotent behavior)
            $existingInvoice = $this->getExistingPendingInvoice($customerId, $planIds);
            if ($existingInvoice) {
                Log::info('Returning existing subscription invoice', [
                    'customer_id' => $customerId,
                    'invoice_id' => $existingInvoice->id,
                ]);

                // Load relationships for response
                $existingInvoice->load(['invoiceItems.pricePlan', 'customer']);

                return $existingInvoice;
            }

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
     * Get existing pending invoice for the same customer and price plans
     * Implements idempotent behavior for subscription creation
     *
     * @param int $customerId
     * @param array $planIds
     * @return Invoice|null
     */
    private function getExistingPendingInvoice(int $customerId, array $planIds): ?Invoice
    {
        // Find existing pending subscriptions for this customer and plans
        $existingSubscriptions = Subscription::where('customer_id', $customerId)
            ->whereIn('price_plan_id', $planIds)
            ->whereIn('status', ['pending'])
            ->with('pricePlan')
            ->get();

        if ($existingSubscriptions->isEmpty()) {
            return null;
        }

        // Check if the subscription count matches (all plans have pending subscriptions)
        $existingPlanIds = $existingSubscriptions->pluck('price_plan_id')->unique()->toArray();
        sort($existingPlanIds);
        $requestedPlanIds = array_values($planIds);
        sort($requestedPlanIds);

        // Only return existing invoice if ALL requested plans have pending subscriptions
        if ($existingPlanIds !== $requestedPlanIds) {
            return null;
        }

        // Find the invoice that contains these subscription items
        // Get invoice IDs from invoice items that match the subscription's price plans
        $invoiceIds = InvoiceItem::whereIn('price_plan_id', $planIds)
            ->whereHas('invoice', function ($query) use ($customerId) {
                $query->where('customer_id', $customerId)
                    ->whereIn('status', ['issued', 'pending']);
            })
            ->pluck('invoice_id')
            ->unique();

        // Find the most recent matching invoice
        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::with('invoiceItems.pricePlan')
                ->find($invoiceId);

            if ($invoice) {
                // Verify this invoice has items for ALL requested plans
                $invoicePlanIds = $invoice->invoiceItems->pluck('price_plan_id')->unique()->toArray();
                sort($invoicePlanIds);

                if ($invoicePlanIds === $requestedPlanIds) {
                    return $invoice;
                }
            }
        }

        return null;
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
            'currency' => strtoupper((string) ($pricePlans->first()?->currency ?? 'TZS')),
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

        // Filter by customer email
        if (isset($filters['customer_email'])) {
            $query->whereHas('customer', function ($q) use ($filters) {
                $q->where('email', $filters['customer_email']);
            });
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

            // Update subscription status to cancelled
            $subscription->update(['status' => 'cancelled']);

            $invoiceItem = InvoiceItem::where('subscription_id', $subscriptionId)->first();
            if ($invoiceItem) {
                $invoice = Invoice::lockForUpdate()->find($invoiceItem->invoice_id);
                if ($invoice) {
                    $invoice->update(['status' => 'cancelled']);

                    $invoicePayments = InvoicePayment::where('invoice_id', $invoice->id)->get();
                    $invoicePayments->each(function ($invoicePayment) use ($subscription) {
                        $payment = Payment::find($invoicePayment->payment_id);
                        if ($payment && $payment->amount == $invoicePayment->amount) {
                            $payment->update(['status' => 'pending']);
                        }else{
                            // recognize it as advance payment
                             AdvancePayment::create([
                                'payment_id' => $payment->id,
                                'customer_id' =>  $subscription->customer_id,
                                'product_id' => $subscription->pricePlan->product_id,
                                'reminder' => $invoicePayment->amount,
                                'amount' => $invoicePayment->amount,
                            ]);
                        }

                        $invoicePayment->delete();
                    });
                }
            }

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscriptionId,
                'customer_id' => $subscription->customer_id,
                'price_plan_id' => $subscription->price_plan_id,
            ]);

            $freshSubscription = $subscription->fresh(['customer', 'pricePlan']);

            // Dispatch subscription.cancelled webhook
            try {
                app(WebhookDispatchService::class)->dispatchSubscriptionCancelled($freshSubscription);
            } catch (\Exception $e) {
                Log::warning('[SubscriptionService] Failed to dispatch subscription.cancelled webhook', [
                    'error' => $e->getMessage(),
                ]);
            }

            return $freshSubscription;
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
             Log:info('Calculating total payments for subscription enablement', [
                'customer_id' => $customerId,
                'product_id' => $productId,
                'pending_payments_sum' => $pendingPaymentsSum,
                'advance_payments_sum' => $advancePaymentsSum,
                'total_payments' => $totalPayments,
                'invoiced_amount' => $invoicedAmount,
                'balance'=>$balance,
                'payment'=>$payment
            ]);
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
            $invoiceItems = InvoiceItem::whereIn('price_plan_id', $priceplanIds)
                ->whereNotIn('invoice_id', function ($query) {
                    $query->select('id')
                        ->from('invoices')
                        ->where('status', '=', 'cancelled');
                })
                ->get();

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
     * Clear one-time and wallet product payment by validating total payments against invoiced amount
     * No subscription logic involved - purely handles payment allocation
     * Processes all one-time products (product_type_id = 1) and wallet products (product_type_id = 3) in the invoice
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
            $invoice = Invoice::with(['invoiceItems.pricePlan.product'])->where('status', '!=', 'cancelled')->findOrFail($invoice_id);

            // Get all unique one-time products (product_type_id = 1) and wallet products (product_type_id = 3) from invoice items
            $oneTimeProducts = $invoice->invoiceItems
                ->map(function ($item) {
                    return $item->pricePlan->product;
                })
                ->filter(function ($product) {
                    return $product->product_type_id == 1 || $product->product_type_id == 3;
                })
                ->unique('id')
                ->values();
            if ($oneTimeProducts->isEmpty()) {
                throw new \Exception('No one-time products or wallet products found in invoice items');
            }

            // Loop through each one-time/wallet product and clear payments
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

                    // Dispatch credits.purchased webhook for wallet products
                    if ($product->product_type_id == 3) {
                        $walletPlan = $invoice->invoiceItems
                            ->first(fn ($i) => $i->pricePlan?->product_id == $productId)
                            ?->pricePlan;
                        if ($walletPlan) {
                            app(WebhookDispatchService::class)->dispatchCreditsPurchased(
                                $invoice, $walletPlan, $pendingPayments->first()
                            );
                        }
                    }
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

                    // Dispatch credits.purchased webhook for wallet products (overpaid → fully cleared)
                    if ($product->product_type_id == 3) {
                        $walletPlan = $invoice->invoiceItems
                            ->first(fn ($i) => $i->pricePlan?->product_id == $productId)
                            ?->pricePlan;
                        if ($walletPlan) {
                            app(WebhookDispatchService::class)->dispatchCreditsPurchased(
                                $invoice, $walletPlan, $pendingPayments->first()
                            );
                        }
                    }
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
            $invoiceItem = InvoiceItem::where('subscription_id', $subscription->id)
                ->whereNotIn('invoice_id', function ($query) {
                    $query->select('id')
                        ->from('invoices')
                        ->where('status', '=', 'cancelled');
                })
                ->first();

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
        $lastSubscription = Subscription::with('pricePlan')
            ->where('customer_id', $customerId)
            ->whereHas('pricePlan.product', function ($query) use ($productId) {
                $query->where('id', $productId);
            })
            ->where('status', '=', 'active')
            ->latest('created_at')
            ->first();

        if (!$lastSubscription) {
            Log::warning('[SubscriptionService] createAutoSubscription: No active subscription found', [
                'customer_id' => $customerId,
                'product_id'  => $productId,
                'amount'      => $amount,
            ]);
            return;
        }

        $pricePlan = $lastSubscription->pricePlan;

        if (!$pricePlan) {
            Log::warning('[SubscriptionService] createAutoSubscription: PricePlan not found for subscription', [
                'subscription_id' => $lastSubscription->id,
                'customer_id'     => $customerId,
                'product_id'      => $productId,
            ]);
            return;
        }

        $rate = $pricePlan->rate;
        // Create product purchase for excess amount
        $quantity = $amount / $rate;
        if ($quantity >= 1) {

            // Create new subscription
            $newSubscription = Subscription::create([
                'customer_id' => $customerId,
                'price_plan_id' => $pricePlan->id,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
                'next_billing_date' => null,
            ]);

            // Create new invoice with excess amount
            $newInvoice = Invoice::create([
                'customer_id' => $customerId,
                'invoice_number' => $this->generateInvoiceNumber(),
                'currency' => strtoupper((string) ($pricePlan->currency ?? 'TZS')),
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
                'price_plan_id' => $pricePlan->id,
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

            // Dispatch subscription.created webhook
            try {
                app(WebhookDispatchService::class)->dispatchSubscriptionCreated(
                    $newSubscription->load(['customer', 'pricePlan.product'])
                );
            } catch (\Exception $e) {
                Log::warning('[SubscriptionService] Failed to dispatch subscription.created webhook', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Calculate prorated amount for subscription upgrade
     * Uses actual billing cycle days for accurate calculation
     *
     * @param Subscription $subscription Current active subscription
     * @param PricePlan $newPlan Plan being upgraded to
     * @return array Proration calculation details
     */
    public function calculateUpgradeProration(Subscription $subscription, PricePlan $newPlan): array
    {
        $organizationTz = $subscription->customer->organization->timezone ?? 'UTC';
        
        // Get billing cycle dates
        $billingCycleStart = Carbon::parse($subscription->current_period_start ?? $subscription->start_date, $organizationTz);
        $billingCycleEnd = Carbon::parse($subscription->current_period_end ?? $subscription->next_billing_date, $organizationTz);
        $upgradeDate = Carbon::now($organizationTz);
        
        // Calculate days
        $billingCycleLength = $billingCycleStart->diffInDays($billingCycleEnd);
        $daysUsed = $billingCycleStart->diffInDays($upgradeDate);
        $daysRemaining = $billingCycleLength - $daysUsed;
        
        // Handle edge cases
        if ($daysRemaining <= 0) {
            $daysRemaining = 1; // At least charge for 1 day
        }
        
        if ($billingCycleLength <= 0) {
            throw new \Exception('Invalid billing cycle dates');
        }
        
        // Get current plan
        $oldPlan = $subscription->pricePlan;
        
        // Calculate daily rates
        $oldPlanDailyRate = $oldPlan->amount / $billingCycleLength;
        $newPlanDailyRate = $newPlan->amount / $billingCycleLength;
        
        // Calculate proration
        $unusedCredit = $oldPlanDailyRate * $daysRemaining;
        $newPlanCharge = $newPlanDailyRate * $daysRemaining;
        $amountToCharge = $newPlanCharge - $unusedCredit;
        
        // Ensure non-negative amount (shouldn't happen in upgrades, but safety check)
        $amountToCharge = max(0, $amountToCharge);
        
        return [
            'amount_to_charge' => round($amountToCharge, 2),
            'old_plan_daily_rate' => round($oldPlanDailyRate, 2),
            'new_plan_daily_rate' => round($newPlanDailyRate, 2),
            'unused_credit' => round($unusedCredit, 2),
            'new_plan_charge' => round($newPlanCharge, 2),
            'days_remaining' => $daysRemaining,
            'days_used' => $daysUsed,
            'billing_cycle_length' => $billingCycleLength,
            'calculation_details' => [
                'billing_cycle_start' => $billingCycleStart->toDateString(),
                'billing_cycle_end' => $billingCycleEnd->toDateString(),
                'upgrade_date' => $upgradeDate->toDateString(),
                'old_plan_name' => $oldPlan->name,
                'old_plan_amount' => $oldPlan->amount,
                'new_plan_name' => $newPlan->name,
                'new_plan_amount' => $newPlan->amount,
            ]
        ];
    }

    /**
     * Calculate credit for subscription downgrade
     *
     * @param Subscription $subscription Current subscription
     * @param PricePlan $newPlan Lower-tier plan
     * @return array Credit calculation details
     */
    public function calculateDowngradeCredit(Subscription $subscription, PricePlan $newPlan): array
    {
        $organizationTz = $subscription->customer->organization->timezone ?? 'UTC';
        
        // Get billing cycle dates
        $billingCycleStart = Carbon::parse($subscription->current_period_start ?? $subscription->start_date, $organizationTz);
        $billingCycleEnd = Carbon::parse($subscription->current_period_end ?? $subscription->next_billing_date, $organizationTz);
        $downgradeDate = Carbon::now($organizationTz);
        
        // Calculate days
        $billingCycleLength = $billingCycleStart->diffInDays($billingCycleEnd);
        $daysUsed = $billingCycleStart->diffInDays($downgradeDate);
        $daysRemaining = $billingCycleLength - $daysUsed;
        
        if ($daysRemaining <= 0) {
            return [
                'credit_amount' => 0,
                'message' => 'No credit available - billing cycle ending',
            ];
        }
        
        // Get current plan
        $oldPlan = $subscription->pricePlan;
        
        // Calculate daily rates
        $oldPlanDailyRate = $oldPlan->amount / $billingCycleLength;
        $newPlanDailyRate = $newPlan->amount / $billingCycleLength;
        
        // Calculate credit (unused value from higher plan minus lower plan cost)
        $unusedValue = $oldPlanDailyRate * $daysRemaining;
        $newPlanCost = $newPlanDailyRate * $daysRemaining;
        $creditAmount = $unusedValue - $newPlanCost;
        
        // Ensure non-negative credit
        $creditAmount = max(0, $creditAmount);
        
        return [
            'credit_amount' => round($creditAmount, 2),
            'unused_value' => round($unusedValue, 2),
            'new_plan_cost' => round($newPlanCost, 2),
            'old_plan_daily_rate' => round($oldPlanDailyRate, 2),
            'new_plan_daily_rate' => round($newPlanDailyRate, 2),
            'days_remaining' => $daysRemaining,
            'days_used' => $daysUsed,
            'billing_cycle_length' => $billingCycleLength,
            'calculation_details' => [
                'billing_cycle_start' => $billingCycleStart->toDateString(),
                'billing_cycle_end' => $billingCycleEnd->toDateString(),
                'downgrade_date' => $downgradeDate->toDateString(),
                'old_plan_name' => $oldPlan->name,
                'old_plan_amount' => $oldPlan->amount,
                'new_plan_name' => $newPlan->name,
                'new_plan_amount' => $newPlan->amount,
            ]
        ];
    }

    /**
     * Upgrade subscription to a higher-tier plan
     *
     * @param int $subscriptionId
     * @param int $newPricePlanId
     * @param array $gatewayConfig Payment gateway configuration
     * @return Invoice Upgrade invoice
     * @throws \Exception
     */
    public function upgradeSubscription(int $subscriptionId, int $newPricePlanId, array $gatewayConfig = []): Invoice
    {
        return DB::transaction(function () use ($subscriptionId, $newPricePlanId, $gatewayConfig) {
            // Get subscription with locks
            $subscription = Subscription::with(['customer', 'pricePlan'])
                ->lockForUpdate()
                ->findOrFail($subscriptionId);
            
            // If subscription is not active, create a new subscription instead of upgrading
            if ($subscription->status !== 'active') {
                Log::info('Subscription not active, creating new subscription instead of upgrading', [
                    'subscription_id' => $subscriptionId,
                    'current_status' => $subscription->status,
                    'new_price_plan_id' => $newPricePlanId,
                ]);
                
                // Cancel the old subscription if it's pending or in trial
                if (in_array($subscription->status, ['pending', 'trial', 'trialing'])) {
                    $subscription->update(['status' => 'cancelled']);
                }
                
                // Create new subscription with the desired plan
                $invoice = $this->createSubscriptionsWithInvoice(
                    $subscription->customer_id,
                    [$newPricePlanId]
                );
                
                return $invoice;
            }
            
            // Get new price plan
            $newPlan = PricePlan::where('id', $newPricePlanId)
                ->firstOrFail();
            
            $oldPlan = $subscription->pricePlan;
            
            // Validate it's an upgrade (higher price)
            if ($newPlan->amount <= $oldPlan->amount) {
                throw new \Exception('New plan must have a higher price than current plan. Use downgrade endpoint for lower-tier plans.');
            }
            
            // Validate same product
            if ($newPlan->product_id !== $oldPlan->product_id) {
                throw new \Exception('Cannot upgrade to a plan from a different product');
            }
            
            // Calculate proration
            $prorationDetails = $this->calculateUpgradeProration($subscription, $newPlan);
            
            // Create upgrade invoice
            $invoice = $this->createUpgradeInvoice($subscription, $newPlan, $prorationDetails, $gatewayConfig);
            
            // Update subscription with new plan
            $subscription->update([
                'previous_plan_id' => $oldPlan->id,
                'price_plan_id' => $newPlan->id,
                'last_upgrade_proration' => $prorationDetails['amount_to_charge'],
            ]);
            
            Log::info('Subscription upgraded successfully', [
                'subscription_id' => $subscription->id,
                'old_plan_id' => $oldPlan->id,
                'new_plan_id' => $newPlan->id,
                'proration_amount' => $prorationDetails['amount_to_charge'],
                'invoice_id' => $invoice->id,
            ]);

            // Dispatch subscription.upgraded webhook
            try {
                app(WebhookDispatchService::class)->dispatchSubscriptionUpgraded(
                    $subscription->fresh(['customer', 'pricePlan.product']),
                    $oldPlan,
                    $newPlan
                );
            } catch (\Exception $e) {
                Log::warning('[SubscriptionService] Failed to dispatch subscription.upgraded webhook', [
                    'error' => $e->getMessage(),
                ]);
            }
            
            return $invoice->load(['invoiceItems.pricePlan', 'customer']);
        });
    }

    /**
     * Downgrade subscription to a lower-tier plan
     *
     * @param int $subscriptionId
     * @param int $newPricePlanId
     * @param bool $applyCredit Whether to apply credit to customer wallet
     * @return array Downgrade result with credit details
     * @throws \Exception
     */
    public function downgradeSubscription(int $subscriptionId, int $newPricePlanId, bool $applyCredit = true): array
    {
        return DB::transaction(function () use ($subscriptionId, $newPricePlanId, $applyCredit) {
            // Get subscription with locks
            $subscription = Subscription::with(['customer', 'pricePlan'])
                ->lockForUpdate()
                ->findOrFail($subscriptionId);
            
            // If subscription is not active, create a new subscription instead of downgrading
            if ($subscription->status !== 'active') {
                Log::info('Subscription not active, creating new subscription instead of downgrading', [
                    'subscription_id' => $subscriptionId,
                    'current_status' => $subscription->status,
                    'new_price_plan_id' => $newPricePlanId,
                ]);
                
                // Cancel the old subscription if it's pending or in trial
                if (in_array($subscription->status, ['pending', 'trial', 'trialing'])) {
                    $subscription->update(['status' => 'cancelled']);
                }
                
                // Create new subscription with the desired plan
                $invoice = $this->createSubscriptionsWithInvoice(
                    $subscription->customer_id,
                    [$newPricePlanId]
                );
                
                // Get the newly created subscription
                $newSubscription = Subscription::where('customer_id', $subscription->customer_id)
                    ->where('price_plan_id', $newPricePlanId)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
                
                return [
                    'success' => true,
                    'subscription' => $newSubscription->load(['customer', 'pricePlan']),
                    'invoice' => $invoice,
                    'credit_details' => [
                        'credit_amount' => 0,
                        'unused_days' => 0,
                        'old_plan_daily_rate' => 0,
                        'billing_cycle_days' => 0,
                    ],
                    'credit_applied' => false,
                    'message' => 'New subscription created with selected plan',
                ];
            }
            
            // Get new price plan
            $newPlan = PricePlan::where('id', $newPricePlanId)
                ->firstOrFail();
            
            $oldPlan = $subscription->pricePlan;
            
            // Validate it's a downgrade (lower price)
            if ($newPlan->amount >= $oldPlan->amount) {
                throw new \Exception('New plan must have a lower price than current plan. Use upgrade endpoint for higher-tier plans.');
            }
            
            // Validate same product
            if ($newPlan->product_id !== $oldPlan->product_id) {
                throw new \Exception('Cannot downgrade to a plan from a different product');
            }
            
            // Calculate credit
            $creditDetails = $this->calculateDowngradeCredit($subscription, $newPlan);
            
            // Update subscription with new plan
            $subscription->update([
                'previous_plan_id' => $oldPlan->id,
                'price_plan_id' => $newPlan->id,
            ]);
            
            // Apply credit to customer wallet if requested
            $creditApplied = false;
            if ($applyCredit && $creditDetails['credit_amount'] > 0) {
                // Create credit usage record (this would integrate with your wallet system)
                // For now, we'll log it
                Log::info('Downgrade credit available', [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $subscription->customer_id,
                    'credit_amount' => $creditDetails['credit_amount'],
                ]);
                $creditApplied = true;
                
                // TODO: Integrate with ProductUsageController to credit wallet
                // $this->productUsageService->credit($subscription->customer_id, $creditDetails['credit_amount']);
            }
            
            Log::info('Subscription downgraded successfully', [
                'subscription_id' => $subscription->id,
                'old_plan_id' => $oldPlan->id,
                'new_plan_id' => $newPlan->id,
                'credit_amount' => $creditDetails['credit_amount'],
                'credit_applied' => $creditApplied,
            ]);
            
            return [
                'success' => true,
                'subscription' => $subscription->load(['customer', 'pricePlan']),
                'credit_details' => $creditDetails,
                'credit_applied' => $creditApplied,
                'message' => 'Subscription downgraded successfully',
            ];
        });
    }

    /**
     * Create upgrade invoice with proration
     *
     * @param Subscription $subscription
     * @param PricePlan $newPlan
     * @param array $prorationDetails
     * @param array $gatewayConfig
     * @return Invoice
     */
    private function createUpgradeInvoice(
        Subscription $subscription,
        PricePlan $newPlan,
        array $prorationDetails,
        array $gatewayConfig
    ): Invoice {
        $customer = $subscription->customer;
        
        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
        
        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => $invoiceNumber,
            'invoice_type' => 'plan_upgrade',
            'currency' => strtoupper($newPlan->currency ?? 'TZS'),
            'status' => 'issued',
            'subtotal' => $prorationDetails['amount_to_charge'],
            'tax_total' => 0,
            'total' => $prorationDetails['amount_to_charge'],
            'proration_credit' => $prorationDetails['unused_credit'],
            'due_date' => now()->toDateString(),
            'metadata' => json_encode([
                'upgrade_details' => $prorationDetails['calculation_details'],
                'proration' => [
                    'days_remaining' => $prorationDetails['days_remaining'],
                    'old_plan_daily_rate' => $prorationDetails['old_plan_daily_rate'],
                    'new_plan_daily_rate' => $prorationDetails['new_plan_daily_rate'],
                ]
            ]),
            'issued_at' => now(),
        ]);
        
        // Create invoice item
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'price_plan_id' => $newPlan->id,
            'subscription_id' => $subscription->id,
            'quantity' => 1,
            'unit_price' => $prorationDetails['amount_to_charge'],
            'total' => $prorationDetails['amount_to_charge'],
        ]);
        
        return $invoice;
    }
}
