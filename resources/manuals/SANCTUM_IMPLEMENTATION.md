# High-Scale Organization-Scoped Sanctum Implementation Summary

## 🎯 Overview

Your payment API has been successfully modernized from a mixed Legacy/Sanctum authentication system to a **professional, high-scale, organization-scoped Sanctum authentication architecture**.

---

## ✅ Completed Tasks

### Task 1: Fixed Missing Foundation ✓

**Created:** [app/Models/User.php](app/Models/User.php)

- ✅ Uses `Laravel\Sanctum\HasApiTokens` trait
- ✅ Defines `belongsTo` relationship to Organization model
- ✅ `organization_id` included in `$fillable` array
- ✅ Added `scopeForOrganization()` query scope for organization filtering
- ✅ Proper casts for datetime fields and password hashing

**Key Features:**
```php
// Relationship
$user->organization; // BelongsTo Organization

// Query scope
User::forOrganization($orgId)->get();
```

---

### Task 2: Deprecated Legacy Authentication ✓

**Changes Made:**

1. **New API Routes Structure** - [routes/api.php](routes/api.php)
   - ✅ Created `/api/v1/*` routes with `auth:sanctum` middleware
   - ✅ Applied `organization.scope` middleware to all v1 routes
   - ✅ Increased rate limiting to `throttle:60,1`
   - ✅ Kept legacy routes for backward compatibility (marked deprecated)
   - ✅ Added public `/api/auth/*` routes for login/register

2. **Route Organization:**
   ```
   PUBLIC:
   POST /api/auth/register
   POST /api/auth/login
   POST /api/auth/logout          (requires auth:sanctum)
   POST /api/auth/logout-all      (requires auth:sanctum)
   GET  /api/auth/me              (requires auth:sanctum)

   API V1 (Sanctum + Organization Scope):
   /api/v1/products
   /api/v1/invoices
   /api/v1/customers
   /api/v1/payments
   ... (all resource routes)

   LEGACY (Deprecated - Backward Compatibility):
   /api/products
   /api/invoices
   ... (same routes with app.access.token)
   ```

---

### Task 3: Organization-Scoped Tokens ✓

**Updated:** [app/Http/Controllers/Auth/AuthController.php](app/Http/Controllers/Auth/AuthController.php)

#### Key Improvements:

1. **Token Rotation on Login:**
   ```php
   // Before creating new token, revoke all existing tokens
   $user->tokens()->delete();
   ```

2. **30-Day Expiration:**
   ```php
   $token = $user->createToken(
       'auth_token',
       ['*'],
       now()->addDays(30)
   )->plainTextToken;
   ```

3. **Audit Logging:**
   ```php
   // Stores IP address and User Agent for security tracking
   protected function logTokenAudit(User $user, Request $request)
   {
       $latestToken = $user->tokens()->latest('id')->first();
       $latestToken->update([
           'ip_address' => $request->ip(),
           'user_agent' => $request->userAgent(),
       ]);
   }
   ```

4. **Organization ID Validation:**
   - Registration requires `organization_id`
   - Validates organization exists before user creation
   - Checks for duplicate email within organization
   - Returns organization context in auth responses

**Created:** [app/Http/Controllers/BaseApiController.php](app/Http/Controllers/BaseApiController.php)

#### Helper Methods Available to All Controllers:

```php
// Get organization instance
$organization = $this->getOrganization();

// Get organization ID
$orgId = $this->getOrganizationId();

// Verify organization access (throws 403 if mismatch)
$this->verifyOrganizationAccess($organizationId);

// Scope query to organization
$products = $this->scopeToOrganization(Product::query())->get();

// Check token ability
if ($this->hasAbility('payments:read')) { ... }

// Require ability (throws 403 if missing)
$this->requireAbility('payments:write', 'Custom error message');
```

---

### Task 4: Advanced Sanctum Configuration ✓

**Updated:** [config/sanctum.php](config/sanctum.php)

1. **Global 30-Day Expiration:**
   ```php
   'expiration' => env('SANCTUM_EXPIRATION', 43200), // 30 days in minutes
   ```

