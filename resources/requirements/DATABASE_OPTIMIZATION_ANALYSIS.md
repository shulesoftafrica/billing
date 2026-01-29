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

