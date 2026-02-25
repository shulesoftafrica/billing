# Subscription & Invoice API Reference

## Overview
This system allows customers to subscribe to multiple price plans simultaneously, with all subscriptions billed together under a single invoice.

## Key Features
- Multiple plans in one request
- Single invoice for all plans
- Atomic transactions (all-or-nothing)
- Duplicate prevention
- Thread safety (row-level locking)
- Unique invoice numbers (INV[YYYYMMDD][XXXX])

## Quick Start
### API Request
```bash
POST /api/subscriptions
Content-Type: application/json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}
```
### Response (Success)
```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": { "id": 1, "invoice_number": "INV202601090001", "total": "179.97" },
    "invoice_items": [...],
    "customer": {...}
  }
}
```

## What Happens Internally
1. Validate customer and plans
2. Lock price plan rows
3. Prevent duplicate subscriptions
4. Create subscriptions (one per plan)
5. Generate invoice and items
6. Commit or rollback

## Database Records Created
- N subscriptions
- 1 invoice
- N invoice_items

## API Endpoint
**POST** `/api/subscriptions`
- `customer_id`: Required, must exist
- `plan_ids`: Required array, min 1, must exist

## Success Response (201 Created)
```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": { "id": 1, "invoice_number": "INV202601091234", "status": "issued", "subtotal": "99.99", "tax_total": "0.00", "total": "99.99", "due_date": "2026-02-08", "issued_at": "2026-01-09T12:34:56.000000Z" },
    "invoice_items": [
      { "id": 1, "price_plan_id": 1, "plan_name": "Basic Plan", "quantity": "1.00", "unit_price": "29.99", "total": "29.99" },
      { "id": 2, "price_plan_id": 2, "plan_name": "Premium Plan", "quantity": "1.00", "unit_price": "49.99", "total": "49.99" },
      { "id": 3, "price_plan_id": 3, "plan_name": "Enterprise Plan", "quantity": "1.00", "unit_price": "99.99", "total": "99.99" }
    ]
  }
}
```

## Configuration
- Subscription dates: start_date, end_date, next_billing_date
- Billing intervals: daily, weekly, monthly, quarterly, yearly
- Invoice: subtotal, tax_total, total, due_date, status
