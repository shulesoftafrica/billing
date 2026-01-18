# 🔍 Missing API Features Analysis

## Overview
This document identifies endpoints and features present in `POSTMAN_OLD.md` that are **NOT** implemented in the current `POSTMAN_API_DOCUMENTATION.md`.

---

## 🚨 Missing Core Features

### 1. **Multi-Currency Operations**
❌ **Missing in current API**

#### Currency Conversion & Price Preview
- **GET** `/api/billing/prices` - Get price preview with currency conversion
- **POST** `/api/billing/convert` - Convert between currencies with buffer support

```json
// Price Preview Example
GET /api/billing/prices?amount=50000&currency=TZS&target_currencies=KES,USD&use_buffer=true

// Currency Conversion Example  
POST /api/billing/convert
{
  "amount": 50000,
  "from_currency": "TZS", 
  "to_currency": "KES",
  "use_buffer": true
}
```

---

### 2. **Wallet System** 
❌ **Completely Missing**

#### Wallet Management Endpoints
- **GET** `/api/billing/wallet/balance?student_id=12345` - Get all wallet balances
- **GET** `/api/billing/wallet/balance?student_id=12345&wallet_type=sms` - Get specific wallet balance
- **POST** `/api/billing/deduct-wallet` - Deduct from wallet balance
- **GET** `/api/billing/wallet/{student_id}/transactions` - Get wallet transaction history

```json
// Wallet Deduction Example
POST /api/billing/deduct-wallet
{
  "student_id": 12345,
  "product_code": "shulesoft",
  "wallet_type": "sms", 
  "amount": 50,
  "description": "Bulk SMS to parents - Grade 10 results",
  "metadata": {
    "recipients_count": 45,
    "message_length": 160,
    "campaign_id": "CAMP-001"
  }
}
```

#### Wallet Types Supported
- `sms` - SMS credits wallet
- `whatsapp_messages` - WhatsApp message credits
- `ai_credits` - AI service credits

---

### 3. **Advanced Invoice Types**
❌ **Missing Specialized Invoice Creation**

#### Missing Invoice Types
- **Wallet Topup Invoices** - For adding credits to customer wallets
- **Plan Change/Upgrade Invoices** - With proration support
- **Plan Downgrade Invoices** - With credit handling

```json
// Wallet Topup Invoice Example
POST /api/billing/create-invoice
{
  "product_code": "shulesoft",
  "invoice_type": "wallet_topup",
  "customer": {
    "name": "Mwenge Secondary School",
    "phone": "255123456789",
    "email": "admin@mwenge.edu.tz"
  },
  "amount": 100000,
  "currency": "TZS",
  "units": 2000,
  "unit_price": 50,
  "wallet_type": "sms"
}

// Plan Change Invoice with Proration
POST /api/billing/create-invoice  
{
  "product_code": "shulesoft",
  "invoice_type": "plan_upgrade",
  "old_plan_code": "basic",
  "new_plan_code": "premium", 
  "proration_credit": 5000
}
```

---

### 4. **Advanced Customer Management**
❌ **Missing Customer Lookup Methods**

#### Multiple Customer Identification Methods
- **GET** `/api/billing/customer/{phone}/status` - Lookup by phone number
- **GET** `/api/billing/customer/{email}/status` - Lookup by email address  
- **GET** `/api/billing/customer/{student_id}/status` - Lookup by student ID

```json
// Customer Status Response (Extended)
{
  "success": true,
  "customer": {
    "student_id": 12345,
    "name": "Mwenge Secondary School",
    "phone": "255123456789",
    "email": "admin@mwenge.edu.tz"
  },
  "subscriptions": [...],
  "wallets": {
    "sms": {"balance": 1500, "currency": "credits"},
    "whatsapp_messages": {"balance": 800, "currency": "credits"}
  },
  "invoices": [...]
}
```

---

### 5. **Advanced Subscription Lifecycle**
❌ **Missing Subscription Operations**

#### Extended Subscription Management
- **POST** `/api/billing/subscription/reactivate` - Reactivate canceled subscription
- **POST** `/api/billing/subscription/change-plan` - Change subscription plan with proration

