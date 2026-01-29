# Subscription System - Architecture & Flow Diagrams

## System Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                         CLIENT                              │
│                    (Postman, Frontend)                      │
└──────────────────────────────────────────────────────────────┘
                         │
                         │ HTTP POST /api/subscriptions
                         │ {customer_id, plan_ids: [1,2,3]}
                         ▼
┌──────────────────────────────────────────────────────────────┐
│                  SubscriptionController                     │
│  • Validate request (Laravel Validator)                     │
│  • Remove duplicate plan IDs                                │
│  • Call service layer                                       │
│  • Format response                                          │
└──────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────────┐
│                  SubscriptionService                        │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 1. Validate Input                                     │  │
│  │    • Check customer exists                            │  │
│  │    • Validate plan IDs                                │  │
│  │    • Ensure minimum 1 plan                            │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 2. Start Database Transaction                         │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 3. Fetch & Lock Price Plans                           │  │
│  │    • WHERE id IN (1,2,3)                              │  │
│  │    • WHERE active = true                              │  │
│  │    • lockForUpdate() - prevent concurrent changes     │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 4. Check for Duplicate Subscriptions                  │  │
│  │    • Query existing active subscriptions              │  │
│  │    • Throw exception if found                         │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 5. Create Subscriptions (3 records)                   │  │
│  │    • For each plan:                                   │  │
│  │      - Create subscription                            │  │
│  │      - Set status = 'active'                          │  │
│  │      - Calculate end_date based on interval           │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 6. Create Invoice (1 record)                          │  │
│  │    • Generate unique invoice number                   │  │
│  │    • Calculate subtotal = sum(plan.amount)            │  │
│  │    • Set tax_total = 0                                │  │
│  │    • Set total = subtotal + tax_total                 │  │
│  │    • Set due_date = now + 30 days                     │  │
│  │    • Set status = 'issued'                            │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 7. Create Invoice Items (3 records)                   │  │
│  │    • For each plan:                                   │  │
│  │      - Create invoice_item                            │  │
│  │      - Link to invoice_id                             │  │
│  │      - Link to price_plan_id                          │  │
│  │      - Set quantity = 1                               │  │
│  │      - Set unit_price = plan.amount                   │  │
│  │      - Set total = plan.amount                        │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ 8. Commit Transaction                                 │  │
│  │    • All successful → COMMIT                          │  │
│  │    • Any error → ROLLBACK                             │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────────┐
│                      DATABASE                               │
│  ┌────────────────┐  ┌───────────────┐  ┌──────────────────┐│
│  │subscriptions   │  │   invoices    │  │ invoice_items    ││
│  │ Record 1 (P1)  │  │ Record 1      │  │ Record 1 (P1)    ││
│  │ Record 2 (P2)  │  │ (INV00001)    │  │ Record 2 (P2)    ││
│  │ Record 3 (P3)  │  │               │  │ Record 3 (P3)    ││
└──────────────────────────────────────────────────────────────┘
