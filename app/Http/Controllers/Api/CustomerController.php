<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'username' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Customer::with('organization')
            ->where('organization_id', $request->organization_id);

        if ($request->filled('username')) {
            $query->where('username', $request->username);
        }

        $customers = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $customers
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:customers,username,NULL,id,organization_id,' . $request->organization_id,
            'email' => 'nullable|string|email|max:255|unique:customers,email,NULL,id,organization_id,' . $request->organization_id,
            'phone' => 'nullable|string|max:255|unique:customers,phone,NULL,id,organization_id,' . $request->organization_id,
            'status' => 'required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::create($validator->validated());
            $customer->load('organization');

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::with('organization')->find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer retrieved successfully',
            'data' => $customer
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'sometimes|exists:organizations,id', // Optional - auto-injected from token by middleware
            'name' => 'sometimes|required|string|max:255',
            'username' => 'nullable|string|max:255|unique:customers,username,' . $id . ',id,organization_id,' . $customer->organization_id,
            'email' => 'nullable|string|email|max:255|unique:customers,email,' . $id . ',id,organization_id,' . $customer->organization_id,
            'phone' => 'nullable|string|max:255|unique:customers,phone,' . $id . ',id,organization_id,' . $customer->organization_id,
            'status' => 'sometimes|required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer->update($validator->validated());
            $customer->load('organization');

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function lookupByPhoneWithStatus(string $phone)
    {
        return $this->getCustomerData('phone', $phone);
    }

    public function lookupByEmailWithStatus(string $email)
    {
        return $this->getCustomerData('email', $email);
    }
    public function getCustomerData($column, $value)
    {
        try {
            $customer = Customer::with([
                'organization',
                'subscriptions.pricePlan',
                'invoices' => function ($query) {
                    $query->where('status', '!=', 'cancelled')
                        ->with('payments');
                }
            ])
                ->where($column, $value)
                ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found with this email address'
                ], 404);
            }

            $totalInvoicedAmount = (float) $customer->invoices->sum('total');
            $totalPaidAmount = (float) $customer->invoices->sum(function ($invoice) {
                return $invoice->payments->sum('pivot.amount');
            });
            $totalBalanceAmount = $totalInvoicedAmount - $totalPaidAmount;
            $outstandingBalance = $totalBalanceAmount;

            // Count active subscriptions
            $activeSubscriptions = $customer->subscriptions->where('status', 'active')->count();

            $customerData = [
                'id' => $customer->id,
                'organization_id' => $customer->organization_id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'status' => $customer->status,
                'wallet_balances' => [],
                'active_subscriptions' => $activeSubscriptions,
                'total_invoices' => $customer->invoices()->count(),
                'total_invoiced_amount' => $totalInvoicedAmount,
                'total_paid_amount' => $totalPaidAmount,
                'total_balance_amount' => $totalBalanceAmount,
                'outstanding_balance' => $outstandingBalance,
                'organization' => $customer->organization,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at
            ];

            return response()->json([
                'success' => true,
                'customer' => $customerData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to lookup customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
