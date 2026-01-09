<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

        switch ($plan->billing_interval) {
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
                $endDate->addMonth(); // Default to monthly
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
}