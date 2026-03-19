## Authentication

This API uses **OAuth 2.0 Client Credentials** for authentication. To integrate with the billing platform, you'll need to obtain API credentials and exchange them for access tokens.

###  Quick Reference

```http
# Get Access Token
POST /api/v1/oauth/token
Content-Type: application/json

{
  "grant_type": "client_credentials",
  "client_id": "org_live_client_abc123xyz...",
  "client_secret": "org_live_secret_xyz789abc...",
  "scope": "*"
}

# Use Token in Requests
GET /api/v1/{endpoint}
Authorization: Bearer shulesoft_2|def456ghi789...
Accept: application/json
```

**Token Expiration:** 90 days (7,776,000 seconds)  
**Rate Limit:** 60 requests per minute  
**Base URL:** `https://api.yourbillingplatform.com`

---

### 🚀 Quick Start: Getting Your API Credentials

**Step 1: Create Your Account (One-time Setup)**

First, create a user account if you don't have one:

**Method:** `POST`  
**URL:** `/api/v1/auth/register`

**Request Body:**
```json
{
  "organization_id": 1,
  "name": "John Doe",
  "email": "john@yourcompany.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "role": "admin"
}
```

**Success Response:** `201 Created`
```json
{
  "message": "User registered successfully",
  "access_token": "shulesoft_1|abc123xyz...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "expires_at": "2026-04-13T10:44:36+00:00",
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@yourcompany.com",
    "role": "admin"
  }
}
```

---

**Step 2: Create OAuth Client Credentials (One-time Setup)**

Use your user token from Step 1 to create API client credentials:

**Method:** `POST`  
**URL:** `/api/v1/oauth/clients`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_USER_TOKEN_FROM_STEP_1} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Production API Client",
  "environment": "live",
  "allowed_scopes": ["*"]
}
```

**Parameters:**
- `name` (required): Descriptive name for this client (e.g., "Production Server", "Mobile App")
- `environment` (required): `test` for testing, `live` for production
- `allowed_scopes` (optional): Array of scopes, use `["*"]` for full access
- `expires_at` (optional): Expiration date in ISO 8601 format

**🔒 Security Note:** The OAuth client is automatically created for your authenticated user's organization. You cannot create clients for other organizations.

**Success Response:** `201 Created`
```json
{
  "message": "OAuth client created successfully",
  "client": {
    "id": 1,
    "name": "Production API Client",
    "client_id": "org_live_client_abc123xyz456def789ghi012jkl345mno",
    "client_secret": "org_live_secret_xyz789abc012def345ghi678jkl901mno234pqr567",
    "environment": "live",
    "allowed_scopes": ["*"],
    "expires_at": null,
    "created_at": "2026-03-14T10:00:00+00:00"
  },
  "warning": "Store the client_secret securely. It will not be shown again."
}
```

**⚠️ CRITICAL:** Save your `client_id` and `client_secret` immediately! The `client_secret` is shown **only once** and cannot be retrieved again. Store it securely (e.g., environment variables, secrets manager).

---

**Step 3: Get Access Token (For Each API Session)**

Exchange your client credentials for an access token:

**Method:** `POST`  
**URL:** `/api/v1/oauth/token`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "grant_type": "client_credentials",
  "client_id": "org_live_client_abc123xyz456def789ghi012jkl345mno",
  "client_secret": "org_live_secret_xyz789abc012def345ghi678jkl901mno234pqr567",
  "scope": "*"
}
```

**Success Response:** `200 OK`
```json
{
  "access_token": "shulesoft_2|def456ghi789jkl012mno345pqr678stu901vwx234",
  "token_type": "Bearer",
  "expires_in": 7776000,
  "scope": "*",
  "organization_id": 1
}
```

**Response Fields:**
- `access_token`: Your API access token (use this for all API requests)
- `token_type`: Always "Bearer"
- `expires_in`: Token lifetime in seconds (90 days = 7,776,000 seconds)
- `scope`: Granted permissions
- `organization_id`: Your organization ID

**Error Response:** `401 Unauthorized`
```json
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
```

---

**Step 4: Use Access Token for API Requests**

Include the access token in the `Authorization` header for all API requests:

```http
GET /api/v1/products
Authorization: Bearer shulesoft_2|def456ghi789jkl012mno345pqr678stu901vwx234
Accept: application/json
```

---

### 📝 Complete Example: cURL

```bash
# Step 1: Register user (one-time)
curl -X POST https://api.yourbillingplatform.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@yourcompany.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "role": "admin"
  }'

# Save the access_token from response

# Step 2: Create OAuth client (one-time)
curl -X POST https://api.yourbillingplatform.com/api/v1/oauth/clients \
  -H "Authorization: Bearer shulesoft_1|abc123xyz..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Production API Client",
    "environment": "live",
    "allowed_scopes": ["*"]
  }'

# Save the client_id and client_secret from response

# Step 3: Get access token (when needed)
curl -X POST https://api.yourbillingplatform.com/api/v1/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "org_live_client_abc123xyz...",
    "client_secret": "org_live_secret_xyz789def...",
    "scope": "*"
  }'

# Step 4: Use access token for API calls
curl -X GET https://api.yourbillingplatform.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_2|def456ghi..." \
  -H "Accept: application/json"
```

---

### 📝 Complete Example: JavaScript/Node.js

```javascript
const axios = require('axios');

const API_BASE_URL = 'https://api.yourbillingplatform.com';

// Store these securely (environment variables)
const CLIENT_ID = 'org_live_client_abc123xyz456def789ghi012jkl345mno';
const CLIENT_SECRET = 'org_live_secret_xyz789abc012def345ghi678jkl901mno234pqr567';

// Step 3: Get access token
async function getAccessToken() {
  const response = await axios.post(`${API_BASE_URL}/api/v1/oauth/token`, {
    grant_type: 'client_credentials',
    client_id: CLIENT_ID,
    client_secret: CLIENT_SECRET,
    scope: '*'
  });
  
  return response.data.access_token;
}

// Step 4: Make API request
async function getProducts() {
  const accessToken = await getAccessToken();
  
  const response = await axios.get(`${API_BASE_URL}/api/v1/products`, {
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Accept': 'application/json'
    }
  });
  
  return response.data;
}

// Usage
getProducts()
  .then(products => console.log('Products:', products))
  .catch(error => console.error('Error:', error.response?.data));
```

---

### 📝 Complete Example: Python

```python
import requests
import os

API_BASE_URL = 'https://api.yourbillingplatform.com'

# Store these securely (environment variables)
CLIENT_ID = os.getenv('BILLING_CLIENT_ID')
CLIENT_SECRET = os.getenv('BILLING_CLIENT_SECRET')

# Step 3: Get access token
def get_access_token():
    response = requests.post(f'{API_BASE_URL}/api/v1/oauth/token', json={
        'grant_type': 'client_credentials',
        'client_id': CLIENT_ID,
        'client_secret': CLIENT_SECRET,
        'scope': '*'
    })
    response.raise_for_status()
    return response.json()['access_token']

# Step 4: Make API request
def get_products():
    access_token = get_access_token()
    
    response = requests.get(
        f'{API_BASE_URL}/api/v1/products',
        headers={
            'Authorization': f'Bearer {access_token}',
            'Accept': 'application/json'
        }
    )
    response.raise_for_status()
    return response.json()

# Usage
if __name__ == '__main__':
    try:
        products = get_products()
        print('Products:', products)
    except requests.exceptions.RequestException as e:
        print('Error:', e.response.json() if e.response else str(e))
```

---

### 📝 Complete Example: PHP

```php
<?php

$apiBaseUrl = 'https://api.yourbillingplatform.com';

// Store these securely (environment variables)
$clientId = getenv('BILLING_CLIENT_ID');
$clientSecret = getenv('BILLING_CLIENT_SECRET');

// Step 3: Get access token
function getAccessToken($apiBaseUrl, $clientId, $clientSecret) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "$apiBaseUrl/api/v1/oauth/token",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '*'
        ])
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data['access_token'];
}

// Step 4: Make API request
function getProducts($apiBaseUrl, $accessToken) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => "$apiBaseUrl/api/v1/products",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken",
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usage
try {
    $accessToken = getAccessToken($apiBaseUrl, $clientId, $clientSecret);
    $products = getProducts($apiBaseUrl, $accessToken);
    print_r($products);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

---

### 🔐 Best Practices

1. **Store Credentials Securely**
   - Never commit `client_secret` to version control
   - Use environment variables or secrets management (AWS Secrets Manager, Azure Key Vault, etc.)
   - Rotate credentials periodically

2. **Token Management**
   - Cache access tokens until they expire (90 days for client credentials)
   - Implement automatic token refresh when tokens expire
   - Handle 401 errors by requesting a new token

3. **Error Handling**
   - Implement retry logic for network failures
   - Handle rate limiting (429 errors) with exponential backoff
   - Log authentication failures for security monitoring

4. **Environment Separation**
   - Use `test` environment for development/staging
   - Use `live` environment for production only
   - Create separate OAuth clients for different environments

---

### 🔄 Token Expiration & Renewal

Access tokens expire after **90 days** (7,776,000 seconds). When you receive a `401 Unauthorized` error, request a new token:

```javascript
// Example: Token renewal on 401 error
async function makeApiRequest(url, options = {}) {
  let accessToken = await getAccessToken();
  
  try {
    return await fetch(url, {
      ...options,
      headers: {
        'Authorization': `Bearer ${accessToken}`,
        'Accept': 'application/json',
        ...options.headers
      }
    });
  } catch (error) {
    if (error.response?.status === 401) {
      // Token expired, get a new one
      accessToken = await getAccessToken();
      return await fetch(url, {
        ...options,
        headers: {
          'Authorization': `Bearer ${accessToken}`,
          'Accept': 'application/json',
          ...options.headers
        }
      });
    }
    throw error;
  }
}
```

---

### 🛠️ Managing Your OAuth Clients

**List All Your OAuth Clients**

**Method:** `GET`  
**URL:** `/api/v1/oauth/clients`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_USER_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Production API Client",
      "client_id": "org_live_client_abc123xyz...",
      "environment": "live",
      "status": "active",
      "allowed_scopes": ["*"],
      "last_used_at": "2026-03-14T11:00:00+00:00",
      "last_used_ip": "192.168.1.100",
      "expires_at": null,
      "created_at": "2026-03-14T10:00:00+00:00"
    }
  ]
}
```

---

**Revoke an OAuth Client**

