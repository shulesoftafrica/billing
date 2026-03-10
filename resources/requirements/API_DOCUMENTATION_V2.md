# 🚀 Billing System - Complete API Documentation

**Last Updated:** March 9, 2026  
**Version:** 2.0  
**Status:** Production Ready

## 📋 Overview

This billing system provides a comprehensive REST API for managing organizations, customers, products, subscriptions, invoices, payments, and wallets. The system supports:

- ✅ **Multi-tenant Architecture** - Organizations, users, and role-based access
- ✅ **Multiple Payment Gateways** - EcoBank UCN, Flutterwave, Stripe
- ✅ **Automated Invoicing** - Recurring subscriptions and one-time billing
- ✅ **Wallet Management** - Credits, topups, deductions, transfers
- ✅ **Webhook Processing** - Real-time payment notifications
- ✅ **Token Authentication** - Laravel Sanctum personal access tokens
- ✅ **Full CRUD Operations** - All resources with validation
- ✅ **Advanced Invoice Types** - Wallet topup, plan upgrades/downgrades

**Base URL:** `http://localhost:8000/api`  
**Production URL:** `https://billing.shulesoft.africa/api`

---

## 🚀 Quick Start Guide

### Authentication Flow

1. **Register or Login to get Bearer token**
2. **Include token in all subsequent requests** as `Authorization: Bearer {token}`
3. **Token never expires** but can be revoked manually

### Making Your First Request

```bash
# 1. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123",
    "device_name": "My App"
  }'

# 2. Use the returned bearer_token in requests
curl -X GET http://localhost:8000/api/customers \
  -H "Authorization: Bearer {your_token_here}" \
  -H "Accept: application/json"
```

---

## 📚 Table of Contents

