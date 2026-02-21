<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\UniqueConstraintViolationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get organization_id from authenticated user
        $organizationId = $request->user()->organization_id;

        $products = Product::with(['organization', 'productType', 'pricePlans'])
            ->where('organization_id', $organizationId)
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
        // Get organization_id from authenticated user
        $organizationId = $request->user()->organization_id;
        
        // Determine if price_plans is required based on product_type_id
        $productTypeId = $request->input('product_type_id');
        $pricePlansRule = $productTypeId == 1 ? 'nullable|array|max:1' : 'required|array|min:1';
        
        $validator = Validator::make($request->all(), [
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:255',
            'description' => 'required|string',
            'active' => 'required|boolean',
            'price_plans' => $pricePlansRule,
            'price_plans.*.name' => 'required|string|max:255',
            'price_plans.*.billing_type' => 'required|in:one_time,recurring,usage',
            'price_plans.*.billing_interval' => 'nullable|required_if:price_plans.*.billing_type,recurring|in:monthly,yearly',
            'price_plans.*.amount' => 'required|numeric|min:0',
            'price_plans.*.currency_code' => 'nullable|string|exists:currencies,code',
            'price_plans.*.currency_name' => 'nullable|string|exists:currencies,name',
            'price_plans.*.features' => 'nullable|array',
            'price_plans.*.active' => 'required|boolean',
        ]);

        // Custom validation: either currency_code or currency_name must be provided for each price plan
        $validator->after(function ($validator) use ($request) {
            $pricePlans = $request->input('price_plans', []);
            foreach ($pricePlans as $index => $plan) {
                if (empty($plan['currency_code']) && empty($plan['currency_name'])) {
                    $validator->errors()->add("price_plans.{$index}.currency", 'Either currency_code or currency_name is required.');
                }
                if (!empty($plan['currency_code']) && !empty($plan['currency_name'])) {
                    $validator->errors()->add("price_plans.{$index}.currency", 'Provide either currency_code or currency_name, not both.');
                }
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        
        // Add organization_id from authenticated user
        $validatedData['organization_id'] = $organizationId;
        
        // Extract price_plans from validated data
        $pricePlans = $validatedData['price_plans'] ?? [];
        unset($validatedData['price_plans']);
        
        try {
            // Create product
            $product = Product::create($validatedData);
            
            // Handle price plans
            if ($productTypeId == 1 && empty($pricePlans)) {
                // Create default price plan for product_type_id = 1 when no price plans provided
                $defaultCurrency = Currency::where('is_base_currency', true)->first() ?? Currency::first();
                $product->pricePlans()->create([
                    'name' => $validatedData['name'],
                    'billing_type' => 'one_time',
                    'billing_interval' => null,
                    'amount' => 0,
                    'currency_id' => $defaultCurrency->id,
                    'active' => true,
                ]);
            } else {
                // Create provided price plans
                foreach ($pricePlans as $plan) {
                    // Resolve currency_code or currency_name to currency_id
                    if (isset($plan['currency_code'])) {
                        $currency = Currency::where('code', $plan['currency_code'])->first();
                        if ($currency) {
                            $plan['currency_id'] = $currency->id;
                            unset($plan['currency_code']); // Remove currency_code as we now have currency_id
                        }
                    } elseif (isset($plan['currency_name'])) {
                        $currency = Currency::where('name', $plan['currency_name'])->first();
                        if ($currency) {
                            $plan['currency_id'] = $currency->id;
                            unset($plan['currency_name']); // Remove currency_name as we now have currency_id
                        }
                    }
                    
                    // Handle features as metadata
                    if (isset($plan['features'])) {
                        $plan['metadata'] = ['features' => $plan['features']];
                        unset($plan['features']); // Remove features as we now have metadata
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
            
        } catch (UniqueConstraintViolationException $e) {
            // Check which constraint was violated
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'products_org_name_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product name already exists for this organization',
                    'errors' => [
                        'name' => ['A product with this name already exists for the selected organization.']
                    ]
                ], 409);
            }
            
            if (str_contains($errorMessage, 'products_org_code_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product code already exists for this organization',
                    'errors' => [
                        'product_code' => ['A product with this product code already exists for the selected organization.']
                    ]
                ], 409);
            }
            
            // Fallback for other unique constraint violations
            return response()->json([
                'success' => false,
                'message' => 'Duplicate product detected',
                'errors' => [
                    'duplicate' => ['This product already exists for the selected organization.']
                ]
            ], 409);
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
            'data' => $product
        ], 200);
    }

    /**
     * Find product by product code for authenticated user's organization.
     */
    public function byCode(Request $request, string $productCode)
    {
        // Get organization_id from authenticated user
        $organizationId = $request->user()->organization_id;

        $product = Product::with([
            'organization', 
            'productType', 
            'pricePlans.currency'
        ])
            ->where('product_code', $productCode)
            ->where('organization_id', $organizationId)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Product not found with code: {$productCode}"
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
            'product_type_id' => 'sometimes|required|exists:product_types,id',
            'name' => 'sometimes|required|string|max:255',
            'product_code' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product->update($validator->validated());
            $product->load(['organization', 'productType']);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
            
        } catch (UniqueConstraintViolationException $e) {
            // Check which constraint was violated
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, 'products_org_name_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product name already exists for this organization',
                    'errors' => [
                        'name' => ['A product with this name already exists for the selected organization.']
                    ]
                ], 409);
            }
            
            if (str_contains($errorMessage, 'products_org_code_unique')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product code already exists for this organization',
                    'errors' => [
                        'product_code' => ['A product with this product code already exists for the selected organization.']
                    ]
                ], 409);
            }
            
            // Fallback for other unique constraint violations
            return response()->json([
                'success' => false,
                'message' => 'Duplicate product detected',
                'errors' => [
                    'duplicate' => ['This product already exists for the selected organization.']
                ]
            ], 409);
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

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
