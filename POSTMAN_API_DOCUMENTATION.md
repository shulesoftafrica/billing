# 🚀 Billing System - API Postman Documentation

## 📋 Overview

This billing system provides a comprehensive API for managing organizations, customers, products, subscriptions, and payments. The system supports multiple organizations, payment gateways integration, and automated invoice generation.

**Base URL:** `http://localhost:8000/api` (Adjust based on your environment)

### 🚀 **Quick Start Authentication**

**For immediate testing, you only need ONE step:**

1. **Login to get token:**
   ```http
   POST /api/auth/login
   Content-Type: application/json
   
   {
     "email": "your_email@example.com",
     "password": "your_password",
     "device_name": "Postman"
   }
   ```

2. **Use the token from response:**
   ```
   Authorization: Bearer {auth_token}
   ```

That's it! You can now call any protected endpoint. The personal access token generation is OPTIONAL for long-term integrations.

---

## 🏢 Core Resources

### 1. **Countries API**

#### GET - List Countries
```http
GET /api/countries
```

**Headers:**
```
Accept: application/json
Content-Type: application/json
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

#### POST - Create Country
```http
POST /api/countries
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
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
    "code": "KEN",
    "created_at": "2026-01-17T00:00:00.000000Z",
    "updated_at": "2026-01-17T00:00:00.000000Z"
  }
}
```

#### GET - Show Country
```http
GET /api/countries/{id}
```

#### PUT - Update Country
```http
PUT /api/countries/{id}
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "United Republic of Tanzania",
  "code": "TZA"
}
```

#### DELETE - Delete Country
```http
DELETE /api/countries/{id}
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

---

### 2. **Currencies API** (Enhanced in Phase 1)

#### GET - List Currencies
```http
GET /api/currencies --NO token verified, but its important
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Tanzanian Shilling",
      "code": "TZS",
      "symbol": "TSh",
      "exchange_rate": "1.000000",
      "is_base_currency": true,
      "last_updated": "2026-01-17T10:00:00.000000Z",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "US Dollar",
      "code": "USD",
      "symbol": "$",
      "exchange_rate": "2350.000000",
      "is_base_currency": false,
      "last_updated": "2026-01-17T10:00:00.000000Z",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

#### POST - Create Currency
```http
POST /api/currencies
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Euro",
  "code": "EUR",
  "symbol": "€",
  "exchange_rate": 2580.50,
  "is_base_currency": false
}
```

---

### 3. **Organizations API**

#### GET - List Organizations
```http
GET /api/organizations
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "ShuleSoft Africa",
      "phone": "+255748771580",
      "email": "info@shulesoft.africa",
      "currency_id": 1,
      "country_id": 1,
      "timezone": "Africa/Dar_es_Salaam",
      "status": "active",
      "currency": {
        "id": 1,
        "name": "Tanzanian Shilling",
        "code": "TZS",
        "symbol": "TSh"
      },
      "country": {
        "id": 1,
        "name": "Tanzania",
        "code": "TZA"
      },
      "payment_gateways": []
    }
  ]
}
```

#### POST - Create Organization
```http
POST /api/organizations
```

**Request Body:**
```json
{
  "name": "Tech Company Ltd",
  "phone": "+255700123456",
  "email": "contact@techcompany.co.tz",
  "currency_id": 1,
  "country_id": 1,
  "timezone": "Africa/Dar_es_Salaam",
  "status": "active"
}
```

#### POST - Integrate Payment Gateway
```http
POST /api/organizations/integrate-payment-gateway
```

**Request Body:**
```json
{
  "organization_id": 1,
  "payment_gateway_id": 1,
  "bank_account_id": 1,
  "attachment": "base64_encoded_document"
}
```

---

### 4. **Users API**

#### GET - List Users
```http
GET /api/users?organization_id=1
```

**Query Parameters:**
- `organization_id` (required): Organization ID

**Response (200 OK):**
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
      "email_verified_at": null,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "ShuleSoft Africa"
      }
    }
  ]
}
```

#### POST - Create User
```http
POST /api/users
```

**Request Body:**
```json
{
  "organization_id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "secure_password",
  "role": "finance",
  "sex": "F"
}
```

**Available Roles:** `admin`, `finance`, `support`

---

### 5. **Customers API** (Enhanced in Phase 1)

#### GET - List Customers
```http
GET /api/customers?organization_id=1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "external_ref": "CUST001",
      "name": "Alice Johnson",
      "email": "alice@customer.com",
      "phone": "+255700987654",
      "customer_type": "individual",
      "status": "active",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "ShuleSoft Africa"
      }
    }
  ]
}
```

#### POST - Create Customer
```http
POST /api/customers
```

**Request Body:**
```json
{
  "organization_id": 1,
  "external_ref": "CUST002",
  "name": "Bob Wilson",
  "email": "bob@customer.com",
  "phone": "+255700123789",
  "customer_type": "school",
  "status": "active"
}
```

#### GET - Lookup Customer by Phone
```http
GET /api/customers/by-phone/{phone}/status
```

**Response (200 OK):**
```json
{
  "success": true,
  "customer": {
    "id": 1,
    "name": "Alice Johnson",
    "email": "alice@customer.com",
    "phone": "+255700987654",
    "customer_type": "individual",
    "status": "active",
    "wallet_balances": {
      "credits": 150.5000,
      "points": 1200.0000
    },
    "active_subscriptions": 3,
    "total_invoices": 12,
    "outstanding_balance": 0.00
  }
}
```

#### GET - Lookup Customer by Email
```http
GET /api/customers/by-email/{email}/status
```

---

### 6. **Customer Addresses API**

#### GET - List Customer Addresses
```http
GET /api/customers/{customer_id}/addresses
```

#### POST - Create Customer Address
```http
POST /api/customers/{customer_id}/addresses
```

**Request Body:**
```json
{
  "type": "billing",
  "country": "Tanzania",
  "city": "Dar es Salaam",
  "address_line": "123 Main Street, Kinondoni"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Address created successfully",
  "data": {
    "id": 1,
    "customer_id": 1,
    "type": "billing",
    "country": "Tanzania",
    "city": "Dar es Salaam",
    "address_line": "123 Main Street, Kinondoni",
    "created_at": "2026-01-17T00:00:00.000000Z",
    "updated_at": "2026-01-17T00:00:00.000000Z"
  }
}
```

---

### 7. **Product Types API**

