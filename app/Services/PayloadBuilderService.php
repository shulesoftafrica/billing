<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Product;

class PayloadBuilderService
{
    /**
     * Build payment success payload
     */
    public function buildPaymentSuccessPayload(Payment $payment): array
    {
        $invoice = $payment->invoice()
            ->with(['customer.organization', 'invoiceItems.pricePlan.product.organization', 'controlNumbers'])
            ->first();
        $customer     = $invoice->customer;
        $product      = $invoice->invoiceItems->first()?->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;
        $subscription = $payment->subscription ?? $customer->subscriptions()->latest()->first();

        return [
            'event'          => 'payment.success',
            'event_id'       => 'evt_' . uniqid(),
            'timestamp'      => now()->toIso8601String(),
            'api_version'    => '2026-03-24',
            'customer_id'    => $customer->id,

            'product'         => $this->buildProductData($product),
            'organization'    => $this->buildOrganizationData($organization),
            'payment'         => $this->buildPaymentData($payment),
            'invoice'         => $this->buildInvoiceData($invoice),
            'customer'        => $this->buildCustomerData($customer),
            'subscription'    => $subscription ? $this->buildSubscriptionData($subscription) : null,
            'gateway_details' => $this->buildGatewayDetails($payment),
            'metadata'        => $this->buildMetadata(),
        ];
    }

    /**
     * Build payment failed payload
     */
    public function buildPaymentFailedPayload(Payment $payment): array
    {
        $payload = $this->buildPaymentSuccessPayload($payment);
        $payload['event'] = 'payment.failed';
        
        // Add failure details
        $gatewayResponse = is_array($payment->gateway_response) ? $payment->gateway_response : json_decode($payment->gateway_response, true);
        $payload['payment']['error_code'] = $gatewayResponse['error']['code'] ?? null;
        $payload['payment']['error_message'] = $gatewayResponse['error']['message'] ?? null;
        
        return $payload;
    }

