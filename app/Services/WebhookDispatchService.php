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
            'current_attempts' => $delivery->attempts,
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
                'X-Retry-Attempt' => (string) ($delivery->attempts + 1),
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
}