#### GET - List Product Types (System-Defined)
```http
GET /api/product-types
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "One-time Product",
      "description": "Products sold once with optional single price plan",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Subscription Product",
      "description": "Recurring products requiring at least one price plan",
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

**Note:** Product types are predefined in the system:
- **Type ID 1**: One-time products (software licenses, digital downloads, etc.)
- **Type ID 2**: Subscription products (SaaS, recurring services, etc.)

---

### 8. **Products API**

#### GET - List Products
```http
GET /api/products?organization_id=1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "product_type_id": 2,
      "name": "Premium SaaS Platform",
      "product_code": "premium-saas",
      "description": "Comprehensive business management platform",
      "active": true,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "ShuleSoft Africa"
      },
      "product_type": {
        "id": 2,
        "name": "Subscription Product"
      },
      "price_plans": [
        {
          "id": 1,
          "product_id": 1,
          "name": "Monthly Plan",
          "billing_type": "recurring",
          "billing_interval": "monthly",
          "amount": "99.99",
          "currency_id": 1,
          "active": true
        }
      ]
    }
  ]
}
```

#### POST - Create Product (with Price Plans)
```http
POST /api/products
```

**Request Body (One-time Product - Type ID 1):**
```json
{
  "organization_id": 1,
  "product_type_id": 1,
  "name": "Professional Software License",
  "product_code": "software-license",
  "description": "Lifetime license for professional software",
  "active": true,
  "price_plans": [
    {
      "name": "One-time License",
      "billing_type": "one_time",
      "billing_interval": null,
      "amount": 499.99,
      "currency_code": "TZS",
      "active": true
    }
  ]
}
```

**Request Body (Subscription Product - Type ID 2):**
```json
{
  "organization_id": 1,
  "product_type_id": 2,
  "name": "Enterprise Analytics Platform",
  "product_code": "enterprise-analytics",
  "description": "Advanced analytics and reporting platform for enterprises",
  "active": true,
  "price_plans": [
    {
      "name": "Monthly Enterprise",
      "billing_type": "recurring",
      "billing_interval": "monthly",
      "amount": 299.99,
      "currency_code": "TZS",
      "features": {
        "max_users": 100,
        "storage_gb": 50,
        "api_calls_per_month": 10000,
        "advanced_analytics": true,
        "real_time_reporting": true,
        "priority_support": true
      },
      "active": true
    },
    {
      "name": "Annual Enterprise",
      "billing_type": "recurring",
      "billing_interval": "yearly",
      "amount": 2999.99,
      "currency_code": "TZS",
      "features": {
        "max_users": 500,
        "storage_gb": 200,
        "api_calls_per_month": 50000,
        "advanced_analytics": true,
        "real_time_reporting": true,
        "priority_support": true,
        "custom_integrations": true,
        "dedicated_account_manager": true
      },
      "active": true
    }
  ]
}
```

**Alternative Example (using currency_name instead of currency_code):**
```json
{
  "organization_id": 1,
  "product_type_id": 2,
  "name": "SafariChat AI Sales Agent",
  "product_code": "safarichat",
  "description": "AI-powered platform to facilitate sales operations and WhatsApp automation",
  "active": true,
  "price_plans": [
    {
      "name": "Trial Plan",
      "billing_type": "recurring",
      "billing_interval": "monthly",
      "amount": 0,
      "currency_name": "Tanzanian Shilling",
      "features": {
        "whatsapp_channels": 1,
        "messages_per_month": 100,
        "ai_credits": 50
      },
      "active": true
    },
    {
      "name": "Starter Plan",
      "billing_type": "recurring",
      "billing_interval": "monthly",
      "amount": 69000,
      "currency_name": "Tanzanian Shilling",
      "features": {
        "whatsapp_channels": 3,
        "messages_per_month": "unlimited",
        "ai_credits": 1000,
        "credits_rollover": true
      },
      "active": true
    },
    {
      "name": "Pro Plan",
      "billing_type": "recurring",
      "billing_interval": "monthly",
      "amount": 149000,
      "currency_name": "US Dollar",
      "features": {
        "whatsapp_channels": 10,
        "messages_per_month": "unlimited",
        "ai_credits": 5000,
        "credits_rollover": true,
        "customer_followups": true,
        "customer_categorization": true,
        "sales_reports": true,
        "advanced_analytics": true
      },
      "active": true
    }
  ]
}
```

**Important Notes:**
- **product_type_id: 1** = One-time products (can have 0 or 1 price plan)
- **product_type_id: 2** = Subscription products (must have at least 1 price plan)
- **product_code** = Unique identifier for the product (used in invoice creation APIs)
- **currency_code** = Use currency code (e.g., "USD", "TZS", "EUR") or currency_name (e.g., "US Dollar", "Tanzanian Shilling") instead of currency_id for easier integration
- **features** = Optional object with key-value pairs describing plan features, limits, and capabilities (e.g., {"max_users": 100, "storage_gb": 50, "api_access": true})
- **Uniqueness Constraint**: `product_code` must be unique within each organization
- **Duplicate Prevention**: Creating a product with an existing `product_code` in the same organization will return a validation error

**Response (422 Validation Error - Duplicate Product Code):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "product_code": [
      "The product code has already been taken for this organization."
    ]
  }
}
```

**Response (422 Validation Error - Duplicate Product Name):**
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name has already been taken for this organization."
    ]
  }
}
```

**Response (201 Created - Success):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 2,
    "organization_id": 1,
    "product_type_id": 2,
    "name": "Enterprise Analytics Platform",
    "product_code": "enterprise-analytics",
    "description": "Advanced analytics and reporting platform for enterprises",
    "active": true,
    "created_at": "2026-01-18T00:00:00.000000Z",
    "updated_at": "2026-01-18T00:00:00.000000Z",
    "price_plans": [
      {
        "id": 3,
        "name": "Monthly Enterprise",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "299.99",
        "currency_code": "TZS",
        "active": true
      }
    ]
  }
}
```

**Handling Duplicate Products:**

If you need to update an existing product instead of creating a duplicate:

#### PUT - Update Product
```http
PUT /api/products/{product_id}
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Updated Enterprise Analytics Platform",
  "product_code": "enterprise-analytics",
  "description": "Updated description for analytics platform",
  "active": true
}
```

#### GET - Check Product Exists by Code
```http
GET /api/products/by-code/{product_code}
```

**Headers:**
```
Authorization: Bearer {{auth_token}}
Accept: application/json
Content-Type: application/json
```

**Note:** The organization is automatically determined from the authenticated user's token. No need to pass `organization_id` parameter.

**Response (200 OK - Product Found):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "organization_id": 1,
    "name": "Enterprise Analytics Platform",
    "product_code": "enterprise-analytics",
    "active": true,
    "organization": {
      "id": 1,
      "name": "ShuleSoft Africa"
    },
    "product_type": {
      "id": 2,
      "name": "Subscription Product"
    },
    "price_plans": [
      {
        "id": 3,
        "name": "Monthly Enterprise",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "299.99",
        "currency_id": 1,
        "active": true
      }
    ]
  }
}
```

**Response (404 Not Found - Product Doesn't Exist):**
```json
{
  "success": false,
  "message": "Product not found with code: enterprise-analytics"
}
```

---

### 9. **Price Plans API** (Enhanced in Phase 1)

#### GET - List Product Price Plans
```http
GET /api/products/{product_id}/price-plans
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "name": "Premium Monthly",
      "plan_code": "PREM-MONTH",
      "feature_code": "PREMIUM_FEATURES",
      "billing_type": "recurring",
      "billing_interval": "monthly",
      "amount": "99.99",
      "currency_id": 1,
      "trial_period_days": 14,
      "setup_fee": "25.00",
      "metadata": {
        "max_users": 50,
        "storage_gb": 100,
        "api_calls_per_month": 10000
      },
      "active": true,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

