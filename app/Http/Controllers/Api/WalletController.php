<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get customer wallet balances
     * GET /api/wallets/balance?customer_id=1&wallet_type=credits
     */
    public function getBalance(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'wallet_type' => 'nullable|string|max:50'
        ]);

        $customerId = $request->customer_id;
        $walletType = $request->wallet_type;

        $balance = $this->walletService->getBalance($customerId, $walletType);

        return response()->json([
            'success' => true,
            'customer_id' => $customerId,
            'wallet_type' => $walletType,
            'balance' => $balance
        ]);
    }

    /**
     * Add credits to wallet
     * POST /api/wallets/credit
     */
    public function addCredits(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'wallet_type' => 'required|string|max:50',
            'units' => 'required|numeric|min:0.0001',
            'unit_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'invoice_id' => 'nullable|integer|exists:invoices,id'
        ]);

        $result = $this->walletService->addCredits(
            $request->customer_id,
            $request->wallet_type,
            $request->units,
            $request->description,
            $request->invoice_id,
            $request->unit_price
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Deduct credits from wallet
     * POST /api/wallets/deduct
     */
    public function deductCredits(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'wallet_type' => 'required|string|max:50',
            'units' => 'required|numeric|min:0.0001',
            'description' => 'nullable|string|max:500'
        ]);

        $result = $this->walletService->deductCredits(
            $request->customer_id,
            $request->wallet_type,
            $request->units,
            $request->description
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Get wallet transaction history
     * GET /api/wallets/{customer_id}/transactions
     */
    public function getTransactionHistory(Request $request, int $customerId): JsonResponse
    {
        $request->validate([
            'wallet_type' => 'nullable|string|max:50',
            'transaction_type' => 'nullable|string|in:topup,deduction,transfer,refund',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        // Verify customer exists
        if (!\App\Models\Customer::find($customerId)) {
            return response()->json([
                'success' => false,
                'error' => 'customer_not_found',
                'message' => 'Customer not found'
            ], 404);
        }

        $filters = [
            'wallet_type' => $request->wallet_type,
            'transaction_type' => $request->transaction_type,
            'limit' => $request->limit ?? 50
        ];

        $transactions = $this->walletService->getTransactionHistory($customerId, $filters);

        return response()->json([
            'success' => true,
            'customer_id' => $customerId,
            'transactions' => $transactions,
            'count' => $transactions->count()
        ]);
    }

    /**
     * Transfer credits between customers
     * POST /api/wallets/transfer
     */
    public function transferCredits(Request $request): JsonResponse
    {
        $request->validate([
            'from_customer_id' => 'required|integer|exists:customers,id',
            'to_customer_id' => 'required|integer|exists:customers,id|different:from_customer_id',
            'wallet_type' => 'required|string|max:50',
            'units' => 'required|numeric|min:0.0001',
            'description' => 'nullable|string|max:500'
        ]);

        $result = $this->walletService->transferCredits(
            $request->from_customer_id,
            $request->to_customer_id,
            $request->wallet_type,
            $request->units,
            $request->description
        );

        $statusCode = $result['success'] ? 200 : 400;

        return response()->json($result, $statusCode);
    }

    /**
     * Check if customer has sufficient balance
     * GET /api/wallets/check-balance
     */
    public function checkBalance(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'wallet_type' => 'required|string|max:50',
            'required_amount' => 'required|numeric|min:0.0001'
        ]);

        $hasSufficient = $this->walletService->hasSufficientBalance(
            $request->customer_id,
            $request->wallet_type,
            $request->required_amount
        );

        $currentBalance = $this->walletService->getBalance(
            $request->customer_id,
            $request->wallet_type
        );

        return response()->json([
            'success' => true,
            'customer_id' => $request->customer_id,
            'wallet_type' => $request->wallet_type,
            'required_amount' => $request->required_amount,
            'current_balance' => $currentBalance,
            'has_sufficient_balance' => $hasSufficient
        ]);
    }

    /**
     * Get all wallet transactions for a given customer and wallet type
     * GET /api/wallets/transactions?customer_id={id}&wallet_type={type}
     */
    public function getTransactionsByWallet(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'wallet_type' => 'required|string|max:50',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        $transactions = \App\Models\WalletTransaction::where('customer_id', $request->customer_id)
            ->where('wallet_type', $request->wallet_type)
            ->orderBy('created_at', 'desc')
            ->limit($request->limit ?? 50)
            ->get();

        return response()->json([
            'success' => true,
            'customer_id' => $request->customer_id,
            'wallet_type' => $request->wallet_type,
            'transactions' => $transactions,
            'count' => $transactions->count()
        ]);
    }
}