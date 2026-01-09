<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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

            return response()->json([
                'success' => true,
                'message' => 'Subscriptions created successfully',
                'data' => [
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'status' => $invoice->status,
                        'subtotal' => $invoice->subtotal,
                        'tax_total' => $invoice->tax_total,
                        'total' => $invoice->total,
                        'due_date' => $invoice->due_date,
                        'issued_at' => $invoice->issued_at,
                    ],
                    'invoice_items' => $invoice->invoiceItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'price_plan_id' => $item->price_plan_id,
                            'plan_name' => $item->pricePlan->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total' => $item->total,
                        ];
                    }),
                    'customer' => [
                        'id' => $invoice->customer->id,
                        'name' => $invoice->customer->name,
                        'email' => $invoice->customer->email,
                    ],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
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
            $filters = [];
            
            // Optional filters
            if ($request->has('status')) {
                $filters['status'] = $request->input('status');
            }
            
            if ($request->has('customer_id')) {
                $filters['customer_id'] = $request->input('customer_id');
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
            $status = $request->input('status');
            
            $subscriptions = $this->subscriptionService->getCustomerSubscriptions($customerId, $status);

            return response()->json([
                'success' => true,
                'data' => $subscriptions->map(function ($subscription) {
                    return [
                        'id' => $subscription->id,
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
}
