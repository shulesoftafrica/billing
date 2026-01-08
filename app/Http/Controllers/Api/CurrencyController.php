<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::all();
        return response()->json([
            'success' => true,
            'data' => $currencies
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:3|unique:currencies,code',
            'symbol' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $currency = Currency::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Currency created successfully',
            'data' => $currency
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currency
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|size:3|unique:currencies,code,' . $id,
            'symbol' => 'sometimes|required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $currency->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Currency updated successfully',
            'data' => $currency
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }

        $currency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Currency deleted successfully'
        ], 200);
    }
}