#### POST - Create Price Plan
```http
POST /api/products/{product_id}/price-plans
```

**Request Body:**
```json
{
  "name": "Enterprise Annual",
  "plan_code": "ENT-ANNUAL",
  "feature_code": "ENTERPRISE_FEATURES",
  "billing_type": "recurring",
  "billing_interval": "yearly",
  "amount": 999.99,
  "currency_code": "TZS",
  "features": {
    "max_users": 1000,
    "storage_gb": 500,
    "api_calls_per_month": 100000,
    "unlimited_users": true,
    "premium_storage": true,
    "priority_support": true,
    "custom_integrations": true,
    "dedicated_account_manager": true
  },
  "trial_period_days": 30,
  "setup_fee": 0.00,
  "metadata": {
    "max_users": 500,
    "storage_gb": 1000,
    "api_calls_per_month": 100000,
    "priority_support": true
  },
  "active": true
}
```

---

### 10. **Payment Gateways API**

#### GET - List Payment Gateways
```http
GET /api/payment-gateways
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "UNC Payment Gateway",
      "type": "control_number",
      "webhook_secret": "webhook_secret_key",
      "config": {
        "api_endpoint": "https://api.unc.example.com",
        "timeout": 30
      },
      "active": true,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

#### POST - Create Payment Gateway
```http
POST /api/payment-gateways
```

**Request Body:**
```json
{
  "name": "Mobile Money Gateway",
  "type": "mobile_money",
  "webhook_secret": "secure_webhook_secret",
  "config": {
    "api_endpoint": "https://api.mobilemoney.com",
    "merchant_id": "MERCHANT123"
  },
  "active": true
}
```

---

### 11. **Bank Accounts API**

#### GET - List Bank Accounts
```http
GET /api/bank-accounts?organization_id=1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Business Account",
      "account_number": "123456789",
      "branch": "Main Branch",
      "refer_bank_id": 1,
      "organization_id": 1,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z",
      "organization": {
        "id": 1,
        "name": "ShuleSoft Africa"
      }
    }
  ]
}
```

#### POST - Create Bank Account
```http
POST /api/bank-accounts
```

**Request Body:**
```json
{
  "name": "Secondary Business Account",
  "account_number": "987654321",
  "branch": "Downtown Branch",
  "refer_bank_id": 2,
  "organization_id": 1
}
```

---

## 🎯 Subscription & Invoice System

### 12. **Subscriptions API** (Enhanced in Phase 1)

#### GET - List Subscriptions
```http
GET /api/subscriptions
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "price_plan_id": 1,
      "subscription_number": "SUB202601170001",
      "status": "active",
      "start_date": "2026-01-17",
      "end_date": null,
      "next_billing_date": "2026-02-17",
      "trial_ends_at": "2026-01-31T23:59:59.000000Z",
      "canceled_at": null,
      "pause_starts_at": null,
      "pause_ends_at": null,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    }
  ]
}
```

#### GET - Get Subscription Details
```http
GET /api/subscriptions/{id}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "subscription": {
      "id": 1,
      "subscription_number": "SUB202601170001",
      "status": "active",
      "start_date": "2026-01-17",
      "end_date": null,
      "next_billing_date": "2026-02-17",
      "trial_ends_at": "2026-01-31T23:59:59.000000Z",
      "canceled_at": null,
      "pause_starts_at": null,
      "pause_ends_at": null,
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    },
    "customer": {
      "id": 1,
      "name": "Mwenge Secondary School",
      "email": "admin@mwenge.edu.tz",
      "phone": "+255123456789",
      "customer_type": "school"
    },
    "price_plan": {
      "id": 2,
      "name": "Premium Plan",
      "description": "Advanced features with premium support",
      "amount": "99.99",
      "billing_interval": "monthly",
      "trial_period_days": 14,
      "metadata": {
        "features": {
          "max_students": 500,
          "premium_support": true,
          "analytics": true
        }
      },
      "product": {
        "id": 1,
        "name": "ShuleSoft Platform",
        "product_code": "shulesoft",
        "description": "Comprehensive school management system"
      }
    },
    "recent_invoices": [
      {
        "id": 15,
        "invoice_number": "INV202601170015",
        "status": "paid",
        "total": "99.99",
        "due_date": "2026-02-01",
        "issued_at": "2026-01-17T00:00:00.000000Z"
      },
      {
        "id": 12,
        "invoice_number": "INV202601170012",
        "status": "paid",
        "total": "99.99",
        "due_date": "2026-01-01",
        "issued_at": "2025-12-17T00:00:00.000000Z"
      }
    ]
  }
}
```

#### POST - Pause Subscription
```http
POST /api/subscriptions/pause
```

**Request Body:**
```json
{
  "subscription_id": 1,
  "pause_starts_at": "2026-02-01",
  "pause_ends_at": "2026-03-01",
  "reason": "Temporary business closure"
}
```

#### POST - Reactivate Subscription
```http
POST /api/subscriptions/reactivate
```

**Request Body:**
```json
{
  "subscription_id": 1,
  "reactivate_date": "2026-01-20"
}
```

#### POST - Change Subscription Plan
```http
POST /api/subscriptions/change-plan
```

**Request Body:**
```json
{
  "subscription_id": 1,
  "new_price_plan_id": 3,
  "effective_date": "2026-02-01",
  "proration_method": "credit" 
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Subscription plan changed successfully",
  "data": {
    "subscription": {
      "id": 1,
      "price_plan_id": 3,
      "status": "active"
    },
    "change_record": {
      "id": 1,
      "change_type": "upgrade",
      "old_price_plan_id": 1,
      "new_price_plan_id": 3,
      "proration_amount": 150.00,
      "effective_date": "2026-02-01"
    },
    "proration_invoice": {
      "id": 25,
      "invoice_number": "INV202601170025",
      "total": 150.00,
      "proration_credit": -50.00
    }
  }
}
```

#### GET - Get Subscription History
```http
GET /api/subscriptions/{id}/history
```

**Response (200 OK):**
```json
{
  "success": true,
  "subscription_id": 1,
  "changes": [
    {
      "id": 1,
      "change_type": "upgrade",
      "old_price_plan": {
        "id": 1,
        "name": "Basic Plan",
        "amount": "29.99"
      },
      "new_price_plan": {
        "id": 3,
        "name": "Premium Plan", 
        "amount": "99.99"
      },
      "proration_amount": "70.00",
      "effective_date": "2026-01-15",
      "created_at": "2026-01-15T10:30:00.000000Z"
    }
  ]
}
```

#### GET - Customer Subscriptions
```http
GET /api/customers/{customer_id}/subscriptions
```

#### POST - Create Subscriptions (with Invoice)
```http
POST /api/subscriptions
```

**Request Body:**
```json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "subscriptions": [
      {
        "id": 1,
        "customer_id": 1,
        "price_plan_id": 1,
        "status": "active",
        "start_date": "2026-01-17",
        "end_date": null,
        "next_billing_date": "2026-02-17",
        "created_at": "2026-01-17T00:00:00.000000Z",
        "updated_at": "2026-01-17T00:00:00.000000Z"
      }
    ],
    "invoice": {
      "id": 1,
      "customer_id": 1,
      "invoice_number": "INV202601170001",
      "status": "issued",
      "description": "Subscription Invoice",
      "subtotal": "429.97",
      "tax_total": "0.00",
      "total": "429.97",
      "due_date": "2026-02-01",
      "issued_at": "2026-01-17T00:00:00.000000Z"
    },
    "invoice_items": [
      {
        "id": 1,
        "invoice_id": 1,
        "subscription_id": 1,
        "price_plan_id": 1,
        "quantity": "1.00",
        "unit_price": "99.99",
        "total": "99.99"
      }
    ],
    "customer": {
      "id": 1,
      "name": "Alice Johnson",
      "email": "alice@customer.com"
    }
  }
}
```

#### POST - Cancel Subscription
```http
POST /api/subscriptions/{id}/cancel
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Subscription canceled successfully",
  "data": {
    "id": 1,
    "status": "canceled",
    "end_date": "2026-01-17"
  }
}
```

---

### 13. **Invoices API** (Enhanced in Phase 1)

#### GET - List Invoices
```http
GET /api/invoices
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "subscription_id": 1,
      "invoice_number": "INV202601170001",
      "invoice_type": "subscription",
      "status": "paid",
      "description": "Monthly subscription - Premium Plan",
      "subtotal": "99.99",
      "tax_total": "18.00",
      "proration_credit": "0.00",
      "total": "117.99",
      "due_date": "2026-02-01",
      "issued_at": "2026-01-17T00:00:00.000000Z",
      "metadata": {
        "billing_period": "2026-01-17 to 2026-02-17",
        "plan_features": ["premium_support", "advanced_analytics"]
      }
    }
  ]
}
```

#### GET - Get Invoice Details
```http
GET /api/invoices/{id}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "invoice": {
      "id": 1,
      "invoice_number": "INV202601170001",
      "invoice_type": "subscription",
      "status": "paid",
      "description": "Monthly subscription - Premium Plan",
      "subtotal": "99.99",
      "tax_total": "18.00",
      "total": "117.99",
      "due_date": "2026-02-01",
      "issued_at": "2026-01-17T00:00:00.000000Z",
      "metadata": {
        "billing_period": "2026-01-17 to 2026-02-17"
      },
      "created_at": "2026-01-17T00:00:00.000000Z",
      "updated_at": "2026-01-17T00:00:00.000000Z"
    },
    "customer": {
      "id": 1,
      "name": "Mwenge Secondary School",
      "email": "admin@mwenge.edu.tz",
      "phone": "+255123456789",
      "customer_type": "school"
    },
    "items": [
      {
        "id": 1,
        "description": "Premium Plan - Monthly Subscription",
        "quantity": "1.00",
        "unit_price": "99.99",
        "total": "99.99",
        "price_plan": {
          "id": 2,
          "name": "Premium Plan",
          "amount": "99.99",
          "billing_interval": "monthly",
          "product": {
            "id": 1,
            "name": "ShuleSoft Platform",
            "product_code": "shulesoft-premium"
          }
        }
      }
    ],
    "subscription": {
      "id": 1,
      "subscription_number": "SUB202601170001",
      "status": "active",
      "start_date": "2026-01-17",
      "next_billing_date": "2026-02-17",
      "price_plan": {
        "id": 2,
        "name": "Premium Plan",
        "amount": "99.99",
        "billing_interval": "monthly"
      }
    }
  }
}
```

#### POST - Create Wallet Topup Invoice
```http
POST /api/invoices/wallet-topup
```

**Request Body:**
```json
{
  "customer_id": 1,
  "wallet_type": "credits",
  "units": 500.0000,
  "unit_price": 1.50,
  "description": "Credit wallet topup - 500 credits"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Wallet topup invoice created successfully",
  "data": {
    "invoice": {
      "id": 15,
      "customer_id": 1,
      "invoice_number": "INV202601170015",
      "invoice_type": "wallet_topup",
      "status": "issued",
      "description": "Credit wallet topup - 500 credits",
      "total": "750.00",
      "metadata": {
        "wallet_type": "credits",
        "units": 500.0000,
        "unit_price": 1.50
      }
    },
    "control_number": "9912345678",
    "payment_instructions": {
      "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number"
    }
  }
}
```

#### POST - Create Plan Upgrade Invoice
```http
POST /api/invoices/plan-upgrade
```

**Request Body:**
```json
{
  "subscription_id": 1,
  "new_price_plan_id": 3,
  "effective_date": "2026-02-01",
  "proration_method": "immediate"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Plan upgrade invoice created successfully",
  "data": {
    "invoice": {
      "id": 16,
      "subscription_id": 1,
      "invoice_number": "INV202601170016",
      "invoice_type": "plan_upgrade",
      "status": "issued",
      "description": "Plan upgrade: Basic Plan -> Premium Plan",
      "subtotal": "25.00",
      "proration_credit": "10.83",
      "total": "14.17",
      "metadata": {
        "old_plan": "Basic Plan ($29/month)",
        "new_plan": "Premium Plan ($59/month)",
        "proration_days": 13,
        "effective_date": "2026-02-01"
      }
    },
    "control_number": "9912345679",
    "payment_instructions": {
      "mobile_banking": "Dial *150*01*9912345679# from your registered mobile number"
    }
  }
}
```

#### POST - Create Plan Downgrade Invoice (Phase 2)
```http
POST /api/invoices/plan-downgrade
```

**Request Body:**
```json
{
  "subscription_id": 1,
  "new_price_plan_id": 1,
  "effective_date": "2026-02-01",
  "credit_method": "wallet_credit"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Plan downgrade processed successfully",
  "data": {
    "invoice": {
      "id": 17,
      "subscription_id": 1,
      "invoice_number": "INV202601170017",
      "invoice_type": "plan_downgrade",
      "status": "processed",
      "description": "Plan downgrade: Premium Plan -> Basic Plan",
      "subtotal": "0.00",
      "credit_applied": "15.83",
      "total": "0.00",
      "metadata": {
        "old_plan": "Premium Plan ($59/month)",
        "new_plan": "Basic Plan ($29/month)",
        "credit_days": 13,
        "effective_date": "2026-02-01"
      }
    },
    "wallet_credit": {
      "amount": "15.83",
      "wallet_type": "credits",
      "applied_at": "2026-01-17T15:30:22.000000Z"
    }
  }
}
```

#### POST - Create Invoice (Control Number)
```http
POST /api/invoices
```

**Request Body (Simple - Existing Customer):**
```json
{
  "organization_id": 1,
  "product_id": 1,
  "customer_id": 1
}
```

**Request Body (Full - Auto-Create Customer):**
```json
{
  "product_code": "shulesoft",
  "invoice_type": "subscription",
  "customer": {
    "name": "Mwenge Secondary School",
    "phone": "+255123456789",
    "email": "admin@mwenge.edu.tz",
    "address": {
      "street": "123 Main Street",
      "city": "Dar es Salaam", 
      "state": "Kinondoni",
      "postal_code": "12345",
      "country_code": "TZ"
    },
    "external_ref": "SCH001",
    "customer_type": "school"
  },
  "amount": 50000,
  "currency_code": "TZS",
  "due_date": "2024-02-15",
  "plan_code": "basic",
  "billing_cycle": "monthly",
  "success_url": "http://localhost:8000/payment-success",
  "cancel_url": "http://localhost:8000/payment-cancel",
  "items": [
    {
      "description": "Basic Plan - Monthly Subscription",
      "quantity": 1,
      "unit_price": 50000.00,
      "total_price": 50000.00
    }
  ],
  "metadata": {
    "school_id": "SCH001",
    "region": "Dar es Salaam",
    "billing_contact": "Finance Department"
  }
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Control number generated successfully",
  "data": {
    "invoice": {
      "id": 1,
      "invoice_number": "INV202601170001",
      "customer_id": 1,
      "organization_id": 1,
      "invoice_type": "subscription",
      "status": "issued",
      "description": "Monthly subscription - Basic Plan",
      "subtotal": "50000.00",
      "tax_total": "0.00",
      "total": "50000.00",
      "currency": "TZS",
      "due_date": "2026-02-01",
      "issued_at": "2026-01-17T00:00:00.000000Z"
    },
    "customer": {
      "id": 1,
      "name": "Mwenge Secondary School",
      "email": "admin@mwenge.edu.tz",
      "phone": "+255123456789",
      "external_ref": "SCH001",
      "customer_type": "school",
      "created": true
    },
    "payment_details": {
      "control_number": "9912345678",
      "amount": "50000.00",
      "currency": "TZS",
      "expires_at": "2026-01-24T23:59:59.000000Z",
      "qr_code": "data:image/png;base64,iVBORw0KGgoAAAANS...",
      "payment_instructions": {
        "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number",
        "internet_banking": "Login to your internet banking and pay bill using control number",
        "agent_banking": "Visit any bank agent and provide the control number"
      }
    },
    "urls": {
      "success_url": "http://localhost:8000/payment-success",
      "cancel_url": "http://localhost:8000/payment-cancel",
      "payment_url": "http://localhost:8000/pay/9912345678"
    }
  }
}
```

---

## � Wallet System (Phase 1 - New!)

### 16. **Wallet Management API**

#### GET - Get Wallet Balance
```http
GET /api/wallets/balance?customer_id=1&wallet_type=credits
```

**Headers:**
```
Accept: application/json
Content-Type: application/json
```

**Query Parameters:**
- `customer_id` (required): Customer ID
- `wallet_type` (optional): Specific wallet type to check

**Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "wallet_type": "credits",
  "balance": 150.5000
}
```

