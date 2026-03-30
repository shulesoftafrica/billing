# OAuth Client Credentials & Sanctum Token Expiration Implementation Guide

## 📋 Implementation Summary

This guide documents the complete implementation of three major authentication improvements:

1. ✅ **All API routes now use Laravel Sanctum** (removed legacy APP_ACCESS_TOKEN)
2. ✅ **Token expiration configured** (industry-standard best practices like Stripe)
3. ✅ **OAuth Client Credentials** (client_id/client_secret for API integrations)

---

## 🎯 What Changed

### 1. Routes Migration (✅ Completed)

**Before:**
```php
// Legacy authentication with APP_ACCESS_TOKEN
Route::middleware(['app.access.token', 'throttle:30,1'])->group(function () {
    // API routes
});
```

**After:**
```php
// Modern Sanctum authentication
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // API routes - accepts both user tokens and OAuth client tokens
});
```

**All routes now:**
- Use `auth:sanctum` middleware
- Accept user authentication tokens (email/password login)
- Accept OAuth client tokens (client_id/client_secret)
- Rate limit increased to 60 requests/minute

---

### 2. Token Expiration (✅ Completed)

**Configuration:** [`config/sanctum.php`](config/sanctum.php)

```php
// User authentication tokens (email/password login)
'user_token_expiration' => 43200, // 30 days (in minutes)

// OAuth client credentials tokens
'client_token_expiration' => 129600, // 90 days (in minutes)

// Default expiration
'expiration' => 10080, // 7 days (industry standard)
```

**Token Response Format:**
```json
{
  "access_token": "shulesoft_1|abc123...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "expires_at": "2026-04-13T10:44:36+00:00"
}
```

**Environment Variables** (add to `.env`):
```env
SANCTUM_USER_TOKEN_EXPIRATION=43200      # 30 days
SANCTUM_CLIENT_TOKEN_EXPIRATION=129600   # 90 days
SANCTUM_EXPIRATION=10080                 # 7 days default
```

---

### 3. OAuth Client Credentials (✅ New Feature)

**Database Table:** `oauth_clients`

**Model:** [`app/Models/OAuthClient.php`](app/Models/OAuthClient.php)

**Controller:** [`app/Http/Controllers/Auth/ClientCredentialsController.php`](app/Http/Controllers/Auth/ClientCredentialsController.php)

---

## 📡 API Endpoints Reference

### 🔑 User Authentication (Email/Password)

#### 1. Register User
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "organization_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "role": "user"
}
```

**Response:**
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
    "email": "john@example.com",
    "role": "user"
  }
}
```

---

#### 2. Login User
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123!"
}
```

**Response:** (Same as register)

---

#### 3. Logout Current Session
```http
POST /api/v1/auth/logout
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

#### 4. Logout All Devices
```http
POST /api/v1/auth/logout-all
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
```json
{
  "message": "Logged out from all devices successfully"
}
```

---

#### 5. Get Current User
```http
GET /api/v1/auth/me
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2026-03-14T10:00:00.000000Z"
  }
}
```

---

#### 6. Generate Personal Access Token
```http
POST /api/v1/auth/generate-token
Authorization: Bearer shulesoft_1|abc123xyz...
Content-Type: application/json

{
  "name": "Mobile App Token",
  "expires_in_days": 90,
  "abilities": ["read", "write"]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Personal access token created successfully",
  "data": {
    "token": "shulesoft_5|newtoken123...",
    "token_id": 5,
    "name": "Mobile App Token",
    "expires_at": "2026-06-12T10:00:00+00:00",
    "abilities": ["read", "write"]
  },
  "warning": "Store this token securely. It will not be shown again."
}
```

---

#### 7. List All Tokens
```http
GET /api/v1/auth/tokens
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "auth_token",
      "abilities": ["*"],
      "last_used_at": "2026-03-14T10:30:00+00:00",
      "expires_at": "2026-04-13T10:44:36+00:00",
      "created_at": "2026-03-14T10:00:00+00:00",
      "is_expired": false
    }
  ]
}
```

---

#### 8. Revoke Specific Token
```http
DELETE /api/v1/auth/tokens/{token_id}
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
```json
{
  "success": true,
  "message": "Token revoked successfully",
  "token_name": "Mobile App Token"
}
```

