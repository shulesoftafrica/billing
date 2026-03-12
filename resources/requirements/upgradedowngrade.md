# Subscription Upgrade/Downgrade Implementation Assessment

**Assessment Date:** March 12, 2026  
**Assessment By:** AI Code Analysis

---

## 📋 REQUIREMENTS SUMMARY

### 1. Subscription Upgrade Requirement (From Original Spec)

**Endpoint Required:** `POST /api/invoices/plan-upgrade`

**Business Logic:**
- Calculate daily cost of current plan
- Calculate amount already spent based on days used
- Subtract spent amount from new plan price
- Charge the difference (prorated amount)

**Example Calculation:**
```
Plans:
- Basic: TZS 30,000/month
- Standard: TZS 75,000/month
- Premium: TZS 120,000/month

Scenario: Customer used Basic for 10 days, upgrading to Standard
Calculation: 75,000 - (30,000/30)*10 = 75,000 - 10,000 = 65,000
Amount to Pay: TZS 65,000

Scenario: Customer used Basic for 10 days, upgrading to Premium
Calculation: 120,000 - (30,000/30)*10 = 120,000 - 10,000 = 110,000
Amount to Pay: TZS 110,000
```

### 2. Subscription Downgrade Requirement (From API Documentation)

**Endpoint Required:** `POST /api/invoices/plan-downgrade`

**Business Logic:**
- Calculate unused credit from current plan
- Apply credit to customer account or next bill
- Switch to lower-tier plan

---

## 🔍 IMPLEMENTATION STATUS

### ✅ Database Schema - **READY**

**File:** `database/migrations/2026_01_17_100007_enhance_invoices_table.php`

The invoices table has been prepared with upgrade/downgrade support:

```php
Schema::table('invoices', function (Blueprint $table) {
    $table->foreignId('subscription_id')->nullable();
    $table->string('invoice_type', 50)->default('subscription');
    // Supports: 'subscription', 'wallet_topup', 'plan_upgrade', 'one_time'
    $table->decimal('proration_credit', 15, 2)->default(0.00);
    $table->json('metadata')->nullable();
});
```

**Status:** ✅ **COMPLETE**
- `invoice_type` column supports `'plan_upgrade'` value
- `proration_credit` field ready for storing credit/debit amounts
- `subscription_id` foreign key for linking upgrades to subscriptions
- `metadata` field for storing upgrade details

---

### ❌ API Routes - **NOT IMPLEMENTED**

**File Checked:** `routes/api.php`

**Expected Routes:**
```php
Route::post('invoices/plan-upgrade', [InvoiceController::class, 'upgradeSubscription']);
Route::post('invoices/plan-downgrade', [InvoiceController::class, 'downgradeSubscription']);
```

**Actual Status:** ❌ **MISSING**
- No upgrade route found
- No downgrade route found
- Routes documented in POSTMAN_API_DOCUMENTATION.md but not implemented

---

### ❌ Controller Methods - **NOT IMPLEMENTED**

**File Checked:** `app/Http/Controllers/Api/InvoiceController.php`

**Methods Found:**
- ✅ `index()` - List invoices
- ✅ `store()` - Create invoice
- ✅ `show()` - Get invoice details
- ✅ `cancel()` - Cancel invoice
- ❌ `upgradeSubscription()` - **MISSING**
- ❌ `downgradeSubscription()` - **MISSING**

**Actual Status:** ❌ **NOT IMPLEMENTED**

---

### ❌ Service Layer - **NOT IMPLEMENTED**

**Files Checked:**
- `app/Services/SubscriptionService.php`
- Other service files in `app/Services/`

**Expected Methods:**
```php
// Expected in SubscriptionService
public function upgradeSubscription(int $subscriptionId, int $newPricePlanId): Invoice
public function downgradeSubscription(int $subscriptionId, int $newPricePlanId): Invoice
public function calculateProration(Subscription $subscription, PricePlan $newPlan): float
```

**Actual Status:** ❌ **NONE FOUND**
- No upgrade logic implemented
- No downgrade logic implemented
- No proration calculation logic

---

### ❌ Model Methods - **NOT IMPLEMENTED**