**Response (All Wallet Types):**
```json
{
  "success": true,
  "customer_id": 1,
  "wallet_type": null,
  "balance": {
    "credits": 150.5000,
    "points": 1200.0000,
    "balance": 45.2500
  }
}
```

#### POST - Add Credits to Wallet
```http
POST /api/wallets/credit
```

**Request Body:**
```json
{
  "customer_id": 1,
  "wallet_type": "credits",
  "units": 100.0000,
  "unit_price": 1.50,
  "description": "Monthly credit topup",
  "invoice_id": 5
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "transaction": {
    "id": 1,
    "customer_id": 1,
    "wallet_type": "credits",
    "transaction_type": "topup",
    "units": "100.0000",
    "unit_price": "1.50",
    "total_amount": "150.00",
    "reference_number": "WTX-20260117143022-A1B2C3",
    "description": "Monthly credit topup",
    "status": "completed",
    "created_at": "2026-01-17T14:30:22.000000Z"
  },
  "new_balance": 250.5000,
  "message": "Successfully added 100.0000 credits credits"
}
```

#### POST - Deduct Credits from Wallet
```http
POST /api/wallets/deduct
```

**Request Body:**
```json
{
  "customer_id": 1,
  "wallet_type": "credits",
  "units": 25.0000,
  "description": "SMS sending charges"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "transaction": {
    "id": 2,
    "customer_id": 1,
    "wallet_type": "credits",
    "transaction_type": "deduction",
    "units": "-25.0000",
    "description": "SMS sending charges",
    "status": "completed",
    "created_at": "2026-01-17T14:35:10.000000Z"
  },
  "new_balance": 225.5000,
  "message": "Successfully deducted 25.0000 credits credits"
}
```