1. [Authentication](#1-authentication)
2. [Token Management](#2-token-management)
3. [Countries](#3-countries)
4. [Currencies](#4-currencies)
5. [Organizations](#5-organizations)
6. [Users](#6-users)
7. [Customers](#7-customers)
8. [Customer Addresses](#8-customer-addresses)
9. [Products & Product Types](#9-products--product-types)
10. [Price Plans](#10-price-plans)
11. [Invoices](#11-invoices)
12. [Subscriptions](#12-subscriptions)
13. [Wallets](#13-wallets)
14. [Payments](#14-payments)
15. [Payment Gateways](#15-payment-gateways)
16. [Bank Accounts](#16-bank-accounts)
17. [Tax Rates](#17-tax-rates)
18. [Webhooks](#18-webhooks)

---

## 1. Authentication

All authentication endpoints are **public** and do not require a Bearer token.

### 1.1 Login

Authenticate a user and receive a Bearer token.

```http
POST /api/auth/login
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123",
  "device_name": "Postman"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "organization_id": 1,
      "name": "John Doe",
      "email": "admin@example.com",
      "role": "admin",
      "sex": "M",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "Tech Solutions Inc",
        "currency_id": 1
      }
    },
    "bearer_token": "1|abc123xyz789...",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### 1.2 Register

Create a new user account and receive a Bearer token.

```http
POST /api/auth/register
Content-Type: application/json
```

**Request Body:**
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

**Validation Rules:**
- `organization_id`: required, must exist in organizations table
- `name`: required, string, max 255 characters
- `email`: required, valid email, unique in users table
- `password`: required, minimum 8 characters, must be confirmed
- `role`: required, one of: admin, finance, support
- `sex`: required, one of: M, F
- `device_name`: required

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 2,
      "organization_id": 1,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "role": "admin",
      "sex": "F",
      "organization": {...}
    },
    "bearer_token": "2|def456uvw123...",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

**Error Response (422 Validation Failed):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

### 1.3 Get Current User

Get the authenticated user's profile.

```http
GET /api/user
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "admin@example.com",
    "organization": {
      "id": 1,
      "name": "Tech Solutions Inc",
      "currency": {
        "id": 1,
        "code": "TZS",
        "name": "Tanzanian Shilling",
        "symbol": "TSh"
      }
    }
  }
}
```

### 1.4 Logout

Revoke the current access token.

```http
POST /api/auth/logout
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

### 1.5 Logout All Devices

Revoke all access tokens for the current user.

```http
POST /api/auth/logout-all
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "All tokens have been revoked"
}
```

---

## 2. Token Management

Manage personal access tokens for API authentication.

### 2.1 Generate Personal Access Token

Create a new personal access token with custom abilities and expiration.

```http
POST /api/auth/generate-token
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Production API Token",
  "expires_at": "2027-03-09T00:00:00Z",
  "abilities": ["read", "write", "delete"]
}
```

**Request Parameters:**
- `name`: required, string, max 255 characters (token name/label)
- `expires_at`: optional, date (future date), token expiration
- `abilities`: optional, array of strings (defaults to `["*"]` for all abilities)

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Personal access token created successfully",
  "data": {
    "personal_token": "3|ghi789rst456...",
    "token_id": 3,
    "name": "Production API Token",
    "abilities": ["read", "write", "delete"],
    "expires_at": "2027-03-09T00:00:00.000000Z",
    "created_at": "2026-03-09T10:30:00.000000Z"
  }
}
```

### 2.2 List All Tokens

Get all active tokens for the current user.

```http
GET /api/auth/tokens
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Postman",
      "abilities": ["*"],
      "created_at": "2026-03-01T10:00:00.000000Z",
      "last_used_at": "2026-03-09T09:30:00.000000Z",
      "expires_at": null
    },
    {
      "id": 3,
      "name": "Production API Token",
      "abilities": ["read", "write", "delete"],
      "created_at": "2026-03-09T10:30:00.000000Z",
      "last_used_at": null,
      "expires_at": "2027-03-09T00:00:00.000000Z"
    }
  ]
}
```

### 2.3 Delete Specific Token

Revoke a specific access token by ID.

```http
DELETE /api/auth/tokens/{token_id}
Authorization: Bearer {token}
```

**Path Parameters:**
- `token_id`: The ID of the token to delete

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Token deleted successfully"
}
```

**Error Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Token not found"
}
```

---

## 3. Countries

Country management endpoints. List and view are public; create, update, delete require authentication and admin role.

### 3.1 List Countries (Public)

```http
GET /api/countries
```

**Success Response (200 OK):**
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
    },
    {
      "id": 2,
      "name": "Kenya",
      "code": "KEN",
      "created_at": "2026-01-18T00:00:00.000000Z",
      "updated_at": "2026-01-18T00:00:00.000000Z"
    }
  ]
}
```

### 3.2 Get Country (Public)

```http
GET /api/countries/{id}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Tanzania",
    "code": "TZA",
    "created_at": "2026-01-17T00:00:00.000000Z",
    "updated_at": "2026-01-17T00:00:00.000000Z"
  }
}
```

### 3.3 Create Country (Admin Only)

```http
POST /api/countries
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Uganda",
  "code": "UGA"
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Country created successfully",
  "data": {
    "id": 3,
    "name": "Uganda",
    "code": "UGA",
    "created_at": "2026-03-09T10:00:00.000000Z",
    "updated_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 3.4 Update Country (Admin Only)

```http
PUT /api/countries/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "United Republic of Tanzania",
  "code": "TZA"
}
```

### 3.5 Delete Country (Admin Only)

```http
DELETE /api/countries/{id}
Authorization: Bearer {token}
```

---

## 4. Currencies

Currency management endpoints. List is public; create, update, delete require authentication and admin role.

### 4.1 List Currencies (Public)

```http
GET /api/currencies
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "TZS",
      "name": "Tanzanian Shilling",
      "symbol": "TSh",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 2,
      "code": "USD",
      "name": "US Dollar",
      "symbol": "$",
      "created_at": "2026-01-18T00:00:00.000000Z",
      "updated_at": "2026-01-18T00:00:00.000000Z"
    }
  ]
}
```

### 4.2 Create Currency (Admin Only)

```http
POST /api/currencies
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "code": "KES",
  "name": "Kenyan Shilling",
  "symbol": "KSh"
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Currency created successfully",
  "data": {
    "id": 3,
    "code": "KES",
    "name": "Kenyan Shilling",
    "symbol": "KSh",
    "created_at": "2026-03-09T10:00:00.000000Z",
    "updated_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 4.3 Update Currency (Admin Only)

```http
PUT /api/currencies/{id}
Authorization: Bearer {token}
```

### 4.4 Delete Currency (Admin Only)

```http
DELETE /api/currencies/{id}
Authorization: Bearer {token}
```

---

## 5. Organizations

Manage organizations in the multi-tenant system.

### 5.1 List Organizations

```http
GET /api/organizations
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Tech Solutions Inc",
      "legal_name": "Tech Solutions Incorporated",
      "currency_id": 1,
      "country_id": 1,
      "timezone": "Africa/Dar_es_Salaam",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "currency": {
        "id": 1,
        "code": "TZS",
        "name": "Tanzanian Shilling"
      },
      "country": {
        "id": 1,
        "name": "Tanzania"
      }
    }
  ]
}
```

### 5.2 Create Organization

```http
POST /api/organizations
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Tech Solutions Inc",
  "legal_name": "Tech Solutions Incorporated",
  "currency_id": 1,
  "country_id": 1,
  "timezone": "Africa/Dar_es_Salaam",
  "tax_number": "123-456-789",
  "email": "info@techsolutions.com",
  "phone": "+255712345678"
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `legal_name`: optional, string, max 255 characters
- `currency_id`: required, must exist in currencies table
- `country_id`: required, must exist in countries table
- `timezone`: required, valid timezone string
- `tax_number`: optional, string
- `email`: optional, valid email
- `phone`: optional, string

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Organization created successfully",
  "data": {
    "id": 2,
    "name": "Tech Solutions Inc",
    "legal_name": "Tech Solutions Incorporated",
    "currency_id": 1,
    "country_id": 1,
    "timezone": "Africa/Dar_es_Salaam",
    "created_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 5.3 Get Organization

```http
GET /api/organizations/{id}
Authorization: Bearer {token}
```

### 5.4 Update Organization

```http
PUT /api/organizations/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

### 5.5 Delete Organization

```http
DELETE /api/organizations/{id}
Authorization: Bearer {token}
```

### 5.6 Integrate Payment Gateway

Link a payment gateway to an organization.

```http
POST /api/organizations/integrate-payment-gateway
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "organization_id": 1,
  "payment_gateway_id": 2,
  "is_active": true,
  "credentials": {
    "api_key": "your_api_key",
    "secret_key": "your_secret_key",
    "merchant_id": "your_merchant_id"
  }
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Payment gateway integrated successfully",
  "data": {
    "id": 1,
    "organization_id": 1,
    "payment_gateway_id": 2,
    "is_active": true,
    "created_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

---

## 6. Users

Manage users within organizations.

### 6.1 List Users

```http
GET /api/users
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin",
      "sex": "M",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "Tech Solutions Inc"
      }
    }
  ]
}
```

### 6.2 Create User

```http
POST /api/users
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "organization_id": 1,
  "name": "Alice Johnson",
  "email": "alice@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "finance",
  "sex": "F"
}
```

**Validation Rules:**
- `organization_id`: required, must exist
- `name`: required, string, max 255
- `email`: required, valid email, unique
- `password`: required, min 8 characters, confirmed
- `role`: required, one of: admin, finance, support
- `sex`: required, one of: M, F

### 6.3 Get User

```http
GET /api/users/{id}
Authorization: Bearer {token}
```

### 6.4 Update User

```http
PUT /api/users/{id}
Authorization: Bearer {token}
```

### 6.5 Delete User

```http
DELETE /api/users/{id}
Authorization: Bearer {token}
```

---

## 7. Customers

Manage customers for billing and invoicing.

### 7.1 List Customers

```http
GET /api/customers
Authorization: Bearer {token}
```

**Query Parameters:**
- `organization_id` (optional): Filter by organization

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "name": "John Smith",
      "email": "john.smith@example.com",
      "phone": "+255712345678",
      "type": "individual",
      "status": "active",
      "created_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### 7.2 Create Customer

```http
POST /api/customers
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "organization_id": 1,
  "name": "John Smith",
  "email": "john.smith@example.com",
  "phone": "+255712345678",
  "type": "individual",
  "company_name": null,
  "tax_number": null,
  "billing_address": "123 Main St",
  "billing_city": "Dar es Salaam",
  "billing_country": "Tanzania",
  "billing_postal_code": "12345"
}
```

**Validation Rules:**
- `organization_id`: required, must exist
- `name`: required, string, max 255
- `email`: required, valid email, unique for organization
- `phone`: required, valid phone number
- `type`: required, one of: individual, business
- `company_name`: required if type is business
- `tax_number`: optional, string
- `billing_address`: optional, string
- `status`: optional, one of: active, inactive (defaults to active)

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 2,
    "organization_id": 1,
    "name": "John Smith",
    "email": "john.smith@example.com",
    "phone": "+255712345678",
    "type": "individual",
    "status": "active",
    "created_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 7.3 Get Customer

```http
GET /api/customers/{id}
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "organization_id": 1,
    "name": "John Smith",
    "email": "john.smith@example.com",
    "phone": "+255712345678",
    "type": "individual",
    "status": "active",
    "addresses": [
      {
        "id": 1,
        "address_line_1": "123 Main St",
        "city": "Dar es Salaam",
        "is_default": true
      }
    ],
    "subscriptions": [
      {
        "id": 1,
        "status": "active",
        "start_date": "2026-01-01",
        "next_billing_date": "2026-04-01"
      }
    ]
  }
}
```

### 7.4 Update Customer

```http
PUT /api/customers/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

