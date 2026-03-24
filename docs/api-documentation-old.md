# API Documentation

**Base URL:** `https://api.example.com`

---

## Authentication

### 1. Obtain API Credentials

Contact your system administrator to:
- Get your **organization_id**
- Create your user account with email and password

### 2. Get Access Token

**POST** `/api/auth/login`

```bash
curl -X POST https://api.example.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your@email.com",
    "password": "your-password"
  }'
```

**Response:**
```json
{
  "access_token": "shulesoft_1|abcdefg1234567890",
  "token_type": "Bearer",
  "expires_in": 43200
}
```

### 3. Use Token in Requests

Include the token in all API requests:

```bash
curl -X GET https://api.example.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_1|abcdefg1234567890"
```

---

## API Endpoints

### Register

**POST** `/api/auth/register`

```bash
curl -X POST https://api.example.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

**Response:**
```json
{
  "access_token": "shulesoft_1|abcdefg1234567890",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### Login

**POST** `/api/auth/login`

```bash
curl -X POST https://api.example.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123!"
  }'
```

**Response:**
```json
{
  "access_token": "shulesoft_1|abcdefg1234567890",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### Logout

**POST** `/api/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

### Get Current User

**GET** `/api/auth/me`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

---

## Products
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!"
  }'
```

```javascript
// JavaScript (Fetch API)
const response = await fetch('https://api.example.com/api/auth/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    organization_id: 1,
    name: 'John Doe',
    email: 'john@example.com',
    password: 'SecurePassword123!',
    password_confirmation: 'SecurePassword123!'
  })
});

const data = await response.json();
const token = data.access_token;

// Store token for subsequent requests
localStorage.setItem('api_token', token);
```

```php
// PHP (with Guzzle)
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://api.example.com']);

$response = $client->post('/api/auth/register', [
    'json' => [
        'organization_id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ],
    'headers' => [
        'Accept' => 'application/json',
    ]
]);

$data = json_decode($response->getBody(), true);
$token = $data['access_token'];
```

```python
# Python (with requests)
import requests

response = requests.post(
    'https://api.example.com/api/auth/register',
    json={
        'organization_id': 1,
        'name': 'John Doe',
        'email': 'john@example.com',
        'password': 'SecurePassword123!',
        'password_confirmation': 'SecurePassword123!'
    },
    headers={'Accept': 'application/json'}
)

data = response.json()
token = data['access_token']
```

---

### Login

Authenticate with email and password to receive an access token.

**Method:** `POST`  
**URL:** `/api/auth/login`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```

**Success Response:** `200 OK`
```json
{
  "message": "Login successful",
  "access_token": "shulesoft_2|xyz789abc456def123ghi890jkl567mno234pqr",
  "token_type": "Bearer",
  "expires_in": 43200,
  "user": {
    "id": 15,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

**Important Notes:**
- ✅ **Token Rotation Enabled**: All previous tokens for this user are automatically revoked upon successful login
- ✅ **30-Day Expiration**: Token expires after 43,200 minutes (30 days)
- ✅ **Audit Logging**: Login IP address and user agent are recorded with the token
- ✅ **Single Active Session**: Only the most recent login token remains valid

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

`422 Unprocessable Entity`
```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

**Code Examples:**

```bash
# cURL
curl -X POST https://api.example.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123!"
  }'
```

```javascript
// JavaScript (Fetch API)
async function login(email, password) {
  const response = await fetch('https://api.example.com/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });

  if (!response.ok) {
    throw new Error('Login failed');
  }

  const data = await response.json();
  
  // Store token securely
  localStorage.setItem('api_token', data.access_token);
  localStorage.setItem('user', JSON.stringify(data.user));
  
  return data;
}

// Usage
login('john@example.com', 'SecurePassword123!')
  .then(data => console.log('Logged in:', data.user))
  .catch(error => console.error('Login error:', error));
```

```php
// PHP (with Guzzle)
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'https://api.example.com']);

try {
    $response = $client->post('/api/auth/login', [
        'json' => [
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!',
        ],
        'headers' => ['Accept' => 'application/json']
    ]);

    $data = json_decode($response->getBody(), true);
    $token = $data['access_token'];
    
    // Store token in session or database
    $_SESSION['api_token'] = $token;
    $_SESSION['user'] = $data['user'];
    
} catch (\GuzzleHttp\Exception\ClientException $e) {
    // Handle 401 or 422 errors
    $error = json_decode($e->getResponse()->getBody(), true);
    echo $error['message'];
}
```

```python
# Python (with requests)
import requests

def login(email, password):
    response = requests.post(
        'https://api.example.com/api/auth/login',
        json={'email': email, 'password': password},
        headers={'Accept': 'application/json'}
    )
    
    if response.status_code == 200:
        data = response.json()
        return data['access_token'], data['user']
    else:
        error = response.json()
        raise Exception(error['message'])

# Usage
try:
    token, user = login('john@example.com', 'SecurePassword123!')
    print(f"Logged in as: {user['name']}")
    print(f"Token: {token}")
except Exception as e:
    print(f"Login failed: {e}")
```

---

### Logout (Current Device)

Revoke the current access token (logout from current device only).

**Method:** `POST`  
**URL:** `/api/auth/logout`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {access_token} |
| Accept | application/json |

**Request Body:** None required

**Success Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "authentication_required"
}
```

**Code Examples:**

```bash
# cURL
curl -X POST https://api.example.com/api/auth/logout \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Accept: application/json"
```

```javascript
// JavaScript
async function logout() {
  const token = localStorage.getItem('api_token');
  
  const response = await fetch('https://api.example.com/api/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });

  if (response.ok) {
    // Clear stored token
    localStorage.removeItem('api_token');
    localStorage.removeItem('user');
    console.log('Logged out successfully');
  }
}
```

```php
// PHP
$response = $client->post('/api/auth/logout', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json'
    ]
]);