```json
// Subscription Reactivation
POST /api/billing/subscription/reactivate
{
  "student_id": 12345,
  "feature_code": "core",
  "plan_code": "basic",
  "billing_cycle": "monthly",
  "payment_method": "existing"
}

// Plan Change with Advanced Options
POST /api/billing/subscription/change-plan
{
  "student_id": 12345,
  "current_feature_code": "core", 
  "new_feature_code": "core",
  "current_plan_code": "basic",
  "new_plan_code": "premium",
  "new_billing_cycle": "yearly",
  "effective_date": "immediate"
}
```

---

### 6. **Payment Processing System**
❌ **Missing Payment Operations**

#### Payment Processing Endpoints
- **POST** `/api/billing/invoice/{invoice_id}/payment` - Process payment for invoice
- **POST** `/api/billing/payments/{invoice_id}/retry` - Retry failed payment

```json
// Payment Processing
POST /api/billing/invoice/INV-20251227-001234/payment
{
  "payment_method": "card",
  "currency": "TZS",
  "return_url": "http://localhost/payment-complete",
  "metadata": {
    "payment_source": "school_dashboard",
    "user_agent": "Mozilla/5.0...",
    "ip_address": "127.0.0.1"
  }
}
```

---

### 7. **Enhanced Webhook System**
❌ **Missing Multiple Payment Providers**

#### Additional Webhook Endpoints
- **POST** `/api/billing/webhooks/test` - Test webhook (development)
- **POST** `/api/billing/webhooks/stripe` - Stripe webhook handler
- **POST** `/api/billing/webhooks/flutterwave` - FlutterWave webhook handler

```json
// Stripe Webhook Example
POST /api/billing/webhooks/stripe
Headers: Stripe-Signature: whsec_test_signature
{
  "id": "evt_test_webhook",
  "object": "event", 
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_test_123456",
      "amount": 5000000,
      "currency": "tzs",
      "status": "succeeded"
    }
  }
}
```

---

### 8. **Reporting & Analytics System**
❌ **Completely Missing**

#### Analytics Endpoints
- **GET** `/api/billing/reports/billing` - Generate billing reports
- **GET** `/api/billing/analytics/customers` - Customer analytics

```json
// Billing Report Request
GET /api/billing/reports/billing?period=monthly&year=2025&month=12

// Customer Analytics Request  
GET /api/billing/analytics/customers?date_from=2025-12-01&date_to=2025-12-27
```

---

### 9. **Enhanced Product Management**
❌ **Missing Advanced Product Features**

#### Complex Product Structure Support
- **Product entitlements** (max_students, storage_gb, api_calls_per_month)
- **Feature-based pricing** (core, premium features)
- **Metadata support** (category, target_market, region)
- **Multiple billing cycles** per product
- **Credit rollover** functionality

```json
// Advanced Product Creation (Missing)
POST /api/billing/products
{
  "product_code": "shulesoft",
  "entitlements": {
    "max_students": 1000,
    "max_teachers": 100, 
    "storage_gb": 50,
    "api_calls_per_month": 50000
  },
  "plans": {
    "premium": {
      "features": [
        "unlimited_messages",
        "whatsapp_channels", 
        "customer_followups",
        "credits_rollover"
      ]
    }
  }
}
```

---

### 10. **Student-Based Customer Model**
❌ **Missing Student ID Concept**

The old API uses a `student_id` concept for customer identification, which provides:
- Unique customer identification
- School-based customer management  
- Integration with educational systems
- Simplified customer lookup

---

## 🎯 Business Logic Differences

### Current API Limitations:
1. **No wallet/credits system** - Cannot handle prepaid services
2. **Basic subscription model** - No plan changes with proration
3. **Single payment gateway** - Only UNC payment supported
4. **No analytics** - Missing business intelligence features
5. **Basic customer model** - Limited identification methods
6. **No currency conversion** - Single currency operations only
7. **Simple invoice types** - Only basic subscription invoices
8. **No payment processing** - Only invoice generation