**Response (Insufficient Balance - 400):**
```json
{
  "success": false,
  "error": "insufficient_balance",
  "current_balance": 10.0000,
  "required": 25.0000,
  "message": "Insufficient credits balance"
}
```

#### POST - Transfer Credits Between Customers
```http
POST /api/wallets/transfer
```

**Request Body:**
```json
{
  "from_customer_id": 1,
  "to_customer_id": 2,
  "wallet_type": "credits",
  "units": 50.0000,
  "description": "Transfer to partner account"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "deduct_transaction": {
    "id": 3,
    "customer_id": 1,
    "transaction_type": "deduction",
    "units": "-50.0000"
  },
  "add_transaction": {
    "id": 4,
    "customer_id": 2,
    "transaction_type": "topup",
    "units": "50.0000"
  },
  "message": "Successfully transferred 50.0000 credits credits"
}
```

#### GET - Check Sufficient Balance
```http
GET /api/wallets/check-balance?customer_id=1&wallet_type=credits&required_amount=75.00
```

**Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "wallet_type": "credits",
  "required_amount": "75.00",
  "current_balance": 175.5000,
  "has_sufficient_balance": true
}
```

#### GET - Get Transaction History
```http
GET /api/wallets/{customer_id}/transactions?wallet_type=credits&transaction_type=topup&limit=20
```

**Query Parameters:**
- `wallet_type` (optional): Filter by wallet type
- `transaction_type` (optional): topup, deduction, transfer, refund
- `limit` (optional): Number of transactions (1-100, default: 50)

**Response (200 OK):**
```json
{
  "success": true,
  "customer_id": 1,
  "transactions": [
    {
      "id": 4,
      "customer_id": 1,
      "wallet_type": "credits",
      "transaction_type": "topup",
      "units": "100.0000",
      "unit_price": "1.50",
      "total_amount": "150.00",
      "invoice_id": 5,
      "reference_number": "WTX-20260117143022-A1B2C3",
      "description": "Monthly credit topup",
      "status": "completed",
      "processed_at": "2026-01-17T14:30:22.000000Z",
      "created_at": "2026-01-17T14:30:22.000000Z",
      "invoice": {
        "id": 5,
        "invoice_number": "INV202601170005",
        "status": "paid"
      }
    },
    {
      "id": 3,
      "customer_id": 1,
      "wallet_type": "credits",
      "transaction_type": "deduction",
      "units": "-25.0000",
      "description": "SMS sending charges",
      "status": "completed",
      "created_at": "2026-01-17T14:35:10.000000Z"
    }
  ],
  "count": 2
}
```

---

## 💱 Enhanced Currency System (Phase 1 - Updated!)

### 17. **Currency Conversion API**

#### GET - Get Exchange Rates
```http
GET /api/currencies/rates
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "from_currency": "USD",
      "to_currency": "TZS",
      "rate": "2350.0000",
      "buffer_percentage": "2.50",
      "effective_rate": "2408.7500",
      "updated_at": "2026-01-17T10:00:00.000000Z"
    },
    {
      "from_currency": "EUR",
      "to_currency": "TZS",
      "rate": "2580.0000",
      "buffer_percentage": "2.00",
      "effective_rate": "2631.6000",
      "updated_at": "2026-01-17T10:00:00.000000Z"
    }
  ]
}
```

#### POST - Convert Between Currencies
```http
POST /api/currencies/convert
```

**Request Body:**
```json
{
  "amount": 100.00,
  "from_currency": "USD",
  "to_currency": "TZS",
  "use_buffer": true
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "original_amount": 100.00,
  "converted_amount": 240875.00,
  "from_currency": "USD",
  "to_currency": "TZS",
  "rate": 2350.0000,
  "effective_rate": 2408.7500,
  "buffer_applied": true
}
```

#### GET - Get Price Preview in Multiple Currencies
```http
GET /api/prices/preview?amount=99.99&base_currency=USD&target_currencies[]=TZS&target_currencies[]=EUR
```

**Response (200 OK):**
```json
{
  "success": true,
  "base_currency": "USD",
  "base_amount": 99.99,
  "previews": {
    "TZS": {
      "currency_code": "TZS",
      "amount": 240849.76,
      "rate": 2350.0000,
      "effective_rate": 2408.7500
    },
    "EUR": {
      "currency_code": "EUR",
      "amount": 92.15,
      "rate": 0.9215,
      "effective_rate": 0.9215
    }
  }
}
```

#### PUT - Update Exchange Rate (Admin)
```http
PUT /api/currencies/{id}/rate
```

**Request Body:**
```json
{
  "to_currency": "TZS",
  "rate": 2375.00,
  "buffer_percentage": 2.5
}
```

---

### 14. **Webhooks API**

#### POST - UNC Payment Webhook
```http
POST /api/webhooks/unc-payment
```

**Request Body (from payment gateway):**
```json
{
  "transaction_id": "TXN123456789",
  "control_number": "9912345678",
  "amount": "99.99",
  "currency": "TZS",
  "status": "success",
  "payment_method": "mobile_banking",
  "reference": "REF123456789",
  "timestamp": "2026-01-17T10:30:00Z"
}
```

#### POST - Stripe Webhook Handler (Phase 2)
```http
POST /api/webhooks/stripe
```

**Headers:**
```
Stripe-Signature: {stripe_signature}
Accept: application/json
Content-Type: application/json
```

**Request Body (Payment Success):**
```json
{
  "id": "evt_1234567890",
  "object": "event",
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_1234567890",
      "amount": 9999,
      "currency": "usd",
      "status": "succeeded",
      "metadata": {
        "invoice_id": "15"
      }
    }
  }
}
```

**Response (200 OK):**
```json
{
  "success": true
}
```

#### POST - FlutterWave Webhook Handler (Phase 2)
```http
POST /api/webhooks/flutterwave
```

**Headers:**
```
verif-hash: {flutterwave_hash}
Accept: application/json
Content-Type: application/json
```

**Request Body (Charge Completed):**
```json
{
  "event": "charge.completed",
  "data": {
    "id": 123456789,
    "amount": 150.00,
    "currency": "USD",
    "status": "successful",
    "payment_type": "card",
    "meta": {
      "invoice_id": "15"
    }
  }
}
```

**Response (200 OK):**
```json
{
  "success": true
}
```

#### POST - Test Webhook Handler (Phase 2)
```http
POST /api/webhooks/test
```

**Request Body:**
```json
{
  "transaction_id": "TXN_TEST_123456",
  "invoice_id": 15,
  "amount": 750.00,
  "status": "success",
  "payment_method": "test_card"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Test webhook processed successfully",
  "data": {
    "payment": {
      "id": 25,
      "invoice_id": 15,
      "amount": "750.00",
      "status": "success",
      "payment_method": "test_card",
      "paid_at": "2026-01-17T15:30:22.000000Z"
    },
    "invoice_status": "paid"
  }
}
```

---

## 📊 Payment Gateway Testing (Phase 2 - New!)

### 15. **Gateway Connectivity Testing**

#### GET - Test Single Gateway Connection
```http
GET /api/payment-gateways/test-connection?gateway_id=1
```

**Query Parameters:**
- `gateway_id` (required): Payment gateway ID to test

**Response (200 OK - Successful Connection):**
```json
{
  "success": true,
  "gateway": {
    "id": 1,
    "name": "Stripe Payment Gateway",
    "type": "stripe"
  },
  "test_result": {
    "status": "connected",
    "response_time_ms": 145.67,
    "api_version": "2023-10-16",
    "account_id": "acct_1234567890",
    "tested_at": "2026-01-17T15:45:22.000000Z"
  }
}
```

**Response (200 OK - Failed Connection):**
```json
{
  "success": true,
  "gateway": {
    "id": 2,
    "name": "FlutterWave Gateway",
    "type": "flutterwave"
  },
  "test_result": {
    "status": "failed",
    "response_time_ms": 5000.0,
    "error": "HTTP 401: Invalid API key",
    "tested_at": "2026-01-17T15:45:22.000000Z"
  }
}
```

#### GET - Test All Gateway Connections
```http
GET /api/payment-gateways/test-all-connections
```

**Response (200 OK):**
```json
{
  "success": true,
  "total_gateways": 3,
  "results": [
    {
      "gateway": {
        "id": 1,
        "name": "Stripe Gateway",
        "type": "stripe"
      },
      "test_result": {
        "status": "connected",
        "response_time_ms": 156.23,
        "account_id": "acct_1234567890"
      }
    },
    {
      "gateway": {
        "id": 2,
        "name": "FlutterWave Gateway",
        "type": "flutterwave"
      },
      "test_result": {
        "status": "failed",
        "response_time_ms": 3000.0,
        "error": "Connection timeout"
      }
    },
    {
      "gateway": {
        "id": 3,
        "name": "UNC Gateway",
        "type": "control_number"
      },
      "test_result": {
        "status": "connected",
        "response_time_ms": 234.45,
        "api_endpoint": "https://api.unc.example.com"
      }
    }
  ]
}
```

---

## 🔐 Authentication (Laravel Sanctum)

The API uses **Laravel Sanctum** for authentication with personal access tokens. Most endpoints require authentication except for public endpoints like countries, currencies, and webhooks.

### **Authentication Flow:**
1. **Option 1 - Login Flow**: Login with email/password to get API token
2. **Option 2 - Personal Access Token**: Generate long-lived tokens for integrations
3. Include token in `Authorization: Bearer {token}` header
4. Use token for all protected endpoints
5. Logout to revoke current token or delete specific tokens

### **🔑 Token Usage:**

**Simple Token Usage:**
- All protected endpoints use: `Authorization: Bearer {auth_token}`
- Get your token from login/register response or generate-token response
- Any valid token works for all protected endpoints

### **Token Generation Methods:**

#### Method 1: Login-based Authentication (Primary - Required First)
- Use `/api/auth/login` with email/password
- **This is your FIRST step** - gets you the initial session token
- Best for: Interactive sessions, short-term access
### **Authentication Flow:**
1. Login with email/password to get your `auth_token`
2. Use `Authorization: Bearer {auth_token}` header for all protected endpoints
3. Optionally generate personal access tokens for long-term integrations
4. All token types work the same way - just use any valid token

### **Simple Authentication Steps:**
1. **Login**: `POST /api/auth/login` → Get `auth_token`
2. **Use Token**: Add `Authorization: Bearer {auth_token}` to all requests
3. **Done**: You can now access all protected endpoints

---

### 16. **Authentication Endpoints**

#### POST - User Login
```http
POST /api/auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "your_password",
  "device_name": "Postman API Client"
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
      "organization_id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin",
      "organization": {
        "id": 1,
        "name": "ShuleSoft Africa"
      }
    },
    "bearer_token": "1|abcdef123456789token_string_here",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