// Clear session
unset($_SESSION['api_token']);
unset($_SESSION['user']);
```

```python
# Python
headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

response = requests.post(
    'https://api.example.com/api/auth/logout',
    headers=headers
)

if response.status_code == 200:
    print("Logged out successfully")
```

---

### Logout from All Devices

Revoke all access tokens for the authenticated user (logout from all devices).

**Method:** `POST`  
**URL:** `/api/auth/logout-all`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {access_token} |
| Accept | application/json |

**Request Body:** None required

**Success Response:** `200 OK`
```json
{
  "message": "Logged out from all devices successfully"
}
```

**Use Cases:**
- Security breach detected
- Password changed
- User requested to revoke all sessions
- Admin action to force re-authentication

**Code Examples:**

```bash
# cURL
curl -X POST https://api.example.com/api/auth/logout-all \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Accept: application/json"
```

```javascript
// JavaScript
async function logoutAllDevices() {
  const token = localStorage.getItem('api_token');
  
  const response = await fetch('https://api.example.com/api/auth/logout-all', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });

  if (response.ok) {
    localStorage.removeItem('api_token');
    localStorage.removeItem('user');
    alert('Logged out from all devices');
  }
}
```

---

### Get Current User

Retrieve the authenticated user's profile information.

**Method:** `GET`  
**URL:** `/api/auth/me`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {access_token} |
| Accept | application/json |

**Success Response:** `200 OK`
```json
{
  "user": {
    "id": 15,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255712345678",
    "role": "user",
    "sex": "M",
    "email_verified_at": null,
    "created_at": "2026-03-11T10:30:00.000000Z",
    "updated_at": "2026-03-11T10:30:00.000000Z"
  }
}
```

**Code Examples:**

```bash
# cURL
curl -X GET https://api.example.com/api/auth/me \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Accept: application/json"
```

```javascript
// JavaScript
async function getCurrentUser() {
  const token = localStorage.getItem('api_token');
  
  const response = await fetch('https://api.example.com/api/auth/me', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });

  const data = await response.json();
  return data.user;
}
```

---

### Using Your Access Token

**All API v1 endpoints require authentication.** Include your access token in the `Authorization` header:

```
Authorization: Bearer shulesoft_1|abcdefghijklmnopqrstuvwxyz1234567890
```

**Token Format:**
- Prefix: `shulesoft_` (for GitHub secret scanning)
- Token ID: Numeric identifier (e.g., `1`)
- Separator: `|`
- Random string: 40-character secure random token

**Example Request:**

```bash
curl -X GET https://api.example.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Accept: application/json"
```

---

### Token Abilities (Permissions)

Tokens can have specific abilities (permissions) to limit what actions they can perform. This is useful for creating read-only tokens, or tokens with restricted access.

**Default Ability:**
- `*` (wildcard): Full access to all resources

**Recommended Abilities:**

| Ability | Description |
|---------|-------------|
| `payments:read` | View payment records |
| `payments:write` | Create/update payments |
| `invoices:read` | View invoices |
| `invoices:write` | Create invoices |
| `invoices:cancel` | Cancel invoices |
| `customers:read` | View customers |
| `customers:write` | Create/update customers |
| `products:read` | View products |
| `products:write` | Create/update products |
| `subscriptions:read` | View subscriptions |
| `subscriptions:write` | Create subscriptions |
| `subscriptions:cancel` | Cancel subscriptions |

**How It Works:**

When you make a request to an endpoint that requires a specific ability:

1. The API checks if your token has the required ability
2. If the token has `*` (wildcard), access is granted
3. If the token has the specific ability (e.g., `payments:read`), access is granted
4. Otherwise, you receive a `403 Forbidden` response

**Example:**

```json
// Request to GET /api/v1/payments
// Requires: payments:read ability

