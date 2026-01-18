# 🚀 Development Requirements - Billing API Enhancement

## 📋 Project Objective
Enhance the current Laravel billing API to match the comprehensive functionality of the legacy SAFARIBOOK billing system, adding 40+ missing endpoints and core business features.

---

## 🎯 Phase 1: Critical Core Features (High Priority)

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

-- New table for currency conversion rates
CREATE TABLE currency_exchange_rates (
    id BIGSERIAL PRIMARY KEY,
    from_currency_id BIGINT NOT NULL REFERENCES currencies(id),
    to_currency_id BIGINT NOT NULL REFERENCES currencies(id),
    rate DECIMAL(10,6) NOT NULL,
    buffer_percentage DECIMAL(5,2) DEFAULT 0.00,
    effective_rate DECIMAL(10,6) NOT NULL,
    updated_at TIMESTAMP,
    UNIQUE(from_currency_id, to_currency_id)
);
```

#### API Endpoints to Implement:
1. **GET** `/api/currencies/rates` - Get current exchange rates
2. **POST** `/api/currencies/convert` - Convert between currencies
3. **GET** `/api/prices/preview` - Get price preview in multiple currencies
4. **PUT** `/api/currencies/{id}/rate` - Update exchange rate (admin)

#### Service Layer:
```php
// app/Services/CurrencyService.php
class CurrencyService {
    public function convertAmount($amount, $fromCurrency, $toCurrency, $useBuffer = true);
    public function getExchangeRate($fromCurrency, $toCurrency);
    public function getPricePreview($amount, $baseCurrency, $targetCurrencies = []);
    public function updateExchangeRates(); // For scheduled updates
}
```

---

### 1.3 Advanced Subscription Lifecycle
**Status:** ⚠️ **Partially Implemented**

#### Database Enhancements:
```sql
-- Enhance subscriptions table (add subscription-specific fields)
ALTER TABLE subscriptions ADD COLUMN subscription_number VARCHAR(100) UNIQUE;
ALTER TABLE subscriptions ADD COLUMN trial_ends_at TIMESTAMP;
ALTER TABLE subscriptions ADD COLUMN canceled_at TIMESTAMP;
ALTER TABLE subscriptions ADD COLUMN pause_starts_at DATE;
ALTER TABLE subscriptions ADD COLUMN pause_ends_at DATE;

-- Enhance price_plans table (plan properties belong here)
ALTER TABLE price_plans ADD COLUMN plan_code VARCHAR(50) UNIQUE;
ALTER TABLE price_plans ADD COLUMN feature_code VARCHAR(100); 
ALTER TABLE price_plans ADD COLUMN trial_period_days INTEGER DEFAULT 0;
ALTER TABLE price_plans ADD COLUMN setup_fee DECIMAL(15,2) DEFAULT 0.00;
ALTER TABLE price_plans ADD COLUMN metadata JSON;

-- New table for subscription changes
CREATE TABLE subscription_changes (
    id BIGSERIAL PRIMARY KEY,
    subscription_id BIGINT NOT NULL REFERENCES subscriptions(id),
    change_type VARCHAR(50) NOT NULL, -- 'upgrade', 'downgrade', 'pause', 'reactivate'
    old_price_plan_id BIGINT REFERENCES price_plans(id),
    new_price_plan_id BIGINT REFERENCES price_plans(id),
    proration_amount DECIMAL(15,2),
    effective_date DATE,
    created_at TIMESTAMP
);
```

#### API Endpoints to Implement:
1. **POST** `/api/subscriptions/reactivate` - Reactivate canceled subscription
2. **POST** `/api/subscriptions/change-plan` - Change subscription plan
3. **POST** `/api/subscriptions/pause` - Pause subscription temporarily
4. **GET** `/api/subscriptions/{id}/history` - Get subscription change history

---

### 1.4 Enhanced Payment Processing
**Status:** ⚠️ **Partially Implemented - Use Existing Payments Table**

#### Database Enhancements:
```sql
-- Enhance existing payments table (no new table needed)
ALTER TABLE payments ADD COLUMN payment_method VARCHAR(50); -- 'card', 'mobile_money', 'bank_transfer'
ALTER TABLE payments ADD COLUMN gateway_response JSON;
ALTER TABLE payments ADD COLUMN retry_count INTEGER DEFAULT 0;
```

#### API Endpoints to Implement:
1. **POST** `/api/invoices/{id}/process-payment` - Process payment for invoice
2. **POST** `/api/payments/{id}/retry` - Retry failed payment
3. **GET** `/api/payments/{id}/status` - Check payment status
4. **POST** `/api/payments/{id}/refund` - Process refund

---

## 🎯 Phase 2: Business Enhancement Features (Medium Priority)

### 2.1 Advanced Invoice Types
**Status:** ⚠️ **Basic Implementation**

#### Database Enhancements:
```sql
-- Enhance invoices table (invoice-specific fields only)
ALTER TABLE invoices ADD COLUMN subscription_id BIGINT REFERENCES subscriptions(id) NULL;
ALTER TABLE invoices ADD COLUMN invoice_type VARCHAR(50) DEFAULT 'subscription'; -- 'subscription', 'wallet_topup', 'plan_upgrade', 'one_time'
ALTER TABLE invoices ADD COLUMN proration_credit DECIMAL(15,2) DEFAULT 0.00;
ALTER TABLE invoices ADD COLUMN metadata JSON;