### 7.5 Delete Customer

```http
DELETE /api/customers/{id}
Authorization: Bearer {token}
```

### 7.6 Lookup Customer by Phone

Get customer details and status by phone number.

```http
GET /api/customers/by-phone/{phone}/status
Authorization: Bearer {token}
```

**Example:**
```http
GET /api/customers/by-phone/+255712345678/status
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "john.smith@example.com",
    "phone": "+255712345678",
    "status": "active",
    "type": "individual",
    "active_subscriptions_count": 2,
    "total_invoices": 15,
    "total_paid": 4500.00,
    "wallet_balance": 250.00
  }
}
```

### 7.7 Lookup Customer by Email

Get customer details and status by email address.

```http
GET /api/customers/by-email/{email}/status
Authorization: Bearer {token}
```

**Example:**
```http
GET /api/customers/by-email/john.smith@example.com/status
```

---

## 8. Customer Addresses

Manage customer addresses (nested under customers).

### 8.1 List Customer Addresses

```http
GET /api/customers/{customer_id}/addresses
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "address_line_1": "123 Main Street",
      "address_line_2": "Apt 4B",
      "city": "Dar es Salaam",
      "state": "Dar es Salaam",
      "postal_code": "12345",
      "country": "Tanzania",
      "is_default": true,
      "created_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### 8.2 Create Customer Address

```http
POST /api/customers/{customer_id}/addresses
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "address_line_1": "456 Oak Avenue",
  "address_line_2": "Suite 200",
  "city": "Arusha",
  "state": "Arusha",
  "postal_code": "23456",
  "country": "Tanzania",
  "is_default": false
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Address created successfully",
  "data": {
    "id": 2,
    "customer_id": 1,
    "address_line_1": "456 Oak Avenue",
    "city": "Arusha",
    "is_default": false,
    "created_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 8.3 Get Customer Address

```http
GET /api/customers/{customer_id}/addresses/{address_id}
Authorization: Bearer {token}
```

### 8.4 Update Customer Address

```http
PUT /api/customers/{customer_id}/addresses/{address_id}
Authorization: Bearer {token}
```

### 8.5 Delete Customer Address

```http
DELETE /api/customers/{customer_id}/addresses/{address_id}
Authorization: Bearer {token}
```

---

## 9. Products & Product Types

Manage products and product categories.

### 9.1 List Product Types (Public)

```http
GET /api/product-types
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "SaaS Subscription",
      "description": "Software as a Service subscriptions",
      "created_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "SMS Credits",
      "description": "Prepaid SMS messaging credits",
      "created_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### 9.2 Create Product Type

```http
POST /api/product-types
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Cloud Storage",
  "description": "Cloud storage plans"
}
```

### 9.3 List Products

```http
GET /api/products
Authorization: Bearer {token}
```

**Query Parameters:**
- `organization_id` (optional): Filter by organization

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "product_type_id": 1,
      "name": "Premium Subscription",
      "product_code": "PREM-001",
      "description": "Premium features package",
      "status": "active",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "product_type": {
        "id": 1,
        "name": "SaaS Subscription"
      },
      "price_plans": [
        {
          "id": 1,
          "name": "Monthly Plan",
          "amount": "29.99",
          "billing_interval": "monthly"
        }
      ]
    }
  ]
}
```

### 9.4 Get Product by Code

```http
GET /api/products/by-code/{product_code}
Authorization: Bearer {token}
```

**Example:**
```http
GET /api/products/by-code/PREM-001
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "product_code": "PREM-001",
    "name": "Premium Subscription",
    "description": "Premium features package",
    "price_plans": [...]
  }
}
```

### 9.5 Create Product

```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "organization_id": 1,
  "product_type_id": 1,
  "name": "Enterprise Subscription",
  "product_code": "ENT-001",
  "description": "Enterprise features with priority support",
  "status": "active",
  "price_plans": [
    {
      "name": "Monthly Enterprise",
      "amount": 299.99,
      "currency_id": 1,
      "billing_interval": "monthly",
      "billing_type": "recurring"
    },
    {
      "name": "Annual Enterprise",
      "amount": 2999.99,
      "currency_id": 1,
      "billing_interval": "yearly",
      "billing_type": "recurring"
    }
  ]
}
```