**File Checked:** `app/Models/Subscription.php`

**Methods Found:**
- ✅ `customer()` - Relationship
- ✅ `pricePlan()` - Relationship
- ✅ `invoice_item()` - Relationship

**Expected Methods:**
```php
public function upgrade(PricePlan $newPlan): Invoice
public function downgrade(PricePlan $newPlan): Invoice
public function getDaysUsed(): int
public function calculateProration(PricePlan $newPlan): float
```

**Actual Status:** ❌ **NOT IMPLEMENTED**

---

## 📊 IMPLEMENTATION GAP SUMMARY

| Component | Required | Status | Priority |
|-----------|----------|--------|----------|
| Database Schema | ✅ | Complete | N/A |
| API Routes | ❌ | Missing | 🔴 HIGH |
| Controller Methods | ❌ | Missing | 🔴 HIGH |
| Service Layer Logic | ❌ | Missing | 🔴 HIGH |
| Proration Calculation | ❌ | Missing | 🔴 HIGH |
| Model Helper Methods | ❌ | Missing | 🟡 MEDIUM |
| API Documentation | ⚠️ | Partial (documented but not implemented) | 🟡 MEDIUM |
| Tests | ❌ | Missing | 🟡 MEDIUM |

---

## 🚨 CRITICAL FINDINGS

### 1. **Documented But Not Implemented**
The API documentation (`POSTMAN_API_DOCUMENTATION.md`) includes upgrade/downgrade endpoints, but **ZERO implementation exists** in the actual codebase. This creates a **documentation-implementation mismatch**.

### 2. **Database Ready, Code Not Ready**
The database schema has been prepared for upgrades (invoice_type, proration_credit), indicating previous planning, but the business logic was never implemented.

### 3. **No Proration Logic**
The core requirement - daily cost calculation for proration - has **no implementation** anywhere in the codebase.

### 4. **Missing Critical Routes**
Neither `POST /api/invoices/plan-upgrade` nor `POST /api/invoices/plan-downgrade` exist in `routes/api.php`.

---

## 📝 IMPLEMENTATION REQUIREMENTS

### Required Files to Create/Modify:

1. **Route Definitions** (`routes/api.php`)
   ```php
   Route::post('invoices/plan-upgrade', [InvoiceController::class, 'upgradeSubscription']);
   Route::post('invoices/plan-downgrade', [InvoiceController::class, 'downgradeSubscription']);
   ```

2. **Controller Methods** (`app/Http/Controllers/Api/InvoiceController.php`)
   - `upgradeSubscription(Request $request)`
   - `downgradeSubscription(Request $request)`

3. **Service Layer** (`app/Services/SubscriptionService.php`)
   - `upgradeSubscription($subscriptionId, $newPricePlanId)`
   - `downgradeSubscription($subscriptionId, $newPricePlanId)`
   - `calculateDailyProration(Subscription $subscription, PricePlan $newPlan)`
   - `getDaysUsedInCurrentBillingCycle(Subscription $subscription)`

4. **Documentation Update** (`docs/api-documentation.md`)
   - Add upgrade endpoint with proration calculation examples
   - Add downgrade endpoint with credit handling examples

---

## 🎯 RECOMMENDED IMPLEMENTATION APPROACH

### Phase 1: Core Proration Logic
1. Implement `calculateDailyProration()` method
2. Add `getDaysUsedInCurrentBillingCycle()` helper
3. Add unit tests for proration calculations

### Phase 2: Upgrade Functionality
1. Create `upgradeSubscription()` service method
2. Create controller endpoint
3. Add route definition
4. Test with real scenarios

### Phase 3: Downgrade Functionality
1. Create `downgradeSubscription()` service method
2. Implement credit handling
3. Create controller endpoint
4. Add route definition

### Phase 4: Documentation & Testing
1. Update API documentation with examples
2. Add integration tests
3. Update Postman collection
4. Add error handling scenarios

---

## 🔢 PRORATION FORMULA - BEST PRACTICE APPROACH

### ❌ Original Simplified Formula (Not Recommended)

