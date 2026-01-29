<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 
        'wallet_type', 
        'transaction_type', 
        'units', 
        'unit_price', 
        'total_amount', 
        'invoice_id',
        'reference_number', 
        'description', 
        'status', 
        'processed_at'
    ];

    protected $casts = [
        'units' => 'decimal:4',
        'unit_price' => 'decimal:2', 
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    /**
     * Get the customer that owns the wallet transaction
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the invoice related to this transaction
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get current balance for a customer's wallet type
     */
    public static function getBalance($customerId, $walletType)
    {
        return self::where('customer_id', $customerId)
            ->where('wallet_type', $walletType)
            ->where('status', 'completed')
            ->where('transaction_type', '!=', 'failed')
            ->sum('units');
    }

    /**
     * Get transaction history for a customer
     */
    public static function getTransactionHistory($customerId, $walletType = null, $limit = 50)
    {
        $query = self::where('customer_id', $customerId);
        
        if ($walletType) {
            $query->where('wallet_type', $walletType);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if customer has sufficient balance
     */
    public static function hasSufficientBalance($customerId, $walletType, $amount)
    {
        $currentBalance = self::getBalance($customerId, $walletType);
        return $currentBalance >= $amount;
    }
}