// Response if token lacks permission:
{
  "error": "Forbidden",
  "message": "You do not have permission to view payment records"
}
```

**Creating Tokens with Specific Abilities:**

Currently, all tokens created via `/api/auth/login` and `/api/auth/register` have full access (`*`). Custom ability tokens can be created programmatically by your organization's administrators.

---

### Organization-Scoped Access

**All API requests are automatically scoped to your organization.** This means:

✅ **Automatic Filtering**: You only see data belonging to your organization  
✅ **Data Isolation**: You cannot access other organizations' data  
✅ **Automatic Injection**: When creating resources, `organization_id` is automatically set to your organization  
✅ **Validation**: Any attempt to access another organization's data returns `403 Forbidden`

**Example Scenarios:**

**Scenario 1: Automatic Organization Filtering**

```bash
# Request
GET /api/v1/products
Authorization: Bearer {token_for_org_1}

# Response: Only products from Organization 1
{
  "success": true,
  "data": [
    {"id": 1, "organization_id": 1, "name": "Product A"},
    {"id": 2, "organization_id": 1, "name": "Product B"}
  ]
}
```

**Scenario 2: Blocked Cross-Organization Access**

```bash
# Request: User from Org 1 trying to create product for Org 2
POST /api/v1/products
Authorization: Bearer {token_for_org_1}
{
  "organization_id": 2,
  "name": "Product X"
}

# Response: 403 Forbidden
{
  "error": "Forbidden",
  "message": "You cannot create or modify resources for other organizations"
}
```

**Scenario 3: Automatic Organization Injection**

```bash
# Request: organization_id not provided
POST /api/v1/products
Authorization: Bearer {token_for_org_1}
{
  "name": "Product Y",
  "product_type_id": 1
}

