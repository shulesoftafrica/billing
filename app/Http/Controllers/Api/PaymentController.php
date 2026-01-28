<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

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
}
