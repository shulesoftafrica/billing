<?php

namespace App\Services;

use App\Models\CustomWebhook;
use App\Models\WebhookDelivery;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatchService
{
    /**
     * Dispatch webhook to a single endpoint
     */
    public function dispatch(CustomWebhook $webhook, array $payload): WebhookDelivery
    {
        $startTime = microtime(true);

        // Create delivery record
        $delivery = WebhookDelivery::create([
            'custom_webhook_id' => $webhook->id,
            'event_type' => $payload['event'],
            'payload' => json_encode($payload),
            'status' => 'pending',
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
    public function dispatchToProduct(Product $product, string $eventType, array $payload): void
    {
        $webhooks = $product->getActiveWebhooksForEvent($eventType);

        if ($webhooks->isEmpty()) {
            Log::debug('[WEBHOOK DISPATCH] No webhooks configured', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'event_type' => $eventType,
            ]);
            return;
        }

        Log::info('[WEBHOOK DISPATCH] Dispatching to multiple webhooks', [
            'product_id' => $product->id,
            'event_type' => $eventType,
            'webhook_count' => $webhooks->count(),
        ]);

        foreach ($webhooks as $webhook) {
            try {
                $this->dispatch($webhook, $payload);
            } catch (\Exception $e) {
                Log::error('[WEBHOOK DISPATCH] Failed to dispatch webhook', [
                    'webhook_id' => $webhook->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other webhooks even if one fails
            }
        }
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
}