**Method:** `DELETE`  
**URL:** `/api/v1/oauth/clients/{client_id}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_USER_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "message": "Client revoked successfully",
  "client": {
    "id": 1,
    "name": "Production API Client",
    "status": "revoked"
  }
}
```

---

### ❓ Common Errors

**Invalid Client Credentials**
```json
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
```
**Solution:** Verify your `client_id` and `client_secret` are correct.

---

**Expired Token**
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```
**Solution:** Request a new access token using your client credentials.

---

**Rate Limit Exceeded**
```json
{
  "message": "Too Many Attempts."
}
```
**Solution:** Wait 60 seconds before making more requests. Current limit: 60 requests per minute.

---

## Products

### List All Products
**Method:** `GET`
**URL:** `/api/v1/products`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "product_type_id": 2,
      "name": "Premium Hosting Plan",
      "description": "Monthly recurring hosting",
      "unit": "month",
      "status": "active",
      "created_at": "2026-03-14T10:00:00+00:00"
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Products
**Method:** `POST`
**URL:** `/api/v1/products`

**Description:** Create a new product with pricing plans. Products must be assigned a product type that determines their billing behavior.

> **💡 Note:** The `organization_id` parameter is **optional**. If not provided, it will be automatically extracted from your access token. You only need to include it if you want to explicitly specify it (must match your token's organization).

**Product Type IDs:**
- `1` - **One-time Product**: For single-charge items (consulting, projects, one-off services)
- `2` - **Subscription Product**: For recurring billing (SaaS, memberships, monthly plans)
- `3` - **Usage Product**: For pay-per-use billing (API calls, storage, bandwidth, credits)

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `organization_id` | integer | Optional | Auto-injected from token. Only specify if needed (must match token's organization) |
| `product_type_id` | integer | **Required** | Product type: `1` (One-time), `2` (Subscription), `3` (Usage-based) |
| `name` | string | **Required** | Product name (max 255 chars, unique per organization) |
| `product_code` | string | Optional | Unique product identifier/SKU (unique per organization, e.g., "safarichat", "premium-plan") |
| `description` | string | Optional | Product description |
| `unit` | string | Optional | Unit of measurement (max 255 chars, e.g., "month", "GB", "API call", "user") |
| `status` | string | **Required** | Product status: `active`, `inactive`, or `archived` |
| `price_plans` | array | Conditional | **Required** for types 2 & 3, Optional for type 1 (max 1 plan) |
| `price_plans[].name` | string | **Required** | Price plan name (max 255 chars) |
| `price_plans[].subscription_type` | string | Conditional | **Required** for type 2. Optional for type 3. Not allowed for type 1. Values: `daily`, `weekly`, `monthly`, `quarterly`, `semi_annually`, `yearly` |
| `price_plans[].amount` | number | **Required** | Price amount (min: 0) |
| `price_plans[].currency` | string | **Required** | Currency code (2-5 chars, e.g., "TZS", "USD", "EUR") |
| `price_plans[].rate` | integer | Optional | Rate or conversion factor (min: 1, useful for usage-based billing) |

**Request Body:**
```json
{
  "product_type_id": 2,  // 1: One-time Product, 2: Subscription Product, 3: Usage Product
  "name": "SafariChat Platform",
  "product_code": "safarichat",
  "description": "WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports",
  "unit": "month",
  "status": "active",
  "price_plans": [
    {
      "name": "Trial Plan",
      "subscription_type": "monthly",
      "amount": 0,
      "currency": "TZS",
      "rate": 3
    },
    {
      "name": "Starter Plan",
      "subscription_type": "monthly",
      "amount": 69000,
      "currency": "TZS",
      "rate": 30
    },
    {
      "name": "Pro Plan",
      "subscription_type": "monthly",
      "amount": 149000,
      "currency": "TZS",
      "rate": 30
    },
    {
      "name": "Premium Plan",
      "subscription_type": "monthly",
      "amount": 299000,
      "currency": "TZS",
      "rate": 30
    }
  ]
}
```

**How to Send the Request:**

**Using cURL (Command Line):**
```bash
curl -X POST "https://yourdomain.com/api/v1/products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_type_id": 2,
    "name": "SafariChat Platform",
    "product_code": "safarichat",
    "description": "WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports",
    "unit": "month",
    "status": "active",
    "price_plans": [
      {
        "name": "Trial Plan",
        "subscription_type": "monthly",
        "amount": 0,
        "currency": "TZS",
        "rate": 3
      },
      {
        "name": "Starter Plan",
        "subscription_type": "monthly",
        "amount": 69000,
        "currency": "TZS",
        "rate": 30
      },
      {
        "name": "Pro Plan",
        "subscription_type": "monthly",
        "amount": 149000,
        "currency": "TZS",
        "rate": 30
      },
      {
        "name": "Premium Plan",
        "subscription_type": "monthly",
        "amount": 299000,
        "currency": "TZS",
        "rate": 30
      }
    ]
  }'
```

**Using PHP (with Guzzle):**
```php
<?php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://yourdomain.com',
    'headers' => [
        'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ]
]);

$response = $client->post('/api/v1/products', [
    'json' => [
        'product_type_id' => 2,
        'name' => 'SafariChat Platform',
        'product_code' => 'safarichat',
        'description' => 'WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports',
        'unit' => 'month',
        'status' => 'active',
        'price_plans' => [
            [
                'name' => 'Trial Plan',
                'subscription_type' => 'monthly',
                'amount' => 0,
                'currency' => 'TZS',
                'rate' => 3
            ],
            [
                'name' => 'Starter Plan',
                'subscription_type' => 'monthly',
                'amount' => 69000,
                'currency' => 'TZS',
                'rate' => 30
            ],
            [
                'name' => 'Pro Plan',
                'subscription_type' => 'monthly',
                'amount' => 149000,
                'currency' => 'TZS',
                'rate' => 30
            ],
            [
                'name' => 'Premium Plan',
                'subscription_type' => 'monthly',
                'amount' => 299000,
                'currency' => 'TZS',
                'rate' => 30
            ]
        ]
    ]
]);

$data = json_decode($response->getBody(), true);
echo "Product created with ID: " . $data['data']['id'];
```

**Using JavaScript (Fetch API):**
```javascript
const createProduct = async () => {
  const response = await fetch('https://yourdomain.com/api/v1/products', {
    method: 'POST',
    headers: {
      'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      product_type_id: 2,
      name: 'SafariChat Platform',
      product_code: 'safarichat',
      description: 'WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports',
      unit: 'month',
      status: 'active',
      price_plans: [
        {
          name: 'Trial Plan',
          subscription_type: 'monthly',
          amount: 0,
          currency: 'TZS',
          rate: 3
        },
        {
          name: 'Starter Plan',
          subscription_type: 'monthly',
          amount: 69000,
          currency: 'TZS',
          rate: 30
        },
        {
          name: 'Pro Plan',
          subscription_type: 'monthly',
          amount: 149000,
          currency: 'TZS',
          rate: 30
        },
        {
          name: 'Premium Plan',
          subscription_type: 'monthly',
          amount: 299000,
          currency: 'TZS',
          rate: 30
        }
      ]
    })
  });

  const data = await response.json();
  console.log('Product created:', data);
  return data;
};

createProduct();
```

**Using Python (requests library):**
```python
import requests
import json