2. **Token Prefix for Secret Scanning:**
   ```php
   'token_prefix' => env('SANCTUM_TOKEN_PREFIX', 'shulesoft_'),
   ```
   
   This enables GitHub Secret Scanning to detect committed tokens.

**Created:** [database/migrations/2026_03_11_185400_add_audit_columns_to_personal_access_tokens_table.php](database/migrations/2026_03_11_185400_add_audit_columns_to_personal_access_tokens_table.php)

Adds audit logging columns to `personal_access_tokens`:
- `ip_address` (VARCHAR 45) - Stores IPv4 and IPv6 addresses
- `user_agent` (TEXT) - Stores browser/client information

**Run Migration:**
```bash
php artisan migrate
```

---

### Task 5: Security Hardening ✓

**Created:** [app/Http/Middleware/EnsureOrganizationScope.php](app/Http/Middleware/EnsureOrganizationScope.php)

#### Automatic Organization Validation:

1. **Route Parameter Validation:**
   ```php
   // If route has {organization_id}, validates it matches user's org
   GET /api/v1/invoices/{organization_id}/123
   ```

2. **Request Body Validation:**
   ```php
   // Prevents creating resources for other organizations
   POST /api/v1/products
   {
     "organization_id": 999  // ← Blocked if not user's org
   }
   ```

3. **Query Parameter Validation:**
   ```php
   // Prevents querying other organization's data
   GET /api/v1/products?organization_id=999  // ← Blocked
   ```

4. **Auto-Injection:**
   ```php
   // Automatically adds organization_id if missing
   POST /api/v1/products
   {
     "name": "Product"
     // organization_id automatically added
   }
   ```

**Updated:** [app/Http/Controllers/Api/PaymentController.php](app/Http/Controllers/Api/PaymentController.php)

#### Ability-Based Access Control:

```php
// Requires 'payments:read' ability to view payment records
$this->requireAbility('payments:read', 'Permission denied');

// Organization-scoped queries
$payments = Payment::with(['customer', 'paymentGateway'])
    ->whereHas('customer', function ($query) use ($organizationId) {
        $query->where('organization_id', $organizationId);
    })
    ->get();
```

**Registered:** [bootstrap/app.php](bootstrap/app.php)

Added middleware alias:
```php
'organization.scope' => EnsureOrganizationScope::class,
```

---

## 🔐 How Token Abilities Work

### Creating Tokens with Specific Abilities

```php
// Full access token (default)
$token = $user->createToken('auth_token', ['*'], now()->addDays(30));

// Read-only token
$token = $user->createToken('readonly_token', ['payments:read', 'invoices:read']);

// Payments-only token
$token = $user->createToken('payment_token', ['payments:read', 'payments:write']);

// Admin token
$token = $user->createToken('admin_token', [
    'payments:read',
    'payments:write',
    'invoices:read',
    'invoices:write',
    'customers:read',
    'customers:write',
]);
```

### Checking Abilities in Controllers

```php
// Option 1: Manual check
if (!$request->user()->tokenCan('payments:write')) {
    abort(403, 'Insufficient permissions');
}

// Option 2: Using BaseApiController helper
$this->requireAbility('payments:write');

// Option 3: Conditional logic
if ($this->hasAbility('payments:delete')) {
    // Allow delete
}
```

### Suggested Ability Naming Convention

```
{resource}:{action}

Examples:
- payments:read
- payments:write
- payments:delete
- invoices:read
- invoices:write
- invoices:cancel
- customers:read
- customers:write
- products:read
- products:write
```

---

## 🚀 Migration Guide

### For New API Clients

**Use the new v1 endpoints:**

```bash
# 1. Register a user
POST /api/auth/register
{
  "organization_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}

# Response:
{
  "message": "User registered successfully",
  "access_token": "shulesoft_1|abcdefghijk...",
  "token_type": "Bearer",
  "expires_in": 43200,
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  }
}

# 2. Use token in subsequent requests
GET /api/v1/products
Authorization: Bearer shulesoft_1|abcdefghijk...
```

### For Existing Clients (Backward Compatibility)

**Legacy routes still work:**

```bash
# Old routes continue working with APP_ACCESS_TOKEN
GET /api/products
Authorization: Bearer {APP_ACCESS_TOKEN}
```

