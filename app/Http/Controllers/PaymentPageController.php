<?php

namespace App\Http\Controllers;

use App\Models\ControlNumber;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentPageController extends Controller
{
    /**
     * Show the payment page for an invoice.
     * GET /billing/pay/{invoice}
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['invoiceItems.pricePlan', 'customer']);
        $customer = $invoice->customer;

        $stripeIntent = $this->resolveStripeIntentFromControlNumbers($invoice);

        return view('billing.payment', [
            'invoice' => $invoice,
            'customer' => $customer,
            'clientSecret' => (string) ($stripeIntent['client_secret'] ?: null),
            'stripePublishableKey' => config('services.stripe.publishable_key'),
        ]);
    }

    /**
     * Handle return after Stripe redirect-based payment (e.g. bank redirect).
     * GET /billing/pay/{invoice}/complete
     */
    public function complete(Request $request, Invoice $invoice): View
    {
        $invoice->load(['invoiceItems.pricePlan', 'customer']);
        $stripeIntent = $this->resolveStripeIntentFromControlNumbers($invoice);
        $clientSecretFromReturn = (string) $request->query('payment_intent_client_secret', '');

        return view('billing.payment', [
            'invoice' => $invoice,
            'customer' => $invoice->customer,
            'clientSecret' => $clientSecretFromReturn !== ''
                ? $clientSecretFromReturn
                : (string) ($stripeIntent['client_secret'] ?: null),
            'stripePublishableKey' => config('services.stripe.publishable_key'),
        ]);
    }

    private function resolveStripeIntentFromControlNumbers(Invoice $invoice): array
    {
        $productIds = $invoice->invoiceItems
            ->map(fn($item) => $item->pricePlan?->product_id)
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return [
                'payment_intent_id' => '',
                'client_secret' => '',
            ];
        }

        $controlNumbers = ControlNumber::with('organizationPaymentGatewayIntegration.paymentGateway')
            ->where('customer_id', $invoice->customer_id)
            ->whereIn('product_id', $productIds)
            ->orderByDesc('id')
            ->get();

        foreach ($controlNumbers as $controlNumber) {
            if (!$this->isStripeControlNumberForInvoice($controlNumber, $invoice->id)) {
                continue;
            }

            $metadata = $this->normalizeControlNumberMetadata($controlNumber->metadata);
            $clientSecret = (string) ($this->extractClientSecretFromControlNumberMetadata($metadata) ?? '');

            if ($clientSecret === '') {
                continue;
            }

            $paymentIntentId = (string) data_get($metadata, 'payment_intent_id', '');

            return [
                'payment_intent_id' => $paymentIntentId,
                'client_secret' => $clientSecret,
            ];
        }

        return [
            'payment_intent_id' => '',
            'client_secret' => '',
        ];
    }

    private function isStripeControlNumberForInvoice(ControlNumber $controlNumber, int $invoiceId): bool
    {
        $integration = $controlNumber->organizationPaymentGatewayIntegration;
        $gatewayName = strtolower(trim((string) ($integration?->paymentGateway?->name ?? '')));

        if ($gatewayName !== 'stripe') {
            return false;
        }

        $metadataInvoiceId = $this->extractInvoiceIdFromControlNumberMetadata($controlNumber->metadata);

        return $metadataInvoiceId !== null && $metadataInvoiceId === $invoiceId;
    }

    private function extractInvoiceIdFromControlNumberMetadata($metadata): ?int
    {
        $metadata = $this->normalizeControlNumberMetadata($metadata);
        $invoiceId = data_get($metadata, 'meta.invoice_id', data_get($metadata, 'invoice_id'));

        return is_numeric($invoiceId) ? (int) $invoiceId : null;
    }

    private function extractClientSecretFromControlNumberMetadata($metadata): ?string
    {
        $metadata = $this->normalizeControlNumberMetadata($metadata);

        $clientSecret = data_get(
            $metadata,
            'client_secret',
            data_get($metadata, 'meta.client_secret', data_get($metadata, 'payment_intent.client_secret'))
        );

        return is_string($clientSecret) && trim($clientSecret) !== '' ? $clientSecret : null;
    }

    private function normalizeControlNumberMetadata($metadata): array
    {
        if (is_string($metadata)) {
            $decoded = json_decode($metadata, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($metadata) ? $metadata : [];
    }
}
