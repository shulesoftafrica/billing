<?php

namespace App\Services;

use App\Models\WalletTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class WalletService
{
    /**
     * Add credits to customer wallet
     */
    public function addCredits($customerId, $walletType, $units, $description = null, $invoiceId = null, $unitPrice = null)
    {
        try {
            // Verify customer exists
            $customer = Customer::findOrFail($customerId);

            $totalAmount = $unitPrice ? ($units * $unitPrice) : null;

            $transaction = WalletTransaction::create([
                'customer_id' => $customerId,
                'wallet_type' => $walletType,
                'transaction_type' => 'topup',
                'units' => $units,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'invoice_id' => $invoiceId,
                'description' => $description ?: "Added {$units} {$walletType} credits",
                'status' => 'completed',
                'processed_at' => now(),
                'reference_number' => $this->generateReferenceNumber()
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'new_balance' => $this->getBalance($customerId, $walletType),
                'message' => "Successfully added {$units} {$walletType} credits"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to add credits'
            ];
        }
    }

    /**
     * Deduct credits from customer wallet
     */
    public function deductCredits($customerId, $walletType, $units, $description = null)
    {
        try {
            // Verify customer exists
            $customer = Customer::findOrFail($customerId);

            // Check if sufficient balance exists
            if (!$this->hasSufficientBalance($customerId, $walletType, $units)) {
                return [
                    'success' => false,
                    'error' => 'insufficient_balance',
                    'current_balance' => $this->getBalance($customerId, $walletType),
                    'required' => $units,
                    'message' => "Insufficient {$walletType} balance"
                ];
            }

            $transaction = WalletTransaction::create([
                'customer_id' => $customerId,
                'wallet_type' => $walletType,
                'transaction_type' => 'deduction',
                'units' => -$units, // Negative for deduction
                'description' => $description ?: "Deducted {$units} {$walletType} credits",
                'status' => 'completed',
                'processed_at' => now(),
                'reference_number' => $this->generateReferenceNumber()
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'new_balance' => $this->getBalance($customerId, $walletType),
                'message' => "Successfully deducted {$units} {$walletType} credits"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to deduct credits'
            ];
        }
    }

    /**
     * Get customer wallet balance
     */
    public function getBalance($customerId, $walletType = null)
    {
        if ($walletType) {
            return WalletTransaction::getBalance($customerId, $walletType);
        }

        // Get all wallet balances for the customer
        $balances = WalletTransaction::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->selectRaw('wallet_type, SUM(units) as balance')
            ->groupBy('wallet_type')
            ->get()
            ->pluck('balance', 'wallet_type')
            ->toArray();

        return $balances;
    }

    /**
     * Check if customer has sufficient balance
     */
    public function hasSufficientBalance($customerId, $walletType, $requiredAmount)
    {
        return WalletTransaction::hasSufficientBalance($customerId, $walletType, $requiredAmount);
    }

    /**
     * Get transaction history for a customer
     */
    public function getTransactionHistory($customerId, $filters = [])
    {
        $walletType = $filters['wallet_type'] ?? null;
        $limit = $filters['limit'] ?? 50;
        $transactionType = $filters['transaction_type'] ?? null;

        $query = WalletTransaction::where('customer_id', $customerId)
            ->with(['invoice']);

        if ($walletType) {
            $query->where('wallet_type', $walletType);
        }

        if ($transactionType) {
            $query->where('transaction_type', $transactionType);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Transfer credits between customers (if needed)
     */
    public function transferCredits($fromCustomerId, $toCustomerId, $walletType, $units, $description = null)
    {
        try {
            // Check if sender has sufficient balance
            if (!$this->hasSufficientBalance($fromCustomerId, $walletType, $units)) {
                return [
                    'success' => false,
                    'error' => 'insufficient_balance',
                    'message' => 'Insufficient balance for transfer'
                ];
            }

            // Deduct from sender
            $deductResult = $this->deductCredits($fromCustomerId, $walletType, $units, 
                $description ?: "Transfer to customer {$toCustomerId}");

            if (!$deductResult['success']) {
                return $deductResult;
            }

            // Add to receiver
            $addResult = $this->addCredits($toCustomerId, $walletType, $units, 
                $description ?: "Transfer from customer {$fromCustomerId}");

            if (!$addResult['success']) {
                // Rollback - add credits back to sender
                $this->addCredits($fromCustomerId, $walletType, $units, 'Transfer rollback');
                
                return [
                    'success' => false,
                    'error' => 'transfer_failed',
                    'message' => 'Transfer failed and was rolled back'
                ];
            }

            return [
                'success' => true,
                'deduct_transaction' => $deductResult['transaction'],
                'add_transaction' => $addResult['transaction'],
                'message' => "Successfully transferred {$units} {$walletType} credits"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Transfer failed'
            ];
        }
    }

    /**
     * Generate unique reference number
     */
    private function generateReferenceNumber()
    {
        return 'WTX-' . now()->format('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
    }
}