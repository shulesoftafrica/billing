# 🚀 API Quick Reference Guide

**Last Updated:** March 9, 2026

## 🔑 Authentication

```bash
# Login
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password123",
  "device_name": "Postman"
}

# Use token in all requests
Authorization: Bearer {your_token}
```

---

## 📋 Most Common Endpoints

### Create Customer
```http
POST /api/customers
{
  "organization_id": 1,
  "name": "John Smith",
  "email": "john@example.com",
  "phone": "+255712345678",
  "type": "individual"
}
```

### Create Product with Price Plans
```http
POST /api/products
{
  "organization_id": 1,
  "product_type_id": 1,
  "name": "Premium Plan",
  "product_code": "PREM-001",
  "price_plans": [
    {
      "name": "Monthly",
      "amount": 29.99,
      "currency_id": 1,
      "billing_interval": "monthly",
      "billing_type": "recurring"
    }
  ]
}
```

### Create Subscription Invoice
```http
POST /api/invoices
{
  "customer_id": 1,
  "invoice_type": "subscription",
  "billing_cycle": "monthly",
  "payment_gateway": "flutterwave",
  "items": [
    {
      "price_plan_id": 1,
      "quantity": 1
    }
  ]
}
```

### Create Wallet Topup
```http
POST /api/invoices/wallet-topup
{
  "customer_id": 1,
  "amount": 100.00,
  "wallet_type": "sms_credits",
  "units": 1000,
  "payment_gateway": "flutterwave"
}
```

### Check Wallet Balance
```http
GET /api/wallets/balance?customer_id=1&wallet_type=sms_credits
```

### Deduct from Wallet
```http
POST /api/wallets/deduct
{
  "customer_id": 1,
  "wallet_type": "sms_credits",
  "units": 50,
  "description": "SMS sending"
}
```

### Cancel Subscription
```http
POST /api/subscriptions/{id}/cancel
{
  "cancellation_reason": "No longer needed"
}
```

---

## 📊 Payment Gateways

### Available Gateways
- `control_number` - EcoBank UCN
- `flutterwave` - Flutterwave (cards, mobile money)
- `stripe` - Stripe (international cards)
- `both` - Generate both control number and Flutterwave link

### Payment Gateway in Invoices
```http
POST /api/invoices
{
  "customer_id": 1,
  "invoice_type": "one_time",
  "payment_gateway": "both",
  "items": [...]
}
```

Response includes:
```json
{
  "invoice": {...},
  "payment_link": {
    "control_number": "CN123456789",
    "flutterwave": {
      "link": "https://checkout.flutterwave.com/...",
      "reference": "FLW-INV-1-123456"
    }
  }
}
```

---

## 🔄 Invoice Types

### 1. One-Time Invoice
```http
POST /api/invoices
{
  "invoice_type": "one_time",
  "customer_id": 1,
  "items": [...]
}
```

### 2. Subscription Invoice
```http
POST /api/invoices
{
  "invoice_type": "subscription",
  "customer_id": 1,
  "billing_cycle": "monthly",
  "items": [...]
}
```

### 3. Wallet Topup Invoice
```http
POST /api/invoices/wallet-topup
{
  "customer_id": 1,
  "amount": 100.00,
  "wallet_type": "sms_credits",
  "units": 1000
}
```

### 4. Plan Upgrade
```http
POST /api/invoices/plan-upgrade
{
  "subscription_id": 1,
  "new_price_plan_id": 3,
  "proration": true
}
```

### 5. Plan Downgrade
```http
POST /api/invoices/plan-downgrade
{
  "subscription_id": 1,
  "new_price_plan_id": 1,
  "apply_credit": true
}
```

---

## 💳 Wallet Operations

### Get Balance
```http
GET /api/wallets/balance?customer_id=1&wallet_type=credits
```

### Add Credits
```http
POST /api/wallets/credit
{
  "customer_id": 1,
  "wallet_type": "credits",
  "units": 1000,
  "unit_price": 0.10,
  "description": "Topup"
}
```

### Deduct Credits
```http
POST /api/wallets/deduct
{
  "customer_id": 1,
  "wallet_type": "credits",
  "units": 50,
  "description": "Usage"
}
```

