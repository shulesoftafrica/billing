<?php

namespace App\Http\Controllers;

use App\Services\UCNPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    protected UCNPaymentService $ucnPaymentService;

    public function __construct(UCNPaymentService $ucnPaymentService)
    {
        $this->ucnPaymentService = $ucnPaymentService;
    }

    /**
     * Handle UCN payment webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleUCNPayment(Request $request): JsonResponse
    {
        // Log incoming webhook
        Log::info('UCN webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $webhookData = $request->all();

        // Process the webhook
        $response = $this->ucnPaymentService->processWebhook($webhookData);

        // Determine HTTP status code based on response code
        $httpStatus = $response['responseCode'] === '000' ? 200 : 400;

        return response()->json($response, $httpStatus);
    }

    public function handleUCNPaymentWebhook(Request $request): JsonResponse
    {
        Log::info(['Request recived' => json_encode($request->all())]);
        return response()->json(['success' => true, 'message' => 'Payment webhook received'], 200);
    }

    /**
     * Handle Stripe webhook
     * POST /api/webhooks/stripe
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        Log::info('Stripe webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'headers' => [
                'stripe-signature' => $request->header('stripe-signature')
            ]
        ]);

        try {
            // Verify webhook signature
            $signature = $request->header('stripe-signature');
            if (!$this->verifyStripeSignature($request->getContent(), $signature)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }

            $payload = $request->all();
            $eventType = $payload['type'] ?? null;

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    return $this->handleStripePaymentSuccess($payload['data']['object']);
                
                case 'payment_intent.payment_failed':
                    return $this->handleStripePaymentFailed($payload['data']['object']);
                
                case 'invoice.payment_succeeded':
                    return $this->handleStripeInvoicePaymentSuccess($payload['data']['object']);
                
                default:
                    Log::info("Unhandled Stripe webhook event: {$eventType}");
                    return response()->json(['success' => true, 'message' => 'Event received'], 200);
            }

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Handle FlutterWave webhook
     * POST /api/webhooks/flutterwave
     */
    public function handleFlutterWaveWebhook(Request $request): JsonResponse
    {
        Log::info('FlutterWave webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'headers' => [
                'verif-hash' => $request->header('verif-hash')
            ]
        ]);

        try {
            // Verify webhook signature
            $hash = $request->header('verif-hash');
            if (!$this->verifyFlutterWaveSignature($request->getContent(), $hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }

            $payload = $request->all();
            $eventType = $payload['event'] ?? null;

            switch ($eventType) {
                case 'charge.completed':
                    return $this->handleFlutterWavePaymentSuccess($payload['data']);
                
                case 'charge.failed':
                    return $this->handleFlutterWavePaymentFailed($payload['data']);
                
                default:
                    Log::info("Unhandled FlutterWave webhook event: {$eventType}");
                    return response()->json(['success' => true, 'message' => 'Event received'], 200);
            }

        } catch (\Exception $e) {
            Log::error('FlutterWave webhook processing failed: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }

    /**
     * Handle test webhook for development
     * POST /api/webhooks/test
     */
    public function handleTestWebhook(Request $request): JsonResponse
    {
        Log::info('Test webhook received', [
            'payload' => $request->all(),
            'ip' => $request->ip()
        ]);

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:success,failed,pending',
            'payment_method' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            
            // Find or create payment record
            $payment = Payment::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'gateway_reference' => $request->transaction_id
                ],
                [
                    'gateway_id' => PaymentGateway::where('name', 'Test Gateway')->first()->id ?? 1,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $request->amount,
                    'status' => $request->status === 'success' ? 'success' : 'failed',
                    'payment_method' => $request->payment_method ?? 'test',
                    'gateway_response' => $request->all(),
                    'paid_at' => $request->status === 'success' ? now() : null
                ]
            );

            // Update invoice status
            if ($request->status === 'success') {
                $invoice->update(['status' => 'paid']);
                
                // If it's a wallet topup invoice, add credits to wallet
                if ($invoice->invoice_type === 'wallet_topup' && isset($invoice->metadata['wallet_type'])) {
                    $walletService = app(\app\Services\WalletService::class);
                    $walletService->addCredits(
                        $invoice->customer_id,
                        $invoice->metadata['wallet_type'],
                        $invoice->metadata['units'],
                        "Wallet topup from invoice {$invoice->invoice_number}",
                        $invoice->id,
                        $invoice->metadata['unit_price']
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Test webhook processed successfully',
                'data' => [
                    'payment' => $payment,
                    'invoice_status' => $invoice->fresh()->status
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Test webhook processing failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Test webhook processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Stripe payment success
     */
    private function handleStripePaymentSuccess(array $paymentIntent): JsonResponse
    {
        try {
            $invoiceId = $paymentIntent['metadata']['invoice_id'] ?? null;
            if (!$invoiceId) {
                throw new \Exception('Invoice ID not found in payment metadata');
            }

            $invoice = Invoice::findOrFail($invoiceId);
            
            Payment::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'gateway_reference' => $paymentIntent['id']
                ],
                [
                    'gateway_id' => PaymentGateway::where('type', 'stripe')->first()->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $paymentIntent['amount'] / 100, // Stripe uses cents
                    'status' => 'success',
                    'payment_method' => 'card',
                    'gateway_response' => $paymentIntent,
                    'paid_at' => now()
                ]
            );

            $invoice->update(['status' => 'paid']);

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            Log::error('Stripe payment success handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle Stripe payment failure
     */
    private function handleStripePaymentFailed(array $paymentIntent): JsonResponse
    {
        try {
            $invoiceId = $paymentIntent['metadata']['invoice_id'] ?? null;
            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    Payment::updateOrCreate(
                        [
                            'invoice_id' => $invoice->id,
                            'gateway_reference' => $paymentIntent['id']
                        ],
                        [
                            'gateway_id' => PaymentGateway::where('type', 'stripe')->first()->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $paymentIntent['amount'] / 100,
                            'status' => 'failed',
                            'payment_method' => 'card',
                            'gateway_response' => $paymentIntent,
                            'retry_count' => ($invoice->payments()->where('gateway_reference', $paymentIntent['id'])->first()->retry_count ?? 0) + 1
                        ]
                    );
                }
            }

            return response()->json(['success' => true], 200);

        } catch (\Exception $e) {
            Log::error('Stripe payment failure handling failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle FlutterWave payment success
     */
    private function handleFlutterWavePaymentSuccess(array $charge): JsonResponse
    {
        try {
            $invoiceId = $charge['meta']['invoice_id'] ?? null;
            if (!$invoiceId) {
                throw new \Exception('Invoice ID not found in charge metadata');
            }

            $invoice = Invoice::findOrFail($invoiceId);
            
            Payment::updateOrCreate(
                [
                    'invoice_id' => $invoice->id,
                    'gateway_reference' => $charge['id']
                ],
                [
                    'gateway_id' => PaymentGateway::where('type', 'flutterwave')->first()->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $charge['amount'],
                    'status' => 'success',
                    'payment_method' => $charge['payment_type'] ?? 'card',
                    'gateway_response' => $charge,
                    'paid_at' => now()
                ]
            );

            $invoice->update(['status' => 'paid']);

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
                            'gateway_id' => PaymentGateway::where('type', 'flutterwave')->first()->id,
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
     * Verify Stripe webhook signature
     */
    private function verifyStripeSignature(string $payload, string $signature = null): bool
    {
        if (!$signature) {
            return false;
        }

        // In a real implementation, you'd verify against your Stripe webhook secret
        // For now, we'll just check if signature is present
        return !empty($signature);
    }

    /**
     * Verify FlutterWave webhook signature
     */
    private function verifyFlutterWaveSignature(string $payload, string $hash = null): bool
    {
        if (!$hash) {
            return false;
        }

        // In a real implementation, you'd verify against your FlutterWave secret hash
        // For now, we'll just check if hash is present
        return !empty($hash);
    }
     private function handleStripeInvoicePaymentSuccess($payload){
        return response()->json(['true']);
     }
}
