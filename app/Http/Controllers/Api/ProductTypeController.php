<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = ProductType::all();
        return response()->json([
            'success' => true,
            'data' => $productTypes
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_types,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productType = ProductType::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product type created successfully',
            'data' => $productType
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productType = ProductType::find($id);

        if (!$productType) {
            return response()->json([
                'success' => false,
                'message' => 'Product type not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $productType
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productType = ProductType::find($id);

        if (!$productType) {
            return response()->json([
                'success' => false,
                'message' => 'Product type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:product_types,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $productType->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product type updated successfully',
            'data' => $productType
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productType = ProductType::find($id);

        if (!$productType) {
            return response()->json([
                'success' => false,
                'message' => 'Product type not found'
            ], 404);
        }

        $productType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product type deleted successfully'
        ], 200);
    }
}