url = "https://yourdomain.com/api/v1/products"
headers = {
    "Authorization": "Bearer YOUR_ACCESS_TOKEN",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

payload = {
    "product_type_id": 2,
    "name": "SafariChat Platform",
    "product_code": "safarichat",
    "description": "WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports",
    "unit": "month",
    "status": "active",
    "price_plans": [
        {
            "name": "Trial Plan",
            "subscription_type": "monthly",
            "amount": 0,
            "currency": "TZS",
            "rate": 3
        },
        {
            "name": "Starter Plan",
            "subscription_type": "monthly",
            "amount": 69000,
            "currency": "TZS",
            "rate": 30
        },
        {
            "name": "Pro Plan",
            "subscription_type": "monthly",
            "amount": 149000,
            "currency": "TZS",
            "rate": 30
        },
        {
            "name": "Premium Plan",
            "subscription_type": "monthly",
            "amount": 299000,
            "currency": "TZS",
            "rate": 30
        }
    ]
}

response = requests.post(url, headers=headers, json=payload)
data = response.json()

if response.status_code == 201:
    print(f"Product created with ID: {data['data']['id']}")
else:
    print(f"Error: {data}")
```

**Using Node.js (Axios):**
```javascript
const axios = require('axios');

const createProduct = async () => {
  try {
    const response = await axios.post(
      'https://yourdomain.com/api/v1/products',
      {
        product_type_id: 2,
        name: 'SafariChat Platform',
        product_code: 'safarichat',
        description: 'WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports',
        unit: 'month',
        status: 'active',
        price_plans: [
          {
            name: 'Trial Plan',
            subscription_type: 'monthly',
            amount: 0,
            currency: 'TZS',
            rate: 3
          },
          {
            name: 'Starter Plan',
            subscription_type: 'monthly',
            amount: 69000,
            currency: 'TZS',
            rate: 30
          },
          {
            name: 'Pro Plan',
            subscription_type: 'monthly',
            amount: 149000,
            currency: 'TZS',
            rate: 30
          },
          {
            name: 'Premium Plan',
            subscription_type: 'monthly',
            amount: 299000,
            currency: 'TZS',
            rate: 30
          }
        ]
      },
      {
        headers: {
          'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );

    console.log('Product created:', response.data);
    return response.data;
  } catch (error) {
    console.error('Error creating product:', error.response?.data || error.message);
  }
};

createProduct();
```

**Advanced Example - Product with Multiple Pricing Tiers:**
```json
{
  "product_type_id": 2,
  "name": "SafariChat Platform",
  "product_code": "safarichat",
  "description": "WhatsApp business messaging platform with AI-powered features",
  "unit": "month",
  "status": "active",
  "price_plans": [
    {
      "name": "Trial Plan",
      "subscription_type": "monthly",
      "amount": 0,
      "currency": "TZS",
      "rate": 3
    },
    {
      "name": "Starter Plan",
      "subscription_type": "monthly",
      "amount": 69000,
      "currency": "TZS",
      "rate": 30
    },
    {
      "name": "Pro Plan",
      "subscription_type": "monthly",
      "amount": 149000,
      "currency": "TZS",
      "rate": 30
    },
    {
      "name": "Premium Plan",
      "subscription_type": "monthly",
      "amount": 299000,
      "currency": "TZS",
      "rate": 30
    }
  ]
}
```

**cURL Example for Multi-Tier Product:**
```bash
curl -X POST "https://yourdomain.com/api/v1/products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_type_id": 2,
    "name": "SafariChat Platform",
    "product_code": "safarichat",
    "description": "WhatsApp business messaging platform with AI-powered features",
    "unit": "month",
    "status": "active",
    "price_plans": [
      {
        "name": "Trial Plan",
        "subscription_type": "monthly",
        "amount": 0,
        "currency": "TZS",
        "rate": 3
      },
      {
        "name": "Starter Plan",
        "subscription_type": "monthly",
        "amount": 69000,
        "currency": "TZS",
        "rate": 30
      },
      {
        "name": "Pro Plan",
        "subscription_type": "monthly",
        "amount": 149000,
        "currency": "TZS",
        "rate": 30
      },
      {
        "name": "Premium Plan",
        "subscription_type": "monthly",
        "amount": 299000,
        "currency": "TZS",
        "rate": 30
      }
    ]
  }'
```

**Usage-Based Product Example:**
```json
{
  "product_type_id": 3,
  "name": "API Credits",
  "product_code": "api-credits",
  "description": "Pay-per-use API call credits",
  "unit": "API call",
  "status": "active",
  "price_plans": [
    {
      "name": "Standard Rate",
      "amount": 50,
      "currency": "TZS",
      "rate": 1
    }
  ]
}
```

**cURL Example for Usage-Based Product:**
```bash
curl -X POST "https://yourdomain.com/api/v1/products" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_type_id": 3,
    "name": "API Credits",
    "product_code": "api-credits",
    "description": "Pay-per-use API call credits",
    "unit": "API call",
    "status": "active",
    "price_plans": [
      {
        "name": "Standard Rate",
        "amount": 50,
        "currency": "TZS",
        "rate": 1
      }
    ]
  }'
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 25,
    "organization_id": 1,
    "product_type_id": 2,
    "name": "Premium Hosting Plan",
    "product_code": "premium-hosting",
    "description": "Monthly recurring hosting with 100GB storage and unlimited bandwidth",
    "unit": "month",
    "status": "active",
    "created_at": "2026-03-11T10:30:00.000000Z",
    "updated_at": "2026-03-11T10:30:00.000000Z",
    "product_type": {
      "id": 2,
      "name": "Subscription Product"
    },
    "price_plans": [
      {
        "id": 45,
        "product_id": 25,
        "name": "Monthly Plan",
        "subscription_type": "monthly",
        "amount": 75000,
        "currency": "TZS",
        "rate": 30
      }
    ]
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "product_type_id": [
      "The product type id field is required.",
      "The selected product type id is invalid."
    ],
    "name": [
      "The name field is required."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

**Integration Tips:**

1. **Replace YOUR_ACCESS_TOKEN** with your actual token obtained from the OAuth client credentials flow
2. **Replace yourdomain.com** with your actual API base URL
3. **Test in development** before deploying to production
4. **Handle errors gracefully** - Always check response status codes
5. **Store tokens securely** - Never hardcode tokens in your source code
6. **Use environment variables** for sensitive data

**Common Mistakes to Avoid:**

- ❌ Forgetting the `Bearer` prefix in Authorization header
- ❌ Missing `Content-Type: application/json` header
- ❌ Sending `product_type_id` as string instead of integer
- ❌ Forgetting to include `subscription_type` for subscription products (type 2)
- ❌ Using invalid subscription_type values (must be: daily, weekly, monthly, quarterly, semi_annually, yearly)
- ❌ Duplicate `name` or `product_code` within the same organization

### Delete Products
**Method:** `DELETE`
**URL:** `/api/v1/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Single Product
**Method:** `GET`
**URL:** `/api/v1/products/{product}`

**Description:** Retrieve a single product by its ID or product code. The response includes the product details, organization, product type, and all associated price plans.

**URL Parameters:**
- `{product}` - **Required**. Can be either:
  - Product ID (integer): e.g., `/api/v1/products/1`
  - Product Code (string): e.g., `/api/v1/products/safarichat`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
No request body required for GET request
```

**How to Use This Endpoint:**

This endpoint retrieves a **specific product** from your organization. You must specify which product you want by including either its **ID** or **product_code** in the URL path (not in the request body).

**Option 1: Get Product by ID**
- Replace `{product}` with the numeric product ID
- Example: `/api/v1/products/1`
- Use this when you know the database ID

**Option 2: Get Product by Code** 
- Replace `{product}` with the product_code string
- Example: `/api/v1/products/safarichat`
- Use this for friendly, memorable identifiers

**Example Requests:**

**Using Product ID:**
```bash
# Get product with ID = 1
curl -X GET "https://yourdomain.com/api/v1/products/1" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

**Using Product Code:**
```bash
# Get product with code = "safarichat"
curl -X GET "https://yourdomain.com/api/v1/products/safarichat" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

**PHP Example (Using Product Code):**
```php
<?php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://yourdomain.com',
    'headers' => [
        'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
        'Accept' => 'application/json'
    ]
]);

// Get product by code
$response = $client->get('/api/v1/products/safarichat');
$product = json_decode($response->getBody(), true);

echo "Product: " . $product['data']['name'] . "\n";
echo "Price Plans: " . count($product['data']['price_plans']) . "\n";
```

**JavaScript Example (Using Product ID):**
```javascript
const getProduct = async (productId) => {
  const response = await fetch(`https://yourdomain.com/api/v1/products/${productId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer YOUR_ACCESS_TOKEN',
      'Accept': 'application/json'
    }
  });

  const data = await response.json();
  console.log('Product:', data.data.name);
  console.log('Price Plans:', data.data.price_plans.length);
  return data;
};

// Get product with ID 1
getProduct(1);
```

**Python Example (Using Product Code):**
```python
import requests

url = "https://yourdomain.com/api/v1/products/safarichat"
headers = {
    "Authorization": "Bearer YOUR_ACCESS_TOKEN",
    "Accept": "application/json"
}

response = requests.get(url, headers=headers)
data = response.json()

if response.status_code == 200:
    print(f"Product: {data['data']['name']}")
    print(f"Price Plans: {len(data['data']['price_plans'])}")
