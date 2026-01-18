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
            'organization_id' => 'required|exists:organizations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customers = Customer::with(['organization', 'addresses'])
            ->where('organization_id', $request->organization_id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'external_ref' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:255',
            'status' => 'required|in:active,suspended',
            'addresses' => 'required|array|min:1',
            'addresses.*.type' => 'required|in:billing,shipping',
            'addresses.*.country' => 'required|string|max:255',
            'addresses.*.city' => 'required|string|max:255',
            'addresses.*.address_line' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();
        $addresses = $validatedData['addresses'];
        unset($validatedData['addresses']);

        $customer = Customer::create($validatedData);

        // Create addresses
        foreach ($addresses as $address) {
            $customer->addresses()->create($address);
        }

        $customer->load(['organization', 'addresses']);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::with(['organization', 'addresses'])->find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
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
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'external_ref' => 'nullable|string|max:255',
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255',
            'phone' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:active,suspended',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer->update($validator->validated());
        $customer->load(['organization', 'addresses']);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'data' => $customer
        ], 200);
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

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ], 200);
    }

    /**
     * Lookup customer by phone with full status
     * GET /api/customers/by-phone/{phone}/status
     */
    public function lookupByPhoneWithStatus(string $phone)
    {
        try {
            $customer = Customer::with([
                'organization',
                'addresses', 
                'subscriptions.pricePlan',
                'invoices' => function($query) {
                    $query->whereIn('status', ['issued', 'overdue']);
                },
                'walletTransactions' => function($query) {
                    $query->where('status', 'completed')
                          ->selectRaw('customer_id, wallet_type, SUM(units) as balance')
                          ->groupBy('customer_id', 'wallet_type');
                }
            ])
            ->where('phone', $phone)
            ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found with this phone number'
                ], 404);
            }

            // Calculate wallet balances
            $walletBalances = [];
            if ($customer->walletTransactions) {
                foreach ($customer->walletTransactions as $transaction) {
                    $walletBalances[$transaction->wallet_type] = (float) $transaction->balance;
                }
            }

            // Calculate outstanding balance
            $outstandingBalance = $customer->invoices->sum('total');

            // Count active subscriptions
            $activeSubscriptions = $customer->subscriptions->where('status', 'active')->count();

            $customerData = [
                'id' => $customer->id,
                'organization_id' => $customer->organization_id,
                'external_ref' => $customer->external_ref,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'status' => $customer->status,
                'wallet_balances' => $walletBalances,
                'active_subscriptions' => $activeSubscriptions,
                'total_invoices' => $customer->invoices()->count(),
                'outstanding_balance' => $outstandingBalance,
                'organization' => $customer->organization,
                'addresses' => $customer->addresses,
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

    /**
     * Lookup customer by email with full status  
     * GET /api/customers/by-email/{email}/status
     */
    public function lookupByEmailWithStatus(string $email)
    {
        try {
            $customer = Customer::with([
                'organization',
                'addresses',
                'subscriptions.pricePlan', 
                'invoices' => function($query) {
                    $query->whereIn('status', ['issued', 'overdue']);
                },
                'walletTransactions' => function($query) {
                    $query->where('status', 'completed')
                          ->selectRaw('customer_id, wallet_type, SUM(units) as balance')
                          ->groupBy('customer_id', 'wallet_type');
                }
            ])
            ->where('email', $email)
            ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found with this email address'
                ], 404);
            }

            // Calculate wallet balances
            $walletBalances = [];
            if ($customer->walletTransactions) {
                foreach ($customer->walletTransactions as $transaction) {
                    $walletBalances[$transaction->wallet_type] = (float) $transaction->balance;
                }
            }

            // Calculate outstanding balance
            $outstandingBalance = $customer->invoices->sum('total');

            // Count active subscriptions
            $activeSubscriptions = $customer->subscriptions->where('status', 'active')->count();

            $customerData = [
                'id' => $customer->id,
                'organization_id' => $customer->organization_id,
                'external_ref' => $customer->external_ref,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'customer_type' => $customer->customer_type,
                'status' => $customer->status,
                'wallet_balances' => $walletBalances,
                'active_subscriptions' => $activeSubscriptions,
                'total_invoices' => $customer->invoices()->count(),
                'outstanding_balance' => $outstandingBalance,
                'organization' => $customer->organization,
                'addresses' => $customer->addresses,
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