**⚠️ Important:** Legacy routes will be removed in a future version. Migrate to `/api/v1/*` routes.

---

## 🔒 Security Improvements

### Before (Legacy System)

❌ Single shared `APP_ACCESS_TOKEN` for all requests
❌ No user/organization context
❌ No token expiration
❌ No token revocation
❌ No audit logging
❌ No ability-based permissions

### After (New Sanctum System)

✅ **User-specific tokens** - Each user has their own tokens
✅ **Organization-scoped** - Automatic organization validation
✅ **30-day expiration** - Tokens auto-expire for security
✅ **Token rotation** - Old tokens revoked on login
✅ **Audit logging** - IP address + User Agent tracked
✅ **Ability-based permissions** - Fine-grained access control
✅ **Secret scanning support** - Token prefix for detection
✅ **Rate limiting** - 60 requests per minute per token

---

## 📊 Database Changes

### New Migration

Run this to add audit columns:

```bash
php artisan migrate
```

**Adds to `personal_access_tokens`:**
- `ip_address` - Tracks where token was created
- `user_agent` - Tracks client/browser information

### Updated Tables

**personal_access_tokens:**
```
id
tokenable_type
tokenable_id
name
token (SHA-256 hashed)
abilities (JSON)
last_used_at
expires_at           ← Now actually used (30 days)
ip_address           ← NEW
user_agent           ← NEW
created_at
updated_at
```

---

## 🎯 Key Configuration Files

| File | Purpose | Changes |
|------|---------|---------|
| [app/Models/User.php](app/Models/User.php) | User model | **CREATED** - HasApiTokens trait |
| [config/sanctum.php](config/sanctum.php) | Sanctum config | Expiration: 43200 min, Prefix: shulesoft_ |
| [routes/api.php](routes/api.php) | API routes | Added v1 routes with Sanctum |
| [bootstrap/app.php](bootstrap/app.php) | Middleware registration | Added organization.scope alias |
| [app/Http/Controllers/Auth/AuthController.php](app/Http/Controllers/Auth/AuthController.php) | Authentication | Token rotation + expiration + audit |
| [app/Http/Controllers/BaseApiController.php](app/Http/Controllers/BaseApiController.php) | **CREATED** - Base controller | Organization helpers |
| [app/Http/Middleware/EnsureOrganizationScope.php](app/Http/Middleware/EnsureOrganizationScope.php) | **CREATED** - Middleware | Organization validation |
| [app/Http/Controllers/Api/PaymentController.php](app/Http/Controllers/Api/PaymentController.php) | Payment endpoints | Ability checks + org scoping |

---

## 🧪 Testing the New System

### 1. Test User Registration

```bash
curl -X POST http://your-api.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 1,
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

### 2. Test User Login

```bash
curl -X POST http://your-api.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123!"
  }'
```

### 3. Test Authenticated Request

```bash
curl -X GET http://your-api.com/api/v1/products \
  -H "Authorization: Bearer shulesoft_1|your-token-here"
```

### 4. Test Organization Isolation

```bash
# User from Org 1 trying to create product for Org 2
curl -X POST http://your-api.com/api/v1/products \
  -H "Authorization: Bearer {token-from-org-1}" \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 2,
    "name": "Product"
  }'

# Expected: 403 Forbidden
```

### 5. Test Token Abilities

```bash
# Create a read-only token (modify AuthController temporarily)
$token = $user->createToken('readonly', ['payments:read']);

# Then try to create a payment (should fail with 403)
curl -X POST http://your-api.com/api/v1/payments \
  -H "Authorization: Bearer {readonly-token}"