else:
    print(f"Error: {data}")
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "organization_id": 1,
    "product_type_id": 2,
    "name": "SafariChat Platform",
    "product_code": "safarichat",
    "description": "WhatsApp business messaging platform with AI-powered features, booking calendars, and sales reports",
    "unit": "month",
    "active": true,
    "status": "active",
    "created_at": "2026-03-14T10:30:00.000000Z",
    "updated_at": "2026-03-14T10:30:00.000000Z",
    "organization": {
      "id": 1,
      "name": "Your Organization",
      "email": "your-org@example.com",
      "phone": "+255123456789",
      "address": "Dar es Salaam, Tanzania",
      "created_at": "2026-01-01T08:00:00.000000Z",
      "updated_at": "2026-01-01T08:00:00.000000Z"
    },
    "product_type": {
      "id": 2,
      "name": "Subscription Product",
      "description": "Recurring billing products",
      "created_at": "2026-01-01T08:00:00.000000Z",
      "updated_at": "2026-01-01T08:00:00.000000Z"
    },
    "price_plans": [
      {
        "id": 1,
        "product_id": 1,
        "name": "Trial Plan",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "0.00",
        "currency_id": 1,
        "currency": "TZS",
        "rate": 3,
        "subscription_type": "monthly",
        "plan_code": null,
        "feature_code": null,
        "trial_period_days": 3,
        "setup_fee": "0.00",
        "metadata": null,
        "created_at": "2026-03-14T10:30:00.000000Z",
        "updated_at": "2026-03-14T10:30:00.000000Z"
      },
      {
        "id": 2,
        "product_id": 1,
        "name": "Starter Plan",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "69000.00",
        "currency_id": 1,
        "currency": "TZS",
        "rate": 30,
        "subscription_type": "monthly",
        "plan_code": null,
        "feature_code": null,
        "trial_period_days": 0,
        "setup_fee": "0.00",
        "metadata": null,
        "created_at": "2026-03-14T10:30:00.000000Z",
        "updated_at": "2026-03-14T10:30:00.000000Z"
      },
      {
        "id": 3,
        "product_id": 1,
        "name": "Pro Plan",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "149000.00",
        "currency_id": 1,
        "currency": "TZS",
        "rate": 30,
        "subscription_type": "monthly",
        "plan_code": null,
        "feature_code": null,
        "trial_period_days": 0,
        "setup_fee": "0.00",
        "metadata": null,
        "created_at": "2026-03-14T10:30:00.000000Z",
        "updated_at": "2026-03-14T10:30:00.000000Z"
      },
      {
        "id": 4,
        "product_id": 1,
        "name": "Premium Plan",
        "billing_type": "recurring",
        "billing_interval": "monthly",
        "amount": "299000.00",
        "currency_id": 1,
        "currency": "TZS",
        "rate": 30,
        "subscription_type": "monthly",
        "plan_code": null,
        "feature_code": null,
        "trial_period_days": 0,
        "setup_fee": "0.00",
        "metadata": null,
        "created_at": "2026-03-14T10:30:00.000000Z",
        "updated_at": "2026-03-14T10:30:00.000000Z"
      }
    ]
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Product
**Method:** `PUT`
**URL:** `/api/v1/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "product_type_id": "sample",
  "name": "sample",
  "description": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "product_type_id": [
      "The product type id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### List Product Price-Plans
**Method:** `GET`
**URL:** `/api/v1/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Product Price-Plans
**Method:** `POST`
**URL:** `/api/v1/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "subscription_type": "sample",
  "amount": "sample",
  "currency": "sample",
  "rate": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "subscription_type": [
      "The subscription type field is invalid."
    ],
    "amount": [
      "The amount field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Product Price-Plans
**Method:** `DELETE`
**URL:** `/api/v1/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Price-Plans
**Method:** `GET`
**URL:** `/api/v1/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Product Price-Plans
**Method:** `PUT`
**URL:** `/api/v1/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "subscription_type": "sample",
  "amount": "sample",
  "currency": "sample",
  "rate": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "subscription_type": [
      "The subscription type field is invalid."
    ],
    "amount": [
      "The amount field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Invoices

### List All Invoices
**Method:** `GET`
**URL:** `/api/v1/invoices`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id|product_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Either organization_id or product_id must be provided"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Invoice Types Overview

The billing system supports three types of invoices, automatically determined by the product type:

| Invoice Type | Product Type | Billing Pattern | Use Case |
|-------------|--------------|-----------------|----------|
| **One-Time** | One-time Product (product_type_id: 1) | Single charge | One-off services, consulting, project work |
| **Subscription** | Subscription Product (product_type_id: 2) | Recurring charges | SaaS, memberships, monthly/yearly plans |
| **Usage-Based** | Usage Product (product_type_id: 3) | Pay-per-use | API calls, storage, bandwidth, credits |

**Important:** The invoice type is automatically determined by the product type associated with the price plan. You don't need to explicitly specify the invoice type.

---

### Create One-Time Invoice
**Method:** `POST`
**URL:** `/api/v1/invoices`

**Description:** One-time invoices are for products that are charged once without creating a subscription. Perfect for consulting services, one-off projects, or standalone purchases.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Required Parameters:**
- `organization_id` (integer) - Your organization ID
- `customer` (object) - Customer information
- `customer.name` (string) - Customer's full name
- `customer.email` (string) - Customer's email address
- `customer.phone` (string) - Customer's phone number
- `products` (array) - Array of products (minimum 1)
- `products.*.price_plan_id` (integer) - Price plan ID for a one-time product
- `products.*.amount` (number) - Invoice amount for this product
- `currency` (string) - 3-letter currency code (e.g., "TZS", "USD")

**Optional Parameters:**
- `tax_rate_ids` (array) - Array of tax rate IDs to apply
- `description` (string) - Invoice description
- `status` (string) - Invoice status: draft, issued, paid, cancelled (default: "issued")
- `date` (string) - Invoice date in Y-m-d format (default: current date)
- `due_date` (string) - Payment due date in Y-m-d format
- `payment_gateway` (string) - flutterwave, control_number, or both
- `success_url` (string) - Redirect URL after successful payment (required for Flutterwave)
- `cancel_url` (string) - Redirect URL after cancelled payment (required for Flutterwave)

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255712345678"
  },
  "products": [
    {
      "price_plan_id": 5,
      "amount": 50000
    }
  ],
  "description": "Website development project",
  "currency": "TZS",
  "status": "issued",
  "date": "2026-02-26",
  "due_date": "2026-03-26"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV-2026-00123",
      "customer_id": 45,
      "customer_name": "John Doe",
      "customer_email": "john@example.com",
      "currency": "TZS",
      "status": "issued",
      "description": "Website development project",
      "subtotal": 50000,
      "tax_total": 0,
      "total": 50000,
      "date": "2026-02-26",
      "due_date": "2026-03-26",
      "issued_at": "2026-02-26T10:30:00.000000Z",
      "items": [
        {
          "id": 456,
          "price_plan_id": 5,
          "product_name": "Website Development",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        }
      ],
      "taxes": [],
      "payments": []
    }
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "organization_id": ["The organization id field is required."],
    "customer.email": ["The customer email must be a valid email address."],
    "products": ["The products field must have at least 1 items."],
    "currency": ["The currency must be 3 characters."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Create Multi-Product Invoice
**Method:** `POST`
**URL:** `/api/v1/invoices`

**Description:** Create a single invoice with multiple products of different types (one-time and subscription products can be combined).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 3,
      "amount": 100000
    },
    {
      "price_plan_id": 5,
      "amount": 50000
    },
    {
      "price_plan_id": 8,
      "amount": 25000
    }
  ],
  "tax_rate_ids": [1, 2],
  "description": "Bundle: Hosting + Domain + SSL",
  "currency": "TZS",
  "status": "issued"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 126,
      "invoice_number": "INV-2026-00126",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Bundle: Hosting + Domain + SSL",
      "subtotal": 175000,
      "tax_total": 31500,
      "total": 206500,
      "issued_at": "2026-02-26T13:00:00.000000Z",
      "items": [
        {
          "id": 459,
          "price_plan_id": 3,
          "subscription_id": 90,
          "product_name": "Premium Hosting",
          "product_type": "Subscription Product",
          "quantity": 1,
          "unit_price": 100000,
          "total": 100000
        },
        {
          "id": 460,
          "price_plan_id": 5,
          "subscription_id": null,
          "product_name": "Domain Registration",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        },
        {
          "id": 461,
          "price_plan_id": 8,
          "subscription_id": null,
          "product_name": "SSL Certificate",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 25000,
          "total": 25000
        }
      ],
      "taxes": [
        {
          "tax_rate_id": 1,
          "name": "VAT",
          "percentage": 15,
          "amount": 26250
        },
        {
          "tax_rate_id": 2,
          "name": "Service Tax",
          "percentage": 3,
          "amount": 5250
        }
      ],
      "subscriptions": [
        {
          "id": 90,
          "price_plan_id": 3,
          "status": "pending",
          "product_name": "Premium Hosting"
        }
      ]
    }
  }
}
```

**Notes:**
- When an invoice contains both one-time and subscription products, subscriptions are created only for subscription-type products
- One-time products are charged without creating a subscription
- Taxes are calculated on the subtotal of all products

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "tax_rate_ids.0": ["The selected tax rate id is invalid."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Create Invoice with Payment Gateway
**Method:** `POST`
**URL:** `/api/v1/invoices`

**Description:** Generate payment links automatically when creating invoices. Supports Flutterwave (card/mobile money) and EcoBank control numbers (bank payments).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Payment Gateway Options:**

| Option | Description | Required Parameters |
|--------|-------------|---------------------|
| `flutterwave` | Card, mobile money, and bank transfer | success_url, cancel_url |
| `control_number` | EcoBank control number for bank payments | None |
| `both` | Both Flutterwave link AND control number | success_url, cancel_url |

**Request Body (Flutterwave):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Sarah Lee",
    "email": "sarah@business.com",
    "phone": "+255756789012"
  },
  "products": [
    {
      "price_plan_id": 7,
      "amount": 120000
    }
  ],
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "description": "Premium hosting package",
  "currency": "TZS"
}
```

**Success Response (Flutterwave):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with Flutterwave payment link",
  "data": {
    "invoice": {
      "id": 127,
      "invoice_number": "INV-2026-00127",
      "total": 120000,
      "status": "issued",
      "customer_email": "sarah@business.com"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789",
        "tx_ref": "INV-2026-00127-1708960000",
        "expires_at": "2026-03-05T14:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer",
        "supported_methods": ["card", "mobile_money", "bank_transfer"]
      }
    },
    "redirect_url": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789"
  }
}
```

**Request Body (Control Number):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Michael Brown",
    "email": "michael@enterprise.com",
    "phone": "+255767890123"
  },
  "products": [
    {
      "product_code": "CLOUD-SERVER-M",
      "amount": 500000
    }
  ],
  "payment_gateway": "control_number",
  "description": "Cloud server subscription",
  "currency": "TZS"
}
```

**Success Response (Control Number):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with control number",
  "data": {
    "invoice": {
      "id": 128,
      "invoice_number": "INV-2026-00128",
      "total": 500000,
      "status": "issued",
      "customer_email": "michael@enterprise.com"
    },
    "payment_details": {
      "control_number": {
        "reference": "9912345678",
        "amount": 500000,
        "currency": "TZS",
        "expires_at": "2026-03-12T14:30:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number: 9912345678",
          "agent_banking": "Visit any bank agent and provide the control number: 9912345678",
          "atm": "Use ATM bill payment option with control number: 9912345678"
        }
      }
    }
  }
}
```

**Request Body (Both Gateways):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "David Chen",
    "email": "david@corp.com",
    "phone": "+255778901234"
  },
  "products": [
    {
      "price_plan_id": 10,
      "amount": 250000
    }
  ],
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "currency": "TZS"
}
```