-- Note: Payment timestamps are tracked in payments.paid_at (no duplication)
```

#### API Endpoints to Implement:
1. **POST** `/api/invoices/wallet-topup` - Create wallet topup invoice
2. **POST** `/api/invoices/plan-upgrade` - Create plan upgrade invoice
3. **POST** `/api/invoices/plan-downgrade` - Create plan downgrade invoice

### 2.2 Enhanced Customer Management
**Status:** ⚠️ **Basic Implementation**

#### Database Enhancements:
```sql
-- Enhanced customer management
ALTER TABLE customers ADD COLUMN customer_type VARCHAR(20) DEFAULT 'individual'; -- 'individual', 'school', 'organization'

-- Customer lookup optimization
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_email ON customers(email);
```

#### API Endpoints to Implement:
1. **GET** `/api/customers/by-phone/{phone}/status` - Lookup by phone with full status
2. **GET** `/api/customers/by-email/{email}/status` - Lookup by email with full status

### 2.3 Multiple Payment Gateway Support
**Status:** ⚠️ **Single Gateway Only**

#### API Endpoints to Implement:
1. **POST** `/api/webhooks/stripe` - Stripe webhook handler
2. **POST** `/api/webhooks/flutterwave` - FlutterWave webhook handler
3. **POST** `/api/webhooks/test` - Test webhook for development
4. **GET** `/api/payment-gateways/test-connection` - Test gateway connectivity

---

## 🎯 Phase 3: Analytics & Reporting (Lower Priority)

### 3.1 Reporting System
**Status:** ❌ **Missing**

#### Database Requirements:
```sql
-- New table for report generation
CREATE TABLE reports (
    id BIGSERIAL PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    organization_id BIGINT REFERENCES organizations(id),
    parameters JSON,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'processing', 'completed', 'failed'
    file_path VARCHAR(500),
    created_at TIMESTAMP,
    completed_at TIMESTAMP
);
```

#### API Endpoints to Implement:
1. **GET** `/api/reports/billing` - Generate billing reports
2. **GET** `/api/analytics/customers` - Customer analytics
3. **GET** `/api/analytics/revenue` - Revenue analytics
4. **GET** `/api/analytics/subscriptions` - Subscription analytics

---

## ✅ Database Design Optimization Notes

**Key Design Principles Applied:**
- **No Redundant Data**: Removed `billing_cycle` from subscriptions (already covered by price_plan billing_type/interval)
- **Proper Data Placement**: Plan codes and features belong in `price_plans`, not `subscriptions`
- **Clean Separation**: Wallet system uses dedicated `wallet_transactions` table instead of cluttering invoices
- **Reuse Existing Tables**: Enhanced existing `payments` table instead of creating duplicate `payment_transactions`
- **No Duplicate Timestamps**: Removed `paid_at` from invoices (already exists in `payments.paid_at`)
- **Single Source of Truth**: Each piece of data has one clear owner and location
- **Referential Integrity**: Proper foreign key relationships maintain data consistency

**Benefits:**
- Eliminates data duplication and confusion
- Improves query performance through proper normalization  
- Makes the system more maintainable and extensible
- Reduces risk of data inconsistencies

---

## 🔧 Technical Implementation Requirements

### Controllers to Create/Enhance:
```php
// New Controllers
- WalletController
- CurrencyConversionController  
- PaymentProcessingController
- ReportController
- AnalyticsController
- WebhookController (enhance existing)

// Enhanced Controllers  
- SubscriptionController (add lifecycle methods)
- InvoiceController (add invoice types)
- CustomerController (add lookup methods)
```

### Services to Create:
```php
// Core Services
- WalletService
- CurrencyService
- PaymentProcessingService
- SubscriptionLifecycleService
- InvoiceTypeService
- ReportService
- AnalyticsService

