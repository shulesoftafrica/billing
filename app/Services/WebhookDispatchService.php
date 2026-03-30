<?php

namespace App\Services;

use App\Models\CustomWebhook;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\WebhookDelivery;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PayloadBuilderService;

class WebhookDispatchService
{
    public function __construct(private PayloadBuilderService $payloadBuilder) {}

    /**
     * Dispatch webhook to a single endpoint
     *
     * @param CustomWebhook $webhook
     * @param array $payload
     * @param int|null $paymentId  Optional payment ID for tracking / replay deduplication
     */
    public function dispatch(
        CustomWebhook $webhook,
        array $payload,
        ?int $paymentId = null,
        ?int $subscriptionId = null
    ): WebhookDelivery {
        $startTime = microtime(true);

        // Create delivery record
        $delivery = WebhookDelivery::create([
            'custom_webhook_id' => $webhook->id,
            'payment_id'        => $paymentId,
            'subscription_id'   => $subscriptionId,
            'event_type'        => $payload['event'],
            'payload'           => json_encode($payload),
            'status'            => 'pending',
        ]);

        Log::info('📤 [WEBHOOK DISPATCH] Sending webhook', [
            'webhook_id' => $webhook->id,
            'webhook_name' => $webhook->name,
            'delivery_id' => $delivery->id,
            'event_type' => $payload['event'],
            'url' => $webhook->url,
        ]);

        try {
            // Generate signature
            $signature = $webhook->generateSignature($payload);

            // Prepare headers
            $defaultHeaders = [
                'X-Webhook-Signature' => $signature,
                'X-Event-Type' => $payload['event'],
                'X-Webhook-ID' => (string) $webhook->id,
                'X-Delivery-ID' => (string) $delivery->id,
                'User-Agent' => 'BillingPlatform-Webhook/1.0',
                'Content-Type' => 'application/json',
            ];

            $headers = array_merge($defaultHeaders, $webhook->headers ?? []);

            // Send HTTP request
            $httpClient = Http::timeout($webhook->timeout);
            
            if (!$webhook->verify_ssl) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient
                ->withHeaders($headers)
                ->{strtolower($webhook->http_method)}($webhook->url, $payload);

            $durationMs = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $delivery->markAsSent(
                    $response->status(),
                    $response->body(),
                    $durationMs
                );

                $webhook->update(['last_triggered_at' => now()]);

                Log::info('✅ [WEBHOOK DISPATCH] Webhook delivered', [
                    'webhook_id' => $webhook->id,
                    'delivery_id' => $delivery->id,
                    'status_code' => $response->status(),
                    'duration_ms' => $durationMs,
                ]);
            } else {
                $delivery->markAsFailed(
                    "HTTP {$response->status()}: " . $response->body(),
                    $response->status(),
                    $durationMs
                );

                Log::warning('⚠️ [WEBHOOK DISPATCH] Webhook failed', [
                    'webhook_id' => $webhook->id,
                    'delivery_id' => $delivery->id,
                    'status_code' => $response->status(),
                    'duration_ms' => $durationMs,
                    'response_body' => substr($response->body(), 0, 200),
                ]);
            }
        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000);
            
            $delivery->markAsFailed($e->getMessage(), null, $durationMs);

            Log::error('🔴 [WEBHOOK DISPATCH] Webhook exception', [
                'webhook_id' => $webhook->id,
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);
        }