**Validation Rules:**
- `organization_id`: required, must exist
- `product_type_id`: required, must exist
- `name`: required, string, max 255
- `product_code`: required, unique for organization
- `description`: optional, text
- `status`: optional, one of: active, inactive (defaults to active)
- `price_plans`: optional, array of price plans to create with product

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 2,
    "organization_id": 1,
    "product_type_id": 1,
    "name": "Enterprise Subscription",
    "product_code": "ENT-001",
    "status": "active",
    "price_plans": [
      {
        "id": 3,
        "name": "Monthly Enterprise",
        "amount": "299.99"
      }
    ]
  }
}
```

### 9.6 Update Product

```http
PUT /api/products/{id}
Authorization: Bearer {token}
```

### 9.7 Delete Product

```http
DELETE /api/products/{id}
Authorization: Bearer {token}
```

---

## 10. Price Plans

Manage pricing plans for products (nested under products).

### 10.1 List Price Plans for Product

```http
GET /api/products/{product_id}/price-plans
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "name": "Monthly Plan",
      "amount": "29.99",
      "currency_id": 1,
      "billing_interval": "monthly",
      "billing_type": "recurring",
      "trial_period_days": 14,
      "is_active": true,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "currency": {
        "code": "TZS",
        "symbol": "TSh"
      }
    }
  ]
}
```

### 10.2 Create Price Plan

```http
POST /api/products/{product_id}/price-plans
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Quarterly Plan",
  "amount": 79.99,
  "currency_id": 1,
  "billing_interval": "quarterly",
  "billing_type": "recurring",
  "trial_period_days": 0,
  "is_active": true
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `amount`: required, numeric, min 0
- `currency_id`: required, must exist
- `billing_interval`: required, one of: daily, weekly, monthly, quarterly, yearly
- `billing_type`: required, one of: one_time, recurring
- `trial_period_days`: optional, integer, min 0 (defaults to 0)
- `is_active`: optional, boolean (defaults to true)

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Price plan created successfully",
  "data": {
    "id": 4,
    "product_id": 1,
    "name": "Quarterly Plan",
    "amount": "79.99",
    "billing_interval": "quarterly",
    "created_at": "2026-03-09T10:00:00.000000Z"
  }
}
```

### 10.3 Get Price Plan

```http
GET /api/products/{product_id}/price-plans/{plan_id}
Authorization: Bearer {token}
```

### 10.4 Update Price Plan

```http
PUT /api/products/{product_id}/price-plans/{plan_id}
Authorization: Bearer {token}
```

### 10.5 Delete Price Plan

```http
DELETE /api/products/{product_id}/price-plans/{plan_id}
Authorization: Bearer {token}
```

---

## 11. Invoices

Comprehensive invoice management including creation, payment tracking, and advanced invoice types.

### 11.1 List Invoices

```http
GET /api/invoices
Authorization: Bearer {token}
```

**Query Parameters:**
- `organization_id` (required): Filter by organization
- `product_id` (optional): Filter by product
- `customer_id` (optional): Filter by customer
- `status` (optional): Filter by status (draft, issued, paid, cancelled, overdue)
- `per_page` (optional): Items per page (default: 15)

**Example:**
```http
GET /api/invoices?organization_id=1&status=paid&per_page=20
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "invoice_number": "INV20260129001",
        "customer_id": 1,
        "organization_id": 1,
        "total_amount": "99.99",
        "tax_amount": "18.00",
        "subtotal": "81.99",
        "status": "paid",
        "invoice_type": "subscription",
        "due_date": "2026-02-15",
        "issued_at": "2026-01-29T10:00:00.000000Z",
        "paid_at": "2026-01-30T14:30:00.000000Z",
        "customer": {
          "id": 1,
          "name": "John Smith",
          "email": "john.smith@example.com"
        },
        "items": [
          {
            "id": 1,
            "price_plan_id": 1,
            "quantity": 1,
            "unit_price": "29.99",
            "total": "29.99",
            "price_plan": {
              "name": "Monthly Plan",
              "product": {
                "name": "Premium Subscription"
              }
            }
          }
        ],
        "payments": [
          {
            "id": 1,
            "amount": "99.99",
            "payment_method": "card",
            "status": "success",
            "paid_at": "2026-01-30T14:30:00.000000Z"
          }
        ]
      }
    ],
    "per_page": 15,
    "total": 45
  }
}
```

### 11.2 Get Invoice

```http
GET /api/invoices/{id}
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV20260129001",
    "customer": {...},
    "items": [...],
    "subscription": {...},
    "payments": [...],
    "taxes": [...],
    "total_amount": "99.99",
    "status": "paid"
  }
}
```

### 11.3 Create One-Time Invoice

Create a one-time invoice for immediate payment.

```http
POST /api/invoices
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "invoice_type": "one_time",
  "due_date": "2026-04-15",
  "description": "One-time service fee",
  "payment_gateway": "flutterwave",
  "items": [
    {
      "price_plan_id": 5,
      "quantity": 1,
      "unit_price": 99.99
    }
  ],
  "taxes": [
    {
      "tax_rate_id": 1,
      "amount": 18.00
    }
  ]
}
```

**Validation Rules:**
- `customer_id`: required, must exist
- `invoice_type`: required, one of: one_time, subscription, wallet_topup
- `due_date`: optional, date (future)
- `description`: optional, string
- `payment_gateway`: optional, one of: control_number, flutterwave, stripe, both
- `items`: required, array (min 1 item)
  - `price_plan_id`: required, must exist
  - `quantity`: required, integer, min 1
  - `unit_price`: optional, numeric (uses plan's price if not provided)
- `taxes`: optional, array

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 10,
      "invoice_number": "INV20260309001",
      "total_amount": "117.99",
      "status": "issued",
      "control_number": "CN123456789",
      "control_number_expires_at": "2026-03-12T10:00:00.000000Z"
    },
    "payment_link": {
      "flutterwave": {
        "link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
        "reference": "FLW-INV-10-1234567890"
      }
    }
  }
}
```

### 11.4 Create Subscription Invoice

Create a recurring subscription invoice.

```http
POST /api/invoices
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "invoice_type": "subscription",
  "billing_cycle": "monthly",
  "start_date": "2026-04-01",
  "payment_gateway": "both",
  "items": [
    {
      "price_plan_id": 1,
      "quantity": 1
    },
    {
      "price_plan_id": 3,
      "quantity": 2
    }
  ]
}
```

**Note:** When `invoice_type` is "subscription", the system automatically creates subscription records for each item.

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Invoice and subscriptions created successfully",
  "data": {
    "invoice": {
      "id": 11,
      "invoice_number": "INV20260309002",
      "total_amount": "89.97",
      "status": "issued"
    },
    "subscriptions": [
      {
        "id": 5,
        "price_plan_id": 1,
        "status": "active",
        "start_date": "2026-04-01",
        "next_billing_date": "2026-05-01"
      }
    ],
    "payment_link": {
      "control_number": "CN987654321",
      "flutterwave": {
        "link": "https://checkout.flutterwave.com/..."
      }
    }
  }
}
```

### 11.5 Create Wallet Topup Invoice

Create an invoice for wallet credit topup.

```http
POST /api/invoices/wallet-topup
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "amount": 100.00,
  "wallet_type": "sms_credits",
  "units": 1000,
  "payment_gateway": "flutterwave",
  "description": "SMS credits topup - 1000 units"
}
```

**Validation Rules:**
- `customer_id`: required, must exist
- `amount`: required, numeric, min 0.01
- `wallet_type`: required, string (e.g., sms_credits, data_credits)
- `units`: required, numeric, min 0.01
- `payment_gateway`: optional, one of: control_number, flutterwave, stripe
- `description`: optional, string

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Wallet topup invoice created successfully",
  "data": {
    "invoice": {
      "id": 12,
      "invoice_number": "INV20260309003",
      "total_amount": "100.00",
      "status": "issued",
      "invoice_type": "wallet_topup"
    },
    "wallet_details": {
      "wallet_type": "sms_credits",
      "units": 1000,
      "unit_price": "0.10"
    },
    "payment_link": {
      "flutterwave": {
        "link": "https://checkout.flutterwave.com/v3/hosted/pay/def456",
        "reference": "FLW-INV-12-1234567891"
      }
    }
  }
}
```

