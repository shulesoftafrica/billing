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

        $pricePlans = $product->pricePlans()->get();

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
            'subscription_type' => 'nullable|in:daily,weekly,monthly,quarterly,semi_annually,yearly',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|min:2|max:5',
            'rate' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $pricePlan = $product->pricePlans()->create([
            'name' => $validated['name'],
            'subscription_type' => $validated['subscription_type'] ?? null,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'TZS',
            'rate' => $validated['rate'] ?? 1,
        ]);

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
            'subscription_type' => 'nullable|in:daily,weekly,monthly,quarterly,semi_annually,yearly',
            'amount' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|min:2|max:5',
            'rate' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Prepare update data with defaults for optional fields
        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['subscription_type'])) $updateData['subscription_type'] = $validated['subscription_type'];
        if (isset($validated['amount'])) $updateData['amount'] = $validated['amount'];
        if (isset($validated['currency'])) $updateData['currency'] = $validated['currency'];
        if (isset($validated['rate'])) $updateData['rate'] = $validated['rate'];

        $pricePlan->update($updateData);

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