**Success Response (Both):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with multiple payment options",
  "data": {
    "invoice": {
      "id": 129,
      "invoice_number": "INV-2026-00129",
      "total": 250000,
      "status": "issued"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/xyz789abc",
        "tx_ref": "INV-2026-00129-1708961000",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      },
      "control_number": {
        "reference": "9912345679",
        "amount": 250000,
        "currency": "TZS",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345679# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number",
          "agent_banking": "Visit any bank agent and provide the control number"
        }
      }
    },
    "urls": {
      "success_url": "https://yourapp.com/payment/success",
      "cancel_url": "https://yourapp.com/payment/cancel"
    }
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "success_url": ["The success url field is required when payment gateway is flutterwave."],
    "cancel_url": ["The cancel url field is required when payment gateway is flutterwave."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Product Lookup Methods

You can specify products using three different lookup methods:

| Method | Parameter | When to Use | Example |
|--------|-----------|-------------|---------|
| **Price Plan ID** | `price_plan_id` | Most specific - when you know the exact plan | `"price_plan_id": 5` |
| **Product Code** | `product_code` | User-friendly - use readable codes | `"product_code": "HOSTING-BASIC"` |
| **Product ID** | `product_id` | Simple product reference | `"product_id": 12` |

**Important:** Each product must have EXACTLY ONE identifier (price_plan_id, product_code, or product_id). Using multiple identifiers will result in a validation error.

**Example using Product Code:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Bob Wilson",
    "email": "bob@startup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "product_code": "HOSTING-BASIC",
      "amount": 50000
    },
    {
      "product_code": "DOMAIN-COM",
      "amount": 15000
    }
  ],
  "currency": "TZS"
}
```

---

### Complete Parameter Reference

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `organization_id` | integer | **Required** | Your organization ID |
| `customer` | object | **Required** | Customer information object |
| `customer.name` | string | **Required** | Customer's full name |
| `customer.email` | string (email) | **Required** | Customer's email address |
| `customer.phone` | string | **Required** | Customer's phone number |
| `products` | array | **Required** | Array of products (minimum 1) |
| `products.*.price_plan_id` | integer | Conditional | Price plan ID (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.product_code` | string | Conditional | Product code (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.product_id` | integer | Conditional | Product ID (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.amount` | number | **Required** | Invoice amount for this product (minimum: 0) |
| `currency` | string (3 chars) | **Required** | 3-letter currency code (e.g., "TZS", "USD", "EUR") |
| `tax_rate_ids` | array | Optional | Array of tax rate IDs to apply to invoice |
| `description` | string | Optional | Invoice description or notes |
| `status` | string | Optional | Invoice status: draft, issued, paid, cancelled (default: "issued") |
| `date` | string (date) | Optional | Invoice date in Y-m-d format (default: current date) |
| `due_date` | string (date) | Optional | Payment due date in Y-m-d format |
| `payment_gateway` | string | Optional | Payment gateway: "flutterwave", "control_number", or "both" |
| `success_url` | string (URL) | Conditional | Required if using Flutterwave - redirect URL after successful payment |
| `cancel_url` | string (URL) | Conditional | Required if using Flutterwave - redirect URL after cancelled payment |

### Get Invoices by Subscriptions
**Method:** `POST`
**URL:** `/api/v1/invoices/by-subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "subscription_ids": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "subscription_ids": [
      "The subscription ids field is invalid."
    ],
    "subscription_ids.*": [
      "The subscription ids 0 field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Cancel Invoices
**Method:** `POST`
**URL:** `/api/v1/invoices/{id}/cancel`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Invoice cancelled successfully",
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Invoice not found"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Invoice is already cancelled"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Invoices by id
**Method:** `GET`
**URL:** `/api/v1/invoices/{invoice_id}`

**Description:** Retrieve detailed information about **ONE SPECIFIC INVOICE** by its ID. This endpoint returns a single invoice, not a list of all invoices.

**How it works:**
- Replace `{invoice_id}` in the URL with the actual invoice ID number
- Example: To get invoice with ID 123, use `/api/invoices/123`
- Example: To get invoice with ID 456, use `/api/invoices/456`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**URL Path Parameters (NOT request body):**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| invoice_id | integer | Yes | The unique ID of the invoice you want to retrieve. This is part of the URL path. |

**Example Request 1: Get invoice with ID 123**
```bash
GET /api/invoices/123
Authorization: Bearer org_live_9Z8Y7X6W5V4U3T2S1R0Q9P8O7N6M5L4K3J2I1H0G
Accept: application/json
```

**Example Request 2: Get invoice with ID 456**
```bash
GET /api/invoices/456
Authorization: Bearer org_live_9Z8Y7X6W5V4U3T2S1R0Q9P8O7N6M5L4K3J2I1H0G
Accept: application/json
```

**cURL Example:**
```bash
curl -X GET "https://your-api-domain.com/api/invoices/123" \
  -H "Authorization: Bearer org_live_9Z8Y7X6W5V4U3T2S1R0Q9P8O7N6M5L4K3J2I1H0G" \
  -H "Accept: application/json"
```

**Request Body:**
```json
// NO REQUEST BODY - The invoice ID is in the URL path, not the body
```

**Important Notes:**
- ✅ This endpoint returns **ONE invoice** only
- ✅ The invoice ID must be in the **URL path** (e.g., `/api/invoices/123`)
- ✅ You do NOT send the invoice ID in the request body
- ✅ You do NOT need any request body at all
- ❌ This does NOT return a list of all invoices

**Quick Comparison:**
| What you want | Endpoint | What it returns |
|---------------|----------|-----------------|
| Get ONE specific invoice | `GET /api/invoices/123` | Single invoice with ID 123 |
| Get ALL invoices | `GET /api/invoices` | List of all invoices |

**Success Response:** `200 OK`

**Returns:** Data for the **single invoice** you requested (invoice ID 123 in this example)

```json
{
  "success": true,
  "message": "Invoice details retrieved successfully",
  "data": {
    "id": 123,
    "invoice_number": "INV-2026-00123",
    "status": "pending",
    "currency": "TZS",
    "description": "Monthly subscription - March 2026",
    "subtotal": 50000.00,
    "tax_breakdown": [
      {
        "invoice_tax_id": 45,
        "tax_rate_id": 1,
        "name": "VAT",
        "country": "TZ",
        "rate": 18.00,
        "amount": 9000.00
      }
    ],
    "tax_total": 9000.00,
    "grand_total": 59000.00,
    "invoiced_amount": 59000.00,
    "paid_amount": 0.00,
    "outstanding_amount": 59000.00,
    "date": "2026-03-01",
    "due_date": "2026-03-15",
    "issued_at": "2026-03-01T10:30:00.000000Z",
    "created_at": "2026-03-01T10:30:00.000000Z",
    "updated_at": "2026-03-01T10:30:00.000000Z",
    "customer": {
      "id": 456,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone": "+255712345678",
      "organization_id": 1
    },
    "price_plans": [
      {
        "id": 10,
        "name": "Premium Monthly Plan",
        "subscription_type": "recurring",
        "quantity": 1,
        "unit_price": 50000.00,
        "amount": 50000.00,
        "product_id": 5,
        "product_name": "Premium Membership",
        "payment_gateways": [
          {
            "id": 3,
            "payment_gateway_id": 2,
            "gateway_name": "Stripe",
            "status": "active",
            "references": "pi_3AbCdEfGhIjKlMnO",
            "client_secret": "pi_3AbCdEfGhIjKlMnO_secret_XyZ123"
          },
          {
            "id": 4,
            "payment_gateway_id": 1,
            "gateway_name": "Flutterwave",
            "status": "active",
            "references": "FLW-123456789"
          }
        ]
      }
    ],
    "subscriptions": [
      {
        "id": 789,
        "product_id": 5,
        "product_name": "Premium Membership",
        "price_plan_id": 10,
        "price_plan_name": "Premium Monthly Plan",
        "subscription_type": "recurring",
        "customer_id": 456,
        "status": "active",
        "start_date": "2026-03-01",
        "end_date": "2026-04-01",
        "next_billing_date": "2026-04-01",
        "created_at": "2026-03-01T10:30:00.000000Z",
        "updated_at": "2026-03-01T10:30:00.000000Z"
      }
    ]
  }
}
```

**Response Fields Explanation:**
- **invoice_number**: Unique invoice identifier for display purposes
- **status**: Current invoice status (`pending`, `paid`, `cancelled`, `overdue`)
- **subtotal**: Total amount before taxes
- **tax_breakdown**: Array of all taxes applied to this invoice
- **grand_total**: Final total amount including all taxes
- **paid_amount**: Total amount already paid
- **outstanding_amount**: Remaining balance to be paid
- **customer**: Complete customer information
- **price_plans**: Array of products/services with quantities and prices
- **payment_gateways**: Available payment methods for this invoice with references
- **subscriptions**: Related subscription details if invoice is for recurring billing

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Invoice not found"
}
```

`500 Internal Server Error`
```json
{
  "success": false,
  "message": "Invoice detail retrieval failed: [error details]"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```


### Get Invoices by product_id
**Method:** `GET`
**URL:** `/api/v1/invoices/{product_id}/product`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "product_id": [
      "The product id field is required."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Invoice Payment Gateways
**Method:** `GET`
**URL:** `/api/v1/invoices/{id}/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Invoice payment gateways retrieved successfully",
  "data": {
    "invoice_id": 1,
    "invoice_number": "INV-000001",
    "price_plans": []
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Invoice not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Subscriptions

### Create Subscription Invoice
**Method:** `POST`
**URL:** `/api/v1/invoices`

**Description:** Subscription invoices automatically create a subscription record for recurring billing. The subscription remains in "pending" status until the invoice is paid, then becomes "active".

**🔄 Idempotent Behavior:**
- If you call this endpoint again with the **same customer and same price plans** while a **pending invoice already exists**, the system will return the existing invoice instead of creating a duplicate or throwing an error.
- This prevents duplicate subscriptions and allows safe retry of invoice creation.
- The response format is identical whether returning an existing invoice or creating a new one.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Required Parameters:**
- `organization_id` (integer) - Your organization ID
- `customer` (object) - Customer information
- `customer.name` (string) - Customer's full name
- `customer.email` (string) - Customer's email address
- `customer.phone` (string) - Customer's phone number
- `products` (array) - Array of products with subscription price plans
- `products.*.price_plan_id` (integer) - Price plan ID for a subscription product
- `products.*.amount` (number) - Invoice amount for this product
- `currency` (string) - 3-letter currency code

**Optional Parameters:**
- `tax_rate_ids` (array) - Array of tax rate IDs to apply
- `description` (string) - Invoice description
- `status` (string) - Invoice status (default: "issued")
- `payment_gateway` (string) - flutterwave, control_number, or both
- `success_url` (string) - Redirect URL after successful payment
- `cancel_url` (string) - Redirect URL after cancelled payment

**Request Body:**
```json
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
  "description": "Premium hosting - Monthly subscription",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Premium hosting - Monthly subscription",
      "subtotal": 75000,
      "tax_total": 0,
      "total": 75000,
      "due_date": null,
      "issued_at": "2026-02-26T11:15:00.000000Z",
      "items": [
        {
          "id": 457,
          "price_plan_id": 8,
          "subscription_id": 89,
          "product_name": "Premium Hosting Plan",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        }
      ],
      "subscription": {
        "id": 89,
        "status": "pending",
        "price_plan_id": 8,
        "start_date": null,
        "next_billing_date": null,
        "note": "Subscription will activate upon payment"
      },
      "payment_details": {
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1708956234",
          "expires_at": "2026-03-05T11:15:00.000000Z"
        }
      }
    }
  }
}
```

**Notes:**
- The subscription is created in "pending" status
- It will automatically activate when the invoice is paid
- Next billing date is calculated based on the price plan's billing interval
- **🔄 Idempotent Behavior:** If a pending subscription already exists for the same customer and price plan, the existing invoice is returned with `200 OK` status (instead of `201 Created`)

**Example - Creating Duplicate Subscription Invoice:**

If you send the same request twice:
```bash
# First Request - Creates new invoice
POST /api/invoices
{
  "customer": {"email": "jane@company.com", ...},
  "products": [{"price_plan_id": 8, "amount": 75000}],
  ...
}
# Response: 201 Created with invoice_id: 124

# Second Request - Same customer, same plan, invoice still pending
POST /api/invoices
{
  "customer": {"email": "jane@company.com", ...},
  "products": [{"price_plan_id": 8, "amount": 75000}],
  ...
}
# Response: 200 OK with SAME invoice_id: 124 (not a new invoice!)
```

The second request returns the existing invoice rather than creating a duplicate. This ensures:
- ✅ Safe retry logic in case of network failures
- ✅ No duplicate subscriptions
- ✅ Same payment link can be reused
- ✅ Idempotent API behavior

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "products.0.price_plan_id": ["The selected price plan id is invalid."],
    "payment_gateway": ["The payment gateway must be one of: flutterwave, control_number, both."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```
---

### List Subscriptions

**Description:** Returns subscriptions for a specific customer identified by their email address. This endpoint requires customer email for security and privacy protection.

