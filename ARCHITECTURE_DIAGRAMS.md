# Subscription System - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT                              │
│                    (Postman, Frontend)                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ HTTP POST /api/subscriptions
                         │ {customer_id, plan_ids: [1,2,3]}
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  SubscriptionController                     │
│  • Validate request (Laravel Validator)                     │
│  • Remove duplicate plan IDs                                │
│  • Call service layer                                       │
│  • Format response                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  SubscriptionService                        │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 1. Validate Input                                     │  │
│  │    • Check customer exists                            │  │
│  │    • Validate plan IDs                                │  │
│  │    • Ensure minimum 1 plan                            │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 2. Start Database Transaction                         │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 3. Fetch & Lock Price Plans                           │  │
│  │    • WHERE id IN (1,2,3)                              │  │
│  │    • WHERE active = true                              │  │
│  │    • lockForUpdate() - prevent concurrent changes     │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 4. Check for Duplicate Subscriptions                  │  │
│  │    • Query existing active subscriptions              │  │
│  │    • Throw exception if found                         │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 5. Create Subscriptions (3 records)                   │  │
│  │    • For each plan:                                   │  │
│  │      - Create subscription                            │  │
│  │      - Set status = 'active'                          │  │
│  │      - Calculate end_date based on interval           │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 6. Create Invoice (1 record)                          │  │
│  │    • Generate unique invoice number                   │  │
│  │    • Calculate subtotal = sum(plan.amount)            │  │
│  │    • Set tax_total = 0                                │  │
│  │    • Set total = subtotal + tax_total                 │  │
│  │    • Set due_date = now + 30 days                     │  │
│  │    • Set status = 'issued'                            │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 7. Create Invoice Items (3 records)                   │  │
│  │    • For each plan:                                   │  │
│  │      - Create invoice_item                            │  │
│  │      - Link to invoice_id                             │  │
│  │      - Link to price_plan_id                          │  │
│  │      - Set quantity = 1                               │  │
│  │      - Set unit_price = plan.amount                   │  │
│  │      - Set total = plan.amount                        │  │
│  └───────────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐  │
│  │ 8. Commit Transaction                                 │  │
│  │    • All successful → COMMIT                          │  │
│  │    • Any error → ROLLBACK                             │  │
│  └───────────────────────────────────────────────────────┘  │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │subscriptions │  │   invoices   │  │invoice_items │      │
│  ├──────────────┤  ├──────────────┤  ├──────────────┤      │
│  │ Record 1     │  │ Record 1     │  │ Record 1     │      │
│  │ (Plan 1)     │  │ (INV00001)   │  │ (Plan 1)     │      │
│  ├──────────────┤  └──────────────┘  ├──────────────┤      │
│  │ Record 2     │                    │ Record 2     │      │
│  │ (Plan 2)     │                    │ (Plan 2)     │      │
│  ├──────────────┤                    ├──────────────┤      │
│  │ Record 3     │                    │ Record 3     │      │
│  │ (Plan 3)     │                    │ (Plan 3)     │      │
│  └──────────────┘                    └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

## Data Flow Diagram

```
INPUT                    PROCESSING                   OUTPUT
─────                    ──────────                   ──────

Customer ID: 1           ┌──────────────┐            Invoice:
Plan IDs: [1, 2, 3]  ──► │   Validate   │            {
                         └──────┬───────┘              id: 1,
                                │                      number: "INV...",
                                ▼                      total: 179.97,
                         ┌──────────────┐              items: [
Plan 1: $29.99       ──► │  Fetch Plans │                {plan: 1, $29.99},
Plan 2: $49.99       ──► │  (with lock) │                {plan: 2, $49.99},
Plan 3: $99.99       ──► │              │                {plan: 3, $99.99}
                         └──────┬───────┘              ]
                                │                    }
                                ▼
                         ┌──────────────┐            Subscriptions:
                         │Check No Dupes│            [
                         └──────┬───────┘              {plan: 1, active},
                                │                      {plan: 2, active},
                                ▼                      {plan: 3, active}
                         ┌──────────────┐            ]
                         │   Create     │
                         │Subscriptions │
                         │  (3 records) │
                         └──────┬───────┘
                                │
                                ▼
                         ┌──────────────┐
Sum: $179.97         ◄── │   Create     │
Tax: $0.00           ◄── │   Invoice    │
Total: $179.97       ◄── │  (1 record)  │
                         └──────┬───────┘
                                │
                                ▼
                         ┌──────────────┐
                         │   Create     │
                         │Invoice Items │
                         │  (3 records) │
                         └──────┬───────┘
                                │
                                ▼
                         ┌──────────────┐
                         │    COMMIT    │
                         │ Transaction  │
                         └──────────────┘
```

## Database Relationships

