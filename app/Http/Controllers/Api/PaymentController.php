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
        $payments = Payment::with([
            'customer',
            'paymentGateway',
            'invoices' => function ($query) use ($invoice_id) {
                $query->where('invoices.id', $invoice_id);
            },
        ])
            ->whereHas('invoices', function ($query) use ($invoice_id) {
                $query->where('invoices.id', $invoice_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $this->formatPaymentsResponse($payments)
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

        $payments = Payment::with(['customer', 'paymentGateway'])->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);
        if ($customer) {
            $payments->where('customer_id', $customer);
        }
        $payments = $payments->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $this->formatPaymentsResponse($payments)
        ]);
    }

    private function formatPaymentsResponse($payments)
    {
        return $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'gateway_id' => $payment->gateway_id,
                'gateway_name' => $payment->paymentGateway?->name,
                'customer_id' => $payment->customer_id,
                'amount' => $payment->amount,
                'transaction_reference' => $payment->gateway_reference,
                'payment_reference' => $payment->payment_reference,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
                                'customer' => $payment->customer,

            ];
        })->values();
    }
}