**Method:** `GET`  
**URL:** `/api/v1/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Query Parameters (sent in URL):**
| Parameter | Type | Required | Description | Possible Values |
|-----------|------|----------|-------------|-----------------|
| customer_email | string | **YES** | Customer's email address (REQUIRED for security) | Valid email format (e.g., `jane@company.com`) |
| status | string | No | Filter by subscription status | `pending`, `active`, `cancelled`, `expired` |

**Request Body:**
```
NO REQUEST BODY - This is a GET request. All parameters are sent as query strings in the URL.
```

**Complete Request Examples:**

**Example 1: Get ALL subscriptions for a customer**
```http
GET /api/subscriptions?customer_email=jane@company.com
Authorization: Bearer org_live_sk_abc123xyz456
Accept: application/json
```
Query Parameters Sent:
```json
{
  "customer_email": "jane@company.com"
}
```

**Example 2: Get only ACTIVE subscriptions for a customer**
```http
GET /api/subscriptions?customer_email=jane@company.com&status=active
Authorization: Bearer org_live_sk_abc123xyz456
Accept: application/json
```
Query Parameters Sent:
```json
{
  "customer_email": "jane@company.com",
  "status": "active"
}
```

**Example 3: Get only PENDING subscriptions for a customer**
```http
GET /api/subscriptions?customer_email=john@startup.io&status=pending
Authorization: Bearer org_live_sk_abc123xyz456
Accept: application/json
```
Query Parameters Sent:
```json
{
  "customer_email": "john@startup.io",
  "status": "pending"
}
```

**cURL Examples:**
```bash
# Get all subscriptions for a customer
curl -X GET "https://your-domain.com/api/subscriptions?customer_email=jane@company.com" \
  -H "Authorization: Bearer org_live_sk_abc123xyz456" \
  -H "Accept: application/json"

# Get active subscriptions for a customer
curl -X GET "https://your-domain.com/api/subscriptions?customer_email=jane@company.com&status=active" \
  -H "Authorization: Bearer org_live_sk_abc123xyz456" \
  -H "Accept: application/json"

# Get pending subscriptions for a customer
curl -X GET "https://your-domain.com/api/subscriptions?customer_email=john@startup.io&status=pending" \
  -H "Authorization: Bearer org_live_sk_abc123xyz456" \
  -H "Accept: application/json"
```

**Important Security Notes:**
- 🔒 **customer_email is REQUIRED** - You cannot list all subscriptions without specifying a customer
- ✅ **Privacy Protection** - This prevents exposing all customers' subscriptions
- ✅ **Email Validation** - Must be a valid email format
- ✅ Query parameters are appended to URL with `?` and separated by `&`

**Success Response:** `200 OK`

**Example: Customer with multiple subscriptions**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer": {
        "id": 45,
        "name": "Jane Smith",
        "email": "jane@company.com"
      },
      "price_plan": {
        "id": 8,
        "name": "Premium hosting - Monthly subscription",
        "amount": 75000,
        "billing_interval": "monthly",
        "product": {
          "id": 3,
          "name": "Cloud Hosting Premium"
        }
      },
      "status": "active",
      "start_date": "2024-01-15",
      "end_date": null,
      "next_billing_date": "2024-04-15",
      "created_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 3,
      "customer": {
        "id": 45,
        "name": "Jane Smith",
        "email": "jane@company.com"
      },
      "price_plan": {
        "id": 15,
        "name": "Basic Plan - Monthly",
        "amount": 25000,
        "billing_interval": "monthly",
        "product": {
          "id": 2,
          "name": "Email Service"
        }
      },
      "status": "cancelled",
      "start_date": "2024-01-01",
      "end_date": "2024-03-01",
      "next_billing_date": null,
      "created_at": "2024-01-01T08:00:00.000000Z"
    }
  ]
}
```

**Response Fields Explanation:**
- `id` - Unique subscription identifier
- `customer` - Customer details (id, name, email) - **All subscriptions belong to the same customer**
- `price_plan` - Subscription plan information
  - `amount` - Price in smallest currency unit (e.g., cents for USD, kobo for NGN)
  - `billing_interval` - How often billing occurs: `monthly`, `yearly`, `quarterly`, etc.
  - `product` - The product/service being subscribed to
- `status` - Subscription state:
  - `pending` - Created but payment not completed (start_date is null)
  - `active` - Currently active and paid
  - `cancelled` - Manually cancelled
  - `expired` - Ended naturally
- `start_date` - When subscription became active (null if pending)
- `end_date` - When subscription ended (null if ongoing)
- `next_billing_date` - Next payment date (null if pending/cancelled/expired)
- `created_at` - When subscription was created

**Empty Result Example (Customer has no subscriptions):**
```json
{
  "success": true,
  "data": []
}
```

**Error Responses:**

`422 Unprocessable Entity` (Missing or invalid customer_email)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "customer_email": [
      "The customer email field is required."
    ]
  }
}
```

**Or (Invalid email format):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "customer_email": [
      "The customer email must be a valid email address."
    ]
  }
}
```

`400 Bad Request`
```json
{
  "success": false,
  "message": "Invalid filter parameters"
}
```

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Cancel Subscriptions
**Method:** `POST`
**URL:** `/api/v1/subscriptions/{id}/cancel`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Upgrade Subscription

**Description:** Upgrade an active subscription to a higher-tier plan within the same product. The system automatically calculates prorated charges based on actual billing cycle days, ensuring fair pricing across months with different lengths (28-31 days).

**Method:** `POST`  
**URL:** `/api/v1/invoices/plan-upgrade`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| subscription_id | integer | **YES** | ID of the active subscription to upgrade |
| new_price_plan_id | integer | **YES** | ID of the higher-tier price plan (must be from same product) |
| payment_gateway | string | **Optional** | Gateway for payment: `flutterwave`, `control_number`, or `both` (default: `both`) |
| success_url | string (URL) | **Optional** | Redirect URL after successful payment |
| cancel_url | string (URL) | **Optional** | Redirect URL if payment is cancelled |

**Business Rules:**
- ✅ Subscription must be in `active` status
- ✅ New plan must have **higher price** than current plan
- ✅ New plan must belong to the **same product**
- ✅ Proration calculated using **actual billing cycle days** (not fixed 30 days)
- ✅ Creates upgrade invoice that must be paid
- ✅ Subscription plan switches immediately after payment

**Proration Formula:**
```
Billing Cycle Days = Days between current_period_start and current_period_end
Days Remaining = Billing Cycle Days - Days Used

Old Plan Daily Rate = Old Plan Price ÷ Billing Cycle Days
New Plan Daily Rate = New Plan Price ÷ Billing Cycle Days

Unused Credit = Old Plan Daily Rate × Days Remaining
New Plan Charge = New Plan Daily Rate × Days Remaining

Amount to Charge = New Plan Charge - Unused Credit
```

**Request Body:**
```json
{
  "subscription_id": 89,
  "new_price_plan_id": 15,
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/upgrade/success",
  "cancel_url": "https://yourapp.com/upgrade/cancel"
}
```

**Example Scenario:**
- Current Plan: Basic (TZS 30,000/month)
- New Plan: Standard (TZS 75,000/month)
- Billing Cycle: Jan 15 - Feb 15 (31 days)
- Upgrade Date: Jan 25 (10 days used, 21 days remaining)
- Calculation:
  - Old Daily Rate: 30,000 ÷ 31 = 967.74 TZS/day
  - New Daily Rate: 75,000 ÷ 31 = 2,419.35 TZS/day
  - Unused Credit: 967.74 × 21 = 20,322.54 TZS
  - New Plan Charge: 2,419.35 × 21 = 50,806.35 TZS
  - **Amount to Pay: 30,483.81 TZS**

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Subscription upgraded successfully",
  "data": {
    "invoice": {
      "id": 250,
      "invoice_number": "INV-2026-00250",
      "status": "issued",
      "currency": "TZS",
      "description": null,
      "subtotal": 30484,
      "tax_breakdown": [],
      "tax_total": 0,
      "grand_total": 30484,
      "invoiced_amount": 30484,
      "paid_amount": 0,
      "outstanding_amount": 30484,
      "date": null,
      "due_date": null,
      "issued_at": "2026-01-25T10:30:00.000000Z",
      "created_at": "2026-01-25T10:30:00.000000Z",
      "updated_at": "2026-01-25T10:30:00.000000Z",
      "customer": {
        "id": 45,
        "name": "Jane Smith",
        "email": "jane@company.com",
        "phone": "+255723456789",
        "organization_id": 1
      },
      "price_plans": [
        {
          "id": 15,
          "name": "Standard Plan",
          "subscription_type": null,
          "quantity": 1,
          "unit_price": 30484,
          "amount": 30484,
          "product_id": 3,
          "product_name": "Cloud Hosting Premium",
          "payment_gateways": [
            {
              "id": 5,
              "payment_gateway_id": 2,
              "gateway_name": "Flutterwave",
              "status": "active",
              "references": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789"
            },
            {
              "id": 8,
              "payment_gateway_id": 1,
              "gateway_name": "Universal Control Number",
              "status": "active",
              "references": "992001234567890"
            }
          ]
        }
      ],
      "subscriptions": [
        {
          "id": 89,
          "product_id": 3,
          "product_name": "Cloud Hosting Premium",
          "price_plan_id": 15,
          "price_plan_name": "Standard Plan",
          "subscription_type": null,
          "customer_id": 45,
          "status": "active",
          "start_date": "2026-01-15",
          "end_date": null,
          "next_billing_date": "2026-02-15",
          "created_at": "2026-01-15T08:00:00.000000Z",
          "updated_at": "2026-01-25T10:30:00.000000Z"
        }
      ]
    },
    "subscription": {
      "id": 89,
      "status": "active",
      "previous_plan_id": 8,
      "current_plan": {
        "id": 15,
        "name": "Standard Plan",
        "amount": 75000,
        "billing_interval": "monthly"
      },
      "next_billing_date": "2026-02-15"
    },
    "proration": {
      "amount_charged": 30484,
      "credit_applied": 20323,
      "description": "Prorated for remaining billing cycle"
    }
  }
}
```

**Payment Details Explanation:**
- `payment_gateways` array contains all available payment methods
- `references` field contains:
  - **Flutterwave**: Direct payment link URL (customer can click to pay)
  - **Universal Control Number**: Control number for bank payment (e.g., 992001234567890)
- Payment links are generated asynchronously and will be available within seconds
- You can poll the invoice endpoint to get updated payment details if needed

**Error Responses:**

`400 Bad Request` - Invalid upgrade attempt
```json
{
  "success": false,
  "message": "Failed to upgrade subscription: New plan must have a higher price than current plan. Use downgrade endpoint for lower-tier plans."
}
```

`400 Bad Request` - Different product
```json
{
  "success": false,
  "message": "Failed to upgrade subscription: Cannot upgrade to a plan from a different product"
}
```

`400 Bad Request` - Not active
```json
{
  "success": false,
  "message": "Failed to upgrade subscription: Only active subscriptions can be upgraded. Current status: pending"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Subscription or price plan not found"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subscription_id": ["The subscription id field is required."],
    "new_price_plan_id": ["The selected new price plan id is invalid."]
  }
}
```

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

---

### Downgrade Subscription

**Description:** Downgrade an active subscription to a lower-tier plan within the same product. The system calculates unused credit from the current plan and can apply it to the customer's wallet for future use.

**Method:** `POST`  
**URL:** `/api/v1/invoices/plan-downgrade`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| subscription_id | integer | **YES** | ID of the active subscription to downgrade |
| new_price_plan_id | integer | **YES** | ID of the lower-tier price plan (must be from same product) |
| apply_credit | boolean | No | Whether to apply unused credit to customer wallet (default: `true`) |

**Business Rules:**
- ✅ Subscription must be in `active` status
- ✅ New plan must have **lower price** than current plan
- ✅ New plan must belong to the **same product**
- ✅ Credit calculated using **actual billing cycle days**
- ✅ **No invoice created** - downgrade is immediate
- ✅ Credit can be applied to customer wallet or saved for next billing
- ✅ Subscription plan switches immediately

**Credit Formula:**
```
Billing Cycle Days = Days between current_period_start and current_period_end
Days Remaining = Billing Cycle Days - Days Used

