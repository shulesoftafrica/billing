<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\WebhookLog;
use App\Services\Stripe\StripeAmountHelper;
use App\Services\SubscriptionService;
use App\Services\WebhookPaymentProcessingService;
use App\Services\PayloadBuilderService;
use App\Services\WebhookDispatchService;
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
        $startTime = microtime(true);
        $requestId = uniqid('stripe_wh_', true);

        // Create webhook log in database with status 'in_progress'
        $webhookLog = WebhookLog::create([
            'request_id' => $requestId,
            'webhook_type' => 'stripe',
            'status' => 'in_progress',
            'payload' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Log incoming webhook request
        Log::info('🔵 [STRIPE WEBHOOK] Request received', [
            'request_id' => $requestId,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'has_signature' => $request->hasHeader('Stripe-Signature'),
            'content_length' => strlen($request->getContent()),
            'timestamp' => now()->toIso8601String(),
        ]);

        if (app()->environment('local')) {

            // Skip signature verification locally
            $payload = $request->getContent();
            $event = json_decode($payload);
            
            Log::debug('[STRIPE WEBHOOK] Signature verification skipped (local environment)', [
                'request_id' => $requestId,
                'event_type' => $event->type ?? 'unknown',
            ]);
        } else {
            try {
                $event = Webhook::constructEvent(
                    $request->getContent(),
                    (string) $request->header('Stripe-Signature'),
                    (string) config('services.stripe.webhook_secret')
                );
                
                Log::info('[STRIPE WEBHOOK] Signature verified successfully', [
                    'request_id' => $requestId,
                    'event_type' => $event->type,
                    'event_id' => $event->id ?? null,
                ]);
            } catch (UnexpectedValueException | SignatureVerificationException $e) {
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                // Update webhook log to error status
                $webhookLog->markAsError(
                    'Invalid webhook signature: ' . $e->getMessage(),
                    400,
                    $duration
                );
                
                Log::warning('🔴 [STRIPE WEBHOOK] Invalid signature - Request rejected', [
                    'request_id' => $requestId,
                    'error' => $e->getMessage(),
                    'duration_ms' => $duration,
                ]);

                return response()->json([
                    'error' => 'Invalid webhook signature',
                ], 400);
            }
        }
        
        // Update webhook log with event type
        $webhookLog->update(['event_type' => $event->type ?? 'unknown']);
        
        // Log event details before processing
        Log::info('[STRIPE WEBHOOK] Processing event', [
            'request_id' => $requestId,
            'event_type' => $event->type ?? 'unknown',
            'event_id' => $event->id ?? null,
            'livemode' => $event->livemode ?? null,
        ]);
        
        // $this->processEvent($event->type, $event->data->object);
        app()->terminating(function () use ($event, $requestId, $startTime, $webhookLog): void { // process the event after the response is sent to avoid timeouts
            try {
                    $this->processEvent($event->type, $event->data->object, $requestId);
                
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                // Mark webhook as completed
                $webhookLog->markAsCompleted(
                    ['message' => 'Event processed successfully'],
                    200,
                    $duration
                );
                
                Log::info('✅ [STRIPE WEBHOOK] Processing completed', [
                    'request_id' => $requestId,
                    'event_type' => $event->type ?? 'unknown',
                    'duration_ms' => $duration,
                ]);
            } catch (\Exception $e) {
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                // Mark webhook as error
                $webhookLog->markAsError($e->getMessage(), 500, $duration);
                
                Log::error('🔴 [STRIPE WEBHOOK] Processing failed', [
                    'request_id' => $requestId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'duration_ms' => $duration,
                ]);
            }
        });

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info('✅ [STRIPE WEBHOOK] Response sent (background processing scheduled)', [
            'request_id' => $requestId,
            'duration_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
        ], 200);
    }

    private function processEvent(string $eventType, mixed $object, string $requestId = null): void
    {
        if (!$object instanceof PaymentIntent) {
            Log::info('Ignoring Stripe event without PaymentIntent payload', [
                'event' => $eventType,
                'payload' => $object,
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
            throw new \Exception('Stripe gateway not configured');
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
        $originalAmount = $metadata['original_amount'] ?? null;
        $originalCurrency = $metadata['original_currency'] ?? null;

        if ($originalAmount === null || $originalCurrency === null) {
            throw new \Exception('Original amount or currency not found in payment metadata');
        }

        $stripeAmount =  $originalAmount; // StripeAmountHelper::fromStripeAmount((int) $originalAmount, (string) $originalCurrency); // since we used original amount no need to convert it back to cents
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'gateway_reference' => $gatewayReference,
            'gateway_id' => $gateway->id,
            'customer_id' => $customerId,
            'amount' => $stripeAmount,
            'status' => 'pending',
            'payment_method' => $intent->payment_method_types[0] ?? 'card',
            'payment_reference' => $gatewayReference,
            'gateway_response' => $intent,
            'paid_at' => now(),
        ]);
        $webhookerController = app(WebhookPaymentProcessingService::class);
        $webhookerController->processByProductAndCustomer($product, $customer, $payment);
        
        // Dispatch custom webhooks
        try {
            $payloadBuilder = app(PayloadBuilderService::class);
            $webhookDispatcher = app(WebhookDispatchService::class);
            $payload = $payloadBuilder->buildPaymentSuccessPayload($payment);
            $webhookDispatcher->dispatchToProduct($product, 'payment.success', $payload);
        } catch (\Exception $e) {
            Log::warning('Failed to dispatch custom webhooks', ['error' => $e->getMessage()]);
        }
        
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