```

---

## 📈 Performance Considerations

### Organization Scoping

The middleware automatically validates organization access on **every request**. For high-scale applications:

1. **Database Indexing:**
   Ensure these indexes exist:
   ```sql
   -- Users table
   CREATE INDEX idx_users_organization_id ON users(organization_id);
   
   -- Other resource tables
   CREATE INDEX idx_products_organization_id ON products(organization_id);
   CREATE INDEX idx_invoices_organization_id ON invoices(organization_id);
   CREATE INDEX idx_customers_organization_id ON customers(organization_id);
   ```

2. **Eager Loading:**
   Always eager load organization in controllers:
   ```php
   $user = auth()->user()->load('organization');
   ```

3. **Query Optimization:**
   Use query scopes from BaseApiController:
   ```php
   // Efficient organization filtering
   $products = $this->scopeToOrganization(Product::query())->get();
   ```

---

## 🔄 Future Enhancements

### Recommended Next Steps

1. **Refresh Tokens:**
   - Implement short-lived access tokens (1 hour)
   - Long-lived refresh tokens (30 days)
   - `/api/auth/refresh` endpoint

2. **Token Monitoring Dashboard:**
   - View active tokens per user
   - Revoke tokens remotely
   - Token usage analytics

3. **Organization Admin Management:**
   - Create users in your organization
   - Assign abilities to users
   - Manage organization settings

4. **API Rate Limiting per Organization:**
   - Different rate limits per organization tier
   - Track API usage by organization

5. **Webhook Authentication:**
   - Signature verification for webhooks
   - Organization-specific webhook secrets

---

## 📝 Environment Variables

Add to your `.env` file:

```env
# Sanctum Configuration
SANCTUM_EXPIRATION=43200          # 30 days in minutes
SANCTUM_TOKEN_PREFIX=shulesoft_    # Token prefix for secret scanning

# Stateful domains (for SPA)
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,yourdomain.com
```

---

## 🆘 Troubleshooting

### Issue: "Unauthenticated" on v1 routes

**Solution:**
1. Check token is sent in header: `Authorization: Bearer {token}`
2. Verify token hasn't expired (30 days from creation)
3. Check token exists in `personal_access_tokens` table
4. Ensure Sanctum middleware is registered in `bootstrap/app.php`

### Issue: "Access denied to organization's resources"

**Solution:**
1. Verify user's `organization_id` matches requested resource
2. Check organization.scope middleware is applied
3. Ensure resource has `organization_id` column

### Issue: "This action requires '{ability}' permission"

**Solution:**
1. Token was created without required ability
2. Create new token with correct abilities:
   ```php
   $token = $user->createToken('name', ['payments:read', 'payments:write']);
   ```

### Issue: Token doesn't have prefix

**Solution:**
1. Clear config cache: `php artisan config:clear`
2. Verify `SANCTUM_TOKEN_PREFIX=shulesoft_` in `.env`
3. New tokens will have prefix; existing tokens won't change

---

## 📚 Additional Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Token Abilities](https://laravel.com/docs/11.x/sanctum#token-abilities)
- [API Authentication Best Practices](https://owasp.org/www-project-api-security/)

---

## ✨ Summary

Your payment API now has:

✅ **Professional authentication** - Industry-standard Sanctum implementation
✅ **Organization isolation** - Users can only access their organization's data
✅ **Token security** - Rotation, expiration, and audit logging
✅ **Ability-based permissions** - Fine-grained access control
✅ **Backward compatibility** - Legacy routes still work
✅ **High-scale ready** - Optimized for performance and security

**Next Steps:**
1. Run migration: `php artisan migrate`
2. Test authentication endpoints
3. Migrate API clients to `/api/v1/*` routes
4. Create tokens with appropriate abilities
5. Plan deprecation of legacy `APP_ACCESS_TOKEN`

---

*Implementation completed: March 11, 2026*
*Documentation: Ready for production*
{
    "message": "OAuth client created successfully",
    "client": {
        "id": 1,
        "name": "Production API Client",
        "client_id": "org_live_client_ZE9HizNKnNzcQ9ZGOlfSieWlvDimu3jH",
        "client_secret": "org_live_secret_GCNpI1J6wbPh3LS0IoUVS8WhqXjH0Ob2m4elud5x",
        "environment": "live",
        "allowed_scopes": [
            "*"
        ],
        "expires_at": null,
        "created_at": "2026-03-18T20:38:10.000000Z"
    },
    "warning": "Store the client_secret securely. It will not be shown again."
}

{
    "access_token": "30|shulesoft_mZIOW59Bm27r4FWOUJ1ozDldUaRf5JFLWpUqZTGW35bd9610",
    "token_type": "Bearer",
    "expires_in": 7776000,
    "scope": "*",
    "organization_id": 1
}