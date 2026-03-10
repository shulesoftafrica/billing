<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PricePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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

        $subscriptionType = $request->input('subscription_type');

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('price_plans')->where(function ($query) use ($productId, $subscriptionType) {
                    $query->where('product_id', $productId);

                    if (is_null($subscriptionType)) {
                        $query->whereNull('subscription_type');
                    } else {
                        $query->where('subscription_type', $subscriptionType);
                    }

                    return $query;
                }),
            ],
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

        $incomingName = $request->input('name', $pricePlan->name);
        $incomingSubscriptionType = $request->has('subscription_type')
            ? $request->input('subscription_type')
            : $pricePlan->subscription_type;

        $validator = Validator::make($request->all(), [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('price_plans')->where(function ($query) use ($productId, $incomingSubscriptionType, $incomingName) {
                    $query->where('product_id', $productId)
                        ->where('name', $incomingName);

                    if (is_null($incomingSubscriptionType)) {
                        $query->whereNull('subscription_type');
                    } else {
                        $query->where('subscription_type', $incomingSubscriptionType);
                    }

                    return $query;
                })->ignore($pricePlan->id),
            ],
            'subscription_type' => 'nullable|in:daily,weekly,monthly,quarterly,semi_annually,yearly',
            'amount' => 'sometimes|required|numeric|min:0',
            'currency' => 'nullable|string|min:2|max:5',
            'rate' => 'nullable|integer|min:1',
        ]);

        if ($request->has('subscription_type') && !$request->has('name')) {
            $validator->after(function ($validator) use ($productId, $incomingName, $incomingSubscriptionType, $pricePlan) {
                $query = PricePlan::where('product_id', $productId)
                    ->where('name', $incomingName)
                    ->where('id', '!=', $pricePlan->id);

                if (is_null($incomingSubscriptionType)) {
                    $query->whereNull('subscription_type');
                } else {
                    $query->where('subscription_type', $incomingSubscriptionType);
                }

                if ($query->exists()) {
                    $validator->errors()->add('name', 'The name has already been taken for this product and subscription type.');
                }
            });
        }

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
