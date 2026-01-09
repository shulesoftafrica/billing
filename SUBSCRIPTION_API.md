# Subscription and Invoice System

## Overview

This system allows customers to subscribe to multiple price plans simultaneously, with all subscriptions billed together under a single invoice.

## Key Features

- ✅ Support for multiple price plans in a single subscription request
- ✅ Database transactions for atomicity (rollback on failure)
- ✅ Duplicate subscription prevention
- ✅ Thread-safe with row-level locking
- ✅ Automatic invoice generation
- ✅ Invoice item tracking per plan
- ✅ Unique invoice numbering

## Architecture

### Models

1. **Subscription** - Represents a customer's subscription to a specific price plan
2. **Invoice** - A billing document for one or more subscriptions
3. **InvoiceItem** - Individual line items on an invoice (one per price plan)

### Service Layer

**SubscriptionService** handles all business logic:
- Input validation
- Duplicate subscription checks
- Transaction management
- Subscription creation
- Invoice and invoice item generation

### Controller

**SubscriptionController** handles HTTP requests and responses.

## API Endpoint

### Create Subscriptions

**POST** `/api/subscriptions`

#### Request Body

```json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}
```

#### Validation Rules

- `customer_id`: Required, must exist in customers table
- `plan_ids`: Required array, minimum 1 item
- `plan_ids.*`: Each ID must exist in price_plans table

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": {
      "id": 1,
      "invoice_number": "INV202601091234",
      "status": "issued",
      "subtotal": "99.99",
      "tax_total": "0.00",
      "total": "99.99",
      "due_date": "2026-02-08",
      "issued_at": "2026-01-09T12:34:56.000000Z"
    },
    "invoice_items": [
      {
        "id": 1,
        "price_plan_id": 1,
        "plan_name": "Basic Plan",
        "quantity": "1.00",
        "unit_price": "29.99",
        "total": "29.99"
      },
      {
        "id": 2,
        "price_plan_id": 2,
        "plan_name": "Premium Plan",
        "quantity": "1.00",
        "unit_price": "49.99",
        "total": "49.99"
      },
      {
        "id": 3,
        "price_plan_id": 3,
        "plan_name": "Enterprise Plan",
        "quantity": "1.00",
        "unit_price": "99.99",
        "total": "99.99"
      }
    ],
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

#### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "customer_id": ["The customer id field is required."],
    "plan_ids": ["The plan ids must be an array."]
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Customer already has active subscriptions for plan IDs: 1, 2"
}
```

## Business Logic Details

### Subscription Creation Flow

1. **Validate Input**
   - Ensure plan_ids array exists and has at least 1 item
   - Verify customer exists
   - Validate all plan IDs are valid integers

2. **Start Transaction**
   - Lock price plan rows to prevent race conditions

3. **Check Duplicates**
   - Query for active subscriptions with same customer_id and plan_ids
   - Throw exception if duplicates found

4. **Create Subscriptions**
   - One subscription record per price plan
   - Status: 'active'
   - Calculate start_date, end_date, next_billing_date based on billing_interval

5. **Create Invoice**
   - Generate unique invoice number (format: INV + YYYYMMDD + sequence)
   - Calculate subtotal (sum of all plan amounts)
   - Set status to 'issued'
   - Set due_date to 30 days from now

6. **Create Invoice Items**
   - One invoice_item per price plan
   - Link to invoice and price plan
   - Set quantity = 1, unit_price = plan amount

7. **Commit Transaction**
   - If any step fails, entire transaction rolls back

### Invoice Number Format

`INV[YYYYMMDD][SEQUENCE]`

Example: `INV202601090001`

- Prefix: INV
- Date: 20260109 (January 9, 2026)
- Sequence: 0001 (4-digit auto-increment per day)

### Subscription End Date Calculation

Based on `billing_interval` from the price plan:

- **daily**: +1 day
- **weekly**: +1 week
- **monthly**: +1 month
- **quarterly**: +3 months
- **yearly**: +1 year

### Thread Safety

- Uses database transactions with `lockForUpdate()` on price plans
- Prevents concurrent requests from creating duplicate subscriptions
- Invoice number generation uses database queries to ensure uniqueness

## Testing the API

### Using cURL

```bash
curl -X POST http://localhost/api/subscriptions \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "plan_ids": [1, 2, 3]
  }'
```

### Using Postman

1. Create a new POST request
2. URL: `http://localhost/api/subscriptions`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}
```

## Database Schema

### subscriptions

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| customer_id | bigint | Foreign key to customers |
| price_plan_id | bigint | Foreign key to price_plans |
| status | enum | active, paused, canceled |
| start_date | date | Subscription start date |
| end_date | date | Subscription end date |
| next_billing_date | date | Next billing cycle date |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

### invoices

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| customer_id | bigint | Foreign key to customers |
| invoice_number | string | Unique invoice identifier |
| subscription_id | bigint | Optional reference to subscription |
| status | enum | draft, issued, paid, overdue, canceled |
| description | string | Invoice description |
| subtotal | decimal(15,2) | Sum before tax |
| tax_total | decimal(15,2) | Total tax amount |
| total | decimal(15,2) | Final amount due |
| due_date | date | Payment due date |
| issued_at | timestamp | When invoice was issued |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

### invoice_items

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_id | bigint | Foreign key to invoices |
| price_plan_id | bigint | Foreign key to price_plans |
| quantity | decimal(15,2) | Item quantity |
| unit_price | decimal(15,2) | Price per unit |
| total | decimal(15,2) | Line item total |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record update time |

## Error Handling

The system handles various error scenarios:

1. **Missing required fields**: 422 validation error
2. **Invalid customer ID**: 422 validation error
3. **Invalid plan IDs**: 400 error with message
4. **Inactive price plans**: 400 error with message
5. **Duplicate subscriptions**: 400 error with existing plan IDs
6. **Database errors**: Transaction rollback, 400 error

## Future Enhancements

- [ ] Tax calculation based on customer location
- [ ] Proration for mid-cycle subscriptions
- [ ] Discount codes and coupons
- [ ] Free trial periods
- [ ] Subscription pause/resume functionality
- [ ] Automatic recurring billing
- [ ] Payment processing integration
- [ ] Invoice PDF generation
- [ ] Email notifications
- [ ] Subscription upgrade/downgrade flows
