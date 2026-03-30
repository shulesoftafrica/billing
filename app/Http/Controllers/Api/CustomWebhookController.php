<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomWebhook;
use App\Models\Product;
use App\Services\WebhookDispatchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomWebhookController extends Controller
{
    protected WebhookDispatchService $dispatchService;

    public function __construct(WebhookDispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * List all webhooks for a product
     * GET /api/v1/products/{product}/webhooks
     */
    public function index(Product $product): JsonResponse
    {
        $webhooks = $product->webhooks()
            ->withCount('deliveries')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $webhooks,
        ]);
    }

    /**
     * Create webhook
     * POST /api/v1/products/{product}/webhooks
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'events' => 'nullable|array',
            'events.*' => Rule::in([
                'payment.success',
                'payment.failed',
                'payment.refunded',
                'invoice.created',
                'invoice.paid',
                'invoice.overdue',
                'invoice.cancelled',
                'subscription.created',
                'subscription.renewed',
                'subscription.cancelled',
                'subscription.paused',
                'subscription.resumed',
                'payment.*',
                'invoice.*',
                'subscription.*',
            ]),
            'http_method' => ['nullable', Rule::in(['GET', 'POST', 'PUT'])],
            'headers' => 'nullable|array',
            'timeout' => 'nullable|integer|min:5|max:60',
            'retry_count' => 'nullable|integer|min:0|max:5',
            'verify_ssl' => 'nullable|boolean',
        ]);

        // Generate secret
        $validated['secret'] = 'whsec_' . Str::random(32);
        $validated['product_id'] = $product->id;

        $webhook = CustomWebhook::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook created successfully',
            'data' => $webhook,
        ], 201);
    }

    /**
     * Get webhook
     * GET /api/v1/products/{product}/webhooks/{webhook}
     */
    public function show(Product $product, CustomWebhook $webhook): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $webhook,
        ]);
    }

    /**
     * Update webhook
     * PUT /api/v1/products/{product}/webhooks/{webhook}
     */
    public function update(Request $request, Product $product, CustomWebhook $webhook): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:500',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'events' => 'nullable|array',
            'events.*' => Rule::in([
                'payment.success',
                'payment.failed',
                'payment.refunded',
                'invoice.created',
                'invoice.paid',
                'invoice.overdue',
                'invoice.cancelled',
                'subscription.created',
                'subscription.renewed',
                'subscription.cancelled',
                'subscription.paused',
                'subscription.resumed',
                'payment.*',
                'invoice.*',
                'subscription.*',
            ]),
            'http_method' => ['sometimes', Rule::in(['GET', 'POST', 'PUT'])],
            'headers' => 'nullable|array',
            'timeout' => 'sometimes|integer|min:5|max:60',
            'retry_count' => 'sometimes|integer|min:0|max:5',
            'verify_ssl' => 'sometimes|boolean',
        ]);

        $webhook->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Webhook updated successfully',
            'data' => $webhook->fresh(),
        ]);
    }

    /**
     * Delete webhook
     * DELETE /api/v1/products/{product}/webhooks/{webhook}
     */
    public function destroy(Product $product, CustomWebhook $webhook): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        $webhook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Webhook deleted successfully',
        ]);
    }

    /**
     * Test webhook
     * POST /api/v1/products/{product}/webhooks/{webhook}/test
     */
    public function test(Product $product, CustomWebhook $webhook): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        if ($webhook->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Webhook must be active to test',
            ], 400);
        }

        $testPayload = [
            'event' => 'webhook.test',
            'event_id' => 'evt_test_' . uniqid(),
            'timestamp' => now()->toIso8601String(),
            'api_version' => '2026-03-24',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'product_code' => $product->product_code,
            ],
            'data' => [
                'message' => 'This is a test webhook from your billing platform',
                'webhook_id' => $webhook->id,
                'webhook_name' => $webhook->name,
            ],
        ];

        $delivery = $this->dispatchService->dispatch($webhook, $testPayload);

        return response()->json([
            'success' => $delivery->status === 'sent',
            'message' => $delivery->status === 'sent' ? 'Test webhook sent successfully' : 'Test webhook failed',
            'delivery' => [
                'id' => $delivery->id,
                'status' => $delivery->status,
                'http_status_code' => $delivery->http_status_code,
                'duration_ms' => $delivery->duration_ms,
                'response_body' => $delivery->response_body,
                'error_message' => $delivery->error_message,
            ],
        ]);
    }

    /**
     * Get delivery history
     * GET /api/v1/products/{product}/webhooks/{webhook}/deliveries
     */
    public function deliveries(Product $product, CustomWebhook $webhook, Request $request): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        $perPage = $request->get('per_page', 50);
        $status = $request->get('status'); // Filter by status
        
        $query = $webhook->deliveries();

        if ($status) {
            $query->where('status', $status);
        }

        $deliveries = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($deliveries);
    }

    /**
     * Replay payment.success webhooks for past payments that missed delivery.
     * POST /api/v1/products/{product}/webhooks/{webhook}/replay
     *
     * Optional body:
     *   from         - ISO date, filter payments paid_at >= from
     *   to           - ISO date, filter payments paid_at <= to
     *   payment_ids  - array of specific payment IDs to replay
     */
    public function replay(Request $request, Product $product, CustomWebhook $webhook): JsonResponse
    {
        if ($webhook->product_id !== $product->id) {
            return response()->json(['success' => false, 'message' => 'Webhook not found'], 404);
        }

        if ($webhook->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Webhook must be active to replay'], 400);
        }

        $validated = $request->validate([
            'from'           => 'nullable|date',
            'to'             => 'nullable|date',
            'payment_ids'    => 'nullable|array',
            'payment_ids.*'  => 'integer|min:1',
        ]);

        $filters = array_filter([
            'from'        => $validated['from'] ?? null,
            'to'          => $validated['to'] ?? null,
            'payment_ids' => $validated['payment_ids'] ?? null,
        ]);

        $result = $this->dispatchService->replayPaymentsToWebhook($webhook, $filters);

        return response()->json([
            'success' => true,
            'message' => "Replay completed: {$result['replayed']} delivered, {$result['failed']} failed.",
            'data'    => $result,
        ]);
    }

    /**
     * Regenerate webhook secret
     * POST /api/v1/products/{product}/webhooks/{webhook}/regenerate-secret
     */
    public function regenerateSecret(Product $product, CustomWebhook $webhook): JsonResponse
    {
        // Ensure webhook belongs to product
        if ($webhook->product_id !== $product->id) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook not found',
            ], 404);
        }

        $newSecret = 'whsec_' . Str::random(32);
        $webhook->update(['secret' => $newSecret]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook secret regenerated successfully',
            'data' => [
                'secret' => $newSecret,
            ],
        ]);
    }
}
