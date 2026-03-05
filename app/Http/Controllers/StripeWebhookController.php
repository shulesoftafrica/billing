<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\Stripe\StripeAmountHelper;
use App\Services\SubscriptionService;
use App\Services\WebhookPaymentProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (app()->environment('local')) {

            // Skip signature verification locally
            $payload = $request->getContent();
            $event = json_decode($payload);
        } else {
            try {
                $event = Webhook::constructEvent(
                    $request->getContent(),
                    (string) $request->header('Stripe-Signature'),
                    (string) config('services.stripe.webhook_secret')
                );
            } catch (UnexpectedValueException | SignatureVerificationException $e) {
                Log::warning('Invalid Stripe webhook signature', [
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'error' => 'Invalid webhook signature',
                ], 400);
            }
        }
        // $this->processEvent($event->type, $event->data->object);
        app()->terminating(function () use ($event): void { // process the event after the response is sent to avoid timeouts
            $this->processEvent($event->type, $event->data->object);
        });

        return response()->json([
            'success' => true,
        ], 200);
    }

    private function processEvent(string $eventType, mixed $object): void
    {
        if (!$object instanceof PaymentIntent) {
            Log::info('Ignoring Stripe event without PaymentIntent payload', [
                'event' => $eventType,
            ]);
            return;
        }

        match ($eventType) {
            'payment_intent.succeeded' => $this->handleSucceeded($object), // we are interested in this event only
            'payment_intent.payment_failed' => $this->handleFailed($object),
            'payment_intent.canceled' => $this->handleCanceled($object),
            'payment_intent.requires_action' => $this->handleRequiresAction($object),
            'payment_intent.processing' => $this->handleProcessing($object),
            default => Log::info('Unhandled Stripe webhook event', ['event' => $eventType]),
        };
    }

    private function handleSucceeded(PaymentIntent $intent)
    {
        $metadata = $intent->metadata;
        if (method_exists($metadata, 'toArray')) {
            $metadata = $metadata->toArray();
        } else {
            $metadata = (array) $metadata;
        }

        //invoice
        $invoiceId = $metadata['invoice_id'] ?? null;
        if (!$invoiceId) {
            throw new \Exception('Invoice ID not found in charge metadata');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            throw new \Exception('Invoice details not found');
        }

        //product
        $productId = $metadata['product_id'] ?? null;
        if (!$productId) {
            throw new \Exception('product ID not found in charge metadata');
        }

        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception('product details not found');
        }
        // customer
        $customerId = $metadata['user_id'] ?? null;
        if (!$customerId) {
            throw new \Exception('Customer ID not found in charge metadata');
        }
        $customer = Customer::find($customerId);
        if (!$customer) {
            throw new \Exception('Customer not found for this transaction');
        }
        $gateway = PaymentGateway::whereRaw('LOWER(name) = ?', ['stripe'])
            ->where('active', true)
            ->first();

        if (!$gateway) {
            throw new \Exception('Flutterwave gateway not configured');
        }

        $gatewayReference = $intent->id;

        $duplicateTransaction = Payment::where('gateway_reference', $gatewayReference)
            ->exists();

        if ($duplicateTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate transaction',
            ], 409);
        }
        $stripeAmount = StripeAmountHelper::fromStripeAmount($intent->amount_received, $intent->currency);
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'gateway_reference' => $gatewayReference,
            'gateway_id' => $gateway->id,
            'customer_id' => $customerId,
            'amount' => $stripeAmount,
            'status' => 'pending',
            'payment_method' => $charge['payment_type'] ?? 'card',
            'payment_reference' => $gatewayReference,
            'gateway_response' => $intent,
            'paid_at' => now(),
        ]);
        $webhookerController = new WebhookPaymentProcessingService(app(SubscriptionService::class));
        $webhookerController->processByProductAndCustomer($product, $customer, $payment);
        return response()->json(['success' => true], 200);
    }

    private function handleFailed(PaymentIntent $intent): void
    {
        return;
    }

    private function handleCanceled(PaymentIntent $intent): void
    {
        return;
    }

    private function handleRequiresAction(PaymentIntent $intent): void
    {
        return;
    }

    private function handleProcessing($intent): void
    {
        return;
    }

    function generateStripeSignature(string $payload, string $secret, ?int $timestamp = null): string
    {
        // Use current time if timestamp not provided
        $timestamp = $timestamp ?? time();

        // Stripe signed payload format
        $signedPayload = $timestamp . '.' . $payload;

        // Generate HMAC SHA256 signature
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        // Return header value
        return "t={$timestamp},v1={$signature}";
    }
}
