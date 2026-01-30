<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUsage;
use App\Models\ProductPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductUsageController extends Controller
{
    /**
     * Create a product usage record
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            // Create product usage record
            $productUsage = ProductUsage::create([
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);

            // Load relationships
            $productUsage->load(['product', 'customer']);

            return response()->json([
                'success' => true,
                'message' => 'Product usage recorded successfully',
                'data' => $productUsage
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record product usage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage report per product for a customer
     *
     * @param int $customerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsageReportByCustomer(int $customerId)
    {
        try {
            // Verify customer exists
            $customer = Customer::findOrFail($customerId);

            // Get all unique products that have purchases or usages for this customer
            $productIds = ProductPurchase::where('customer_id', $customerId)
                ->pluck('product_id')
                ->merge(ProductUsage::where('customer_id', $customerId)->pluck('product_id'))
                ->unique()
                ->toArray();

            if (empty($productIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No usage data found for this customer',
                    'data' => [
                        'customer' => [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'email' => $customer->email,
                        ],
                        'usage_by_product' => []
                    ]
                ], 200);
            }

            // Get all products
            $products = Product::whereIn('id', $productIds)->get();

            // Build usage report per product
            $usageByProduct = [];
            foreach ($products as $product) {
                $totalPurchased = ProductPurchase::where('customer_id', $customerId)
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $totalUsed = ProductUsage::where('customer_id', $customerId)
                    ->where('product_id', $product->id)
                    ->sum('quantity');

                $balance = $totalPurchased - $totalUsed;

                $usageByProduct[] = [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'unit' => $product->unit,
                    ],
                    'usage' => [
                        'total_purchased' => $totalPurchased,
                        'total_used' => $totalUsed,
                        'balance' => $balance,
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Usage report retrieved successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                    'usage_by_product' => $usageByProduct
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve usage report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage balance for a customer and product
     * Balance = sum(product_purchase.quantity) - sum(product_usage.quantity)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customerId = $request->input('customer_id');
            $productId = $request->input('product_id');

            // Verify customer and product exist
            $customer = Customer::findOrFail($customerId);
            $product = Product::findOrFail($productId);

            // Calculate total purchased quantity
            $totalPurchased = ProductPurchase::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->sum('quantity');

            // Calculate total used quantity
            $totalUsed = ProductUsage::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->sum('quantity');

            // Calculate balance
            $balance = $totalPurchased - $totalUsed;

            return response()->json([
                'success' => true,
                'message' => 'Usage balance retrieved successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ],
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'unit' => $product->unit,
                    ],
                    'usage' => [
                        'total_purchased' => $totalPurchased,
                        'total_used' => $totalUsed,
                        'balance' => $balance,
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve usage balance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get usage history for a customer and product
     *
     * @param int $customerId
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(int $customerId, int $productId)
    {
        try {
            // Verify customer and product exist
            $customer = Customer::findOrFail($customerId);
            $product = Product::findOrFail($productId);

            // Get all purchases
            $purchases = ProductPurchase::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->latest()
                ->get();

            // Get all usages
            $usages = ProductUsage::where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->latest()
                ->get();

            // Calculate totals
            $totalPurchased = $purchases->sum('quantity');
            $totalUsed = $usages->sum('quantity');
            $balance = $totalPurchased - $totalUsed;

            return response()->json([
                'success' => true,
                'message' => 'Usage history retrieved successfully',
                'data' => [
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'customer_name' => $customer->name,
                    'product_name' => $product->name,
                    'total_purchased' => $totalPurchased,
                    'total_used' => $totalUsed,
                    'balance' => $balance,
                    'purchases' => $purchases,
                    'usages' => $usages,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve usage history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
