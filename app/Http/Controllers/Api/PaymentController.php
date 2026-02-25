<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\FlutterwaveService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
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

        $payments = Payment::whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to)
            ->get();

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
}
