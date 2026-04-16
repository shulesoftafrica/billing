<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\Product;
use App\Models\PricePlan;
use App\Jobs\Payments\CreateEcobankReferenceJob;
use App\Jobs\Payments\CreateFlutterwaveReferenceJob;
use App\Jobs\Payments\CreateStripeReferenceJob;
use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Create subscriptions for a customer with multiple price plans
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer|exists:customers,id',
            'plan_ids' => 'required|array|min:1',
            'plan_ids.*' => 'required|integer|exists:price_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Remove duplicate plan IDs
            $planIds = array_unique($request->input('plan_ids'));

            $invoice = $this->subscriptionService->createSubscriptionsWithInvoice(
                $request->input('customer_id'),
                $planIds
            );

            // Get organization ID from the first price plan
            $firstPlan = PricePlan::with('product')->find($planIds[0]);
            $organizationId = $firstPlan->product->organization_id;
            $customer = $invoice->customer;

            // Get all organization integrated gateways
            $organizationGateways = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                ->where('organization_id', $organizationId)
                ->get();

            // Get all products associated with the price plans
            $products = Product::whereIn(
                'id',
                PricePlan::whereIn('id', $planIds)->pluck('product_id')
            )->get();

            // Process payment gateway references synchronously
            $gatewayJobs = [];
            foreach ($products as $product) {
                foreach ($organizationGateways as $orgGateway) {
                    $gatewayName = strtolower(trim((string) $orgGateway->paymentGateway->name));

                    if (
                        $gatewayName === 'universal control number'
                        || $gatewayName === 'flutterwave'
                        || $gatewayName === 'stripe'
                    ) {
                        $gatewayJobs[] = [
                            'product_id' => $product->id,
                            'organization_gateway_id' => $orgGateway->id,
                            'gateway_name' => $gatewayName,
                        ];
                    }
                }
            }

            // Create payment references synchronously to include URLs in response
            $invoiceController = app(InvoiceController::class);
            if (count($gatewayJobs) > 0) {
                foreach ($gatewayJobs as $jobData) {
                    $successUrl = $request->input('success_url') ?: config('app.url') . '/payment/callback';
                    $product = Product::find($jobData['product_id']);
                    $orgGateway = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
                        ->find($jobData['organization_gateway_id']);

                    if (!$product || !$orgGateway) {
                        continue;
                    }

                    $mockRequest = Request::create('/', 'POST', [
                        'success_url' => $successUrl,
                        'redirect_url' => $successUrl,
                        'customizations' => $request->input('customizations', []),
                        'meta' => $request->input('meta', []),
                    ]);

                    if ($jobData['gateway_name'] === 'universal control number') {
                        $invoiceController->createControlNumber(
                            $orgGateway->merchants->first(),
                            $product,
                            $customer,
                            $orgGateway
                        );
                    } elseif ($jobData['gateway_name'] === 'flutterwave') {
                        $invoiceController->createFlutterWaveReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                    } elseif ($jobData['gateway_name'] === 'stripe') {
                        $invoiceController->createStripeReference($invoice, $product, $customer, $mockRequest, $orgGateway);
                    }
                }
            }

            // Load full relationships for comprehensive response including newly created payment references
            $invoice->load([
                'customer',
                'invoiceItems.pricePlan.product',
                'invoiceItems.subscription',
            ]);

            // Build control numbers map
            $controlNumbersMap = $this->buildControlNumbersMap($customer->id, $invoice->id);

            // Build comprehensive response
            $responseData = [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'currency' => $invoice->currency,
                    'subtotal' => $invoice->subtotal,
                    'tax_total' => $invoice->tax_total,
                    'total' => $invoice->total,
                    'due_date' => $invoice->due_date,
                    'issued_at' => $invoice->issued_at,
                ],
                'customer' => [
                    'id' => $invoice->customer->id,
                    'name' => $invoice->customer->name,
                    'email' => $invoice->customer->email,
                    'phone' => $invoice->customer->phone,
                ],
                'subscriptions' => $invoice->invoiceItems
                    ->filter(fn($item) => $item->subscription !== null)
                    ->map(function ($item) {
                        $subscription = $item->subscription;
                        return [
                            'id' => $subscription->id,
                            'price_plan_id' => $item->price_plan_id,
                            'plan_name' => $item->pricePlan->name,
                            'product_name' => $item->pricePlan->product->name,
                            'status' => $subscription->status,
                            'start_date' => $subscription->start_date,
                            'end_date' => $subscription->end_date,
                            'next_billing_date' => $subscription->next_billing_date,
                            'amount' => $item->unit_price,
                        ];
                    })->values(),
                'control_numbers' => $controlNumbersMap,
                'payment_message' => count($controlNumbersMap) > 0
                    ? 'Control numbers and payment links are being generated. You will receive them shortly.'
                    : 'Payment gateway is not configured for this organization.',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Subscriptions created successfully',
                'data' => $responseData,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Subscription creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Build control numbers map for customer and invoice products
     */
    private function buildControlNumbersMap(int $customerId, int $invoiceId): array
    {
        // Get product IDs from the invoice
        $invoice = \App\Models\Invoice::with('invoiceItems.pricePlan')->find($invoiceId);
        if (!$invoice) {
            return [];
        }

        $productIds = $invoice->invoiceItems
            ->pluck('pricePlan.product_id')
            ->filter()
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return [];
        }

        // Query control numbers by customer and products
        $controlNumbers = \App\Models\ControlNumber::where('customer_id', $customerId)
            ->whereIn('product_id', $productIds->toArray())
            ->with('paymentGatewayIntegration.paymentGateway')
            ->get();

        return $controlNumbers->map(function ($cn) {
            return [
                'reference' => $cn->reference,
                'payment_link' => $cn->metadata['payment_link'] ?? null,
                'gateway' => $cn->paymentGatewayIntegration->paymentGateway->name ?? 'Unknown',
                'expires_at' => $cn->expires_at,
            ];
        })->toArray();
    }

    /**
     * Get all subscriptions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Require customer email for security
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'customer_email' => 'nullable|email',
                'status' => 'nullable|string|in:pending,active,cancelled,expired',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = [
                'customer_email' => $request->input('customer_email')
            ];

            // Optional status filter
            if ($request->has('status')) {
                $filters['status'] = $request->input('status');
            }

            $subscriptions = $this->subscriptionService->getAllSubscriptions($filters);

            return response()->json([
                'success' => true,
                'data' => $subscriptions->map(function ($subscription) {
                    return [
                        'id' => $subscription->id,
                        'customer' => [
                            'id' => $subscription->customer->id,
                            'name' => $subscription->customer->name,
                            'email' => $subscription->customer->email,
                        ],
                        'price_plan' => [
                            'id' => $subscription->pricePlan->id,
                            'name' => $subscription->pricePlan->name,
                            'amount' => $subscription->pricePlan->amount,
                            'billing_interval' => $subscription->pricePlan->billing_interval,
                            'product' => [
                                'id' => $subscription->pricePlan->product->id,
                                'name' => $subscription->pricePlan->product->name,
                            ],
                        ],
                        'status' => $subscription->status,
                        'start_date' => $subscription->start_date,
                        'end_date' => $subscription->end_date,
                        'next_billing_date' => $subscription->next_billing_date,
                        'created_at' => $subscription->created_at,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get subscriptions for a specific customer
     *
     * @param int $customerId
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomerSubscriptions(int $customerId, Request $request): JsonResponse
    {
        try {
            Log::info('requst', $request->all());
            $status = $request->input('status');
            $product_id = $request->input('product_id');
            Log::info("Fetching subscriptions for customer_id: $customerId with status: $status and product_id: $product_id");

            $subscriptions = $this->subscriptionService->getCustomerSubscriptions($customerId, $status, $product_id);

            return response()->json([
                'success' => true,
                'data' => $subscriptions->map(function ($subscription) {
                    $invoiceItem = $subscription->invoice_item;
                    $invoice = $invoiceItem?->invoice;
                    $invoiceController = app(InvoiceController::class);
                     $controlNumbersMap = $invoiceController->buildControlNumbersMap(collect([$invoice]));
                    $formatedInvoice = $invoiceController->formatInvoiceDetailResponse($invoice, $controlNumbersMap, false, false, true);
                    return [
                        'id' => $subscription->id,
                        'status' => $subscription->status,
                        'start_date' => $subscription->start_date,
                        'end_date' => $subscription->end_date,
                        'next_billing_date' => $subscription->next_billing_date,
                        'created_at' => $subscription->created_at,
                        'price_plan' => [
                            'id' => $subscription->pricePlan->id,
                            'name' => $subscription->pricePlan->name,
                            'amount' => $invoiceItem?->total ?? $subscription->pricePlan->amount,
                            'billing_interval' => $subscription->pricePlan->billing_interval,
                            'product' => [
                                'id' => $subscription->pricePlan->product->id,
                                'name' => $subscription->pricePlan->product->name,
                            ],
                        ],
                        'invoice' => $formatedInvoice ? $formatedInvoice : null,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a subscription
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $subscription = $this->subscriptionService->cancelSubscription($id);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => [
                    'id' => $subscription->id,
                    'customer' => [
                        'id' => $subscription->customer->id,
                        'name' => $subscription->customer->name,
                        'email' => $subscription->customer->email,
                    ],
                    'price_plan' => [
                        'id' => $subscription->pricePlan->id,
                        'name' => $subscription->pricePlan->name,
                    ],
                    'status' => $subscription->status,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get subscription details by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $subscription = \App\Models\Subscription::with([
                'customer',
                'pricePlan.product',
                'invoices' => function ($query) {
                    $query->orderBy('created_at', 'desc')->take(5);
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => [
                        'id' => $subscription->id,
                        'subscription_number' => $subscription->subscription_number,
                        'status' => $subscription->status,
                        'start_date' => $subscription->start_date,
                        'end_date' => $subscription->end_date,
                        'next_billing_date' => $subscription->next_billing_date,
                        'trial_ends_at' => $subscription->trial_ends_at,
                        'canceled_at' => $subscription->canceled_at,
                        'pause_starts_at' => $subscription->pause_starts_at,
                        'pause_ends_at' => $subscription->pause_ends_at,
                        'created_at' => $subscription->created_at,
                        'updated_at' => $subscription->updated_at,
                    ],
                    'customer' => [
                        'id' => $subscription->customer->id,
                        'name' => $subscription->customer->name,
                        'email' => $subscription->customer->email,
                        'phone' => $subscription->customer->phone,
                        'customer_type' => $subscription->customer->customer_type,
                    ],
                    'price_plan' => [
                        'id' => $subscription->pricePlan->id,
                        'name' => $subscription->pricePlan->name,
                        'description' => $subscription->pricePlan->description,
                        'amount' => $subscription->pricePlan->amount,
                        'billing_interval' => $subscription->pricePlan->billing_interval,
                        'trial_period_days' => $subscription->pricePlan->trial_period_days,
                        'metadata' => $subscription->pricePlan->metadata,
                        'product' => [
                            'id' => $subscription->pricePlan->product->id,
                            'name' => $subscription->pricePlan->product->name,
                            'product_code' => $subscription->pricePlan->product->product_code,
                            'description' => $subscription->pricePlan->product->description,
                        ],
                    ],
                    'recent_invoices' => $subscription->invoices->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'invoice_number' => $invoice->invoice_number,
                            'status' => $invoice->status,
                            'total' => $invoice->total,
                            'due_date' => $invoice->due_date,
                            'issued_at' => $invoice->issued_at,
                        ];
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