```
Upgrade Amount = NewPlanPrice - (CurrentPlanPrice / 30) * DaysUsed
```

**Problem:** This assumes all months have 30 days, which is **inaccurate and unfair**:
- February customers (28/29 days) would pay more per day
- January/March customers (31 days) would pay less per day
- Inconsistent daily rates across months

---

### ✅ RECOMMENDED: Actual Billing Cycle Days Approach

This is the industry standard used by Stripe, Chargebee, and major SaaS billing platforms.

#### Core Principle:
**Calculate based on ACTUAL DAYS in the specific billing cycle, not fixed 30 days**

#### Formula Components:

```
Billing Cycle Length = Days between current_period_start and current_period_end
Days Used = Days between current_period_start and upgrade_date
Days Remaining = Billing Cycle Length - Days Used

Old Plan Daily Rate = Old Plan Price / Billing Cycle Length
New Plan Daily Rate = New Plan Price / Billing Cycle Length

Unused Credit = Old Plan Daily Rate × Days Remaining
New Plan Charge = New Plan Daily Rate × Days Remaining

Amount to Charge = New Plan Charge - Unused Credit
```

#### Detailed Upgrade Calculation:

```php
// Step 1: Calculate billing cycle length
$billingCycleStart = $subscription->current_period_start;  // e.g., 2026-01-15
$billingCycleEnd = $subscription->next_billing_date;       // e.g., 2026-02-15
$billingCycleLength = $billingCycleStart->diffInDays($billingCycleEnd); // 31 days

// Step 2: Calculate days used and remaining
$upgradeDate = now();                                      // e.g., 2026-01-25
$daysUsed = $billingCycleStart->diffInDays($upgradeDate); // 10 days
$daysRemaining = $billingCycleLength - $daysUsed;         // 21 days

// Step 3: Calculate daily rates
$oldPlanDailyRate = $oldPlan->amount / $billingCycleLength;
$newPlanDailyRate = $newPlan->amount / $billingCycleLength;

// Step 4: Calculate proration
$unusedCredit = $oldPlanDailyRate * $daysRemaining;
$newPlanCharge = $newPlanDailyRate * $daysRemaining;
$amountToCharge = $newPlanCharge - $unusedCredit;
```

---

### 📊 REAL-WORLD EXAMPLES

#### Example 1: Upgrade in January (31 days)

**Scenario:**
- Current Plan: Basic TZS 30,000/month
- New Plan: Standard TZS 75,000/month
- Billing cycle: Jan 15 → Feb 15 (31 days)
- Upgrade date: Jan 25 (10 days used, 21 days remaining)

**Calculation:**
```
Old Plan Daily Rate = 30,000 ÷ 31 = 967.74 TZS/day
New Plan Daily Rate = 75,000 ÷ 31 = 2,419.35 TZS/day

Unused Credit (21 days) = 967.74 × 21 = 20,322.54 TZS
New Plan Charge (21 days) = 2,419.35 × 21 = 50,806.35 TZS

Amount to Charge = 50,806.35 - 20,322.54 = 30,483.81 TZS
```

**Rounded:** TZS 30,484

---

#### Example 2: Upgrade in February (28 days - Non-leap year)

**Scenario:**
- Current Plan: Basic TZS 30,000/month
- New Plan: Standard TZS 75,000/month
- Billing cycle: Feb 15 → Mar 15 (28 days)
- Upgrade date: Feb 25 (10 days used, 18 days remaining)

**Calculation:**
```
Old Plan Daily Rate = 30,000 ÷ 28 = 1,071.43 TZS/day
New Plan Daily Rate = 75,000 ÷ 28 = 2,678.57 TZS/day

Unused Credit (18 days) = 1,071.43 × 18 = 19,285.74 TZS
New Plan Charge (18 days) = 2,678.57 × 18 = 48,214.26 TZS

Amount to Charge = 48,214.26 - 19,285.74 = 28,928.52 TZS
```

**Rounded:** TZS 28,929

---

#### Example 3: Comparison - Why Actual Days Matter

**Same scenario, different months:**

