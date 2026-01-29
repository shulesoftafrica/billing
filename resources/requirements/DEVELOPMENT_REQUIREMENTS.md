# 🚀 Development Requirements - Billing API Enhancement

## 📋 Project Objective
Enhance the current Laravel billing API to match the comprehensive functionality of the legacy SAFARIBOOK billing system, adding 40+ missing endpoints and core business features.

---

## 🏆 Phase 1: Critical Core Features (High Priority)

### 1.1 Wallet System Implementation
**Status:** ❌ **Missing - Critical**

#### Database Requirements:
```sql
-- New Tables Needed
CREATE TABLE wallet_transactions (
    id BIGSERIAL PRIMARY KEY,
    customer_id BIGINT NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    wallet_type VARCHAR(50) NOT NULL, -- 'balance', 'points', 'credits'
    transaction_type VARCHAR(30) NOT NULL, -- 'topup', 'deduction', 'transfer', 'refund'
    units DECIMAL(15,4) NOT NULL,
    unit_price DECIMAL(15,2),
    total_amount DECIMAL(15,2),
    invoice_id BIGINT REFERENCES invoices(id) NULL, -- Link to invoice if paid
    reference_number VARCHAR(100),
    description TEXT,
    status VARCHAR(20) DEFAULT 'completed', -- 'pending', 'completed', 'failed'
    processed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_customer_wallet (customer_id, wallet_type),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_status (status)
);
```

#### API Endpoints to Implement:
1. **GET** `/api/wallets/balance` - Get customer wallet balances
2. **POST** `/api/wallets/deduct` - Deduct from wallet
3. **POST** `/api/wallets/credit` - Add credits to wallet  
4. **GET** `/api/wallets/{customer_id}/transactions` - Get transaction history

#### Models & Relationships:
```php
// app/Models/WalletTransaction.php  
class WalletTransaction extends Model {
    protected $fillable = [
        'customer_id', 'wallet_type', 'transaction_type', 
        'units', 'unit_price', 'total_amount', 'invoice_id',
        'reference_number', 'description', 'status', 'processed_at'
    ];
    
    protected $casts = [
        'units' => 'decimal:4',
        'unit_price' => 'decimal:2', 
        'total_amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];
    
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    
    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    
    // Get current balance for a wallet type
    public static function getBalance($customerId, $walletType) {
        return self::where('customer_id', $customerId)
            ->where('wallet_type', $walletType)
            ->where('status', 'completed')
            ->sum('units');
    }
}
```

#### Service Layer:
```php
// app/Services/WalletService.php
class WalletService {
    public function deductCredits($customerId, $walletType, $amount, $description);
    public function addCredits($customerId, $walletType, $amount, $description);  
    public function getBalance($customerId, $walletType = null);
    public function getTransactionHistory($customerId, $filters = []);
}
```

---

### 1.2 Multi-Currency System
**Status:** ❌ **Missing - Critical**

#### Database Requirements:
```sql
-- Enhance existing currencies table
ALTER TABLE currencies ADD COLUMN exchange_rate DECIMAL(10,6) DEFAULT 1.000000;
ALTER TABLE currencies ADD COLUMN is_base_currency BOOLEAN DEFAULT false;
ALTER TABLE currencies ADD COLUMN last_updated TIMESTAMP;