### 11.6 Create Plan Upgrade Invoice

Create an invoice for upgrading a subscription to a higher plan.

```http
POST /api/invoices/plan-upgrade
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "subscription_id": 5,
  "new_price_plan_id": 3,
  "proration": true,
  "payment_gateway": "stripe"
}
```

**Validation Rules:**
- `subscription_id`: required, must exist and be active
- `new_price_plan_id`: required, must exist, must be more expensive than current plan
- `proration`: optional, boolean (defaults to true) - whether to prorate unused time
- `payment_gateway`: optional, payment gateway to use

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Upgrade invoice created successfully",
  "data": {
    "invoice": {
      "id": 13,
      "invoice_number": "INV20260309004",
      "total_amount": "15.50",
      "status": "issued",
      "description": "Plan upgrade with proration"
    },
    "upgrade_details": {
      "old_plan": "Monthly Plan - $29.99",
      "new_plan": "Premium Plan - $49.99",
      "prorated_credit": "-$14.49",
      "upgrade_cost": "$49.99",
      "amount_due": "$15.50"
    },
    "payment_link": {
      "stripe": {
        "client_secret": "pi_xxxxx_secret_yyyyy",
        "payment_intent_id": "pi_xxxxx"
      }
    }
  }
}
```

### 11.7 Create Plan Downgrade Invoice

Create an invoice for downgrading a subscription to a lower plan.

```http
POST /api/invoices/plan-downgrade
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "subscription_id": 5,
  "new_price_plan_id": 1,
  "apply_credit": true
}
```

**Validation Rules:**
- `subscription_id`: required, must exist and be active
- `new_price_plan_id`: required, must exist, must be less expensive than current plan
- `apply_credit`: optional, boolean (defaults to true) - whether to apply credit for unused time

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Downgrade scheduled successfully",
  "data": {
    "subscription": {
      "id": 5,
      "current_plan": "Premium Plan - $49.99",
      "new_plan": "Basic Plan - $19.99",
      "downgrade_scheduled_for": "2026-04-01",
      "credit_to_apply": "$15.00"
    }
  }
}
```

### 11.8 Get Invoices by Product

Get all invoices for a specific product.

```http
GET /api/invoices/{product_id}/product
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "invoice_number": "INV20260129001",
      "customer": {...},
      "total_amount": "99.99",
      "status": "paid"
    }
  ]
}
```

### 11.9 Get Invoices by Subscriptions

Get invoices for specific subscription IDs.

```http
POST /api/invoices/by-subscriptions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "subscription_ids": [1, 2, 5, 8]
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "subscription_id": 1,
      "invoices": [
        {
          "id": 1,
          "invoice_number": "INV20260129001",
          "total_amount": "29.99",
          "status": "paid"
        }
      ]
    }
  ]
}
```

### 11.10 Update Invoice

```http
PUT /api/invoices/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

**Note:** Only invoices with status "draft" can be updated.

### 11.11 Delete Invoice

```http
DELETE /api/invoices/{id}
Authorization: Bearer {token}
```

**Note:** Only invoices with status "draft" can be deleted.

---

## 12. Subscriptions

Manage recurring subscriptions for customers.

### 12.1 List Subscriptions

```http
GET /api/subscriptions
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (optional): Filter by customer
- `status` (optional): Filter by status (active, cancelled, expired, suspended)
- `per_page` (optional): Items per page (default: 15)

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "customer_id": 1,
        "price_plan_id": 1,
        "status": "active",
        "start_date": "2026-01-01",
        "end_date": null,
        "next_billing_date": "2026-04-01",
        "cancelled_at": null,
        "cancellation_reason": null,
        "customer": {
          "id": 1,
          "name": "John Smith"
        },
        "price_plan": {
          "id": 1,
          "name": "Monthly Plan",
          "amount": "29.99",
          "billing_interval": "monthly",
          "product": {
            "id": 1,
            "name": "Premium Subscription"
          }
        }
      }
    ],
    "per_page": 15,
    "total": 18
  }
}
```

### 12.2 Create Subscription

Create new subscriptions for a customer.

```http
POST /api/subscriptions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3],
  "start_date": "2026-04-01"
}
```

**Note:** This creates an invoice with all subscriptions and returns the invoice details along with subscription records.

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": {
      "id": 15,
      "invoice_number": "INV20260309005",
      "total_amount": "179.97"
    },
    "subscriptions": [
      {
        "id": 10,
        "price_plan_id": 1,
        "status": "active",
        "start_date": "2026-04-01",
        "next_billing_date": "2026-05-01"
      }
    ],
    "customer": {
      "id": 1,
      "name": "John Smith"
    }
  }
}
```

### 12.3 Get Subscription

```http
GET /api/subscriptions/{id}
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "customer_id": 1,
    "price_plan_id": 1,
    "status": "active",
    "start_date": "2026-01-01",
    "next_billing_date": "2026-04-01",
    "customer": {...},
    "price_plan": {...},
    "invoices": [
      {
        "id": 1,
        "invoice_number": "INV20260129001",
        "total_amount": "29.99",
        "status": "paid"
      }
    ]
  }
}
```

### 12.4 Cancel Subscription

Cancel an active subscription.

```http
POST /api/subscriptions/{id}/cancel
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "cancellation_reason": "No longer needed"
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Subscription cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled",
    "cancelled_at": "2026-03-09T10:00:00.000000Z",
    "cancellation_reason": "No longer needed",
    "end_date": "2026-04-01"
  }
}
```

### 12.5 Get Customer Subscriptions

Get all subscriptions for a specific customer.

```http
GET /api/customers/{customer_id}/subscriptions
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "status": "active",
      "price_plan": {...},
      "next_billing_date": "2026-04-01"
    }
  ]
}
```

---

## 13. Wallets

Comprehensive wallet management for customer credits and balances.

### 13.1 Get Wallet Balance

Get the current balance of a customer's wallet.

```http
GET /api/wallets/balance
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (required): Customer ID
- `wallet_type` (optional): Wallet type filter (e.g., sms_credits, data_credits)

**Example:**
```http
GET /api/wallets/balance?customer_id=1&wallet_type=sms_credits
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "wallet_type": "sms_credits",
  "balance": 1500.00
}
```

### 13.2 Credit Wallet

Add credits to a customer's wallet.

```http
POST /api/wallets/credit
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "wallet_type": "sms_credits",
  "units": 1000,
  "unit_price": 0.10,
  "description": "SMS credits purchase",
  "invoice_id": 12
}
```

**Validation Rules:**
- `customer_id`: required, must exist
- `wallet_type`: required, string, max 50 characters
- `units`: required, numeric, min 0.0001
- `unit_price`: optional, numeric, min 0
- `description`: optional, string, max 500 characters
- `invoice_id`: optional, must exist (links transaction to invoice)

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Credits added successfully",
  "data": {
    "transaction_id": 45,
    "customer_id": 1,
    "wallet_type": "sms_credits",
    "units": 1000,
    "balance_before": 500.00,
    "balance_after": 1500.00,
    "description": "SMS credits purchase"
  }
}
```

