<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'product_type' => 'integer|exists:product_types,id',
        ]);
        $product_type = $request->product_type ?? null;

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $productQuery = Product::with(['organization', 'productType', 'pricePlans'])
            ->where('organization_id', $request->organization_id);
        if ($product_type) {
            $productQuery->where('product_type_id', $product_type);
        }
        $products = $productQuery->get();

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Determine rules based on product_type_id
        $productTypeId = $request->input('product_type_id');

        // For product_type_id = 1: price_plans optional (max 1), subscription_type not allowed
        // For product_type_id = 3: price_plans mandatory, subscription_type optional
        // For other product_type_id: price_plans mandatory, array with min 1, subscription_type required
        $pricePlansRule = $productTypeId == 1 ? 'nullable|array|max:1' : 'required|array|min:1';

        // Valid subscription types
        $validSubscriptionTypes = ['daily', 'weekly', 'monthly', 'quarterly', 'semi_annually', 'yearly'];
        $subscriptionTypeRule = $productTypeId == 1
            ? 'nullable|string|max:255'
            : ($productTypeId == 3
                ? 'nullable|in:' . implode(',', $validSubscriptionTypes)
                : 'required_if:price_plans,!=null|in:' . implode(',', $validSubscriptionTypes));

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,archived',
            'price_plans' => $pricePlansRule,
            'price_plans.*.name' => 'required_if:price_plans,!=null|string|max:255',
            'price_plans.*.subscription_type' => $subscriptionTypeRule,
            'price_plans.*.amount' => 'required_if:price_plans,!=null|numeric|min:0',
            'price_plans.*.currency' => 'required_if:price_plans,!=null|string|min:2|max:5',
            'price_plans.*.rate' => 'nullable|integer|min:1',
        ]);

        // Additional validation: subscription_type not allowed for product_type_id = 1
        if ($productTypeId == 1 && $request->has('price_plans') && is_array($request->input('price_plans'))) {
            foreach ($request->input('price_plans') as $idx => $plan) {
                if (isset($plan['subscription_type']) && !empty($plan['subscription_type'])) {
                    $validator->after(function ($validator) use ($idx) {
                        $validator->errors()->add(
                            "price_plans.{$idx}.subscription_type",
                            'subscription_type is not allowed for product_type_id = 1'
                        );
                    });
                }
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $pricePlans = $validatedData['price_plans'] ?? [];
            unset($validatedData['price_plans']);

            // Create product
            $product = Product::create($validatedData);

            // Handle price plans
            if ($productTypeId == 1 && empty($pricePlans)) {
                // Create default price plan for product_type_id = 1 when no price plans provided
                $product->pricePlans()->create([
                    'name' => $validatedData['name'],
                    'subscription_type' => null,
                    'amount' => 0,
                    'currency' => 'TZS', // Default currency
                ]);
            } else {
                // Create provided price plans
                foreach ($pricePlans as $plan) {
                    // Set default rate if not provided for product_type_id = 3
                    if ($productTypeId == 3 && !isset($plan['rate'])) {
                        $plan['rate'] = 1;
                    }
                    $product->pricePlans()->create($plan);
                }
            }

            $product->load(['organization', 'productType', 'pricePlans']);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['organization', 'productType', 'pricePlans'])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'product_type_id' => 'sometimes|required|exists:product_types,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:active,inactive,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product->update($validator->validated());
            $product->load(['organization', 'productType', 'pricePlans']);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        try {
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
