<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = PaymentGateway::all();

        return response()->json([
            'success' => true,
            'data' => $gateways
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:card,bank,mobile_money,control_number',
            'webhook_secret' => 'required|string|max:255',
            'config' => 'nullable|array',
            'active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $gateway = PaymentGateway::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway created successfully',
            'data' => $gateway
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $gateway = PaymentGateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $gateway
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $gateway = PaymentGateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:card,bank,mobile_money',
            'webhook_secret' => 'sometimes|required|string|max:255',
            'config' => 'nullable|array',
            'active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $gateway->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway updated successfully',
            'data' => $gateway
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $gateway = PaymentGateway::find($id);

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not found'
            ], 404);
        }

        $gateway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment gateway deleted successfully'
        ], 200);
    }
}