Old Plan Daily Rate = Old Plan Price ÷ Billing Cycle Days
New Plan Daily Rate = New Plan Price ÷ Billing Cycle Days

Unused Value = Old Plan Daily Rate × Days Remaining
New Plan Cost = New Plan Daily Rate × Days Remaining

Credit Amount = Unused Value - New Plan Cost
```

**Request Body:**
```json
{
  "subscription_id": 89,
  "new_price_plan_id": 8,
  "apply_credit": true
}
```

**Example Scenario:**
- Current Plan: Standard (TZS 75,000/month)
- New Plan: Basic (TZS 30,000/month)
- Billing Cycle: Jan 15 - Feb 15 (31 days)
- Downgrade Date: Jan 25 (10 days used, 21 days remaining)
- Calculation:
  - Old Daily Rate: 75,000 ÷ 31 = 2,419.35 TZS/day
  - New Daily Rate: 30,000 ÷ 31 = 967.74 TZS/day
  - Unused Value: 2,419.35 × 21 = 50,806.35 TZS
  - New Plan Cost: 967.74 × 21 = 20,322.54 TZS
  - **Credit: 30,483.81 TZS** (available for future use)

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Subscription downgraded successfully",
  "data": {
    "subscription": {
      "id": 89,
      "status": "active",
      "previous_plan_id": 15,
      "current_plan": {
        "id": 8,
        "name": "Basic Plan",
        "amount": 30000,
        "billing_interval": "monthly"
      },
      "next_billing_date": "2026-02-15"
    },
    "credit": {
      "credit_amount": 30484,
      "credit_applied": true,
      "days_remaining": 21,
      "description": "Credit from unused portion of higher plan"
    },
    "payment_details": {
      "available_gateways": [
        {
          "id": 5,
          "payment_gateway_id": 2,
          "gateway_name": "Flutterwave",
          "status": "active"
        },
        {
          "id": 8,
          "payment_gateway_id": 1,
          "gateway_name": "Universal Control Number",
          "status": "active"
        }
      ],
      "note": "No payment required for downgrade. These payment methods will be available for your next billing cycle on 2026-02-15"
    }
  }
}
```

**Payment Details Explanation:**
- `payment_details` contains available payment gateways for this organization
- **No payment required** for downgrade - this is informational only
- Shows payment methods that will be available for the next billing cycle
- Credit can be applied to reduce future payments

**Error Responses:**

`400 Bad Request` - Invalid downgrade attempt
```json
{
  "success": false,
  "message": "Failed to downgrade subscription: New plan must have a lower price than current plan. Use upgrade endpoint for higher-tier plans."
}
```

`400 Bad Request` - Different product
```json
{
  "success": false,
  "message": "Failed to downgrade subscription: Cannot downgrade to a plan from a different product"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Subscription or price plan not found"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subscription_id": ["The subscription id field is required."]
  }
}
```

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

---

## Wallets

### Get All Wallets

**Description:** Retrieve all wallet products (product_type_id = 3) for your organization. Wallets are usage-based products that customers can top up and consume.

**Method:** `GET`
**URL:** `/api/v1/wallets`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Query Parameters (Optional):**
| Parameter | Type | Description |
|-----------|------|-------------|
| active | boolean | Filter by active status (true/false) |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Wallets retrieved successfully",
  "data": [
    {
      "id": 12,
      "organization_id": 1,
      "product_type_id": 3,
      "name": "ZAN Credits",
      "product_code": "ZAN-CREDITS",
      "description": "Prepaid Meta credits for ZAN messaging",
      "unit": "ZAN-CREDITS",
      "active": true,
      "created_at": "2026-03-20T10:30:00.000000Z",
      "updated_at": "2026-03-20T10:30:00.000000Z",
      "organization": {
        "id": 1,
        "name": "Acme Corporation"
      },
      "product_type": {
        "id": 3,
        "name": "Usage Product",
        "description": "Usage-based or wallet product"
      },
      "price_plans": [
        {
          "id": 29,
          "name": "ZAN Credit Package",
          "billing_type": "usage",
          "billing_interval": null,
          "amount": 0,
          "rate": 215,
          "currency_id": 1,
          "active": true,
          "created_at": "2026-03-20T10:30:00.000000Z"
        }
      ]
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "active": ["The active field must be true or false."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Create Wallet Product

**Description:** Create a new wallet product. Wallets can be created with or without price plans. Price plans are typically added when creating invoices to fund the wallet.

**Method:** `POST`
**URL:** `/api/v1/products`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body (Without Price Plans):**
```json
{
  "product_type_id": 3,
  "name": "ZAN Credits",
  "product_code": "ZAN-CREDITS",
  "description": "Prepaid Meta credits for ZAN messaging",
  "unit": "ZAN-CREDITS",
  "active": true
}
```

**Request Body (With Price Plans):**
```json
{
  "product_type_id": 3,
  "name": "ZAN Credits",
  "product_code": "ZAN-CREDITS",
  "description": "Prepaid Meta credits for ZAN messaging",
  "unit": "ZAN-CREDITS",
  "active": true,
  "price_plans": [
    {
      "name": "ZAN Credit Package",
      "currency_id": 1,
      "rate": 215
    }
  ]
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 12,
    "organization_id": 1,
    "product_type_id": 3,
    "name": "ZAN Credits",
    "product_code": "ZAN-CREDITS",
    "description": "Prepaid Meta credits for ZAN messaging",
    "unit": "ZAN-CREDITS",
    "active": true,
    "created_at": "2026-03-20T10:30:00.000000Z",
    "updated_at": "2026-03-20T10:30:00.000000Z",
    "organization": {
      "id": 1,
      "name": "Acme Corporation"
    },
    "product_type": {
      "id": 3,
      "name": "Usage Product",
      "description": "Usage-based or wallet product"
    },
    "price_plans": []
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name has already been taken for this organization."],
    "product_type_id": ["The selected product type id is invalid."]
  }
}
```

---

### Record Product Usage

**Description:** Usage-based billing is a two-step process: first record usage throughout the billing period, then create invoices based on accumulated usage.

#### Step 1: Record Product Usage
**Method:** `POST`
**URL:** `/api/v1/product-usage`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": 45,
  "product_id": 12,
  "quantity": 5000
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Product usage recorded successfully",
  "data": {
    "id": 789,
    "customer_id": 45,
    "product_id": 12,
    "quantity": 5000,
    "created_at": "2026-02-26T12:00:00.000000Z",
    "product": {
      "id": 12,
      "name": "API Calls",
      "product_type": "usage",
      "unit": "calls"
    },
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com"
    }
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_id": ["Product usage is only allowed for products with type usage."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

#### Step 2: Get Usage Report
**Method:** `GET`
**URL:** `/api/v1/product-usage/report/{customer_id}`

**Description:** Retrieve accumulated usage data for a customer to calculate charges.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "customer_id": 45,
    "customer_name": "Tech Startup Inc",
    "usage_summary": [
      {
        "product_id": 12,
        "product_name": "API Calls",
        "product_code": "API-USAGE",
        "total_purchased": 50000,
        "total_used": 45000,
        "balance": 5000,
        "unit": "calls"
      },
      {
        "product_id": 13,
        "product_name": "Cloud Storage",
        "product_code": "STORAGE-GB",
        "total_purchased": 1000,
        "total_used": 750,
        "balance": 250,
        "unit": "GB"
      }
    ]
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": true,
  "message": "No usage data found for this customer",
  "data": {
    "customer_id": 45,
    "customer_name": "Tech Startup Inc",
    "usage_summary": []
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

#### Step 3: Create Invoice for Usage
**Method:** `POST`
**URL:** `/api/v1/invoices`

**Description:** Create an invoice based on the usage data. Calculate the amount based on your pricing model (e.g., price per API call, per GB).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Tech Startup Inc",
    "email": "billing@techstartup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "price_plan_id": 15,
      "amount": 45000
    }
  ],
  "description": "API Usage - 45,000 calls @ TZS 1 per call",
  "currency": "TZS",
  "status": "issued"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 125,
      "invoice_number": "INV-2026-00125",
      "customer_id": 45,
      "currency": "TZS",
      "status": "issued",
      "description": "API Usage - 45,000 calls @ TZS 1 per call",
      "subtotal": 45000,
      "tax_total": 0,
      "total": 45000,
      "issued_at": "2026-02-26T12:30:00.000000Z",
      "items": [
        {
          "id": 458,
          "price_plan_id": 15,
          "product_name": "API Usage Charges",
          "quantity": 1,
          "unit_price": 45000,
          "total": 45000,
          "metadata": {
            "usage_period": "2026-02-01 to 2026-02-28",
            "total_calls": 45000,
            "rate_per_call": 1
          }
        }
      ]
    }
  }
}
```

**Usage-Based Billing Pattern:**
Record usage throughout the billing period → Retrieve usage report → Calculate charges → Create invoice

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "products.0.amount": ["The products.0.amount must be at least 0."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

---

### Record Product Usage
**Method:** `POST`
**URL:** `/api/v1/product-usages`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "product_id": "sample",
  "quantity": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "product_id": [
      "The product id field is invalid."
    ],
    "quantity": [
      "The quantity field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Usage/Wallet balance
**Method:** `GET`
**URL:** `/api/v1/product-usages/balance`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "product_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "product_id": [
      "The product id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Usage/Wallet Report
**Method:** `GET`
**URL:** `/api/v1/product-usages/{customer_id}/report`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Usage/Wallet History
**Method:** `GET`
**URL:** `/api/v1/product-usages/{customer_id}/{product_id}/history`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Webhooks

### Handle UCN payment Webhooks
**Method:** `POST`
**URL:** `/api/v1/webhooks/ecobank/notification`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "responseCode": "000"
}
```

