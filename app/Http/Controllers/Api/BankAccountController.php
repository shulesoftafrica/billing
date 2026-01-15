<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
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

        $bankAccounts = BankAccount::with('organization')
            ->where('organization_id', $request->organization_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bankAccounts
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'refer_bank_id' => 'required|integer',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bankAccount = BankAccount::create($validator->validated());
        $bankAccount->load('organization');

        return response()->json([
            'success' => true,
            'message' => 'Bank account created successfully',
            'data' => $bankAccount
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bankAccount = BankAccount::with('organization')->find($id);

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $bankAccount
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bankAccount = BankAccount::find($id);

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'account_number' => 'sometimes|required|string|max:255',
            'branch' => 'sometimes|required|string|max:255',
            'refer_bank_id' => 'sometimes|required|integer',
            'organization_id' => 'sometimes|required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $bankAccount->update($validator->validated());
        $bankAccount->load('organization');

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully',
            'data' => $bankAccount
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::find($id);

        if (!$bankAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Bank account not found'
            ], 404);
        }

        $bankAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully'
        ], 200);
    }
}
