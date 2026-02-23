<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxRateController extends Controller
{
    /**
     * Display a listing of tax rates.
     */
    public function index(Request $request)
    {
        $query = TaxRate::query();

        if ($request->has('active')) {
            $query->where('active', filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('country')) {
            $query->where('country', strtoupper($request->input('country')));
        }

        $taxRates = $query->orderBy('country')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $taxRates,
        ], 200);
    }

    /**
     * Store a newly created tax rate.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required|string|min:2',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $payload['country'] = strtoupper($payload['country']);

        $taxRate = TaxRate::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate created successfully',
            'data' => $taxRate,
        ], 201);
    }

    /**
     * Display the specified tax rate.
     */
    public function show(string $id)
    {
        $taxRate = TaxRate::find($id);

        if (!$taxRate) {
            return response()->json([
                'success' => false,
                'message' => 'Tax rate not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $taxRate,
        ], 200);
    }

    /**
     * Update the specified tax rate.
     */
    public function update(Request $request, string $id)
    {
        $taxRate = TaxRate::find($id);

        if (!$taxRate) {
            return response()->json([
                'success' => false,
                'message' => 'Tax rate not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'country' => 'sometimes|required|string|min:2',
            'name' => 'sometimes|required|string|max:255',
            'rate' => 'sometimes|required|numeric|min:0|max:100',
            'active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        if (array_key_exists('country', $payload)) {
            $payload['country'] = strtoupper($payload['country']);
        }

        $taxRate->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate updated successfully',
            'data' => $taxRate,
        ], 200);
    }

    /**
     * Remove the specified tax rate.
     */
    public function destroy(string $id)
    {
        $taxRate = TaxRate::find($id);

        if (!$taxRate) {
            return response()->json([
                'success' => false,
                'message' => 'Tax rate not found',
            ], 404);
        }

        try {
            $taxRate->delete();
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Tax rate cannot be deleted because it is used by invoices',
            ], 409);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tax rate deleted successfully',
        ], 200);
    }
}