| Month | Days in Cycle | Daily Rate (30k plan) | 10 Days Used Cost | Fair? |
|-------|---------------|----------------------|-------------------|-------|
| **Jan (31 days)** | 31 | 967.74 TZS | 9,677.40 TZS | ✅ Yes |
| **Feb (28 days)** | 28 | 1,071.43 TZS | 10,714.30 TZS | ✅ Yes |
| **Fixed 30 days** | 30 | 1,000.00 TZS | 10,000.00 TZS | ❌ Unfair |

**With fixed 30-day approach:**
- February users pay 10,000 but "should" pay 10,714 (underpaying by 714 TZS)
- January users pay 10,000 but "should" pay 9,677 (overpaying by 323 TZS)

**With actual days approach:**
- ✅ Every customer pays exactly for the days they used
- ✅ Fair across all months
- ✅ Mathematically accurate

---

### 🎯 IMPLEMENTATION GUIDELINES

#### 1. **Store Billing Period Dates**
```php
// In subscriptions table
$subscription->current_period_start   // Start of current billing cycle
$subscription->current_period_end     // End of current billing cycle (same as next_billing_date)
```

#### 2. **Always Calculate from Billing Cycle, Not Calendar Month**
```php
// ❌ WRONG: Using calendar month
$daysInMonth = Carbon::now()->daysInMonth; // 28, 29, 30, or 31

// ✅ CORRECT: Using actual billing cycle
$billingCycleLength = $subscription->current_period_start
    ->diffInDays($subscription->current_period_end);
```

#### 3. **Handle Edge Cases**

**Same-day upgrade:**
```php
if ($daysRemaining === $billingCycleLength) {
    // Upgrade on same day as billing cycle start
    // Charge full new plan amount
    $amountToCharge = $newPlan->amount;
}
```

**Last-day upgrade:**
```php
if ($daysRemaining === 0) {
    // Upgrade on last day of cycle
    // Charge only 1 day difference
    $amountToCharge = ($newPlanDailyRate - $oldPlanDailyRate) * 1;
}
```

**Mid-cycle cancellation then upgrade:**
```php
// If subscription was cancelled but still in grace period
// Calculate from current date to period end
$daysRemaining = now()->diffInDays($subscription->current_period_end);
```

#### 4. **Timezone Handling**
```php
// Always use organization's timezone for consistency
$organizationTz = $subscription->customer->organization->timezone;

$billingCycleStart = Carbon::parse($subscription->current_period_start, $organizationTz);
$upgradeDate = Carbon::now($organizationTz);
```

#### 5. **Rounding Rules**
```php
// Standard practice: Round to 2 decimal places
$amountToCharge = round($amountToCharge, 2);

// Alternative: Always round UP (customer-friendly for small amounts)
$amountToCharge = ceil($amountToCharge * 100) / 100;
```

---

### 🔄 DOWNGRADE CALCULATION

For downgrades, apply the same principle but handle credit differently:

```php
// Calculate unused value from old (higher) plan
$unusedCredit = $oldPlanDailyRate * $daysRemaining;

// Calculate what new (lower) plan would cost for remaining days
$newPlanCharge = $newPlanDailyRate * $daysRemaining;

// Credit to apply (either to wallet or next invoice)
$creditAmount = $unusedCredit - $newPlanCharge;

// Downgrade is effective immediately, no charge, credit applied to account
```

**Example:**
- Downgrade from Standard (75k) to Basic (30k) on Jan 25
- 21 days remaining in 31-day cycle
- Credit: (75,000/31 × 21) - (30,000/31 × 21) = 30,483.81 TZS
- This credit can be:
  - Applied to customer's wallet
  - Applied to next invoice
  - Refunded (based on business policy)

---

### ✅ BENEFITS OF THIS APPROACH

1. **Mathematically Accurate**
   - Customers pay exactly for the time they use each plan
   - No overpayment or underpayment

2. **Fair Across All Months**
   - February users don't subsidize January users
   - Consistent daily rate within each billing cycle

3. **Industry Standard**
   - Matches behavior of Stripe, Chargebee, Recurly
   - Meets customer expectations from other SaaS products

4. **Transparent**
   - Easy to explain to customers with clear math
   - Audit-friendly calculations