**Error Responses:**

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Handle Flutterwave Webhooks
**Method:** `POST`
**URL:** `/api/v1/webhooks/flutterwave`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| flutterwave-signature | {base64_hmac_sha256} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Event received"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Handle Stripe PaymantIntent Webhooks
**Method:** `POST`
**URL:** `/api/v1/webhooks/stripe`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Stripe-Signature | t={timestamp},v1={signature} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true
}
```

**Error Responses:**

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "error": "Invalid webhook signature"
}
```

## Reconciliation

### Get Payments by Date Range 
**Method:** `GET`
**URL:** `/api/v1/payments`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "date_from": "sample",
  "date_to": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "date_from": [
      "The date from field is invalid."
    ],
    "date_to": [
      "The date to field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Payments by invoice 
**Method:** `GET`
**URL:** `/api/v1/payments/by-invoice/{invoice_id}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```
## Taxes

### List Tax Rates
**Method:** `GET`
**URL:** `/api/v1/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Tax Rates
**Method:** `POST`
**URL:** `/api/v1/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "country": "sample",
  "name": "sample",
  "rate": "sample",
  "active": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "country": [
      "The country field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Tax Rates
**Method:** `DELETE`
**URL:** `/api/v1/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Tax Rates
**Method:** `GET`
**URL:** `/api/v1/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Tax Rates
**Method:** `PUT`
**URL:** `/api/v1/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "country": "sample",
  "name": "sample",
  "rate": "sample",
  "active": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "country": [
      "The country field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```
## Bank Accounts

### List Bank Accounts
**Method:** `GET`
**URL:** `/api/v1/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample"
}
```

> **💡 Note:** The `organization_id` parameter is **optional**. If not provided, it will be automatically extracted from your access token.

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Bank accounts retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Main Business Account",
      "account_number": "1234567890",
      "branch": "Main Branch",
      "refer_bank_id": "12345",
      "organization_id": 1,
      "created_at": "2026-03-01T10:00:00.000000Z",
      "updated_at": "2026-03-01T10:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Bank Accounts
**Method:** `POST`
**URL:** `/api/v1/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Business Checking Account",
  "account_number": "9876543210",
  "branch": "Downtown Branch",
  "refer_bank_id": "54321"
}
```

> **💡 Note:** The `organization_id` parameter is **optional**. If not provided, it will be automatically extracted from your access token. You only need to include it if you want to explicitly specify it (must match your token's organization).

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Bank account created successfully",
  "data": {
    "id": 2,
    "name": "Business Checking Account",
    "account_number": "9876543210",
    "branch": "Downtown Branch",
    "refer_bank_id": "54321",
    "organization_id": 1,
    "created_at": "2026-03-11T14:30:00.000000Z",
    "updated_at": "2026-03-11T14:30:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "account_number": [
      "The account number field is invalid."
    ],
    "branch": [
      "The branch field is invalid."
    ],
    "refer_bank_id": [
      "The refer bank id field is invalid."
    ],
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Bank Accounts
**Method:** `DELETE`
**URL:** `/api/v1/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Bank account deleted successfully",
  "data": {
    "id": 1,
    "name": "Main Business Account",
    "deleted": true
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Bank account not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Bank Accounts
**Method:** `GET`
**URL:** `/api/v1/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Bank account retrieved successfully",
  "data": {
    "id": 1,
    "name": "Main Business Account",
    "account_number": "1234567890",
    "branch": "Main Branch",
    "refer_bank_id": "12345",
    "organization_id": 1,
    "created_at": "2026-03-01T10:00:00.000000Z",
    "updated_at": "2026-03-01T10:00:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Bank Accounts
**Method:** `PUT`
**URL:** `/api/v1/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Updated Business Account",
  "account_number": "1234567899",
  "branch": "Updated Branch",
  "refer_bank_id": "54399",
  "organization_id": "1"
}
```

**Note:** `organization_id` is optional when using organization API keys.

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Bank account updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Business Account",
    "account_number": "1234567899",
    "branch": "Updated Branch",
    "refer_bank_id": "54399",
    "organization_id": 1,
    "created_at": "2026-03-01T10:00:00.000000Z",
    "updated_at": "2026-03-11T14:45:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "account_number": [
      "The account number field is invalid."
    ],
    "branch": [
      "The branch field is invalid."
    ],
    "refer_bank_id": [
      "The refer bank id field is invalid."
    ],
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Product Types

### List Product Types
**Method:** `GET`
**URL:** `/api/v1/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Product Types
**Method:** `POST`
**URL:** `/api/v1/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Product Types
**Method:** `DELETE`
**URL:** `/api/v1/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Types
**Method:** `GET`
**URL:** `/api/v1/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Product Types
**Method:** `PUT`
**URL:** `/api/v1/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Customers

### List Customers
**Method:** `GET`
**URL:** `/api/v1/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "1",
  "username": "john_doe"
}
```

> **💡 Note:** The `organization_id` parameter is **optional**. If not provided, it will be automatically extracted from your access token. The `username` filter is also optional.

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Customers retrieved successfully",
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "name": "John Doe",
      "username": "john_doe",
      "email": "john@example.com",
      "phone": "+255123456789",
      "status": "active",
      "created_at": "2026-02-01T08:00:00.000000Z",
      "updated_at": "2026-02-01T08:00:00.000000Z"
    },
    {
      "id": 2,
      "organization_id": 1,
      "name": "Jane Smith",
      "username": "jane_smith",
      "email": "jane@example.com",
      "phone": "+255987654321",
      "status": "active",
      "created_at": "2026-02-15T10:30:00.000000Z",
      "updated_at": "2026-02-15T10:30:00.000000Z"
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Customers
**Method:** `POST`
**URL:** `/api/v1/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Alice Brown",
  "username": "alice_brown",
  "email": "alice@example.com",
  "phone": "+255700123456",
  "status": "active"
}
```

> **💡 Note:** The `organization_id` parameter is **optional**. If not provided, it will be automatically extracted from your access token. The `status` field defaults to "active" if not provided.

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 3,
    "organization_id": 1,
    "name": "Alice Brown",
    "username": "alice_brown",
    "email": "alice@example.com",
    "phone": "+255700123456",
    "status": "active",
    "created_at": "2026-03-11T15:20:00.000000Z",
    "updated_at": "2026-03-11T15:20:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

###  Find Customer by Email
**Method:** `GET`
**URL:** `/api/v1/customers/by-email/{email}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Find Customer by Phone
**Method:** `GET`
**URL:** `/api/v1/customers/by-phone/{phone}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Customers
**Method:** `DELETE`
**URL:** `/api/v1/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Customers
**Method:** `GET`
**URL:** `/api/v1/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Customers
**Method:** `PUT`
**URL:** `/api/v1/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "name": "sample",
  "username": "sample",
  "email": "sample",
  "phone": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getcustomersubscriptions Customers
**Method:** `GET`
**URL:** `/api/v1/customers/{customer}/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Payment Gateways

### List Payment Gateways
**Method:** `GET`
**URL:** `/api/v1/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Payment Gateways
**Method:** `POST`
**URL:** `/api/v1/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "type": "sample",
  "webhook_secret": "sample",
  "config": "sample",
  "active": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "type": [
      "The type field is invalid."
    ],
    "webhook_secret": [
      "The webhook secret field is invalid."
    ],
    "config": [
      "The config field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Payment Gateways
**Method:** `DELETE`
**URL:** `/api/v1/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Payment Gateways
**Method:** `GET`
**URL:** `/api/v1/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Payment Gateways
**Method:** `PUT`
**URL:** `/api/v1/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "type": "sample",
  "webhook_secret": "sample",
  "config": "sample",
  "active": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "type": [
      "The type field is invalid."
    ],
    "webhook_secret": [
      "The webhook secret field is invalid."
    ],
    "config": [
      "The config field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Organizations

### List Organizations
**Method:** `GET`
**URL:** `/api/v1/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Organizations
**Method:** `POST`
**URL:** `/api/v1/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "phone": "sample",
  "email": "sample",
  "currency": "sample",
  "country_id": "sample",
  "status": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "country_id": [
      "The country id field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Integratepaymentgateway Organizations
**Method:** `POST`
**URL:** `/api/v1/organizations/integrate-payment-gateway`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "payment_gateway_id": "sample",
  "endpoint": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "payment_gateway_id": [
      "The payment gateway id field is invalid."
    ],
    "endpoint": [
      "The endpoint field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Organizations
**Method:** `DELETE`
**URL:** `/api/v1/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Organizations
**Method:** `GET`
**URL:** `/api/v1/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Organizations
**Method:** `PUT`
**URL:** `/api/v1/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "phone": "sample",
  "email": "sample",
  "currency": "sample",
  "country_id": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "country_id": [
      "The country id field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Countries

### List Countries
**Method:** `GET`
**URL:** `/api/v1/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Countries retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Tanzania",
      "code": "TZ",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Kenya",
      "code": "KE",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    {
      "id": 3,
      "name": "Uganda",
      "code": "UG",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Countries
**Method:** `POST`
**URL:** `/api/v1/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Rwanda",
  "code": "RW"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Country created successfully",
  "data": {
    "id": 4,
    "name": "Rwanda",
    "code": "RW",
    "created_at": "2026-03-11T15:00:00.000000Z",
    "updated_at": "2026-03-11T15:00:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "code": [
      "The code field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Countries
**Method:** `DELETE`
**URL:** `/api/v1/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Country deleted successfully",
  "data": {
    "id": 4,
    "name": "Rwanda",
    "deleted": true
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Countries
**Method:** `GET`
**URL:** `/api/v1/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Country retrieved successfully",
  "data": {
    "id": 1,
    "name": "Tanzania",
    "code": "TZ",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-01-01T00:00:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Countries
**Method:** `PUT`
**URL:** `/api/v1/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "United Republic of Tanzania",
  "code": "TZA"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Country updated successfully",
  "data": {
    "id": 1,
    "name": "United Republic of Tanzania",
    "code": "TZA",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-03-11T15:10:00.000000Z"
  }
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "code": [
      "The code field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```



