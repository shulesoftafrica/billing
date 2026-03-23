# create subscription invoice - DOCUMENTED ✅

## Issue
The subscription invoice creation endpoint documentation was unclear:
- The blade documentation showed a simplified endpoint (`POST /api/v1/subscriptions`) 
- The proper main endpoint (`POST /api/v1/invoices`) was not clearly documented in the subscriptions section
- Users were confused about which endpoint to use

## Solution
Updated documentation to show the **primary method** for creating subscription invoices:

### Proper Endpoint: POST /api/v1/invoices

This is the **main, feature-rich endpoint** for creating subscription invoices:

```json
POST /api/v1/invoices

{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    },
    {
      "price_plan_id": 12,
      "amount": 75000
    }
  ],
  "description": "Monthly subscription - SafariChat Platform",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
```

**Required Parameters:**
- `organization_id` (integer) - Your organization ID
- `customer` (object) - Customer information
  - `name` (string) - Customer's full name
  - `email` (string) - Customer's email address
  - `phone` (string) - Customer's phone number
- `products` (array) - Array of products with subscription price plans
  - `price_plan_id` (integer) - Price plan ID for subscription product
  - `amount` (number) - Invoice amount for this product
- `currency` (string) - 3-letter currency code

**Optional Parameters:**
- `description` (string) - Invoice description
- `status` (string) - Invoice status (default: "issued")
- `tax_rate_ids` (array) - Array of tax rate IDs to apply
- `payment_gateway` (string) - "flutterwave", "control_number", or "both"
- `success_url` (string) - URL to redirect after successful payment
- `cancel_url` (string) - URL to redirect after cancelled payment

### Why This Endpoint?

**POST /api/v1/invoices** advantages:
- ✅ Automatically creates/finds customers (no need for customer_id)
- ✅ Creates subscription records automatically when price plan belongs to subscription product
- ✅ Generates control numbers and payment links based on payment_gateway parameter
- ✅ Supports tax calculations with tax_rate_ids
- ✅ Can mix one-time and subscription products in single invoice
- ✅ More flexible with custom amounts, descriptions, and statuses
- ✅ Idempotent behavior (prevents duplicate subscriptions)
- ✅ Returns comprehensive response with invoice, subscriptions, and payment details

### Response Format
Full invoice response includes:
- **Invoice details:** id, invoice_number, status, totals, due_date
- **Invoice items:** All subscription products with details
- **Subscriptions:** Array of created subscriptions (status: pending until paid)
- **Payment details:** Control numbers and/or Flutterwave payment links
- **Customer:** Customer information

### Alternative: POST /api/v1/subscriptions

There's also a simplified endpoint (`POST /api/v1/subscriptions`) that:
- Takes only `customer_id` and `plan_ids` array
- Customer must already exist
- Less flexible (no tax support, no custom amounts, no payment gateway choice)
- Suitable for basic use cases only

**Recommendation:** Use `POST /api/v1/invoices` for all subscription invoice creation.

### Files Modified
1. `resources/views/docs/sections/subscriptions.blade.php`
   - Changed endpoint from `/api/v1/subscriptions` to `/api/v1/invoices`
   - Updated request body to show full customer object and products array
   - Added comprehensive parameter documentation
   - Updated response example to match invoice controller output
   - Added notes about automatic customer creation and subscription lifecycle

## Verification
Test the proper endpoint:
```bash
POST /api/v1/invoices
Authorization: Bearer {token}
Content-Type: application/json

{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    }
  ],
  "currency": "TZS",
  "payment_gateway": "both"
}
```

Expected: 201 Created with full invoice, subscriptions, payment_details, and customer info. 