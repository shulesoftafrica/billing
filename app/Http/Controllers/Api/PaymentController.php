<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\Stripe\PaymentIntentService;
use Illuminate\Validation\ValidationException;

class PaymentController extends BaseApiController
{
    public function __construct(
        private readonly PaymentIntentService $paymentIntentService
    ) {
    }

    /**
     * Get all payments for a given invoice ID
     * GET /api/v1/payments/by-invoice/{invoice_id}
     * 
     * Requires: payments:read ability
     */
    public function getByInvoice($invoice_id)
    {
        // Require payments:read ability
        $this->requireAbility('payments:read', 'You do not have permission to view payment records');

        $organizationId = $this->getOrganizationId();

        $payments = Payment::with([
            'customer',
            'paymentGateway',
            'invoices' => function ($query) use ($invoice_id) {
                $query->where('invoices.id', $invoice_id);
            },
        ])
            ->whereHas('invoices', function ($query) use ($invoice_id, $organizationId) {
                $query->where('invoices.id', $invoice_id)
                      ->where('invoices.organization_id', $organizationId);
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
     * GET /api/v1/payments?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&customer_id=123
     * 
     * Requires: payments:read ability
     */
    public function getByDateRange(Request $request)
    {
        // Require payments:read ability
        $this->requireAbility('payments:read', 'You do not have permission to view payment records');

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $organizationId = $this->getOrganizationId();
        $customerId = $request->input('customer_id');

        $payments = Payment::with(['customer', 'paymentGateway'])
            ->whereHas('customer', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);

        if ($customerId) {
            // Verify customer belongs to the same organization
            $payments->where('customer_id', $customerId);
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
