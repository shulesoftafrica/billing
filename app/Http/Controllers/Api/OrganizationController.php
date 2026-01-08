<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = Organization::with(['currency', 'country'])->get();
        return response()->json([
            'success' => true,
            'data' => $organizations
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'country_id' => 'required|exists:countries,id',
            'timezone' => 'required|string|max:255',
            'status' => 'required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $organization = Organization::create($validator->validated());
        $organization->load(['currency', 'country']);

        return response()->json([
            'success' => true,
            'message' => 'Organization created successfully',
            'data' => $organization
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $organization = Organization::with(['currency', 'country'])->find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $organization
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'legal_name' => 'sometimes|required|string|max:255',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'country_id' => 'sometimes|required|exists:countries,id',
            'timezone' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $organization->update($validator->validated());
        $organization->load(['currency', 'country']);

        return response()->json([
            'success' => true,
            'message' => 'Organization updated successfully',
            'data' => $organization
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $organization = Organization::find($id);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }

        $organization->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully'
        ], 200);
    }
}
