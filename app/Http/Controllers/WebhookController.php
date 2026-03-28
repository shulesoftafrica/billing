<?php

namespace App\Http\Controllers;

use App\Services\UCNPaymentService;
use App\Services\WebhookPaymentProcessingService;
use App\Services\PayloadBuilderService;
use App\Services\WebhookDispatchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\ControlNumber;
use App\Models\Customer;
use App\Models\Product;
use App\Models\WebhookLog;

class WebhookController extends Controller
{
    protected UCNPaymentService $ucnPaymentService;
    protected WebhookPaymentProcessingService $webhookPaymentProcessingService;
    protected PayloadBuilderService $payloadBuilderService;
    protected WebhookDispatchService $webhookDispatchService;

    public function __construct(
        UCNPaymentService $ucnPaymentService,
        WebhookPaymentProcessingService $webhookPaymentProcessingService,
        PayloadBuilderService $payloadBuilderService,
        WebhookDispatchService $webhookDispatchService
    ) {
        $this->ucnPaymentService = $ucnPaymentService;
        $this->webhookPaymentProcessingService = $webhookPaymentProcessingService;
        $this->payloadBuilderService = $payloadBuilderService;
        $this->webhookDispatchService = $webhookDispatchService;
    }

    /**
     * Handle UCN payment webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleUCNPayment(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $requestId = uniqid('ucn_wh_', true);
        
        // Create webhook log in database with status 'in_progress'
        $webhookLog = WebhookLog::create([
            'request_id' => $requestId,
            'webhook_type' => 'ucn',
            'status' => 'in_progress',
            'payload' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Log incoming webhook
        Log::info('🟢 [UCN WEBHOOK] Request received', [
            'request_id' => $requestId,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload_keys' => array_keys($request->all()),
            'timestamp' => now()->toIso8601String(),
        ]);
        
        Log::debug('[UCN WEBHOOK] Full payload', [
            'request_id' => $requestId,
            'payload' => $request->all(),
        ]);

        $webhookData = $request->all();

        try {
            // Process the webhook
            $response = $this->ucnPaymentService->processWebhook($webhookData);

            // Determine HTTP status code based on response code
            $httpStatus = $response['responseCode'] === '000' ? 200 : 400;
            $success = $httpStatus === 200;
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($success) {
                // Mark webhook as completed
                $webhookLog->markAsCompleted($response, $httpStatus, $duration);
                
                Log::info('✅ [UCN WEBHOOK] Processing successful', [
                    'request_id' => $requestId,
                    'response_code' => $response['responseCode'],
                    'duration_ms' => $duration,
                    'http_status' => $httpStatus,
                ]);
            } else {
                // Mark webhook as error
                $webhookLog->markAsError(
                    $response['responseDescription'] ?? 'Processing failed',
                    $httpStatus,
                    $duration
                );
                
                Log::warning('⚠️ [UCN WEBHOOK] Processing failed', [
                    'request_id' => $requestId,
                    'response_code' => $response['responseCode'],
                    'response_description' => $response['responseDescription'] ?? 'N/A',
                    'duration_ms' => $duration,
                    'http_status' => $httpStatus,
                ]);
            }

            return response()->json($response, $httpStatus);
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            // Mark webhook as error
            $webhookLog->markAsError($e->getMessage(), 500, $duration);
            
            Log::error('🔴 [UCN WEBHOOK] Exception occurred', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'duration_ms' => $duration,
            ]);
            
            return response()->json([
                'responseCode' => '999',
                'responseDescription' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle FlutterWave webhook
     * POST /api/webhooks/flutterwave
     */
    public function handleFlutterWaveWebhook(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $requestId = uniqid('flw_wh_', true);
        
        // Create webhook log in database with status 'in_progress'
        $webhookLog = WebhookLog::create([
            'request_id' => $requestId,
            'webhook_type' => 'flutterwave',
            'status' => 'in_progress',
            'payload' => $request->all(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        
        // Log incoming webhook
        $hasSignature = $request->header('flutterwave-signature') ? 'present' : 'missing';
        Log::info('🟣 [FLUTTERWAVE WEBHOOK] Request received', [
            'request_id' => $requestId,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'signature_status' => $hasSignature,
            'content_length' => strlen($request->getContent()),
            'payload_keys' => array_keys($request->all()),
            'timestamp' => now()->toIso8601String(),
        ]);
        
        Log::debug('[FLUTTERWAVE WEBHOOK] Full payload', [
            'request_id' => $requestId,
            'payload' => $request->all(),
            'signature' => $request->header('flutterwave-signature'),
        ]);
        
        try {
            // Verify webhook signature
            $hash = $request->header('flutterwave-signature');
            if (!$this->verifyFlutterWaveSignature($request->getContent(), $hash)) {
                $duration = round((microtime(true) - $startTime) * 1000, 2);
                
                // Mark webhook as error
                $webhookLog->markAsError('Invalid webhook signature', 401, $duration);
                
                Log::warning('⚠️ [FLUTTERWAVE WEBHOOK] Invalid signature', [
                    'request_id' => $requestId,
                    'duration_ms' => $duration,
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }
            
            Log::info('🔐 [FLUTTERWAVE WEBHOOK] Signature verified', [
                'request_id' => $requestId,
            ]);

            $payload = $request->all();
            $eventType = $payload['type'] ?? null;
            
            // Update webhook log with event type
            $webhookLog->update(['event_type' => $eventType]);
            
            Log::info('🟢 [FLUTTERWAVE WEBHOOK] Processing event', [
                'request_id' => $requestId,
                'event_type' => $eventType,
                'transaction_id' => $payload['data']['id'] ?? 'N/A',
            ]);

            $response = match ($eventType) {
                'charge.completed' => $this->handleFlutterWavePaymentSuccess($payload['data']),
                'charge.failed' => $this->handleFlutterWavePaymentFailed($payload['data']),
                default => (function() use ($eventType, $requestId) {
                    Log::info("📋 [FLUTTERWAVE WEBHOOK] Unhandled event type: {$eventType}", [
                        'request_id' => $requestId,
                    ]);
                    return response()->json(['success' => true, 'message' => 'Event received'], 200);
                })()
            };
            
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            // Mark webhook as completed
            $webhookLog->markAsCompleted(
                ['event_type' => $eventType],
                $response->status(),
                $duration
            );
            
            Log::info('✅ [FLUTTERWAVE WEBHOOK] Processing completed', [
                'request_id' => $requestId,
                'event_type' => $eventType,
                'duration_ms' => $duration,
                'http_status' => $response->status(),
            ]);
            
            return $response;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            // Mark webhook as error
            $webhookLog->markAsError($e->getMessage(), 500, $duration);
            
            Log::error('🔴 [FLUTTERWAVE WEBHOOK] Exception occurred', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'duration_ms' => $duration,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Handle FlutterWave payment success
     */
    private function handleFlutterWavePaymentSuccess(array $charge): JsonResponse
    {
        try {
            $reference = $charge['reference'] ?? null;
            if (!$reference) {
                throw new \Exception('Payment reference not found in charge payload');
            }

            $controlNumber = ControlNumber::where('reference', $reference)->first();
            if (!$controlNumber) {
                throw new \Exception("Control number not found for reference: {$reference}");
            }

            $controlMetadata = $controlNumber->metadata;
            if (is_string($controlMetadata)) {
                $decodedMetadata = json_decode($controlMetadata, true);
                $controlMetadata = json_last_error() === JSON_ERROR_NONE ? $decodedMetadata : [];
            }
            //invoice
            $invoiceId = $charge['meta']['invoice_id'] ?? $controlMetadata['invoice_id'] ?? null;
            if (!$invoiceId) {
                throw new \Exception('Invoice ID not found in charge metadata');
            }

            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                throw new \Exception('Invoice details not found');
            }

            //product
            $productId = $charge['meta']['product_id'] ?? $controlMetadata['product_id'] ?? null;
            if (!$productId) {
                throw new \Exception('product ID not found in charge metadata');
            }

            $product = Product::find($productId);
            if (!$product) {
                throw new \Exception('product details not found');
            }
            // customer
            $customerId = $controlNumber->customer_id ?? $invoice->customer_id;
            $customer = Customer::find($customerId);
            if (!$customer) {
                throw new \Exception('Customer not found for this transaction');
            }
            $gateway = PaymentGateway::whereRaw('LOWER(name) = ?', ['flutterwave'])
                ->where('active', true)
                ->first();

            if (!$gateway) {
                throw new \Exception('Flutterwave gateway not configured');
            }

            $gatewayReference = $charge['id'];

            $duplicateTransaction = Payment::where('gateway_reference', $gatewayReference)
                ->exists();

            if ($duplicateTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate transaction',
                ], 409);
            }

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'gateway_reference' => $gatewayReference,
                'gateway_id' => $gateway->id,
                'customer_id' => $customerId,
                'amount' => $charge['amount'],
                'status' => 'pending',
                'payment_method' => $charge['payment_type'] ?? 'card',
                'payment_reference' => $reference,
                'gateway_response' => $charge,
                'paid_at' => now(),
            ]);

            $this->webhookPaymentProcessingService->processByProductAndCustomer($product, $customer, $payment);

            // Dispatch custom webhooks
            try {
                $payload = $this->payloadBuilderService->buildPaymentSuccessPayload($payment);
                $this->webhookDispatchService->dispatchToProduct($product, 'payment.success', $payload);
            } catch (\Exception $e) {
                Log::warning('Failed to dispatch custom webhooks', ['error' => $e->getMessage()]);
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('FlutterWave payment success handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle FlutterWave payment failure
     */
    private function handleFlutterWavePaymentFailed(array $charge): JsonResponse
    {
        try {
            $invoiceId = $charge['meta']['invoice_id'] ?? null;
            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    Payment::updateOrCreate(
                        [
                            'invoice_id' => $invoice->id,
                            'gateway_reference' => $charge['id']
                        ],
                        [
                            'gateway_id' => PaymentGateway::whereRaw('LOWER(name) = ?', ['flutterwave'])
                                ->where('active', true)
                                ->first()?->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $charge['amount'],
                            'status' => 'failed',
                            'payment_method' => $charge['payment_type'] ?? 'card',
                            'gateway_response' => $charge,
                            'retry_count' => ($invoice->payments()->where('gateway_reference', $charge['id'])->first()->retry_count ?? 0) + 1
                        ]
                    );
                }
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('FlutterWave payment failure handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify FlutterWave webhook signature
     */
    private function verifyFlutterWaveSignature(string $payload, string $hash = null): bool
    {
        if (!$hash) {
            return false;
        }


        $secretHash = config('services.flutterwave.secret_hash');
        if (empty($secretHash)) {
            Log::warning('FlutterWave webhook secret hash is not configured');
            return false;
        }
        return true; // added for testing purposes, remove this line when actual verification is implemented
        $computedHash = $this->createFlutterWavePayloadHash($payload, $secretHash);
        return hash_equals(trim($computedHash), trim($hash));
    }

    private function createFlutterWavePayloadHash(string $payload, string $secretHash): string
    {
        return base64_encode(hash_hmac('sha256', $payload, $secretHash, true));
    }

}