### 13.3 Deduct from Wallet

Deduct credits from a customer's wallet.

```http
POST /api/wallets/deduct
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "customer_id": 1,
  "wallet_type": "sms_credits",
  "units": 50,
  "description": "SMS sending - 50 messages"
}
```

**Validation Rules:**
- `customer_id`: required, must exist
- `wallet_type`: required, string
- `units`: required, numeric, min 0.0001
- `description`: optional, string

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Credits deducted successfully",
  "data": {
    "transaction_id": 46,
    "customer_id": 1,
    "wallet_type": "sms_credits",
    "units": -50,
    "balance_before": 1500.00,
    "balance_after": 1450.00,
    "description": "SMS sending - 50 messages"
  }
}
```

**Error Response (400 Insufficient Balance):**
```json
{
  "success": false,
  "error": "insufficient_balance",
  "message": "Insufficient wallet balance",
  "data": {
    "current_balance": 30.00,
    "requested_amount": 50.00,
    "shortage": 20.00
  }
}
```

### 13.4 Transfer Credits

Transfer credits between two customers.

```http
POST /api/wallets/transfer
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "from_customer_id": 1,
  "to_customer_id": 2,
  "wallet_type": "sms_credits",
  "units": 100,
  "description": "Transfer to partner account"
}
```

**Validation Rules:**
- `from_customer_id`: required, must exist
- `to_customer_id`: required, must exist, must be different from from_customer_id
- `wallet_type`: required, string
- `units`: required, numeric, min 0.0001
- `description`: optional, string

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Credits transferred successfully",
  "data": {
    "from_transaction_id": 47,
    "to_transaction_id": 48,
    "from_customer_id": 1,
    "to_customer_id": 2,
    "wallet_type": "sms_credits",
    "units": 100,
    "from_balance_after": 1350.00,
    "to_balance_after": 600.00
  }
}
```

### 13.5 Check Balance (Alternative)

Alternative endpoint to check wallet balance.

```http
GET /api/wallets/check-balance
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (required): Customer ID
- `wallet_type` (optional): Wallet type

**Success Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "balances": {
    "sms_credits": 1350.00,
    "data_credits": 5000.00,
    "voucher_credits": 250.00
  }
}
```

### 13.6 Get Wallet Transaction History

Get transaction history for a customer's wallet.

```http
GET /api/wallets/{customer_id}/transactions
Authorization: Bearer {token}
```

**Query Parameters:**
- `wallet_type` (optional): Filter by wallet type
- `transaction_type` (optional): Filter by type (topup, deduction, transfer, refund)
- `limit` (optional): Max records (1-100, default: 50)

**Example:**
```http
GET /api/wallets/1/transactions?wallet_type=sms_credits&limit=20
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "transactions": [
    {
      "id": 47,
      "wallet_type": "sms_credits",
      "transaction_type": "deduction",
      "units": -100,
      "balance_before": 1450.00,
      "balance_after": 1350.00,
      "description": "Transfer to partner account",
      "invoice_id": null,
      "created_at": "2026-03-09T11:00:00.000000Z"
    },
    {
      "id": 46,
      "wallet_type": "sms_credits",
      "transaction_type": "deduction",
      "units": -50,
      "balance_before": 1500.00,
      "balance_after": 1450.00,
      "description": "SMS sending - 50 messages",
      "created_at": "2026-03-09T10:30:00.000000Z"
    }
  ],
  "count": 2
}
```

### 13.7 Get Transactions by Wallet (Alternative)

Alternative endpoint for wallet transactions.

```http
GET /api/wallets/transactions
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (required): Customer ID
- `wallet_type` (optional): Filter by wallet type

---

## 14. Payments

Payment tracking and management.

### 14.1 Get Payments by Invoice

Get all payments for a specific invoice.

```http
GET /api/payments/by-invoice/{invoice_id}
Authorization: Bearer {token}
```

**Example:**
```http
GET /api/payments/by-invoice/1
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "invoice_id": 1,
      "customer_id": 1,
      "amount": 99.99,
      "currency": "TZS",
      "status": "success",
      "payment_method": "card",
      "payment_gateway": "flutterwave",
      "transaction_id": "FLW-12345",
      "reference": "FLW-INV-1-1234567890",
      "paid_at": "2026-01-30T14:30:00.000000Z",
      "created_at": "2026-01-30T14:30:00.000000Z"
    }
  ]
}
```

### 14.2 Get Payments by Date Range

Get all payments within a specific date range.

```http
GET /api/payments
Authorization: Bearer {token}
```

**Query Parameters:**
- `date_from` (required): Start date (YYYY-MM-DD)
- `date_to` (required): End date (YYYY-MM-DD)
- `customer_id` (optional): Filter by customer

**Example:**
```http
GET /api/payments?date_from=2026-03-01&date_to=2026-03-09&customer_id=1
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "invoice_id": 12,
      "customer_id": 1,
      "amount": 100.00,
      "status": "success",
      "payment_method": "card",
      "payment_gateway": "flutterwave",
      "paid_at": "2026-03-05T09:15:00.000000Z"
    },
    {
      "id": 8,
      "invoice_id": 15,
      "customer_id": 1,
      "amount": 179.97,
      "status": "success",
      "payment_method": "mobile_money",
      "payment_gateway": "flutterwave",
      "paid_at": "2026-03-08T16:45:00.000000Z"
    }
  ]
}
```

### 14.3 Verify Flutterwave Payment

Manually verify a Flutterwave payment transaction.

```http
GET /api/payments/verify/{transaction_id}
Authorization: Bearer {token}
```

**Note:** Payment verification is normally done automatically via webhooks. This endpoint is for manual verification if needed.

**Example:**
```http
GET /api/payments/verify/FLW-12345
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "transaction_id": "FLW-12345",
    "tx_ref": "FLW-INV-12-1234567891",
    "flw_ref": "FLW-MOCK-REF-12345",
    "amount": 100.00,
    "currency": "TZS",
    "status": "successful",
    "payment_type": "card",
    "charged_amount": 100.00,
    "customer": {
      "email": "john.smith@example.com",
      "name": "John Smith"
    }
  }
}
```

**Error Response (400 Payment Failed):**
```json
{
  "success": false,
  "message": "Payment verification failed",
  "data": {
    "status": "failed",
    "message": "Transaction was declined"
  }
}
```

### 14.4 Create Payment Intent (Stripe)

Create a Stripe PaymentIntent for processing payments.

```http
POST /api/payments/intent
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "amount": 9999,
  "currency": "tzs",
  "customer": "cus_abc123",
  "description": "Payment for Invoice #INV20260309001",
  "metadata": {
    "invoice_id": 1,
    "customer_id": 1
  },
  "receipt_email": "john.smith@example.com",
  "capture_method": "automatic",
  "statement_descriptor": "SuleSoft Bill"
}
```

**Validation Rules:**
- `amount`: required, integer, min 1 (amount in cents/smallest currency unit)
- `currency`: required, string, 3 characters (ISO currency code)
- `customer`: optional, string (Stripe customer ID)
- `description`: optional, string
- `metadata`: optional, object (key-value pairs)
- `receipt_email`: optional, valid email
- `capture_method`: optional, one of: automatic, automatic_async, manual
- `statement_descriptor`: optional, string, max 22 characters

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "payment_intent_id": "pi_3AbC123XyZ",
    "client_secret": "pi_3AbC123XyZ_secret_dEf456GhI",
    "amount": 9999,
    "currency": "tzs",
    "status": "requires_payment_method"
  }
}
```

