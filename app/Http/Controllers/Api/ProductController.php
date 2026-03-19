<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Helper method to find product by ID (numeric) or product_code (string)
     */
    private function findProduct(string $identifier)
    {
        return Product::where(function ($query) use ($identifier) {
            if (is_numeric($identifier)) {
                $query->where('id', $identifier);
            }
            $query->orWhere('product_code', $identifier);
        })->first();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'product_type' => 'integer|exists:product_types,id',
            'active' => 'sometimes|boolean',
        ]);
        $product_type = $request->product_type ?? null;
        $name = $request->name ?? null;
        $active = $request->active ?? null;

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
        if ($name) {
            $productQuery->where('name', $name);
        }
        if ($active !== null) {
            $productQuery->where('active', $active);
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
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'product_type_id' => 'required|exists:product_types,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                }),
            ],
            'product_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                }),
            ],
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
            'price_plans' => $pricePlansRule,
            'price_plans.*.name' => 'required|string|max:255',
            'price_plans.*.subscription_type' => $subscriptionTypeRule,
            'price_plans.*.amount' => 'required|numeric|min:0',
            'price_plans.*.currency_id' => 'required|integer|exists:currencies,id',
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
                // Get default currency_id (use first currency or id=1)
                $defaultCurrencyId = \DB::table('currencies')->value('id') ?? 1;
                
                $product->pricePlans()->create([
                    'name' => $validatedData['name'],
                    'billing_type' => 'one_time',
                    'billing_interval' => null,
                    'subscription_type' => null,
                    'amount' => 0,
                    'currency_id' => $defaultCurrencyId,
                ]);
            } else {
                // Create provided price plans
                foreach ($pricePlans as $plan) {
                    // Ensure amount is set (required field)
                    if (!isset($plan['amount'])) {
                        $plan['amount'] = 0;
                    }
                    
                    // Map product_type_id to billing_type
                    if ($productTypeId == 1) {
                        $plan['billing_type'] = 'one_time';
                        $plan['billing_interval'] = null;
                    } elseif ($productTypeId == 2) {
                        $plan['billing_type'] = 'recurring';
                        // Map subscription_type to billing_interval
                        $plan['billing_interval'] = $plan['subscription_type'] ?? null;
                    } elseif ($productTypeId == 3) {
                        $plan['billing_type'] = 'usage';
                        $plan['billing_interval'] = null;
                        // Set default rate if not provided for product_type_id = 3
                        if (!isset($plan['rate'])) {
                            $plan['rate'] = 1;
                        }
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
        // Find product by ID (if numeric) or by product_code (if string)
        $product = $this->findProduct($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // Load relationships
        $product->load(['organization', 'productType', 'pricePlans']);

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
        // Find product by ID or product_code
        $product = $this->findProduct($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'product_type_id' => 'sometimes|required|exists:product_types,id',
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request, $product) {
                    $organizationId = $request->input('organization_id', $product->organization_id);

                    return $query->where('organization_id', $organizationId);
                })->ignore($product->id),
            ],
            'product_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products')->where(function ($query) use ($request, $product) {
                    $organizationId = $request->input('organization_id', $product->organization_id);

                    return $query->where('organization_id', $organizationId);
                })->ignore($product->id),
            ],
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:255',
            'active' => 'sometimes|boolean',
        ]);

        if ($request->has('organization_id') && !$request->has('name')) {
            $validator->after(function ($validator) use ($request, $product) {
                $exists = Product::where('organization_id', $request->organization_id)
                    ->where('name', $product->name)
                    ->where('id', '!=', $product->id)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('name', 'The name has already been taken for this organization.');
                }
            });
        }

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
        // Find product by ID or product_code
        $product = $this->findProduct($id);

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