---

### 🔐 OAuth Client Credentials (API Integrations)

#### 1. Create OAuth Client
```http
POST /api/v1/oauth/clients
Authorization: Bearer shulesoft_1|abc123xyz...
Content-Type: application/json

{
  "organization_id": 1,
  "name": "Production API Client",
  "environment": "live",
  "allowed_scopes": ["*"],
  "expires_at": "2027-03-14T00:00:00Z"
}
```

**Response:**
```json
{
  "message": "OAuth client created successfully",
  "client": {
    "id": 1,
    "name": "Production API Client",
    "client_id": "org_live_client_abc123xyz...",
    "client_secret": "org_live_secret_xyz789def...",
    "environment": "live",
    "allowed_scopes": ["*"],
    "expires_at": "2027-03-14T00:00:00+00:00",
    "created_at": "2026-03-14T10:00:00+00:00"
  },
  "warning": "Store the client_secret securely. It will not be shown again."
}
```

**⚠️ IMPORTANT:** The `client_secret` is shown **only once**. Store it securely!

---

#### 2. Get Access Token (Client Credentials Grant)
```http
POST /api/v1/oauth/token
Content-Type: application/json

{
  "grant_type": "client_credentials",
  "client_id": "org_live_client_abc123xyz...",
  "client_secret": "org_live_secret_xyz789def...",
  "scope": "*"
}
```

**Response:**
```json
{
  "access_token": "shulesoft_2|xyz789abc...",
  "token_type": "Bearer",
  "expires_in": 7776000,
  "scope": "*",
  "organization_id": 1
}
```

**Use this token for all API requests:**
```http
GET /api/v1/products
Authorization: Bearer shulesoft_2|xyz789abc...
```

---

#### 3. List OAuth Clients
```http
GET /api/v1/oauth/clients
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
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
      "expires_at": "2027-03-14T00:00:00+00:00",
      "created_at": "2026-03-14T10:00:00+00:00"
    }
  ]
}
```

---

#### 4. Revoke OAuth Client
```http
DELETE /api/v1/oauth/clients/{client_id}
Authorization: Bearer shulesoft_1|abc123xyz...
```

**Response:**
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

## 🔄 Authentication Flow Comparison

### User Authentication (Web/Mobile Apps)
```
1. User → POST /api/v1/auth/login (email + password)
2. API → Validates credentials
3. API → Creates Sanctum token (30-day expiration)
4. API → Returns access_token
5. User → Stores token
6. User → Includes token in Authorization header for all requests
```

### OAuth Client Credentials (Server-to-Server)
```
1. System → POST /api/v1/oauth/token (client_id + client_secret)
2. API → Validates client credentials
3. API → Creates Sanctum token (90-day expiration)
4. API → Returns access_token
5. System → Stores token
6. System → Includes token in Authorization header for all requests
```

---

## 🔒 Security Features

### Token Security
- ✅ SHA-256 hashing for OAuth client secrets
- ✅ Secure token prefix (`shulesoft_`) for GitHub secret scanning
- ✅ Automatic token expiration
- ✅ Token rotation on login (old tokens revoked)
- ✅ IP address and user agent logging
- ✅ Ability-based permissions (scopes)

### OAuth Client Format
```
client_id:     org_live_client_abc123xyz... (40 chars)
client_secret: org_live_secret_xyz789def... (40 chars)

Stored:
- client_id: Plain text (indexed)
- client_secret: SHA-256 hash only
- client_secret_prefix: First 12 chars (for indexing)
```

---

## 📊 Token Expiration Best Practices

| Token Type | Expiration | Use Case |
|------------|-----------|----------|
| User Login Token | 30 days | Web/mobile app users |
| OAuth Client Token | 90 days | Server-to-server integrations |
| Short-lived Token | 1 hour | High-security operations |
| Long-lived Token | 1 year | Trusted background services |

**Stripe Comparison:**
- Stripe API keys never expire (but can be revoked)
- Our implementation: **Configurable expiration with automatic enforcement**
- Better security with token rotation

---

## 🚀 Usage Examples

