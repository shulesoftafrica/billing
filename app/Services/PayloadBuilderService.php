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
        $invoice = $payment->invoice()->with(['customer.product.organization', 'invoiceItems.pricePlan', 'controlNumbers'])->first();
        $customer = $invoice->customer;
        $product = $customer->product;
        $organization = $product->organization;
        $subscription = $payment->subscription ?? $customer->subscriptions()->latest()->first();

        return [
            'event' => 'payment.success',
            'event_id' => 'evt_' . uniqid(),
            'timestamp' => now()->toIso8601String(),
            'api_version' => '2026-03-24',
            
            'product' => $this->buildProductData($product),
            'organization' => $this->buildOrganizationData($organization),
            'payment' => $this->buildPaymentData($payment),
            'invoice' => $this->buildInvoiceData($invoice),
            'customer' => $this->buildCustomerData($customer),
            'subscription' => $subscription ? $this->buildSubscriptionData($subscription) : null,
            'gateway_details' => $this->buildGatewayDetails($payment),
            'metadata' => $this->buildMetadata(),
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
        $invoice->load(['customer.product.organization', 'invoiceItems.pricePlan', 'controlNumbers']);
        $customer = $invoice->customer;
        $product = $customer->product;
        $organization = $product->organization;

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
        $invoice->load(['customer.product.organization', 'invoiceItems.pricePlan', 'controlNumbers', 'payments']);
        $customer = $invoice->customer;
        $product = $customer->product;
        $organization = $product->organization;

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
        $subscription->load(['customer.product.organization', 'pricePlan']);
        $customer = $subscription->customer;
        $product = $customer->product;
        $organization = $product->organization;

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
        
        return [
            'id' => $payment->id,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency ?? 'TZS',
            'status' => $payment->status,
            'payment_method' => $payment->payment_method,
            'gateway' => $gateway ? $gateway->type : 'unknown',
            'gateway_reference' => $payment->gateway_reference,
            'gateway_fee' => (float) ($payment->gateway_fee ?? 0),
            'net_amount' => (float) ($payment->amount - ($payment->gateway_fee ?? 0)),
            'description' => $payment->description,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'created_at' => $payment->created_at->toIso8601String(),
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
            'id' => $customer->id,
            'product_id' => $customer->product_id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
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
}
