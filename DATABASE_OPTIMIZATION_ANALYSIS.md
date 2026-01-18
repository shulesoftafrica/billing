# Database Optimization Analysis

## Current State vs Proposed Changes

### Issue 1: `billing_type` vs `billing_cycle` Confusion

**Current State:**
- `price_plans.billing_type` = ['one_time', 'recurring', 'usage'] 
- `price_plans.billing_interval` = ['monthly', 'yearly']

**Proposed (INCORRECT):**
- Adding `billing_cycle` to subscriptions table

**OPTIMIZATION:**
✅ **Keep existing structure** - it's already optimal:
- `billing_type` defines WHAT kind of billing (one_time, recurring, usage)
- `billing_interval` defines HOW OFTEN (monthly, yearly)
- No need for `billing_cycle` in subscriptions - it's redundant

---

### Issue 2: `proration_credit` Placement

**Analysis:** This should be in `invoices` table, NOT subscriptions.

**Reasoning:**
- Proration credits are applied to specific invoices
- Multiple invoices can have prorations for the same subscription
- Invoice is the transaction record, subscription is the service agreement

**OPTIMIZATION:**
```sql
-- Add to invoices table only
ALTER TABLE invoices ADD COLUMN proration_credit DECIMAL(15,2) DEFAULT 0.00;
```

---

### Issue 3: Wallet-Related Fields Placement

**Current Proposal (PROBLEMATIC):**
Adding to invoices: `units`, `unit_price`, `wallet_type`

**OPTIMIZATION:**
Create separate `wallet_transactions` table instead:

```sql
CREATE TABLE wallet_transactions (
    id BIGINT PRIMARY KEY,
    customer_id BIGINT REFERENCES customers(id),
    wallet_type VARCHAR(50), -- 'balance', 'points', 'credits'
    transaction_type VARCHAR(30), -- 'topup', 'deduction', 'transfer'
    units DECIMAL(15,4),
    unit_price DECIMAL(15,2),
    total_amount DECIMAL(15,2),
    invoice_id BIGINT REFERENCES invoices(id) NULL, -- Link to invoice if paid
    reference_number VARCHAR(100),
    status VARCHAR(20),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Benefits:**
- Clean separation of concerns
- Supports multiple wallet types per customer
- Maintains transaction history
- Links to invoices when payment is involved

---

### Issue 4: Metadata and Codes Placement

**Current Structure (GOOD):**
- `price_plans` has the plan definition
- `subscriptions` links customer to plan

**Proposed (INCORRECT):**
Adding `metadata`, `feature_code`, `plan_code` to subscriptions

**OPTIMIZATION:**
✅ **Add to `price_plans` table instead:**

```sql
-- Add to price_plans table
ALTER TABLE price_plans ADD COLUMN plan_code VARCHAR(50) UNIQUE;
ALTER TABLE price_plans ADD COLUMN feature_code VARCHAR(100);
ALTER TABLE price_plans ADD COLUMN metadata JSONB;
```

**Reasoning:**
- Plan codes and features are properties of the PLAN, not the subscription
- Multiple subscriptions can use the same plan
- Metadata about plan features belongs with the plan definition
- Reduces data duplication

---

## Optimized Database Schema

### 1. Enhanced Price Plans
```sql
-- price_plans table (enhanced)
CREATE TABLE price_plans (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    product_id BIGINT REFERENCES products(id),
    plan_code VARCHAR(50) UNIQUE,           -- NEW: Unique plan identifier
    feature_code VARCHAR(100),              -- NEW: Features this plan enables
    billing_type VARCHAR(20),               -- EXISTS: one_time, recurring, usage
    billing_interval VARCHAR(20),           -- EXISTS: monthly, yearly
    amount DECIMAL(15,2),
    currency_id BIGINT REFERENCES currencies(id),
    trial_period_days INTEGER DEFAULT 0,   -- NEW: Trial period
    setup_fee DECIMAL(15,2) DEFAULT 0.00, -- NEW: One-time setup fee
    metadata JSONB,                        -- NEW: Additional plan configuration
    active BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Clean Subscriptions Table
```sql
-- subscriptions table (optimized)
CREATE TABLE subscriptions (
    id BIGINT PRIMARY KEY,
    customer_id BIGINT REFERENCES customers(id),
    price_plan_id BIGINT REFERENCES price_plans(id),
    subscription_number VARCHAR(100) UNIQUE,    -- NEW: Human-readable identifier
    status VARCHAR(20),                         -- pending, active, paused, canceled
    start_date DATE,
    end_date DATE,
    next_billing_date DATE,
    trial_ends_at TIMESTAMP,                   -- NEW: Trial expiration
    canceled_at TIMESTAMP,                     -- NEW: Cancellation timestamp
    pause_starts_at DATE,                      -- NEW: Pause period start
    pause_ends_at DATE,                        -- NEW: Pause period end
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 3. Enhanced Invoices
```sql
-- invoices table (enhanced)
CREATE TABLE invoices (
    id BIGINT PRIMARY KEY,
    customer_id BIGINT REFERENCES customers(id),
    subscription_id BIGINT REFERENCES subscriptions(id) NULL, -- Link to subscription
    invoice_number VARCHAR(100) UNIQUE,
    status VARCHAR(20),
    description TEXT,
    subtotal DECIMAL(15,2),
    tax_total DECIMAL(15,2),
    proration_credit DECIMAL(15,2) DEFAULT 0.00, -- NEW: Proration credits
    total DECIMAL(15,2),
    due_date DATE,
    issued_at TIMESTAMP,
    paid_at TIMESTAMP,                           -- NEW: Payment timestamp
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. New Wallet Transactions Table
```sql
-- wallet_transactions table (new)
CREATE TABLE wallet_transactions (
    id BIGINT PRIMARY KEY,
    customer_id BIGINT REFERENCES customers(id),
    wallet_type VARCHAR(50),        -- 'balance', 'points', 'credits'
    transaction_type VARCHAR(30),   -- 'topup', 'deduction', 'transfer', 'refund'
    units DECIMAL(15,4),           -- Quantity of wallet units
    unit_price DECIMAL(15,2),      -- Price per unit (for topups)
    total_amount DECIMAL(15,2),    -- Total monetary value
    invoice_id BIGINT REFERENCES invoices(id) NULL,
    reference_number VARCHAR(100), -- External reference
    description TEXT,
    status VARCHAR(20),            -- 'pending', 'completed', 'failed'
    processed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_customer_wallet (customer_id, wallet_type),
    INDEX idx_transaction_type (transaction_type),
    INDEX idx_status (status)
);
```

---

## Benefits of This Optimization

### 1. **Eliminates Redundancy**
- No duplicate billing information
- Plan properties stay with plans
- Transaction data properly separated

### 2. **Improves Data Integrity**
- Foreign key constraints prevent orphaned records
- Proper normalization reduces update anomalies
- Clear ownership of each data element

### 3. **Enhances Flexibility**
- Wallet system supports multiple currencies/types
- Plans can have rich metadata without affecting subscriptions
- Easy to extend with new features

### 4. **Better Performance**
- Proper indexing on frequently queried fields
- Smaller subscription records (no redundant data)
- Efficient wallet balance calculations

### 5. **Cleaner Business Logic**
- Clear separation between plan definition and subscription instance
- Wallet operations independent of invoices
- Proration calculations tied to specific invoices

---

## Migration Strategy

1. **Keep existing tables as-is** for now
2. **Add new columns** to existing tables where beneficial
3. **Create wallet_transactions** as new table
4. **Update models** to use optimized structure
5. **Create data migration scripts** to move any existing data

This approach maintains backward compatibility while implementing the optimized design.