// Integration Services
- StripeWebhookService
- FlutterWaveWebhookService
- PaymentGatewayService
```

### Middleware Requirements:
```php
// New Middleware
- WalletBalanceMiddleware (check sufficient balance)
- CurrencyValidationMiddleware
- PaymentGatewayMiddleware
- RateLimitingMiddleware (for webhooks)
```

---

## 🗃️ Database Migration Plan

### Phase 1 Migrations:
```php
// Priority 1 Migrations (Optimized Design)
2026_01_17_100000_create_wallet_transactions_table.php
2026_01_17_100001_enhance_currencies_table.php
2026_01_17_100002_create_currency_exchange_rates_table.php
2026_01_17_100003_enhance_price_plans_table.php
2026_01_17_100004_enhance_subscriptions_table.php
2026_01_17_100005_create_subscription_changes_table.php
2026_01_17_100006_enhance_payments_table.php          // Use existing table
2026_01_17_100007_enhance_invoices_table.php
2026_01_17_100008_enhance_customers_table.php
```

---

## 📋 Feature Implementation Checklist

### Phase 1 (Critical - 4-6 weeks):
- [ ] Wallet system (3 tables, 5 endpoints, 1 service)
- [ ] Multi-currency support (2 tables, 4 endpoints, 1 service)  
- [ ] Advanced subscriptions (2 tables, 4 endpoints, 1 service)
- [ ] Payment processing (1 table, 4 endpoints, 1 service)

### Phase 2 (Enhancement - 3-4 weeks):
- [ ] Advanced invoice types (table enhancements, 3 endpoints)
- [ ] Enhanced customer management (table enhancements, 3 endpoints)
- [ ] Multiple payment gateways (4 endpoints, 3 services)

### Phase 3 (Analytics - 2-3 weeks):
- [ ] Reporting system (1 table, 4 endpoints, 2 services)
- [ ] Analytics dashboard (frontend integration)

---

## 🧪 Testing Requirements

### Unit Tests:
```php
// Test Classes to Create
WalletServiceTest.php
CurrencyServiceTest.php
PaymentProcessingServiceTest.php
SubscriptionLifecycleServiceTest.php
InvoiceTypeServiceTest.php
```

### Integration Tests:
```php
// Integration Test Classes
WalletAPITest.php
CurrencyConversionAPITest.php
PaymentProcessingAPITest.php
WebhookIntegrationTest.php
```

### Feature Tests:
```php
// Feature Test Classes  
FullBillingWorkflowTest.php
MultiCurrencyWorkflowTest.php
WalletTopupWorkflowTest.php
SubscriptionLifecycleTest.php
```

---

## 📦 Dependencies to Add

### Composer Packages:
```json
{
  "stripe/stripe-php": "^10.0",
  "flutterwave/flutterwave-php": "^1.0",
  "league/csv": "^9.0",
  "maatwebsite/excel": "^3.1",
  "pusher/pusher-php-server": "^7.0"
}
```

### NPM Packages (for frontend):
```json
{
  "chart.js": "^4.0",
  "moment": "^2.29",
  "numeral": "^2.0"
}
```

---

## 🚀 Implementation Timeline

### **Month 1**: Phase 1 Critical Features
- Week 1-2: Wallet system implementation
- Week 3: Multi-currency support
- Week 4: Advanced subscription lifecycle

### **Month 2**: Phase 1 Completion + Phase 2 Start  
- Week 1: Payment processing system
- Week 2-3: Advanced invoice types
- Week 4: Enhanced customer management

### **Month 3**: Phase 2-3 Completion
- Week 1-2: Multiple payment gateway support
- Week 3-4: Reporting and analytics system

---

## 📊 Success Metrics

### Technical Metrics:
- [ ] 40+ new API endpoints implemented
- [ ] 100% test coverage for new features
- [ ] API response time < 200ms average
- [ ] Zero breaking changes to existing API

### Business Metrics:
- [ ] Support for multiple wallet types (3+)
- [ ] Multi-currency conversion (5+ currencies)
- [ ] Advanced subscription management (4+ lifecycle operations)
- [ ] Comprehensive reporting (5+ report types)

---

## 🔒 Security Considerations

### Authentication & Authorization:
- Implement rate limiting for wallet operations
- Add IP whitelisting for webhook endpoints
- Secure API key management for payment gateways
- Audit logging for all financial transactions

### Data Protection:
- Encrypt sensitive payment data
- Implement PCI DSS compliance measures
- Add data anonymization for analytics
- Secure webhook signature validation

---

## 📚 Documentation Requirements

### API Documentation:
- [ ] Update Postman collection with new endpoints
- [ ] Create comprehensive API documentation
- [ ] Add code examples for complex workflows
- [ ] Document webhook integration guides

### Developer Documentation:
- [ ] Database schema documentation
- [ ] Service layer architecture guide
- [ ] Integration testing guide
- [ ] Deployment and configuration guide

---

**Estimated Total Development Time**: **2-3 months** with 2-3 developers  
**Estimated Cost**: **High** (significant backend development required)  
**Risk Level**: **Medium** (complex financial system integration)  
**Business Impact**: **Very High** (transforms basic API into comprehensive billing platform)