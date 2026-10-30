<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseApiController;
use App\Models\Customer;
use App\Models\ControlNumber;
use App\Models\InvoiceItem;
use App\Models\PaymentGateway;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Payment;
use App\Services\Stripe\PaymentIntentService;
use Illuminate\Validation\ValidationException;

class PaymentController extends BaseApiController
{
    public function __construct(
        private readonly PaymentIntentService $paymentIntentService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * Manually record a payment already received outside a real payment
     * gateway (bank deposit, migrated/backfilled history, etc.) and, if it
     * fully covers the invoice balance, activate the subscription the same
     * way a real webhook payment would.
     *
     * POST /api/v1/payments
     * Requires: payments:write ability
     *
     * Amount vs. invoice total is NOT a caller choice — whether this
     * activates the subscription or leaves it correctly-recorded-but-
     * pending is decided by SubscriptionService::enableSubscription()
     * exactly as it would for a real gateway payment.
     *
     * A payment only counts toward an invoice's balance if its
     * payment_reference matches a real ControlNumber already generated for
     * that customer+product (created automatically when the subscription
     * was created) — this endpoint resolves and reuses that internally, a
     * caller does not (and cannot) supply it directly.
     */
    public function store(Request $request)
    {
        $this->requireAbility('payments:write', 'You do not have permission to record payments');

        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'nullable|date',
            'external_reference' => 'nullable|string|max:255', // e.g. a safaribook.proforma_payments.id, for traceability
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $organizationId = $this->getOrganizationId();

        $subscription = Subscription::with(['customer', 'pricePlan.product'])->find($request->subscription_id);
        if (!$subscription || $subscription->customer->organization_id !== $organizationId) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $productId = $subscription->pricePlan->product_id;

        $controlNumber = ControlNumber::where('customer_id', $subscription->customer_id)
            ->where('product_id', $productId)
            ->first();

        if (!$controlNumber) {
            return response()->json([
                'success' => false,
                'message' => 'No control number exists for this customer/product — a payment gateway integration must be configured for this organization before a payment can be recorded.',
            ], 422);
        }

        $invoiceItem = InvoiceItem::where('subscription_id', $subscription->id)
            ->orderByDesc('id')
            ->first();

        if (!$invoiceItem) {
            return response()->json(['success' => false, 'message' => 'No invoice item found for this subscription'], 422);
        }

        $gateway = PaymentGateway::firstOrCreate(
            ['name' => 'Manual / Bank Deposit'],
            ['type' => 'bank', 'active' => true, 'webhook_secret' => \Illuminate\Support\Str::random(64)]
        );

        $reference = $request->input('external_reference');
        $gatewayReference = $reference
            ? 'MANUAL-' . preg_replace('/[^A-Za-z0-9_-]/', '', $reference)
            : 'MANUAL-' . now()->timestamp . '-' . \Illuminate\Support\Str::random(8);

        // gateway_reference is unique — if the same external_reference is pushed
        // twice (e.g. a retried request), fail clearly rather than silently
        // duplicate-recording the same real-world payment.
        if (Payment::where('gateway_reference', $gatewayReference)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'A payment with this external_reference has already been recorded.',
            ], 409);
        }

        // The exists() check above and this create() aren't atomic -- two
        // concurrent requests with the same external_reference can both
        // pass the check before either commits. gateway_reference's DB-level
        // unique constraint still prevents an actual duplicate row (no
        // double-charge/double-activation), but without this catch the
        // loser of that race got an unhandled 500 instead of the documented
        // clean 409 -- a reliability/correctness bug, not a financial one.
        try {
            $payment = Payment::create([
                'gateway_id' => $gateway->id,
                'customer_id' => $subscription->customer_id,
                'amount' => (float) $request->amount,
                'notification_status' => 'n/a',
                'gateway_reference' => $gatewayReference,
                'status' => 'pending', // enableSubscription() flips this to 'cleared' itself once matched
                'payment_reference' => $controlNumber->reference,
                'paid_at' => $request->input('paid_at', now()),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'A payment with this external_reference has already been recorded.',
                ], 409);
            }
            throw $e;
        }

        $this->subscriptionService->enableSubscription(
            $invoiceItem->invoice_id,
            $subscription,
            (float) $invoiceItem->total,
            $payment
        );

        $subscription->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded',
            'data' => [
                'payment_id' => $payment->id,
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
                'subscription_end_date' => $subscription->end_date,
            ],
        ], 201);
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
                    ->whereHas('customer', function ($customerQuery) use ($organizationId) {
                        $customerQuery->where('organization_id', $organizationId);
                    });
            })
            ->latest()
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