**Response (401 Unauthorized - Invalid Credentials):**
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

#### POST - User Registration
```http
POST /api/auth/register
```

**Request Body:**
```json
{
  "organization_id": 1,
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "secure_password",
  "password_confirmation": "secure_password",
  "role": "finance",
  "sex": "F",
  "device_name": "Postman API Client"
}
```

**Available Roles:** `admin`, `finance`, `support`

**Response (201 Created):**
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
      "role": "finance",
      "sex": "F",
      "created_at": "2026-01-17T10:30:00.000000Z"
    },
    "bearer_token": "2|xyz789456123token_string_here",
    "token_type": "Bearer",
    "expires_in": null
  }
}
```

#### GET - Get Authenticated User
```http
GET /api/user
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "sex": "M",
    "organization": {
      "id": 1,
      "name": "ShuleSoft Africa",
      "currency": {
        "code": "TZS",
        "symbol": "TSh"
      }
    }
  }
}
```

#### POST - User Logout
```http
POST /api/auth/logout
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

#### POST - Generate Personal Access Token
```http
POST /api/auth/generate-token
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "API Integration Token",
  "expires_at": "2026-12-31T23:59:59Z",
  "abilities": ["*"]
}
```

**Request Body (Scoped Token):**
```json
{
  "name": "Limited Scope Token",
  "expires_at": "2026-06-17T23:59:59Z",
  "abilities": ["invoices:read", "customers:read", "subscriptions:write"]
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Personal access token created successfully",
  "data": {
    "personal_token": "3|abcdef123456789new_token_string_here",
    "token_id": 3,
    "name": "API Integration Token",
    "abilities": ["*"],
    "expires_at": "2026-12-31T23:59:59Z",
    "created_at": "2026-01-17T15:45:22.000000Z"
  }
}
```