---

## 15. Payment Gateways

Manage payment gateway configurations and test connections.

### 15.1 List Payment Gateways

```http
GET /api/payment-gateways
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "EcoBank UCN",
      "code": "ecobank_ucn",
      "is_active": true,
      "supports_webhooks": true,
      "created_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Flutterwave",
      "code": "flutterwave",
      "is_active": true,
      "supports_webhooks": true,
      "created_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Stripe",
      "code": "stripe",
      "is_active": true,
      "supports_webhooks": true,
      "created_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### 15.2 Create Payment Gateway

```http
POST /api/payment-gateways
Authorization: Bearer {token}
```

### 15.3 Test Payment Gateway Connection

Test the connection to a specific payment gateway.

```http
GET /api/payment-gateways/test-connection
Authorization: Bearer {token}
```

**Query Parameters:**
- `gateway_code` (required): Gateway code (e.g., flutterwave, stripe)

**Example:**
```http
GET /api/payment-gateways/test-connection?gateway_code=flutterwave
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Gateway connection successful",
  "data": {
    "gateway": "flutterwave",
    "status": "active",
    "test_result": "Connection verified",
    "timestamp": "2026-03-09T10:00:00.000000Z"
  }
}
```

**Error Response (503 Service Unavailable):**
```json
{
  "success": false,
  "message": "Gateway connection failed",
  "error": "Invalid API credentials"
}
```

### 15.4 Test All Payment Gateway Connections

Test connections to all configured payment gateways.

```http
GET /api/payment-gateways/test-all-connections
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "flutterwave": {
      "status": "active",
      "connection": "successful"
    },
    "stripe": {
      "status": "active",
      "connection": "successful"
    },
    "ecobank_ucn": {
      "status": "inactive",
      "connection": "not_configured"
    }
  }
}
```

---

## 16. Bank Accounts

Manage bank account information for organizations.

### 16.1 List Bank Accounts

```http
GET /api/bank-accounts
Authorization: Bearer {token}
```

**Query Parameters:**
- `organization_id` (optional): Filter by organization

### 16.2 Create Bank Account

```http
POST /api/bank-accounts
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "organization_id": 1,
  "bank_name": "CRDB Bank",
  "account_name": "Tech Solutions Inc",
  "account_number": "0150123456789",
  "swift_code": "CORUTZTZ",
  "branch": "Posta Branch",
  "currency_id": 1,
  "is_default": true
}
```

### 16.3 Get Bank Account

```http
GET /api/bank-accounts/{id}
Authorization: Bearer {token}
```

### 16.4 Update Bank Account

```http
PUT /api/bank-accounts/{id}
Authorization: Bearer {token}
```

### 16.5 Delete Bank Account

```http
DELETE /api/bank-accounts/{id}
Authorization: Bearer {token}
```

---

## 17. Tax Rates

Manage tax rates for invoicing.

### 17.1 List Tax Rates

```http
GET /api/tax-rates
Authorization: Bearer {token}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "VAT",
      "rate": "18.00",
      "description": "Value Added Tax",
      "is_active": true,
      "created_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

### 17.2 Create Tax Rate

```http
POST /api/tax-rates
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "GST",
  "rate": 15.00,
  "description": "Goods and Services Tax",
  "is_active": true
}
```

### 17.3 Update Tax Rate

```http
PUT /api/tax-rates/{id}
Authorization: Bearer {token}
```

### 17.4 Delete Tax Rate

```http
DELETE /api/tax-rates/{id}
Authorization: Bearer {token}
```

---

## 18. Webhooks

Public webhook endpoints for payment gateway notifications. These endpoints do not require authentication but verify signatures.

### 18.1 EcoBank UCN Webhook

Receive payment notifications from EcoBank Universal Collection Number system.

```http
POST /api/webhooks/ecobank/notification
Content-Type: application/json
```

**Rate Limit:** 30 requests per minute

**Expected Payload:**
```json
{
  "control_number": "CN123456789",
  "amount": 50000.00,
  "payment_reference": "PAY-REF-123",
  "payment_date": "2026-03-09T14:30:00Z",
  "status": "success",
  "payer_name": "John Smith",
  "payer_phone": "+255712345678"
}
```

**Success Response (200 OK):**
```json
{
  "responseCode": "000",
  "responseDescription": "Payment processed successfully",
  "data": {
    "invoice_id": 1,
    "payment_id": 10,
    "invoice_status": "paid"
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "responseCode": "400",
  "responseDescription": "Control number not found",
  "error": "CONTROL_NUMBER_NOT_FOUND"
}
```

### 18.2 Flutterwave Webhook

Receive payment notifications from Flutterwave.

```http
POST /api/webhooks/flutterwave
Content-Type: application/json
verif-hash: {flutterwave_signature}
```

**Rate Limit:** 30 requests per minute

**Security:** Verifies `verif-hash` header against webhook secret.

**Expected Events:**
- `charge.completed` - Payment successful
- `charge.failed` - Payment failed
- `transfer.completed` - Transfer completed