```
┌─────────────────┐
│   organizations │
└────────┬────────┘
         │ 1
         │
         │ N
┌────────┴────────┐         ┌──────────────┐
│   customers     │────────►│   countries  │
└────────┬────────┘  N:1    └──────────────┘
         │ 1
         │
         ├──────────────────┐
         │                  │
         │ N                │ N
┌────────┴────────┐  ┌──────┴───────┐
│ subscriptions   │  │   invoices   │
└────────┬────────┘  └──────┬───────┘
         │ N                │ 1
         │                  │
         │                  │ N
         │           ┌──────┴───────────┐
         │           │  invoice_items   │
         │           └──────┬───────────┘
         │                  │ N
         │ N                │
         │           ┌──────┴───────┐
         └──────────►│ price_plans  │
              N:1    └──────┬───────┘
                            │ N
                            │
                            │ 1
                     ┌──────┴───────┐
                     │   products   │
                     └──────────────┘
```

## Concurrency Scenario

```
Request A                    Database                    Request B
─────────                    ────────                    ─────────
(same time)                                             (same time)

1. Start Transaction         [ISOLATED]                 1. Start Transaction
   │                                                        │
2. Fetch Plans               [LOCK ROWS]                2. Wait for lock...
   lockForUpdate()           Plans 1,2,3                   │
   │                         (locked by A)                 │
3. Check duplicates                                        │
   None found                                              │
   │                                                        │
4. Create subscriptions                                    │
   │                                                        │
5. Create invoice                                          │
   │                                                        │
6. Create items                                            │
   │                                                        │
7. COMMIT                    [RELEASE LOCK]                │
   │                                                        ▼
   │                                                     2. Fetch Plans
   │                                                        lockForUpdate()
   │                                                        │
   │                                                     3. Check duplicates
   │                                                        FOUND! ❌
   │                                                        │
   │                                                     4. Throw Exception
   │                                                        │
   │                                                     5. ROLLBACK
   │                                                        │
SUCCESS ✅                                               ERROR 400 ❌
```

## Error Handling Flow

```
┌─────────────────┐
│  API Request    │
└────────┬────────┘
         │
         ▼
    ┌─────────┐   Invalid?
    │Validate │───────┐
    │ Input   │       │
    └────┬────┘       │
         │            ▼
         │      ┌──────────┐
         │      │Return 422│
         │      │Validation│
         │      │  Error   │
         │      └──────────┘
         │
         ▼
    ┌─────────┐
    │  Start  │
    │  Trans  │
    └────┬────┘
         │
         ▼
    ┌─────────┐   Not found?
    │  Fetch  │───────┐
    │  Plans  │       │
    └────┬────┘       │
         │            ▼
         │      ┌──────────┐
         │      │Rollback  │
         │      │Return 400│
         │      │  Error   │
         │      └──────────┘
         │
         ▼
    ┌─────────┐   Found?
    │  Check  │───────┐
    │  Dupes  │       │
    └────┬────┘       │
         │            ▼
         │      ┌──────────┐
         │      │Rollback  │
         │      │Return 400│
         │      │Duplicate │
         │      └──────────┘
         │
         ▼
    ┌─────────┐
    │ Create  │
    │  All    │
    │ Records │
    └────┬────┘
         │
         ▼
    ┌─────────┐
    │ Commit  │
    └────┬────┘
         │
         ▼
    ┌─────────┐
    │Return   │
    │  201    │
    │ Success │
    └─────────┘
```

## Invoice Number Generation

```
Current Date: 2026-01-09

┌─────────────────────────────────────┐
│ Query last invoice for today        │
│ WHERE invoice_number LIKE 'INV20260109%' │
└────────────┬────────────────────────┘
             │
             ▼
       ┌──────────┐
       │  Found?  │
       └──┬───┬───┘
          │   │
      Yes │   │ No
          │   │
          ▼   ▼
    ┌─────┐ ┌─────┐
    │Last │ │Start│
    │=0003│ │=0001│
    └──┬──┘ └──┬──┘
       │       │
       ▼       │
    ┌─────┐    │
    │+1   │    │
    │=0004│    │
    └──┬──┘    │
       │       │
       └───┬───┘
           ▼
    ┌────────────┐
    │ Format:    │
    │ INV        │  Prefix
    │ 20260109   │  YYYYMMDD
    │ 0004       │  Sequence
    ├────────────┤
    │INV20260109 │
    │0004        │
    └────────────┘
```

## Subscription Date Calculation

```
Input:
  Start Date: 2026-01-09
  Billing Interval: monthly

┌──────────────────┐
│  Start Date      │
│  2026-01-09      │
└────────┬─────────┘
         │
         ▼
    ┌─────────────┐
    │Check Interval│
    └─────┬───────┘
          │
    ┌─────┴──────┐
    │            │
    ▼            ▼
daily         monthly        yearly
│             │              │
+1 day        +1 month       +1 year
│             │              │
▼             ▼              ▼
2026-01-10    2026-02-09     2027-01-09

End Date = Next Billing Date
```

---

**Legend:**
- `→` : Data flow
- `┌─┐` : Process/Component
- `─┼─` : Decision point
- `...` : Continuation
- `[LOCK]` : Database lock
- `✅` : Success
- `❌` : Error