    /**
     * Build invoice created payload
     */
    public function buildInvoiceCreatedPayload(Invoice $invoice): array
    {
        $invoice->load(['customer.organization', 'invoiceItems.pricePlan.product.organization', 'controlNumbers']);
        $customer     = $invoice->customer;
        $product      = $invoice->invoiceItems->first()?->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event' => 'invoice.created',
            'event_id' => 'evt_' . uniqid(),
            'timestamp' => now()->toIso8601String(),
            'api_version' => '2026-03-24',
            
            'product' => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'invoice' => $this->buildInvoiceData($invoice),
            'customer' => $this->buildCustomerData($customer),
            'subscription' => null,
            'payment' => null,
            'gateway_details' => ['stripe' => null, 'flutterwave' => null, 'ucn' => null],
            'metadata' => $this->buildMetadata(),
        ];
    }

    /**
     * Build invoice paid payload
     */
    public function buildInvoicePaidPayload(Invoice $invoice, Payment $payment): array
    {
        $invoice->load(['customer.organization', 'invoiceItems.pricePlan.product.organization', 'controlNumbers', 'payments']);
        $customer     = $invoice->customer;
        $product      = $invoice->invoiceItems->first()?->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event' => 'invoice.paid',
            'event_id' => 'evt_' . uniqid(),
            'timestamp' => now()->toIso8601String(),
            'api_version' => '2026-03-24',
            
            'product' => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'invoice' => $this->buildInvoiceData($invoice),
            'customer' => $this->buildCustomerData($customer),
            'payment' => $this->buildPaymentData($payment),
            'subscription' => null,
            'gateway_details' => $this->buildGatewayDetails($payment),
            'metadata' => $this->buildMetadata(),
        ];
    }

    /**
     * Build subscription created payload
     */
    public function buildSubscriptionCreatedPayload(Subscription $subscription): array
    {
        $subscription->load(['customer.organization', 'pricePlan.product.organization']);
        $customer     = $subscription->customer;
        $product      = $subscription->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event' => 'subscription.created',
            'event_id' => 'evt_' . uniqid(),
            'timestamp' => now()->toIso8601String(),
            'api_version' => '2026-03-24',
            
            'product' => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'subscription' => $this->buildSubscriptionData($subscription),
            'customer' => $this->buildCustomerData($customer),
            'invoice' => null,
            'payment' => null,
            'gateway_details' => ['stripe' => null, 'flutterwave' => null, 'ucn' => null],
            'metadata' => $this->buildMetadata(),
        ];
    }

    private function buildProductData($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'product_code' => $product->product_code,
            'organization_id' => $product->organization_id,
            'status' => $product->status,
        ];
    }

    private function buildOrganizationData($organization): array
    {
        return [
            'id' => $organization->id,
            'name' => $organization->name,
        ];
    }

    private function buildPaymentData(Payment $payment): array
    {
        $gateway = $payment->gateway;

        // Normalize internal status values to standard external values
        $statusMap = [
            'cleared'   => 'success',
            'completed' => 'success',
            'success'   => 'success',
            'pending'   => 'pending',
            'failed'    => 'failed',
            'cancelled' => 'cancelled',
            'refunded'  => 'refunded',
        ];
        $externalStatus = $statusMap[$payment->status] ?? $payment->status;

        return [
            'id'                => $payment->id,
            'transaction_id'    => $payment->gateway_reference, // required by receivers
            'amount'            => (float) $payment->amount,
            'currency'          => $payment->currency ?? 'TZS',
            'status'            => $externalStatus,
            'payment_method'    => $payment->payment_method,
            'gateway'           => $gateway ? $gateway->type : 'unknown',
            'gateway_reference' => $payment->gateway_reference,
            'gateway_fee'       => (float) ($payment->gateway_fee ?? 0),
            'net_amount'        => (float) ($payment->amount - ($payment->gateway_fee ?? 0)),
            'description'       => $payment->description,
            'paid_at'           => $payment->paid_at?->toIso8601String(),
            'created_at'        => $payment->created_at->toIso8601String(),
        ];
    }

    private function buildInvoiceData(Invoice $invoice): array
    {
        return [
            'id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'subtotal' => (float) $invoice->subtotal,
            'tax_total' => (float) ($invoice->tax_total ?? 0),
            'total' => (float) $invoice->total,
            'amount_paid' => (float) ($invoice->payments->sum('amount') ?? 0),
            'amount_due' => (float) ($invoice->total - ($invoice->payments->sum('amount') ?? 0)),
            'currency' => $invoice->currency ?? 'TZS',
            'status' => $invoice->status,
            'due_date' => $invoice->due_date?->toDateString(),
            'issued_at' => $invoice->issued_at?->toIso8601String(),
            'paid_at' => $invoice->status === 'paid' ? $invoice->updated_at->toIso8601String() : null,
            'items' => $invoice->invoiceItems->map(fn($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total,
                'price_plan_id' => $item->price_plan_id,
                'price_plan_name' => $item->pricePlan?->name,
            ])->toArray(),
            'ucn' => $invoice->controlNumbers->first()?->control_number,
            'control_number' => $invoice->controlNumbers->first()?->control_number,
            'control_numbers' => $invoice->controlNumbers->pluck('control_number')->toArray(),
        ];
    }

    private function buildCustomerData($customer): array
    {
        return [
            'id'     => $customer->id,
            'name'   => $customer->name,
            'email'  => $customer->email,
            'phone'  => $customer->phone,
            'status' => $customer->status,
        ];
    }

    private function buildSubscriptionData($subscription): ?array
    {
        if (!$subscription) {
            return null;
        }

        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
            'price_plan_id' => $subscription->price_plan_id,
            'price_plan_name' => $subscription->pricePlan?->name,
            'billing_interval' => $subscription->pricePlan?->billing_interval,
            'amount' => (float) ($subscription->pricePlan?->amount ?? 0),
            'currency' => $subscription->pricePlan?->currency ?? 'TZS',
            'current_period_start' => $subscription->current_period_start?->toDateString(),
            'current_period_end' => $subscription->current_period_end?->toDateString(),
            'next_billing_date' => $subscription->next_billing_date?->toDateString(),
            'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
            'canceled_at' => $subscription->canceled_at?->toIso8601String(),
        ];
    }

    private function buildGatewayDetails(Payment $payment): array
    {
        $gateway = $payment->gateway;
        $gatewayType = $gateway ? $gateway->type : 'unknown';
        
        return [
            'stripe' => $gatewayType === 'stripe' ? $this->extractStripeDetails($payment) : null,
            'flutterwave' => $gatewayType === 'flutterwave' ? $this->extractFlutterwaveDetails($payment) : null,
            'ucn' => $gatewayType === 'ucn' ? $this->extractUCNDetails($payment) : null,
        ];
    }

    private function extractStripeDetails(Payment $payment): ?array
    {
        $response = is_array($payment->gateway_response) 
            ? $payment->gateway_response 
            : json_decode($payment->gateway_response, true);
        
        if (!$response) {
            return null;
        }

        return [
            'payment_intent_id' => $response['id'] ?? null,
            'charge_id' => $response['charges']['data'][0]['id'] ?? null,
            'payment_method_id' => $response['payment_method'] ?? null,
            'customer_id' => $response['customer'] ?? null,
            'last4' => $response['charges']['data'][0]['payment_method_details']['card']['last4'] ?? null,
            'brand' => $response['charges']['data'][0]['payment_method_details']['card']['brand'] ?? null,
            'country' => $response['charges']['data'][0]['payment_method_details']['card']['country'] ?? null,
            'receipt_url' => $response['charges']['data'][0]['receipt_url'] ?? null,
        ];
    }

    private function extractFlutterwaveDetails(Payment $payment): ?array
    {
        $response = is_array($payment->gateway_response) 
            ? $payment->gateway_response 
            : json_decode($payment->gateway_response, true);
        
        if (!$response) {
            return null;
        }

        return [
            'transaction_id' => $response['id'] ?? null,
            'flw_ref' => $response['flw_ref'] ?? null,
            'tx_ref' => $response['tx_ref'] ?? null,
            'payment_type' => $response['payment_type'] ?? null,
            'card_brand' => $response['card']['type'] ?? null,
            'last4' => $response['card']['last_4digits'] ?? null,
        ];
    }

    private function extractUCNDetails(Payment $payment): ?array
    {
        $response = is_array($payment->gateway_response) 
            ? $payment->gateway_response 
            : json_decode($payment->gateway_response, true);
        
        $invoice = $payment->invoice;
        
        return [
            'control_number' => $invoice?->controlNumbers->first()?->control_number,
            'bill_id' => $response['bill_id'] ?? null,
            'payer_name' => $response['payer_name'] ?? null,
            'payer_phone' => $response['payer_phone'] ?? null,
            'payment_channel' => $response['payment_channel'] ?? 'bank_transfer',
            'sp_code' => $response['sp_code'] ?? null,
        ];
    }

    private function buildMetadata(): array
    {
        return [
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'webhook_triggered_at' => now()->toIso8601String(),
        ];
    }

    // ----------------------------------------------------------------
    // Additional event payload builders
    // ----------------------------------------------------------------

    public function buildSubscriptionRenewedPayload(Subscription $subscription, ?Payment $payment = null): array
    {
        $subscription->loadMissing(['customer.organization', 'pricePlan.product.organization']);
        $customer     = $subscription->customer;
        $product      = $subscription->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event'        => 'subscription.renewed',
            'event_id'     => 'evt_' . uniqid(),
            'timestamp'    => now()->toIso8601String(),
            'api_version'  => '2026-03-24',
            'customer_id'  => $customer->id,
            'product'      => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'subscription' => $this->buildSubscriptionData($subscription),
            'customer'     => $this->buildCustomerData($customer),
            'payment'      => $payment ? $this->buildPaymentData($payment) : null,
            'metadata'     => $this->buildMetadata(),
        ];
    }

    public function buildSubscriptionCancelledPayload(Subscription $subscription, ?string $reason = null): array
    {
        $subscription->loadMissing(['customer.organization', 'pricePlan.product.organization']);
        $customer     = $subscription->customer;
        $product      = $subscription->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event'        => 'subscription.cancelled',
            'event_id'     => 'evt_' . uniqid(),
            'timestamp'    => now()->toIso8601String(),
            'api_version'  => '2026-03-24',
            'customer_id'  => $customer->id,
            'product'      => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'subscription' => $this->buildSubscriptionData($subscription),
            'customer'     => $this->buildCustomerData($customer),
            'cancellation' => [
                'reason'       => $reason ?? 'Not specified',
                'cancelled_at' => now()->toIso8601String(),
            ],
            'metadata'     => $this->buildMetadata(),
        ];
    }

    public function buildSubscriptionExpiredPayload(Subscription $subscription): array
    {
        $subscription->loadMissing(['customer.organization', 'pricePlan.product.organization']);
        $customer     = $subscription->customer;
        $product      = $subscription->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event'        => 'subscription.expired',
            'event_id'     => 'evt_' . uniqid(),
            'timestamp'    => now()->toIso8601String(),
            'api_version'  => '2026-03-24',
            'customer_id'  => $customer->id,
            'product'      => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'subscription' => $this->buildSubscriptionData($subscription),
            'customer'     => $this->buildCustomerData($customer),
            'expired_at'   => $subscription->end_date ?? now()->toIso8601String(),
            'metadata'     => $this->buildMetadata(),
        ];
    }

    public function buildCreditsPurchasedPayload(mixed $creditTransaction, ?Payment $payment = null): array
    {
        // AdvancePayment has its own product_id — the product comes from the
        // transaction itself, not from the customer's (now-removed) product_id.
        $creditTransaction->loadMissing(['customer.organization', 'product.organization']);
        $customer     = $creditTransaction->customer;
        $product      = $creditTransaction->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event'        => 'credits.purchased',
            'event_id'     => 'evt_' . uniqid(),
            'timestamp'    => now()->toIso8601String(),
            'api_version'  => '2026-03-24',
            'customer_id'  => $customer->id,
            'product'      => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'customer'     => $this->buildCustomerData($customer),
            'credits'      => [
                'id'          => $creditTransaction->id,
                'amount'      => $creditTransaction->amount,
                'balance'     => $creditTransaction->balance ?? null,
                'description' => $creditTransaction->description ?? null,
                'purchased_at'=> $creditTransaction->created_at?->toIso8601String(),
            ],
            'payment'      => $payment ? $this->buildPaymentData($payment) : null,
            'metadata'     => $this->buildMetadata(),
        ];
    }

    public function buildSubscriptionUpgradedPayload(
        Subscription $subscription,
        mixed $oldPlan = null,
        mixed $newPlan = null
    ): array {
        $subscription->loadMissing(['customer.organization', 'pricePlan.product.organization']);
        $customer     = $subscription->customer;
        $product      = $subscription->pricePlan?->product;
        $organization = $product?->organization ?? $customer->organization;

        return [
            'event'        => 'subscription.upgraded',
            'event_id'     => 'evt_' . uniqid(),
            'timestamp'    => now()->toIso8601String(),
            'api_version'  => '2026-03-24',
            'customer_id'  => $customer->id,
            'product'      => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'subscription' => $this->buildSubscriptionData($subscription),
            'customer'     => $this->buildCustomerData($customer),
            'upgrade'      => [
                'previous_plan' => $oldPlan ? [
                    'id'       => $oldPlan->id,
                    'name'     => $oldPlan->name,
                    'amount'   => (float) ($oldPlan->amount ?? $oldPlan->rate ?? 0),
                    'interval' => $oldPlan->billing_interval ?? null,
                ] : null,
                'new_plan' => $newPlan ? [
                    'id'       => $newPlan->id,
                    'name'     => $newPlan->name,
                    'amount'   => (float) ($newPlan->amount ?? $newPlan->rate ?? 0),
                    'interval' => $newPlan->billing_interval ?? null,
                ] : null,
                'upgraded_at' => now()->toIso8601String(),
            ],
            'metadata'     => $this->buildMetadata(),
        ];
    }
}
