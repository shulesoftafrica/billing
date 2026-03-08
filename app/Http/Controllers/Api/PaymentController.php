<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\FlutterwaveService;
use Illuminate\Support\Facades\Log;
use App\Services\Stripe\PaymentIntentService;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentIntentService $paymentIntentService
    ) {
    }

    /**
     * Get all payments for a given invoice ID
     * GET /api/payments/by-invoice/{invoice_id}
     */
    public function getByInvoice($invoice_id)
    {
        $payments = Payment::where('invoice_id', $invoice_id)->get();
        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get all payments within a date range
     * GET /api/payments?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
     */
    public function getByDateRange(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);
        $customer =  request('customer_id') ?? null;

        $payments = Payment::whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);
        if ($customer) {
            $payments->where('customer_id', $customer);
        }
        $payments = $payments->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Verify Flutterwave payment transaction (Optional)
     * GET /api/payments/verify/{transaction_id}
     * 
     * Note: Payment verification is also done automatically via webhooks.
     * This endpoint is provided for manual verification if needed.
     */
    public function verifyFlutterwavePayment($transactionId)
    {
        try {
            $flutterwaveService = new FlutterwaveService();
            
            if (!$flutterwaveService->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flutterwave gateway not configured or inactive'
                ], 503);
            }

            $result = $flutterwaveService->verifyPayment($transactionId);

            if ($result['success']) {
                Log::info('Payment verification successful', [
                    'transaction_id' => $transactionId,
                    'tx_ref' => $result['data']['tx_ref'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Payment verification failed',
                'data' => $result['data'] ?? null
            ], 400);

        } catch (\Exception $e) {
            Log::error('Payment verification error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during payment verification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Stripe PaymentIntent.
     * POST /api/payments/intent
     */
    public function createIntent(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'customer' => 'nullable|string',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
            'receipt_email' => 'nullable|email',
            'capture_method' => 'nullable|string|in:automatic,automatic_async,manual',
            'statement_descriptor' => 'nullable|string|max:22',
        ]);

        try {
            $intent = $this->paymentIntentService->create($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_intent_id' => $intent->id,
                    'client_secret' => $intent->client_secret,
                    'amount' => $intent->amount,
                    'currency' => $intent->currency,
                    'status' => $intent->status,
                ],
            ], 200);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'payment' => [$e->getMessage()],
            ]);
        }
    }
}
