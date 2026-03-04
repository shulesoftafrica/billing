<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
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