### Old API Advanced Features:
1. **Full wallet ecosystem** with multiple wallet types
2. **Advanced subscription lifecycle** with plan changes
3. **Multiple payment providers** (Stripe, FlutterWave, UNC)
4. **Comprehensive analytics** and reporting
5. **Multi-currency support** with conversion rates
6. **Complex product entitlements** and feature management
7. **Advanced invoice types** (topup, upgrades, downgrades)
8. **Full payment processing** with retry mechanisms

---

## 📊 Impact Assessment

### High Priority Missing Features:
1. **Wallet System** - Critical for prepaid service models
2. **Multi-Currency Support** - Essential for international operations
3. **Advanced Subscription Management** - Required for SaaS businesses
4. **Payment Processing** - Needed for complete billing flow

### Medium Priority Missing Features:
1. **Multiple Webhook Providers** - Important for payment diversity
2. **Enhanced Customer Management** - Improves user experience
3. **Reporting & Analytics** - Valuable for business insights

### Low Priority Missing Features:
1. **Advanced Product Entitlements** - Nice-to-have features
2. **Webhook Testing** - Development convenience
3. **Complex Metadata Support** - Additional flexibility

---

## 🚀 Recommendations

### Immediate Implementation Needed:
1. Implement wallet system for credit-based services
2. Add multi-currency support with conversion rates
3. Enhance subscription lifecycle management
4. Add payment processing capabilities

### Future Enhancements:
1. Build comprehensive analytics system
2. Add support for multiple payment providers
3. Implement advanced product entitlement system
4. Create webhook testing infrastructure

---

## ✅ Common Features (Exist in Both APIs)

While there are significant differences, some core functionality exists in both APIs, though often with different implementation approaches:

### 1. **Currency Management**
**Current API:** Basic CRUD operations for currencies
```json
// Current Implementation
GET /api/currencies
POST /api/currencies
{
  "name": "US Dollar",
  "code": "USD", 
  "symbol": "$"
}
```

**Old API:** Currency listing with conversion capabilities
```json
// Old Implementation  
GET /api/billing/currencies
{
  "success": true,
  "data": {
    "currencies": [
      {
        "code": "TZS",
        "name": "Tanzanian Shilling", 
        "symbol": "TSh",
        "country": "Tanzania",
        "is_base": true
      }
    ]
  }
}
```

### 2. **Product Management**
**Current API:** Basic product CRUD with price plans
```json
// Current Implementation
GET /api/products?organization_id=1
POST /api/products
{
  "organization_id": 1,
  "product_type_id": 2,
  "name": "Enterprise Platform",
  "price_plans": [...]
}
```

**Old API:** Advanced product management with entitlements and features
```json
// Old Implementation
GET /api/billing/products?product_code=shulesoft
POST /api/billing/products
{
  "product_code": "shulesoft",
  "entitlements": {...},
  "plans": {...},
  "wallet_types": [...]
}
```

### 3. **Customer Operations** 
**Current API:** Basic customer CRUD with organization filtering
```json
// Current Implementation
GET /api/customers?organization_id=1
POST /api/customers
{
  "organization_id": 1,
  "name": "Customer Name",
  "email": "customer@example.com"
}
```

**Old API:** Multiple customer lookup methods with comprehensive status
```json
// Old Implementation
GET /api/billing/customer/255123456789/status
GET /api/billing/customer/admin@school.edu/status  
GET /api/billing/customer/12345/status
```

### 4. **Subscription Management**
**Current API:** Basic subscription creation and cancellation
```json
// Current Implementation
POST /api/subscriptions
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}

POST /api/subscriptions/{id}/cancel
```

**Old API:** Advanced subscription lifecycle management
```json
// Old Implementation
POST /api/billing/subscription/cancel
POST /api/billing/subscription/reactivate
POST /api/billing/subscription/change-plan
```

### 5. **Invoice Operations**
**Current API:** Basic invoice listing and control number generation
```json
// Current Implementation  
GET /api/invoices
POST /api/invoices
{
  "organization_id": 1,
  "product_id": 1,
  "customer_id": 1
}
```