#### GET - List Personal Access Tokens
```http
GET /api/auth/tokens
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Postman API Client",
      "abilities": ["*"],
      "created_at": "2026-01-17T10:30:00.000000Z",
      "last_used_at": "2026-01-17T15:45:22.000000Z",
      "expires_at": null
    },
    {
      "id": 3,
      "name": "API Integration Token",
      "abilities": ["*"],
      "created_at": "2026-01-17T15:45:22.000000Z",
      "last_used_at": null,
      "expires_at": "2026-12-31T23:59:59.000000Z"
    }
  ]
}
```

#### DELETE - Revoke Personal Access Token
```http
DELETE /api/auth/tokens/{token_id}
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Token deleted successfully"
}
```

**Response (404 Not Found):**
```json
{
  "success": false,
  "message": "Token not found"
}
```

#### POST - Revoke All Tokens
```http
POST /api/auth/logout-all
```

**Headers:**
```
Authorization: Bearer {auth_token}
Accept: application/json
Content-Type: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "All tokens have been revoked"
}
```

### **Authentication Requirements:**

#### 🔓 **Public Endpoints (No Authentication Required):**
- `GET /api/countries` - List countries
- `GET /api/countries/{id}` - Show specific country
- `GET /api/currencies` - List currencies
- `POST /api/webhooks/*` - All webhook endpoints
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration

#### 🔒 **Protected Endpoints (Require Authentication):**
- `POST /api/countries` - Create country (Admin only)
- `PUT /api/countries/{id}` - Update country (Admin only) 
- `DELETE /api/countries/{id}` - Delete country (Admin only)
- `POST /api/currencies` - Create currency (Admin only)
- `PUT /api/currencies/{id}` - Update currency (Admin only)
- `DELETE /api/currencies/{id}` - Delete currency (Admin only)
- All user management, customer management, subscriptions, invoices, etc.

### **Authentication Error Responses:**

#### Missing Token (401)
```json
{
  "message": "Unauthenticated."
}
```

#### Invalid/Expired Token (401)
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error": "Token is invalid or expired"
}
```

#### Insufficient Permissions (403)
```json
{
  "success": false,
  "message": "Forbidden",
  "error": "You don't have permission to access this resource"
}
```

---

## 🚨 Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "email": ["The email field is required."],
    "organization_id": ["The selected organization id is invalid."]
  }
}
```

