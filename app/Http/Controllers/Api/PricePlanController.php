<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PricePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $pricePlans = $product->pricePlans()->with('currency')->get();

        return response()->json([
            'success' => true,
            'data' => $pricePlans
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'billing_type' => 'required|in:one_time,recurring,usage',
            'billing_interval' => 'required_if:billing_type,recurring|in:monthly,yearly',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pricePlan = $product->pricePlans()->create($validator->validated());
        $pricePlan->load('currency');

        return response()->json([
            'success' => true,
            'message' => 'Price plan created successfully',
            'data' => $pricePlan
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $productId, string $id)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $pricePlan = $product->pricePlans()->find($id);

        if (!$pricePlan) {
            return response()->json([
                'success' => false,
                'message' => 'Price plan not found'
            ], 404);
        }

        $pricePlan->load('currency');

        return response()->json([
            'success' => true,
            'data' => $pricePlan
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $productId, string $id)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $pricePlan = $product->pricePlans()->find($id);

        if (!$pricePlan) {
            return response()->json([
                'success' => false,
                'message' => 'Price plan not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'billing_type' => 'sometimes|required|in:one_time,recurring,usage',
            'billing_interval' => 'sometimes|required_if:billing_type,recurring|in:monthly,yearly',
            'amount' => 'sometimes|required|numeric|min:0',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $pricePlan->update($validator->validated());
        $pricePlan->load('currency');

        return response()->json([
            'success' => true,
            'message' => 'Price plan updated successfully',
            'data' => $pricePlan
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $productId, string $id)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $pricePlan = $product->pricePlans()->find($id);

        if (!$pricePlan) {
            return response()->json([
                'success' => false,
                'message' => 'Price plan not found'
            ], 404);
        }

        $pricePlan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Price plan deleted successfully'
        ], 200);
    }
}