5. **Handles Annual Plans**
   - Same formula works for yearly subscriptions
   - Just use 365/366 days instead of ~30

---

### 📝 ADDITIONAL CONSIDERATIONS

#### Annual Subscriptions
For annual plans, use the same formula:
```php
$billingCycleLength = 365; // or 366 for leap years
$dailyRate = $annualPrice / $billingCycleLength;
```

#### Quarterly/Semi-Annual Plans
```php
// Quarterly: ~91 days
$billingCycleLength = $currentPeriodStart->diffInDays($currentPeriodEnd);

// Semi-annual: ~182 days
$billingCycleLength = $currentPeriodStart->diffInDays($currentPeriodEnd);
```

#### Trial Periods
```php
// If upgrade happens during trial
if ($subscription->status === 'trial') {
    // No credit from trial (was free)
    $amountToCharge = $newPlanDailyRate * $daysRemaining;
}
```

---

### 🔧 RECOMMENDED SERVICE METHOD SIGNATURE

```php
/**
 * Calculate prorated amount for subscription upgrade
 *
 * @param Subscription $subscription Current active subscription
 * @param PricePlan $newPlan Plan being upgraded to
 * @return array [
 *     'amount_to_charge' => float,
 *     'old_plan_daily_rate' => float,
 *     'new_plan_daily_rate' => float,
 *     'unused_credit' => float,
 *     'new_plan_charge' => float,
 *     'days_remaining' => int,
 *     'billing_cycle_length' => int,
 *     'calculation_details' => array
 * ]
 */
public function calculateUpgradeProration(
    Subscription $subscription, 
    PricePlan $newPlan
): array
{
    // Implementation using actual billing cycle days
    $billingCycleStart = Carbon::parse($subscription->current_period_start);
    $billingCycleEnd = Carbon::parse($subscription->next_billing_date);
    $upgradeDate = Carbon::now($subscription->customer->organization->timezone ?? 'UTC');
    
    $billingCycleLength = $billingCycleStart->diffInDays($billingCycleEnd);
    $daysUsed = $billingCycleStart->diffInDays($upgradeDate);
    $daysRemaining = $billingCycleLength - $daysUsed;
    
    $oldPlan = $subscription->pricePlan;
    $oldPlanDailyRate = $oldPlan->amount / $billingCycleLength;
    $newPlanDailyRate = $newPlan->amount / $billingCycleLength;
    
    $unusedCredit = $oldPlanDailyRate * $daysRemaining;
    $newPlanCharge = $newPlanDailyRate * $daysRemaining;
    $amountToCharge = round($newPlanCharge - $unusedCredit, 2);
    
    return [
        'amount_to_charge' => $amountToCharge,
        'old_plan_daily_rate' => round($oldPlanDailyRate, 2),
        'new_plan_daily_rate' => round($newPlanDailyRate, 2),
        'unused_credit' => round($unusedCredit, 2),
        'new_plan_charge' => round($newPlanCharge, 2),
        'days_remaining' => $daysRemaining,
        'days_used' => $daysUsed,
        'billing_cycle_length' => $billingCycleLength,
        'calculation_details' => [
            'billing_cycle_start' => $billingCycleStart->toDateString(),
            'billing_cycle_end' => $billingCycleEnd->toDateString(),
            'upgrade_date' => $upgradeDate->toDateString(),
            'old_plan_name' => $oldPlan->name,
            'old_plan_amount' => $oldPlan->amount,
            'new_plan_name' => $newPlan->name,
            'new_plan_amount' => $newPlan->amount,
        ]
    ];
}
```

---

## ✅ CONCLUSION

**IMPLEMENTATION STATUS: 0% COMPLETE**

While the database schema has been prepared for subscription upgrades/downgrades, **NO actual implementation exists** in the codebase. The feature is documented in API specifications but entirely missing from:
- Routes
- Controllers
- Services
- Business Logic
- Models

**Action Required:** Full implementation needed following the recommended approach above.

**Estimated Effort:** 
- Core Implementation: 8-12 hours
- Testing: 4-6 hours
- Documentation: 2-3 hours
- **Total: 14-21 hours**