**Example Payload (charge.completed):**
```json
{
  "event": "charge.completed",
  "data": {
    "id": 12345,
    "tx_ref": "FLW-INV-1-1234567890",
    "flw_ref": "FLW-MOCK-REF-12345",
    "amount": 99.99,
    "currency": "TZS",
    "charged_amount": 99.99,
    "status": "successful",
    "payment_type": "card",
    "created_at": "2026-03-09T14:30:00Z",
    "customer": {
      "email": "john.smith@example.com",
      "phone_number": "+255712345678",
      "name": "John Smith"
    },
    "meta": {
      "invoice_id": 1
    }
  }
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Event received",
  "invoice_id": 1,
  "payment_status": "success"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

### 18.3 Stripe Webhook

Receive payment notifications from Stripe.

```http
POST /api/webhooks/stripe
Content-Type: application/json
stripe-signature: {stripe_signature}
```

**Rate Limit:** 30 requests per minute

**Security:** Verifies `stripe-signature` header using webhook secret.

**Expected Events:**
- `payment_intent.succeeded` - Payment successful
- `payment_intent.payment_failed` - Payment failed
- `invoice.payment_succeeded` - Invoice payment successful
- `customer.subscription.updated` - Subscription updated
- `customer.subscription.deleted` - Subscription cancelled

**Example Payload (payment_intent.succeeded):**
```json
{
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_3AbC123XyZ",
      "amount": 9999,
      "currency": "tzs",
      "status": "succeeded",
      "metadata": {
        "invoice_id": "1",
        "customer_id": "1"
      },
      "receipt_email": "john.smith@example.com",
      "created": 1709989800
    }
  }
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Event received"
}
```

### 18.4 Generate Flutterwave Hash (Utility)

Utility endpoint to generate hash for Flutterwave payloads (useful for testing).

```http
POST /api/flutterwave/hash
Content-Type: application/json
```

**Request Body:**
```json
{
  "event": "charge.completed",
  "data": {
    "id": 12345,
    "tx_ref": "FLW-INV-1-1234567890",
    "amount": 99.99
  }
}
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "hash": "a1b2c3d4e5f6...",
  "payload": {...}
}
```

---

## 📊 Common Response Codes

### Success Codes
- `200 OK` - Request successful
- `201 Created` - Resource created successfully

### Client Error Codes
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid authentication
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed

### Server Error Codes
- `500 Internal Server Error` - Server error occurred
- `503 Service Unavailable` - Service temporarily unavailable

---

## 🔒 Standard Error Response Format

All endpoints return consistent error responses:

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

**Authentication Error (401):**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**Not Found Error (404):**
```json
{
  "success": false,
  "message": "Resource not found"
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Detailed error message (only in development)"
}
```

---

## 📝 Important Notes

### Invoice Numbers
- Format: `INV[YYYYMMDD][XXXX]`
- Example: `INV20260309001`
- Auto-generated and unique

### Timestamps
- All timestamps are in UTC
- Format: ISO 8601 (`2026-03-09T10:30:00.000000Z`)

### Monetary Amounts
- Always use 2 decimal precision
- Stripe amounts are in cents (multiply by 100)
- Currency codes follow ISO 4217 (TZS, USD, KES, etc.)

### Payment Gateways
- **EcoBank UCN**: Control number generation for bank payments
- **Flutterwave**: Card payments, mobile money, bank transfers
- **Stripe**: International card payments, payment intents

### Subscription Behavior
- Auto-renew based on billing interval
- Billing intervals: daily, weekly, monthly, quarterly, yearly
- Cancellation takes effect at end of current billing period
- Upgrade/downgrade with proration support

### Wallet Transactions
- All operations are atomic
- Balance cannot go negative (deductions fail if insufficient)
- All transactions are logged with before/after balances
- Supports multiple wallet types per customer

### Webhook Security
- **Flutterwave**: Verify `verif-hash` header
- **Stripe**: Verify `stripe-signature` header
- All webhooks are rate-limited (30 requests/minute)
- Failed verifications return 401 Unauthorized

---

## 🚀 Implementation Status

### ✅ Completed Features
- ✅ Authentication & Token Management (8 endpoints)
- ✅ Country & Currency Management (8 endpoints)
- ✅ Organization Management (6 endpoints)
- ✅ User Management (5 endpoints)
- ✅ Customer Management (7 endpoints)
- ✅ Customer Addresses (5 endpoints)
- ✅ Product & Product Type Management (7 endpoints)
- ✅ Price Plans (5 endpoints)
- ✅ Invoice Management (11 endpoints)
  - One-time invoices
  - Subscription invoices
  - Wallet topup invoices
  - Plan upgrade invoices
  - Plan downgrade invoices
- ✅ Subscription Management (5 endpoints)
- ✅ Wallet Management (7 endpoints)
- ✅ Payment Management (4 endpoints)
- ✅ Payment Gateway Management (4 endpoints)
- ✅ Bank Account Management (5 endpoints)
- ✅ Tax Rate Management (4 endpoints)
- ✅ Webhook Processing (4 endpoints)
  - EcoBank UCN webhook
  - Flutterwave webhook
  - Stripe webhook

**Total Endpoints:** 95+ fully functional API endpoints

### 🎯 Recent Updates (March 2026)
- ✅ Enhanced authentication with personal access token management
- ✅ Advanced customer lookup endpoints (by phone, by email with status)
- ✅ Wallet topup invoices with automatic credit addition
- ✅ Plan upgrade/downgrade with proration
- ✅ Multiple payment gateway support per invoice
- ✅ Comprehensive webhook processing for all payment gateways
- ✅ Payment intent creation for Stripe
- ✅ Wallet transfer between customers
- ✅ Enhanced transaction history with filters

---

## 🔗 Additional Resources

- **[Implementation Summary](IMPLEMENTATION_SUMMARY_CHECKLIST.md)** - Complete feature checklist
- **[Subscription API Reference](SUBSCRIPTION_API_REFERENCE.md)** - Detailed subscription guide
- **[Flutterwave Implementation](FLUTTERWAVE_IMPLEMENTATION_SUMMARY.md)** - Flutterwave integration guide
- **[Architecture Diagrams](ARCHITECTURE_DIAGRAMS.md)** - System architecture visual guide
- **[Development Requirements](DEVELOPMENT_REQUIREMENTS.md)** - Development setup guide

---

## 📞 Support

For API support and questions:
- **Email:** support@shulesoft.africa
- **Documentation:** https://billing.shulesoft.africa/docs
- **Status Page:** https://status.shulesoft.africa

---

**End of API Documentation**  
**Version 2.0** | **Last Updated: March 9, 2026**