### Not Found Error (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Internal server error",
  "error_code": "SERVER_ERROR"
}
```

---

## 📚 Business Rules

### Product Types
- **Type ID 1**: One-time products (software licenses, digital downloads, consulting services)
  - Can have 0 or 1 price plan
  - Uses `billing_type: "one_time"`
  - No recurring charges
- **Type ID 2**: Subscription products (SaaS platforms, recurring services, memberships)
  - Must have at least 1 price plan
  - Uses `billing_type: "recurring"`
  - Regular billing intervals (monthly/yearly)

**Usage in API:**
```json
{
  "product_type_id": 1,  // For one-time products
  "product_type_id": 2   // For subscription products
}
```

### Billing Types
- `one_time`: Single payment
- `recurring`: Regular payments (monthly/yearly)
- `usage`: Usage-based billing

### Subscription Status
- `pending`: Awaiting activation
- `active`: Currently active
- `paused`: Temporarily suspended
- `canceled`: Permanently canceled

### Invoice Status
- `draft`: Not yet issued
- `issued`: Sent to customer
- `paid`: Payment received
- `overdue`: Past due date
- `canceled`: Canceled invoice

### **New in Phase 1:**

### Customer Types
- `individual`: Personal customers
- `school`: Educational institutions
- `organization`: Business entities

### User Roles
- `admin`: Full system access and management
- `finance`: Financial operations and reporting
- `support`: Customer support and assistance

### Wallet Transaction Types
- `topup`: Adding credits to wallet
- `deduction`: Using/consuming credits
- `transfer`: Moving credits between customers
- `refund`: Refunding credits back to wallet

### Invoice Types
- `subscription`: Regular subscription billing
- `wallet_topup`: Wallet credit purchases
- `plan_upgrade`: Subscription plan upgrades
- `plan_downgrade`: Subscription plan downgrades
- `one_time`: One-time purchases

### Subscription Changes
- `upgrade`: Moving to higher-tier plan
- `downgrade`: Moving to lower-tier plan
- `pause`: Temporarily suspending subscription
- `reactivate`: Resuming paused subscription

### Currency Features
- Exchange rate management with buffer protection
- Multi-currency price previews
- Automatic rate conversion with fallbacks

---

## 🔧 Environment Configuration

### Development
```
Base URL: http://localhost:8000/api
Database: PostgreSQL (billing schema)
```

### Production
```
Base URL: https://billing.shulesoft.africa/api
Database: PostgreSQL (billing schema)
Authentication: Laravel Sanctum
```

---

## 📦 Postman Collection Variables

Create these variables in your Postman environment:

```json
{
  "base_url": "http://localhost:8000/api",
  "organization_id": "1",
  "customer_id": "1",
  "product_id": "1",
  "user_token": "{{auth_token}}",
  "auth_token": "",
  "user_email": "john@example.com",
  "user_password": "your_password",
  "device_name": "Postman API Client"
}
```

### **Authentication Setup in Postman:**

#### **Simple Authentication Flow:**
1. **Create Login Request:**
   - Method: POST
   - URL: `{{base_url}}/auth/login`
   - Body: 
     ```json
     {
       "email": "{{user_email}}",
       "password": "{{user_password}}",
       "device_name": "{{device_name}}"
     }
     ```

2. **Auto-Save Token (Add to Login Tests tab):**
   ```javascript
   pm.test("Login Successful", function () {
       var jsonData = pm.response.json();
       if (jsonData.success && jsonData.data.bearer_token) {
           pm.environment.set("auth_token", jsonData.data.bearer_token);
           console.log("Token saved: " + jsonData.data.bearer_token);
       }
   });
   ```

3. **Set Authorization Header:**
   - In Collection/Folder settings → Authorization
   - Type: Bearer Token
   - Token: `{{auth_token}}`

#### **Optional: Generate Personal Access Token (For Long-term Integration)**
- Use the `/api/auth/generate-token` endpoint if you need long-lived tokens
- Both token types work the same way - use either in `Authorization: Bearer {{auth_token}}`

### **Token Scopes/Abilities:**

Personal access tokens can be scoped to specific abilities:

#### **Available Scopes:**
- `*` - Full access (default)
- `customers:read` - Read customer data
- `customers:write` - Create/update customers
- `invoices:read` - Read invoices
- `invoices:write` - Create/update invoices
- `subscriptions:read` - Read subscriptions
- `subscriptions:write` - Manage subscriptions
- `payments:read` - Read payment data
- `payments:write` - Process payments
- `wallets:read` - Read wallet balances
- `wallets:write` - Manage wallet transactions
- `admin:*` - Administrative functions

#### **Example Scoped Token:**
```json
{
  "name": "Customer Service Token",
  "abilities": ["customers:read", "customers:write", "invoices:read", "subscriptions:read"],
  "expires_at": "2026-06-17T23:59:59Z"
}
```

3. **Set Authorization Header:**
   - In Collection/Folder settings → Authorization
   - Type: Bearer Token
   - Token: `{{auth_token}}`

---

## 🎯 Testing Workflow

### **Authentication Setup (Required First):**

#### **Simple Authentication:**
1. **Login**: POST `/api/auth/login` with email/password to get your `auth_token`
2. **Set Authorization**: Use `Authorization: Bearer {{auth_token}}` for all protected endpoints
3. **Done**: You can now test all endpoints

**Optional**: Generate personal access tokens with `/api/auth/generate-token` for long-term integrations

### **Main Testing Flow:**
1. **Setup Data**: Create Country → Currency → Organization
2. **User Management**: Create Users for the organization  
3. **Customer Management**: Create Customers and their addresses
4. **Product Catalog**: Create Product Types → Products with Price Plans
5. **Payment Setup**: Create Payment Gateways and Bank Accounts
6. **Integration**: Link Payment Gateways to Organizations
7. **Subscriptions**: Create subscriptions and generate invoices
8. **Payments**: Process payments via webhooks
9. **🆕 Wallet System**: Test wallet credits, deductions, and transfers
10. **🆕 Currency Conversion**: Test multi-currency pricing and conversions
11. **🆕 Subscription Lifecycle**: Test plan changes, pauses, and reactivations

### **Phase 1 Testing Scenarios:**

#### Wallet System Testing:
1. Create customer wallet with initial credits
2. Test credit deduction with insufficient balance
3. Test credit transfers between customers
4. Verify transaction history and balance calculations
5. Test wallet topup via invoice payment

#### Multi-Currency Testing:
1. Set up multiple currencies with exchange rates
2. Test currency conversion with buffer rates
3. Generate price previews in multiple currencies
4. Test subscription billing in different currencies

#### Enhanced Subscription Testing:
1. Create subscription with trial period
2. Test plan upgrade with proration
3. Test subscription pause and reactivation
4. Verify subscription change history
5. Test subscription cancellation flows

---

## 🚀 **Phase 2 Features Summary**

### **NEW! Advanced Invoice Types (Phase 2)**
- **Wallet Topup Invoices**: `/api/invoices/wallet-topup`
- **Plan Upgrade Invoices**: `/api/invoices/plan-upgrade` 
- **Plan Downgrade Invoices**: `/api/invoices/plan-downgrade`

### **NEW! Enhanced Customer Lookup (Phase 2)**  
- **Phone Lookup with Status**: `/api/customers/by-phone/{phone}/status`
- **Email Lookup with Status**: `/api/customers/by-email/{email}/status`
- Returns comprehensive customer status including wallet balances, subscription counts, and outstanding balances

### **NEW! Multiple Payment Gateway Webhooks (Phase 2)**
- **Stripe Webhook Handler**: `/api/webhooks/stripe`
- **FlutterWave Webhook Handler**: `/api/webhooks/flutterwave`
- **Test Webhook Handler**: `/api/webhooks/test`
- All with signature verification and automatic payment processing

### **NEW! Payment Gateway Testing (Phase 2)**
- **Single Gateway Test**: `/api/payment-gateways/test-connection?gateway_id={id}`
- **All Gateways Test**: `/api/payment-gateways/test-all-connections`
- Comprehensive connectivity testing with response time monitoring and error reporting

### **Phase 2 Testing Scenarios:**

#### Advanced Invoice Testing:
1. Create wallet topup invoice for different wallet types
2. Test plan upgrades with immediate proration
3. Test plan downgrades with wallet credits
4. Verify proration calculations and credit applications

#### Customer Status Lookup Testing:
1. Lookup customer by phone with full status
2. Lookup customer by email with wallet balances
3. Test with non-existent customers
4. Verify comprehensive status data accuracy

#### Multi-Gateway Webhook Testing:
1. Test Stripe payment webhook processing
2. Test FlutterWave charge completion webhook
3. Test webhook signature verification
4. Test invalid webhook payloads and error handling

#### Gateway Connectivity Testing:
1. Test individual gateway connections
2. Test all gateways simultaneously
3. Verify connection status reporting
4. Test with invalid gateway configurations

This comprehensive API documentation covers all the major endpoints and workflows in the billing system, including all Phase 1 and Phase 2 enhancements. Each endpoint includes sample requests and responses to help with testing and integration.