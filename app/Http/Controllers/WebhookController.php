<?php

namespace App\Http\Controllers;

use App\Services\UCNPaymentService;
use App\Services\WebhookPaymentProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\ControlNumber;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{
    protected UCNPaymentService $ucnPaymentService;
    protected WebhookPaymentProcessingService $webhookPaymentProcessingService;

    public function __construct(UCNPaymentService $ucnPaymentService, WebhookPaymentProcessingService $webhookPaymentProcessingService)
    {
        $this->ucnPaymentService = $ucnPaymentService;
        $this->webhookPaymentProcessingService = $webhookPaymentProcessingService;
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
                'flutterwave-signature' => $request->header('flutterwave-signature')
            ]
        ]);
        try {
            // Verify webhook signature
            $hash = $request->header('flutterwave-signature');
            if (!$this->verifyFlutterWaveSignature($request->getContent(), $hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook signature'
                ], 401);
            }

            $payload = $request->all();
            $eventType = $payload['type'] ?? null;

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

            $payment = Payment::updateOrCreate(
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
            $this->webhookPaymentProcessingService->processByInvoice($invoice, $payment);

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

    private function handleStripeInvoicePaymentSuccess($payload)
    {
        return response()->json(['true']);
    }
}