        return $delivery->fresh();
    }

    /**
     * Dispatch webhook event to all active webhooks for a product
     */
    public function dispatchToProduct(Product $product, string $eventType, array $payload): array
    {
        $webhooks = $product->getActiveWebhooksForEvent($eventType);

        if ($webhooks->isEmpty()) {
            Log::debug('[WEBHOOK DISPATCH] No webhooks configured', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'event_type' => $eventType,
            ]);
            return [
                'dispatched' => 0,
                'successful' => 0,
                'failed' => 0,
            ];
        }

        Log::info('[WEBHOOK DISPATCH] Dispatching to multiple webhooks', [
            'product_id' => $product->id,
            'event_type' => $eventType,
            'webhook_count' => $webhooks->count(),
        ]);

        $successful = 0;
        $failed = 0;

        foreach ($webhooks as $webhook) {
            try {
                $delivery = $this->dispatch($webhook, $payload);
                
                if ($delivery->status === 'sent') {
                    $successful++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('[WEBHOOK DISPATCH] Failed to dispatch webhook', [
                    'webhook_id' => $webhook->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other webhooks even if one fails
            }
        }

        return [
            'dispatched' => $webhooks->count(),
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    /**
     * Replay payment.success webhooks for a specific webhook endpoint.
     *
     * This is used when a webhook URL is registered after payments have already been
     * processed — it finds all cleared payments for the product that have NOT yet
     * been successfully delivered to this webhook and dispatches them.
     *
     * @param  CustomWebhook  $webhook
     * @param  array{from?: string, to?: string, payment_ids?: int[]}  $filters
     * @return array{replayed: int, skipped: int, failed: int}
     */
    public function replayPaymentsToWebhook(CustomWebhook $webhook, array $filters = []): array
    {
        $product = $webhook->product;

        if (!$product) {
            throw new \Exception('Webhook has no associated product.');
        }

        // ── 1. Collect payment IDs already successfully delivered to this webhook ──
        $alreadySentPaymentIds = WebhookDelivery::where('custom_webhook_id', $webhook->id)
            ->where('status', 'sent')
            ->whereNotNull('payment_id')
            ->pluck('payment_id')
            ->toArray();

        // ── 2. Find all cleared payments for this product ──
        $query = Payment::whereHas('customer', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->where('status', 'cleared')
            ->whereNotIn('id', $alreadySentPaymentIds)
            ->with(['customer']);

        if (!empty($filters['from'])) {
            $query->where('paid_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('paid_at', '<=', $filters['to']);
        }
        if (!empty($filters['payment_ids'])) {
            $query->whereIn('id', $filters['payment_ids']);
        }

        $payments = $query->orderBy('paid_at')->get();

        Log::info('[WEBHOOK REPLAY] Starting replay', [
            'webhook_id'   => $webhook->id,
            'webhook_name' => $webhook->name,
            'product_id'   => $product->id,
            'total_found'  => $payments->count(),
            'filters'      => $filters,
        ]);

        $replayed = 0;
        $skipped  = 0;
        $failed   = 0;

        foreach ($payments as $payment) {
            try {
                $payload = $this->payloadBuilder->buildPaymentSuccessPayload($payment);
                $delivery = $this->dispatch($webhook, $payload, $payment->id);

                if ($delivery->status === 'sent') {
                    $replayed++;
                    Log::info('[WEBHOOK REPLAY] Delivered', [
                        'webhook_id'  => $webhook->id,
                        'payment_id'  => $payment->id,
                        'delivery_id' => $delivery->id,
                    ]);
                } else {
                    $failed++;
                    Log::warning('[WEBHOOK REPLAY] Delivery failed', [
                        'webhook_id'  => $webhook->id,
                        'payment_id'  => $payment->id,
                        'delivery_id' => $delivery->id,
                        'error'       => $delivery->error_message,
                    ]);
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('[WEBHOOK REPLAY] Exception for payment', [
                    'webhook_id' => $webhook->id,
                    'payment_id' => $payment->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        Log::info('[WEBHOOK REPLAY] Completed', [
            'webhook_id' => $webhook->id,
            'replayed'   => $replayed,
            'skipped'    => $skipped,
            'failed'     => $failed,
        ]);

        return compact('replayed', 'skipped', 'failed');
    }

    /**
     * Retry failed webhooks that are due for retry
     */
    public function retryFailedWebhooks(): int
    {
        $deliveries = WebhookDelivery::pendingRetry()
            ->with('customWebhook')
            ->get();

        $retried = 0;
        
        foreach ($deliveries as $delivery) {
            if (!$delivery->customWebhook) {
                Log::warning('[WEBHOOK RETRY] Webhook not found', [
                    'delivery_id' => $delivery->id,
                ]);
                continue;
            }

            $payload = json_decode($delivery->payload, true);
            
            if (!$payload) {
                Log::error('[WEBHOOK RETRY] Invalid payload', [
                    'delivery_id' => $delivery->id,
                ]);
                continue;
            }

            Log::info('[WEBHOOK RETRY] Retrying webhook', [
                'delivery_id' => $delivery->id,
                'webhook_id' => $delivery->custom_webhook_id,
                'attempt_count' => $delivery->attempt_count,
            ]);

            $this->dispatch($delivery->customWebhook, $payload);
            $retried++;
        }

        if ($retried > 0) {
            Log::info("[WEBHOOK RETRY] Completed retry batch", [
                'retried_count' => $retried,
            ]);
        }

        return $retried;
    }

    /**
     * Retry a specific webhook delivery
     */
    public function retryDelivery(WebhookDelivery $delivery): array
    {
        if (!$delivery->customWebhook) {
            throw new \Exception('Webhook configuration not found');
        }

        $payload = json_decode($delivery->payload, true);
        
        if (!$payload) {
            throw new \Exception('Invalid payload JSON');
        }

        Log::info('[WEBHOOK RETRY] Manually retrying delivery', [
            'delivery_id' => $delivery->id,
            'webhook_id' => $delivery->custom_webhook_id,
            'webhook_name' => $delivery->customWebhook->name,
            'current_attempts' => $delivery->attempt_count,
        ]);

        $startTime = microtime(true);
        
        try {
            // Generate signature
            $signature = $delivery->customWebhook->generateSignature($payload);

            // Prepare headers
            $defaultHeaders = [
                'X-Webhook-Signature' => $signature,
                'X-Event-Type' => $payload['event'],
                'X-Webhook-ID' => (string) $delivery->customWebhook->id,
                'X-Delivery-ID' => (string) $delivery->id,
                'X-Retry-Attempt' => (string) ($delivery->attempt_count + 1),
                'User-Agent' => 'BillingPlatform-Webhook/1.0',
                'Content-Type' => 'application/json',
            ];

            $headers = array_merge($defaultHeaders, $delivery->customWebhook->headers ?? []);

            // Send HTTP request
            $httpClient = Http::timeout($delivery->customWebhook->timeout);
            
            if (!$delivery->customWebhook->verify_ssl) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient
                ->withHeaders($headers)
                ->{strtolower($delivery->customWebhook->http_method)}($delivery->customWebhook->url, $payload);

            $durationMs = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $delivery->markAsSent(
                    $response->status(),
                    $response->body(),
                    $durationMs
                );

                $delivery->customWebhook->update(['last_triggered_at' => now()]);

                Log::info('✅ [WEBHOOK RETRY] Delivery successful', [
                    'delivery_id' => $delivery->id,
                    'status_code' => $response->status(),
                    'duration_ms' => $durationMs,
                ]);

                return [
                    'success' => true,
                    'http_status' => $response->status(),
                    'response_time' => $durationMs,
                    'message' => 'Webhook delivered successfully',
                ];
            } else {
                $delivery->markAsFailed(
                    "HTTP {$response->status()}: " . $response->body(),
                    $response->status(),
                    $durationMs
                );

                Log::warning('⚠️ [WEBHOOK RETRY] Delivery failed', [
                    'delivery_id' => $delivery->id,
                    'status_code' => $response->status(),
                    'duration_ms' => $durationMs,
                ]);

                return [
                    'success' => false,
                    'http_status' => $response->status(),
                    'response_time' => $durationMs,
                    'error_message' => "HTTP {$response->status()}: " . substr($response->body(), 0, 200),
                ];
            }
        } catch (\Exception $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000);
            
            $delivery->markAsFailed($e->getMessage(), null, $durationMs);

            Log::error('🔴 [WEBHOOK RETRY] Exception occurred', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'http_status' => null,
                'response_time' => $durationMs,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    // ----------------------------------------------------------------
    // Event-specific dispatch helpers
    // Each method loads the product, builds the payload, and calls
    // dispatchToProduct() which fans out to all matching active webhooks.
    // Errors are caught internally so they never bubble up to callers.
    // ----------------------------------------------------------------

    public function dispatchPaymentFailed(Payment $payment): void
    {
        $payment->loadMissing('customer.product');
        $product = $payment->customer?->product;
        if (!$product) return;

        try {
            $payload = $this->payloadBuilder->buildPaymentFailedPayload($payment);
            $this->dispatchToProduct($product, 'payment.failed', $payload);
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchPaymentFailed failed', [
                'payment_id' => $payment->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchSubscriptionCreated(Subscription $subscription): void
    {
        $subscription->loadMissing('customer.product');
        $product = $subscription->customer?->product;
        if (!$product) return;

        try {
            $payload   = $this->payloadBuilder->buildSubscriptionCreatedPayload($subscription);
            $webhooks  = $product->getActiveWebhooksForEvent('subscription.created');
            foreach ($webhooks as $webhook) {
                $this->dispatch($webhook, $payload, null, $subscription->id);
            }
            return;
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchSubscriptionCreated failed', [
                'subscription_id' => $subscription->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchSubscriptionRenewed(Subscription $subscription, ?Payment $payment = null): void
    {
        $subscription->loadMissing('customer.product');
        $product = $subscription->customer?->product;
        if (!$product) return;

        try {
            $payload  = $this->payloadBuilder->buildSubscriptionRenewedPayload($subscription, $payment);
            $webhooks = $product->getActiveWebhooksForEvent('subscription.renewed');
            foreach ($webhooks as $webhook) {
                $this->dispatch($webhook, $payload, $payment?->id, $subscription->id);
            }
            return;
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchSubscriptionRenewed failed', [
                'subscription_id' => $subscription->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchSubscriptionCancelled(Subscription $subscription, ?string $reason = null): void
    {
        $subscription->loadMissing('customer.product');
        $product = $subscription->customer?->product;
        if (!$product) return;

        try {
            $payload  = $this->payloadBuilder->buildSubscriptionCancelledPayload($subscription, $reason);
            $webhooks = $product->getActiveWebhooksForEvent('subscription.cancelled');
            foreach ($webhooks as $webhook) {
                $this->dispatch($webhook, $payload, null, $subscription->id);
            }
            return;
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchSubscriptionCancelled failed', [
                'subscription_id' => $subscription->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchSubscriptionExpired(Subscription $subscription): void
    {
        $subscription->loadMissing('customer.product');
        $product = $subscription->customer?->product;
        if (!$product) return;

        try {
            $payload  = $this->payloadBuilder->buildSubscriptionExpiredPayload($subscription);
            $webhooks = $product->getActiveWebhooksForEvent('subscription.expired');
            foreach ($webhooks as $webhook) {
                $this->dispatch($webhook, $payload, null, $subscription->id);
            }
            return;
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchSubscriptionExpired failed', [
                'subscription_id' => $subscription->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchCreditsPurchased(mixed $creditTransaction, ?Payment $payment = null): void
    {
        $creditTransaction->loadMissing('customer.product');
        $product = $creditTransaction->customer?->product;
        if (!$product) return;

        try {
            $payload = $this->payloadBuilder->buildCreditsPurchasedPayload($creditTransaction, $payment);
            $this->dispatchToProduct($product, 'credits.purchased', $payload);
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchCreditsPurchased failed', [
                'transaction_id' => $creditTransaction->id, 'error' => $e->getMessage(),
            ]);
        }
    }

    public function dispatchSubscriptionUpgraded(
        Subscription $subscription,
        mixed $oldPlan = null,
        mixed $newPlan = null
    ): void {
        $subscription->loadMissing('customer.product');
        $product = $subscription->customer?->product;
        if (!$product) return;

        try {
            $payload  = $this->payloadBuilder->buildSubscriptionUpgradedPayload($subscription, $oldPlan, $newPlan);
            $webhooks = $product->getActiveWebhooksForEvent('subscription.upgraded');
            foreach ($webhooks as $webhook) {
                $this->dispatch($webhook, $payload, null, $subscription->id);
            }
            return;
        } catch (\Exception $e) {
            Log::warning('[WEBHOOK] dispatchSubscriptionUpgraded failed', [
                'subscription_id' => $subscription->id, 'error' => $e->getMessage(),
            ]);
        }
    }
}

