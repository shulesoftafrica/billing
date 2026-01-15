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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $products = Product::with(['organization', 'productType', 'pricePlans'])
            ->where('organization_id', $request->organization_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Determine if price_plans is required based on product_type_id
        $productTypeId = $request->input('product_type_id');
        $pricePlansRule = $productTypeId == 1 ? 'nullable|array|max:1' : 'required|array|min:1';
        
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'active' => 'required|boolean',
            'price_plans' => $pricePlansRule,
            'price_plans.*.name' => 'required|string|max:255',
            'price_plans.*.billing_type' => 'required|in:one_time,recurring,usage',
            'price_plans.*.billing_interval' => 'nullable|required_if:price_plans.*.billing_type,recurring|in:monthly,yearly',
            'price_plans.*.amount' => 'required|numeric|min:0',
            'price_plans.*.currency_id' => 'required|exists:currencies,id',
            'price_plans.*.active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        
        // Extract price_plans from validated data
        $pricePlans = $validatedData['price_plans'] ?? [];
        unset($validatedData['price_plans']);
        
        // Create product
        $product = Product::create($validatedData);
        
        // Handle price plans
        if ($productTypeId == 1 && empty($pricePlans)) {
            // Create default price plan for product_type_id = 1 when no price plans provided
            $product->pricePlans()->create([
                'name' => $validatedData['name'],
                'billing_type' => 'one_time',
                'billing_interval' => null,
                'amount' => 0,
                'currency_id' => $request->input('currency_id', 1), // Default to currency_id 1 if not provided
                'active' => true,
            ]);
        } else {
            // Create provided price plans
            foreach ($pricePlans as $plan) {
                $product->pricePlans()->create($plan);
            }
        }
        
        $product->load(['organization', 'productType', 'pricePlans']);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
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
            'description' => 'sometimes|required|string',
            'active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($validator->validated());
        $product->load(['organization', 'productType']);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ], 200);
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

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