# Response: organization_id automatically set to 1
{
  "success": true,
  "data": {
    "id": 50,
    "organization_id": 1,  // ← Automatically injected
    "name": "Product Y",
    "product_type_id": 1
  }
}
```

---

### Token Security Best Practices

1. **Store Securely**
   - Never commit tokens to version control
   - Use environment variables for server-side applications
   - Use secure storage (e.g., `httpOnly` cookies, encrypted storage) for client-side apps

2. **Rotate Regularly**
   - Tokens auto-rotate on re-login (old tokens are revoked)
   - For long-running integrations, plan for token refresh

3. **Monitor Usage**
   - All tokens log IP address and user agent on creation
   - Track unusual access patterns
   - Use `/api/auth/logout-all` if breach suspected

4. **Limit Scope**
   - Request tokens with minimal required abilities
   - Use read-only tokens for reporting tools
   - Use write tokens only when necessary

5. **Handle Expiration**
   - Tokens expire after 30 days
   - Implement automatic re-authentication when receiving `401 Unauthenticated`
   - Plan for graceful token refresh

**Example: Handling Token Expiration**

```javascript
async function apiRequest(url, options = {}) {
  let token = localStorage.getItem('api_token');
  
  const response = await fetch(url, {
    ...options,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      ...options.headers
    }
  });

  // Token expired - re-authenticate
  if (response.status === 401) {
    const email = localStorage.getItem('email');
    const password = await promptForPassword(); // Implement securely
    
    const loginData = await login(email, password);
    token = loginData.access_token;
    
    // Retry original request with new token
    return fetch(url, {
      ...options,
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        ...options.headers
      }
    });
  }

  return response;
}
```

---

### Migration from Legacy Authentication

**Legacy Endpoints (Deprecated):**
- `/api/login` → Use `/api/auth/login`
- All `/api/*` routes → Use `/api/v1/*` routes
- `APP_ACCESS_TOKEN` → Use Sanctum personal access tokens

**Migration Timeline:**
1. **Now**: Both legacy and v1 endpoints are available
2. **All new integrations**: Should use v1 endpoints
3. **Existing integrations**: Migrate to v1 within 6 months
4. **Future**: Legacy endpoints will be removed (date TBD)

**Key Differences:**

| Feature | Legacy | New (Sanctum) |
|---------|--------|---------------|
| **Authentication endpoint** | `/api/login` | `/api/auth/login` |
| **API endpoints** | `/api/products` | `/api/v1/products` |
| **Token format** | `org_live_*` | `shulesoft_{id}\|{token}` |
| **Token expiration** | Never | 30 days |
| **Token rotation** | No | Yes (on login) |
| **Organization scoping** | Manual | Automatic |
| **Audit logging** | No | Yes (IP + User Agent) |
| **Rate limiting** | 30 req/min | 60 req/min |

---

## API Endpoints Migration Guide

### Endpoint URL Patterns

**All API endpoints follow this migration pattern:**

| Resource | Legacy (Deprecated) | New (v1) |
|----------|-------------------|----------|
| Products | `/api/products` | `/api/v1/products` |
| Invoices | `/api/invoices` | `/api/v1/invoices` |
| Subscriptions | `/api/subscriptions` | `/api/v1/subscriptions` |
| Payments | `/api/payments` | `/api/v1/payments` |
| Customers | `/api/customers` | `/api/v1/customers` |
| Product Usage | `/api/product-usage` | `/api/v1/product-usage` |

**Simple Migration Rule:**
```
Old: https://api.example.com/api/{resource}
New: https://api.example.com/api/v1/{resource}
```

### Header Changes

**Old (Legacy):**
```http
Authorization: Bearer {YOUR_API_KEY}
```

**New (Sanctum):**
```http
Authorization: Bearer {YOUR_SANCTUM_TOKEN}
```

### Request Body Changes

**Old:** Most requests required `organization_id` in the body  
**New:** `organization_id` is automatically determined from your token

**Example - Creating a Product:**

```json
// OLD (Legacy)
{
  "organization_id": 1,  // ← Required
  "product_type_id": 2,
  "name": "My Product"
}

// NEW (v1)
{
  // organization_id automatically set from token
  "product_type_id": 2,
  "name": "My Product"
}
```

### Code Migration Examples

**JavaScript/Fetch:**
```javascript
// OLD
const response = await fetch('https://api.example.com/api/products', {
  headers: {
    'Authorization': 'Bearer org_live_abc123',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    organization_id: 1,
    product_type_id: 2,
    name: 'Product X'
  })
});

// NEW
const token = localStorage.getItem('api_token'); // From /api/auth/login
const response = await fetch('https://api.example.com/api/v1/products', {
  headers: {
    'Authorization': `Bearer ${token}`,  // Sanctum token
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    // organization_id removed
    product_type_id: 2,
    name: 'Product X'
  })
});
```

**PHP/Guzzle:**
```php
// OLD
$client->post('/api/products', [
    'headers' => ['Authorization' => 'Bearer org_live_abc123'],
    'json' => [
        'organization_id' => 1,
        'product_type_id' => 2,
        'name' => 'Product X'
    ]
]);

// NEW
$client->post('/api/v1/products', [
    'headers' => ['Authorization' => 'Bearer ' . $sanctumToken],
    'json' => [
        // organization_id removed
        'product_type_id' => 2,
        'name' => 'Product X'
    ]
]);
```

**Python/Requests:**
```python
# OLD
requests.post('https://api.example.com/api/products',
    headers={'Authorization': 'Bearer org_live_abc123'},
    json={
        'organization_id': 1,
        'product_type_id': 2,
        'name': 'Product X'
    })

# NEW
requests.post('https://api.example.com/api/v1/products',
    headers={'Authorization': f'Bearer {sanctum_token}'},
    json={
        # organization_id removed
        'product_type_id': 2,
        'name': 'Product X'
    })
```

---

## Products

> **Note:** All product endpoints automatically filter results to your organization. You don't need to provide `organization_id` in requests—it's automatically determined from your access token.

### List All Products
**Method:** `GET`
**URL:** `/api/v1/products`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_SANCTUM_TOKEN} |
| Accept | application/json |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| product_type | integer | No | Filter by product type ID (1=one-time, 2=subscription, 3=usage) |

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
      "created_at": "2026-03-10T08:00:00.000000Z",
      "updated_at": "2026-03-10T08:00:00.000000Z"
    }
  ]
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "authentication_required"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

**Code Examples:**

```bash
# cURL
curl -X GET "https://api.example.com/api/v1/products?product_type=2" \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Accept: application/json"
```

```javascript
// JavaScript
async function getProducts(productType = null) {
  const token = localStorage.getItem('api_token');
  const url = new URL('https://api.example.com/api/v1/products');
  
  if (productType) {
    url.searchParams.append('product_type', productType);
  }
  
  const response = await fetch(url, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });

  return response.json();
}
```

---

### Create Products
**Method:** `POST`
**URL:** `/api/v1/products`

**Description:** Create a new product with pricing plans. Products must be assigned a product type that determines their billing behavior.

**Product Type IDs:**
- `1` - **One-time Product**: For single-charge items (consulting, projects, one-off services)
- `2` - **Subscription Product**: For recurring billing (SaaS, memberships, monthly plans)
- `3` - **Usage Product**: For pay-per-use billing (API calls, storage, bandwidth, credits)

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_SANCTUM_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
> **Note:** `organization_id` is automatically set from your access token—no need to include it.

```json
{
  "product_type_id": 2,
  "name": "Premium Hosting Plan",
  "description": "Monthly recurring hosting with 100GB storage and unlimited bandwidth",
  "unit": "month",
  "status": "active",
  "price_plans": [
    {
      "name": "Monthly Plan",
      "subscription_type": "monthly",
      "amount": 75000,
      "currency": "TZS",
      "rate": 30
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
    "id": 25,
    "organization_id": 1,
    "product_type_id": 2,
    "name": "Premium Hosting Plan",
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
  "error": "authentication_required"
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

**Code Examples:**

```bash
# cURL
curl -X POST https://api.example.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_1|your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "product_type_id": 2,
    "name": "Premium Hosting Plan",
    "description": "Monthly recurring hosting with 100GB storage",
    "unit": "month",
    "status": "active",
    "price_plans": [
      {
        "name": "Monthly Plan",
        "subscription_type": "monthly",
        "amount": 75000,
        "currency": "TZS",
        "rate": 30
      }
    ]
  }'
```

```javascript
// JavaScript
async function createProduct(productData) {
  const token = localStorage.getItem('api_token');
  
  const response = await fetch('https://api.example.com/api/v1/products', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify(productData)
  });

  return response.json();
}

// Usage
const product = {
  product_type_id: 2,
  name: 'Premium Hosting Plan',
  description: 'Monthly recurring hosting',
  unit: 'month',
  status: 'active',
  price_plans: [
    {
      name: 'Monthly Plan',
      subscription_type: 'monthly',
      amount: 75000,
      currency: 'TZS',
      rate: 30
    }
  ]
};

createProduct(product).then(data => console.log('Product created:', data));
```

---

### Delete Products
**Method:** `DELETE`
**URL:** `/api/v1/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_SANCTUM_TOKEN} |
| Accept | application/json |

**Request Body:** None required

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "authentication_required"
}
```

`403 Forbidden`
```json
{
  "error": "Forbidden",
  "message": "You cannot delete products from other organizations"
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

---

### Get Single Product
**Method:** `GET`
**URL:** `/api/v1/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_SANCTUM_TOKEN} |
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

### Update Product
**Method:** `PUT`
**URL:** `/api/v1/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_SANCTUM_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
> **Note:** `organization_id` cannot be changed and is automatically validated.

```json
{
  "product_type_id": 2,
  "name": "Updated Product Name",
  "description": "Updated description",
  "status": "active"
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
**URL:** `/api/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Description:** One-time invoices are for products that are charged once without creating a subscription. Perfect for consulting services, one-off projects, or standalone purchases.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Description:** Create a single invoice with multiple products of different types (one-time and subscription products can be combined).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Description:** Generate payment links automatically when creating invoices. Supports Flutterwave (card/mobile money) and EcoBank control numbers (bank payments).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices/by-subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices/{id}/cancel`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices/{invoice}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
  "message": "Invoice not found"
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
**URL:** `/api/invoices/{product_id}/product`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices/{id}/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Description:** Subscription invoices automatically create a subscription record for recurring billing. The subscription remains in "pending" status until the invoice is paid, then becomes "active".

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
- If a pending subscription already exists for the same customer and price plan, the existing invoice is returned

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
**Method:** `GET`
**URL:** `/api/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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

### Cancel Subscriptions
**Method:** `POST`
**URL:** `/api/subscriptions/{id}/cancel`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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

## Wallets

### Create Wallet

**Description:** Usage-based billing is a two-step process: first record usage throughout the billing period, then create invoices based on accumulated usage.

#### Step 1: Record Product Usage
**Method:** `POST`
**URL:** `/api/product-usage`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-usage/report/{customer_id}`

**Description:** Retrieve accumulated usage data for a customer to calculate charges.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/invoices`

**Description:** Create an invoice based on the usage data. Calculate the amount based on your pricing model (e.g., price per API call, per GB).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-usages`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-usages/balance`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-usages/{customer_id}/report`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-usages/{customer_id}/{product_id}/history`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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

> **⚠️ DEPRECATED:** This is legacy documentation. Please refer to [api-documentation.md](./api-documentation.md) for the complete, up-to-date webhook documentation including Custom Webhooks Management.

### Incoming Webhooks (Payment Gateway Notifications)

These endpoints receive webhook notifications **FROM** payment gateways. Configure these URLs in your payment gateway dashboard.

### Handle UCN payment Webhook
**Method:** `POST`  
**URL:** `/api/v1/webhooks/ecobank/notification` *(Legacy: `/api/webhooks/ecobank/notification`)*

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Accept | application/json |

**Request Body:** (Gateway-specific)
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

---

### Handle Flutterwave Webhook
**Method:** `POST`  
**URL:** `/api/v1/webhooks/flutterwave` *(Legacy: `/api/webhooks/flutterwave`)*

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| flutterwave-signature | {base64_hmac_sha256} |
| Accept | application/json |

**Request Body:** (Gateway-specific)
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

---

### Handle Stripe PaymentIntent Webhook
**Method:** `POST`  
**URL:** `/api/v1/webhooks/stripe` *(Legacy: `/api/webhooks/stripe`)*

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Stripe-Signature | t={timestamp},v1={signature} |
| Accept | application/json |

**Request Body:** (Gateway-specific)
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

`401 Unauthorized`
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

---

> **📚 For Custom Webhooks:** To configure webhook endpoints in YOUR application to receive event notifications FROM this billing platform, see the **Custom Webhooks Management** section in [api-documentation.md](./api-documentation.md).

## Reconciliation

### Get Payments by Date Range 
**Method:** `GET`
**URL:** `/api/payments`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payments/by-invoice/{invoice_id}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample"
}
```

**Note:** `organization_id` is optional when using organization API keys.

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
**URL:** `/api/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "Business Checking Account",
  "account_number": "9876543210",
  "branch": "Downtown Branch",
  "refer_bank_id": "54321",
  "organization_id": "1"
}
```

**Note:** `organization_id` is optional when using organization API keys.

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
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "1",
  "username": "john_doe"
}
```

**Note:** `organization_id` is optional when using organization API keys. `username` filter is optional.

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
**URL:** `/api/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "1",
  "name": "Alice Brown",
  "username": "alice_brown",
  "email": "alice@example.com",
  "phone": "+255700123456",
  "status": "active"
}
```

**Note:** `organization_id` is optional when using organization API keys. `status` defaults to "active" if not provided.

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
**URL:** `/api/customers/by-email/{email}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers/by-phone/{phone}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/customers/{customer}/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations/integrate-payment-gateway`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {YOUR_API_KEY} |
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
