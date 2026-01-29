# 🚀 Billing System - Complete API Documentation

## 📋 Overview

This billing system provides a comprehensive API for managing organizations, customers, products, subscriptions, invoices, payments, and wallets. The system supports:
- ✅ **Multiple Organizations** - Multi-tenant architecture
- ✅ **Payment Gateways** - UNC, Flutterwave, Stripe integration
- ✅ **Automated Invoicing** - Subscription and one-time billing
- ✅ **Wallet Management** - Credits, topup, deductions
- ✅ **Full CRUD Operations** - Products, customers, invoices

**Base URL:** `http://localhost:8000/api`

---

## 🚀 Quick Start Authentication

**Authentication Flow:**

1. **Login to get Bearer token:**
   ```http
   POST /api/auth/login
   Content-Type: application/json
   
   {
     "email": "your_email@example.com",
     "password": "your_password",
     "device_name": "Postman"
   }
   ```

2. **Use the token in all subsequent requests:**
   ```
   Authorization: Bearer {your_access_token}
   ```

---

## 📚 Table of Contents

1. [Authentication](#authentication)
2. [Countries](#countries)
3. [Currencies](#currencies)
4. [Organizations](#organizations)
5. [Users](#users)
6. [Customers](#customers)
7. [Products](#products)
8. [Price Plans](#price-plans)
9. [Invoices](#invoices)
10. [Subscriptions](#subscriptions)
11. [Wallets](#wallets)
12. [Payments](#payments)
13. [Webhooks](#webhooks)

---

## 🔐 Authentication

### Login
```http
POST /api/auth/login
```

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password123",
  "device_name": "Postman"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "admin@example.com",
      "organization": {...}
    },
    "bearer_token": "1|abc123...",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

### Register
```http
POST /api/auth/register
```

**Request:**
```json
{
  "organization_id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin",
  "sex": "F",
  "device_name": "Postman"
}
```

### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

---

## 🌍 Countries

---

## 🌍 Countries

### List Countries
```http
GET /api/countries
```

**Headers:**
```
Accept: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Tanzania",
      "code": "TZA",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### Create Country
```http
POST /api/countries
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Kenya",
  "code": "KEN"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Country created successfully",
  "data": {
    "id": 2,
    "name": "Kenya",
    "code": "KEN"
  }
}
```

---

## 💱 Currencies

### List Currencies
```http
GET /api/currencies
```

### Create Currency
```http
POST /api/currencies
Authorization: Bearer {token}
```

**Request:**
```json
{
  "code": "USD",
  "name": "US Dollar",
  "symbol": "$"
}
```

---

## 🏢 Organizations

### List Organizations
```http
GET /api/organizations
Authorization: Bearer {token}
```

### Create Organization
```http
POST /api/organizations
Authorization: Bearer {token}
```

**Request:**
```json
{
  "name": "Tech Solutions Inc",
  "legal_name": "Tech Solutions Incorporated",
  "currency_id": 1,
  "country_id": 1,
  "timezone": "Africa/Dar_es_Salaam"
}
```

---

## 👥 Customers

### List Customers
```http
GET /api/customers
Authorization: Bearer {token}
```

### Create Customer
```http
POST /api/customers
Authorization: Bearer {token}
```

**Request:**
```json
{
  "organization_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+255712345678",
  "type": "individual"
}
```

### Get Customer
```http
GET /api/customers/{id}
Authorization: Bearer {token}
```

### Update Customer
```http
PUT /api/customers/{id}
Authorization: Bearer {token}
```

### Delete Customer
```http
DELETE /api/customers/{id}
Authorization: Bearer {token}
```

---

## 📦 Products

### List Products
```http
GET /api/products
Authorization: Bearer {token}
```

**Query Parameters:**
- `organization_id` (optional) - Filter by organization

### Get Product by Code
```http
GET /api/products/by-code/{product_code}
Authorization: Bearer {token}
```

### Create Product
```http
POST /api/products
Authorization: Bearer {token}
```

**Request:**
```json
{
  "organization_id": 1,
  "product_type_id": 1,
  "name": "Premium Subscription",
  "product_code": "PREM-001",
  "description": "Premium features package",
  "price_plans": [
    {
      "name": "Monthly Plan",
      "amount": 29.99,
      "currency_id": 1,
      "billing_interval": "monthly",
      "billing_type": "recurring"
    }
  ]
}
```

### Update Product
```http
PUT /api/products/{id}
Authorization: Bearer {token}
```

### Delete Product
```http
DELETE /api/products/{id}
Authorization: Bearer {token}
```

---

## 💰 Price Plans

### List Price Plans
```http
GET /api/price-plans
Authorization: Bearer {token}
```

### Create Price Plan
```http
POST /api/price-plans
Authorization: Bearer {token}
```

**Request:**
```json
{
  "product_id": 1,
  "name": "Annual Plan",
  "amount": 299.99,
  "currency_id": 1,
  "billing_interval": "yearly",
  "billing_type": "recurring"
}
```

---

## 🧾 Invoices

### List Invoices
```http
GET /api/invoices
Authorization: Bearer {token}
```

**Query Parameters:**
- `product_id` (optional) - Filter by product
- `status` (optional) - Filter by status (draft, issued, paid, cancelled)

### Get Invoice
```http
GET /api/invoices/{id}
Authorization: Bearer {token}
```

**Response includes full relationships:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV20260129001",
    "customer": {...},
    "items": [...],
    "subscription": {...},
    "payments": [...]
  }
}
```

### Create One-Time Invoice
```http
POST /api/invoices
Authorization: Bearer {token}
```

**Request:**
```json
{
  "customer_id": 1,
  "invoice_type": "one_time",
  "items": [
    {
      "price_plan_id": 1,
      "quantity": 1,
      "unit_price": 99.99
    }
  ]
}
```

### Create Subscription Invoice
```http
POST /api/invoices
Authorization: Bearer {token}
```

**Request:**
```json
{
  "customer_id": 1,
  "invoice_type": "subscription",
  "billing_cycle": "monthly",
  "items": [
    {
      "price_plan_id": 2,
      "quantity": 1
    }
  ]
}
```

**Note:** Automatically creates a subscription when `invoice_type='subscription'`

### Wallet Topup Invoice
```http
POST /api/invoices/wallet-topup
Authorization: Bearer {token}
```

**Request:**
```json
{
  "customer_id": 1,
  "amount": 100.00,
  "wallet_type": "credits",
  "units": 1000
}
```

### Upgrade Subscription
```http
POST /api/invoices/plan-upgrade
Authorization: Bearer {token}
```

**Request:**
```json
{
  "subscription_id": 1,
  "new_price_plan_id": 3,
  "proration": true
}
```

### Downgrade Subscription
```http
POST /api/invoices/plan-downgrade
Authorization: Bearer {token}
```

**Request:**
```json
{
  "subscription_id": 1,
  "new_price_plan_id": 1,
  "apply_credit": true
}
```

### Update Invoice
```http
PUT /api/invoices/{id}
Authorization: Bearer {token}
```

### Delete Invoice
```http
DELETE /api/invoices/{id}
Authorization: Bearer {token}
```

---

## 🔄 Subscriptions

### List Subscriptions
```http
GET /api/subscriptions
Authorization: Bearer {token}
```

### Get Subscription
```http
GET /api/subscriptions/{id}
Authorization: Bearer {token}
```

### Cancel Subscription
```http
POST /api/subscriptions/{id}/cancel
Authorization: Bearer {token}
```

**Request:**
```json
{
  "cancellation_reason": "No longer needed"
}
```

---

## 💳 Wallets

### Get Wallet Balance
```http
GET /api/wallets/balance
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (required)
- `wallet_type` (optional) - credits, points, balance

**Example:**
```http
GET /api/wallets/balance?customer_id=1&wallet_type=credits
```

**Response:**
```json
{
  "success": true,
  "data": {
    "customer_id": 1,
    "wallet_type": "credits",
    "balance": 1500.00
  }
}
```

### Credit Wallet
```http
POST /api/wallets/credit
Authorization: Bearer {token}
```

**Request:**
```json
{
  "customer_id": 1,
  "wallet_type": "credits",
  "amount": 500.00,
  "description": "Wallet topup"
}
```

### Deduct from Wallet
```http
POST /api/wallets/deduct
Authorization: Bearer {token}
```

**Request:**
```json
{
  "customer_id": 1,
  "wallet_type": "credits",
  "amount": 50.00,
  "description": "SMS credits usage"
}
```

### Get Wallet Transactions
```http
GET /api/wallets/transactions
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (required)
- `wallet_type` (optional)

**Example:**
```http
GET /api/wallets/transactions?customer_id=1&wallet_type=credits
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "transaction_type": "credit",
      "amount": 500.00,
      "balance_after": 1500.00,
      "description": "Wallet topup",
      "created_at": "2026-01-29T10:00:00.000000Z"
    }
  ]
}
```

---

## 💸 Payments

### List Payments
```http
GET /api/payments
Authorization: Bearer {token}
```

**Query Parameters:**
- `date_from` (optional) - Filter from date (YYYY-MM-DD)
- `date_to` (optional) - Filter to date (YYYY-MM-DD)

**Example:**
```http
GET /api/payments?date_from=2026-01-01&date_to=2026-01-31
```

### Get Payments by Invoice
```http
GET /api/payments/by-invoice/{invoice_id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "invoice_id": 1,
      "amount": 99.99,
      "status": "success",
      "payment_method": "card",
      "gateway": "stripe",
      "paid_at": "2026-01-29T10:00:00.000000Z"
    }
  ]
}
```

---

## 🔔 Webhooks

### UNC Payment Webhook
```http
POST /api/webhooks/unc-payment
```

**Public endpoint** - No authentication required

**Expected Payload:**
```json
{
  "control_number": "CN123456789",
  "amount": 50000,
  "payment_reference": "PAY123",
  "status": "success"
}
```

### Flutterwave Webhook
```http
POST /api/webhooks/flutterwave
```

**Public endpoint** - Signature verification required

**Headers:**
```
verif-hash: {flutterwave_signature}
```

**Expected Events:**
- `charge.completed` - Payment successful
- `charge.failed` - Payment failed

**Payload:**
```json
{
  "event": "charge.completed",
  "data": {
    "id": "FLW123",
    "amount": 50000,
    "meta": {
      "invoice_id": 1
    },
    "payment_type": "card"
  }
}
```

### Stripe Webhook
```http
POST /api/webhooks/stripe
```

**Public endpoint** - Signature verification required

**Headers:**
```
stripe-signature: {stripe_signature}
```

**Expected Events:**
- `payment_intent.succeeded` - Payment successful
- `payment_intent.payment_failed` - Payment failed
- `invoice.payment_succeeded` - Invoice payment successful

**Payload:**
```json
{
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_123",
      "amount": 5000,
      "metadata": {
        "invoice_id": 1
      }
    }
  }
}
```

---

## 📊 Implementation Status

### ✅ Completed Features (24/29)
- Product Management (5/5)
- Invoice & Subscription (10/10)
- Wallet Management (4/4)
- Payment Management (6/6)
  - UNC Payment Gateway
  - Flutterwave Integration
  - Stripe Integration

### ❌ Pending Features (5/29)
- Testing environment setup
- Production environment setup
- Landing page
- Admin dashboard
- Enhanced documentation

---

## 🔒 Error Responses

All endpoints return consistent error responses:

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Resource not found"
}
```

**500 Server Error:**
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## 📝 Notes

- All timestamps are in UTC
- All monetary amounts use 2 decimal precision
- Invoice numbers follow format: `INV[YYYYMMDD][XXXX]`
- Subscriptions auto-renew based on billing interval
- Wallet transactions are atomic and logged
- Payment webhooks update invoice status automatically