### JavaScript/Node.js
```javascript
// User login
const loginResponse = await fetch('https://api.example.com/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'SecurePass123!'
  })
});

const { access_token } = await loginResponse.json();

// Use token for API requests
const products = await fetch('https://api.example.com/api/v1/products', {
  headers: { 
    'Authorization': `Bearer ${access_token}`,
    'Accept': 'application/json'
  }
});
```

### Python
```python
import requests

# OAuth client credentials
response = requests.post('https://api.example.com/api/v1/oauth/token', json={
    'grant_type': 'client_credentials',
    'client_id': 'org_live_client_abc123xyz...',
    'client_secret': 'org_live_secret_xyz789def...',
    'scope': '*'
})

token = response.json()['access_token']

# Use token
headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}
products = requests.get('https://api.example.com/api/v1/products', headers=headers)
```

### PHP/Laravel
```php
use Illuminate\Support\Facades\Http;

// User login
$response = Http::post('https://api.example.com/api/v1/auth/login', [
    'email' => 'john@example.com',
    'password' => 'SecurePass123!'
]);

$token = $response->json()['access_token'];

// Use token
$products = Http::withToken($token)
    ->get('https://api.example.com/api/v1/products')
    ->json();
```

### cURL
```bash
# Get OAuth token
curl -X POST https://api.example.com/api/v1/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "org_live_client_abc123xyz...",
    "client_secret": "org_live_secret_xyz789def...",
    "scope": "*"
  }'

# Use token
curl -X GET https://api.example.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_2|xyz789abc..." \
  -H "Accept: application/json"
```

---

## 📁 Files Created/Modified

### New Files
- [`database/migrations/2026_03_14_104436_create_oauth_clients_table.php`](database/migrations/2026_03_14_104436_create_oauth_clients_table.php)
- [`app/Models/OAuthClient.php`](app/Models/OAuthClient.php)
- [`app/Http/Controllers/Auth/ClientCredentialsController.php`](app/Http/Controllers/Auth/ClientCredentialsController.php)
- `OAUTH_IMPLEMENTATION_GUIDE.md` (this file)

### Modified Files
- [`routes/api.php`](routes/api.php) - Updated all routes to use Sanctum
- [`config/sanctum.php`](config/sanctum.php) - Added token expiration configs
- [`app/Http/Controllers/Auth/AuthController.php`](app/Http/Controllers/Auth/AuthController.php) - Added token management methods

---

## ✅ Testing Checklist

- [ ] User can register and receive token (30-day expiration)
- [ ] User can login and receive token (30-day expiration)
- [ ] User can logout (current token revoked)
- [ ] User can logout-all (all tokens revoked)
- [ ] User can generate personal access tokens
- [ ] User can list all their tokens
- [ ] User can revoke specific tokens
- [ ] User can create OAuth clients (requires authentication)
- [ ] OAuth client can get access token (90-day expiration)
- [ ] OAuth token works for API requests
- [ ] Expired tokens are rejected
- [ ] Invalid client credentials are rejected
- [ ] All /api/v1/* routes require Sanctum authentication
- [ ] All legacy /api/* routes now use Sanctum (no APP_ACCESS_TOKEN)

---

## 🔧 Environment Configuration

Add these to your `.env` file:

```env
# Sanctum Token Expiration (in minutes)
SANCTUM_USER_TOKEN_EXPIRATION=43200      # 30 days for user login
SANCTUM_CLIENT_TOKEN_EXPIRATION=129600   # 90 days for OAuth clients
SANCTUM_EXPIRATION=10080                 # 7 days default

# Sanctum Token Prefix (for GitHub secret scanning)
SANCTUM_TOKEN_PREFIX=shulesoft_
```

---

## 📚 Additional Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [OAuth 2.0 RFC 6749](https://tools.ietf.org/html/rfc6749)
- [Stripe API Authentication](https://stripe.com/docs/api/authentication)

---

## 🎉 Implementation Complete!

All three requirements have been successfully implemented:

1. ✅ **All routes use Sanctum** - Removed legacy APP_ACCESS_TOKEN
2. ✅ **Token expiration configured** - Industry-standard best practices
3. ✅ **OAuth client credentials** - client_id/client_secret for API integrations

Your API now follows modern authentication standards similar to Stripe, Twilio, and other leading API providers.