### Transfer Credits
```http
POST /api/wallets/transfer
{
  "from_customer_id": 1,
  "to_customer_id": 2,
  "wallet_type": "credits",
  "units": 100
}
```

### Get Transaction History
```http
GET /api/wallets/1/transactions?wallet_type=credits&limit=20
```

---

## 🔍 Lookup Endpoints

### Customer by Phone
```http
GET /api/customers/by-phone/+255712345678/status
```

### Customer by Email
```http
GET /api/customers/by-email/john@example.com/status
```

### Product by Code
```http
GET /api/products/by-code/PREM-001
```

### Payments by Invoice
```http
GET /api/payments/by-invoice/1
```

### Payments by Date Range
```http
GET /api/payments?date_from=2026-03-01&date_to=2026-03-09&customer_id=1
```

---

## 🔔 Webhook Endpoints (Public)

### EcoBank UCN
```http
POST /api/webhooks/ecobank/notification
{
  "control_number": "CN123456789",
  "amount": 50000.00,
  "payment_reference": "PAY-REF-123",
  "status": "success"
}
```

### Flutterwave
```http
POST /api/webhooks/flutterwave
Header: verif-hash: {signature}
{
  "event": "charge.completed",
  "data": {
    "tx_ref": "FLW-INV-1-123",
    "amount": 99.99,
    "status": "successful"
  }
}
```

### Stripe
```http
POST /api/webhooks/stripe
Header: stripe-signature: {signature}
{
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_123",
      "amount": 9999,
      "metadata": {"invoice_id": "1"}
    }
  }
}
```

---

## 🎯 Common Query Parameters

### Pagination
- `per_page` - Items per page (default: 15)
- Example: `GET /api/invoices?per_page=20`

### Filtering
- `organization_id` - Filter by organization
- `customer_id` - Filter by customer
- `product_id` - Filter by product
- `status` - Filter by status
- Example: `GET /api/invoices?organization_id=1&status=paid`

### Date Ranges
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)
- Example: `GET /api/payments?date_from=2026-03-01&date_to=2026-03-09`

---

## 📝 Standard Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Error detail"]
  }
}
```

---

## 🔢 HTTP Status Codes

- `200 OK` - Success
- `201 Created` - Resource created
- `400 Bad Request` - Invalid request
- `401 Unauthorized` - Authentication failed
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

---

## 💡 Quick Tips

### Invoice Number Format
- `INV[YYYYMMDD][XXXX]`
- Example: `INV20260309001`
- Auto-generated and unique

### Billing Intervals
- `daily`, `weekly`, `monthly`, `quarterly`, `yearly`

### Invoice Statuses
- `draft`, `issued`, `paid`, `cancelled`, `overdue`

### Subscription Statuses
- `active`, `cancelled`, `expired`, `suspended`

### Payment Methods
- `card`, `mobile_money`, `bank_transfer`, `cash`

### User Roles
- `admin`, `finance`, `support`

### Customer Types
- `individual`, `business`

---

## 🔐 Authentication Tips

### Generate Personal Access Token
```http
POST /api/auth/generate-token
{
  "name": "Production Token",
  "expires_at": "2027-03-09T00:00:00Z",
  "abilities": ["read", "write"]
}
```

### List All Tokens
```http
GET /api/auth/tokens
```

### Revoke Specific Token
```http
DELETE /api/auth/tokens/{token_id}
```

### Revoke All Tokens
```http
POST /api/auth/logout-all
```

---

## 🧪 Testing Endpoints

### Test Payment Gateway
```http
GET /api/payment-gateways/test-connection?gateway_code=flutterwave
```

### Test All Gateways
```http
GET /api/payment-gateways/test-all-connections
```

### Generate Flutterwave Hash (for testing)
```http
POST /api/flutterwave/hash
{
  "event": "charge.completed",
  "data": {...}
}
```

---

## 📚 Full Documentation

For complete API documentation with all endpoints, validation rules, and examples, see:
- **[API Documentation V2](API_DOCUMENTATION_V2.md)** - Complete reference (95+ endpoints)
- **[Postman Collection](POSTMAN_API_DOCUMENTATION.md)** - Original documentation

---

**End of Quick Reference**  
**Version 2.0** | **Last Updated: March 9, 2026**