**Old API:** Multiple invoice types with advanced features
```json
// Old Implementation
POST /api/billing/create-invoice
{
  "invoice_type": "subscription|wallet_topup|plan_upgrade",
  "product_code": "shulesoft",
  "proration_credit": 5000
}
```

### 6. **Webhook Support**
**Current API:** Single webhook endpoint (UNC Payment)
```json
// Current Implementation
POST /api/webhooks/unc-payment
{
  "transaction_id": "TXN123456789",
  "control_number": "9912345678",
  "status": "success"
}
```

**Old API:** Multiple webhook providers with testing
```json
// Old Implementation
POST /api/billing/webhooks/stripe
POST /api/billing/webhooks/flutterwave
POST /api/billing/webhooks/test
```

### 7. **Authentication & Security**
**Current API:** Laravel Sanctum token-based authentication
```json
// Current Implementation
GET /api/user
Headers: Authorization: Bearer {token}
```

**Old API:** API Key-based authentication  
```json
// Old Implementation
Headers: X-API-Key: YOUR_API_KEY_HERE
```

---

## 📊 Feature Comparison Matrix

| Feature Category | Current API | Old API | Status |
|------------------|------------|---------|--------|
| **Basic CRUD Operations** | ✅ Full | ✅ Full | **Both Complete** |
| **Currency Management** | ✅ Basic | ✅ Advanced | **Old API Superior** |
| **Product Management** | ✅ Good | ✅ Advanced | **Old API Superior** |
| **Customer Management** | ✅ Basic | ✅ Advanced | **Old API Superior** |
| **Subscription Lifecycle** | ✅ Basic | ✅ Advanced | **Old API Superior** |
| **Invoice Types** | ✅ Basic | ✅ Multiple | **Old API Superior** |
| **Payment Processing** | ❌ None | ✅ Full | **Missing in Current** |
| **Wallet System** | ❌ None | ✅ Full | **Missing in Current** |
| **Multi-Currency** | ❌ None | ✅ Full | **Missing in Current** |
| **Analytics/Reports** | ❌ None | ✅ Full | **Missing in Current** |
| **Webhook Providers** | ✅ Single | ✅ Multiple | **Old API Superior** |
| **Authentication** | ✅ Sanctum | ✅ API Key | **Both Different** |

---

## 🎯 Implementation Approach Differences

### Current API Philosophy:
- **Resource-Oriented**: Traditional RESTful API design
- **Organization-Centric**: Multi-tenant with organization isolation
- **Database-Driven**: Direct model-to-API mapping
- **Laravel Standards**: Following Laravel conventions
- **Foundation-First**: Building core functionality first

### Old API Philosophy:
- **Service-Oriented**: Business logic-centric design
- **Student-Centric**: Educational system focused
- **Feature-Rich**: Comprehensive business functionality
- **Payment-Focused**: Billing and payment processing emphasis
- **Production-Ready**: Full-featured billing platform

---

## 🔗 Integration Compatibility

### Data Model Mapping:
```json
// Current API → Old API Mapping
{
  "customers.id": "student_id",
  "organizations.id": "organization_id", 
  "products.id": "product_code",
  "price_plans.id": "plan_code",
  "invoices.invoice_number": "invoice_id"
}
```

### Endpoint Mapping:
```json
// Similar Functionality Mapping
{
  "GET /api/customers": "GET /api/billing/customer/{id}/status",
  "POST /api/subscriptions": "POST /api/billing/create-invoice (subscription)",
  "POST /api/invoices": "POST /api/billing/create-invoice",
  "POST /api/webhooks/unc-payment": "POST /api/billing/webhooks/*"
}
```

---

**Summary**: The current API (POSTMAN_API_DOCUMENTATION.md) is missing approximately **40+ endpoints** and several **core business features** that exist in the old API (POSTMAN_OLD.md). While both APIs share **7 common feature areas**, the old API provides **significantly more advanced functionality** in each area. The most critical gaps are in wallet management, multi-currency operations, advanced subscription lifecycle, and payment processing capabilities.

**Recommendation**: The current API serves as an excellent **foundation**, but requires **substantial enhancement** to match the comprehensive billing platform capabilities of the old API.