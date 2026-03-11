# Authorization & Authentication Assessment

## Executive Summary

This application implements a **dual authentication system** using:
1. **Laravel Sanctum** for user-based personal access tokens
2. **Custom Organization API Keys** for organization-level authentication
3. **Legacy APP_ACCESS_TOKEN** for backward compatibility

---

## 1. Laravel Sanctum Implementation

### 1.1 Sanctum Configuration

**File:** `config/sanctum.php`

```php
'expiration' => null,  // Tokens DO NOT expire by default
'guard' => ['web'],
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

**Key Settings:**
- ✅ **No Global Expiration**: Tokens are permanent unless individually set
- ✅ **Token Prefix**: Can be configured via environment variable
- ✅ **Stateful Domains**: Configured for localhost and production domains

### 1.2 Personal Access Tokens Table

**Migration:** `2026_01_17_225003_create_personal_access_tokens_table.php`

**Schema:**
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->string('tokenable_type');        // Polymorphic: 'App\Models\User'
    $table->unsignedBigInteger('tokenable_id'); // User ID
    $table->string('name');                  // Token name (e.g., 'auth_token')
    $table->string('token', 80)->unique();   // SHA-256 hashed token (64 chars)
    $table->json('abilities')->nullable();   // Permissions/scopes
    $table->timestampTz('last_used_at')->nullable();
    $table->timestampTz('expires_at')->nullable()->index(); // Individual expiration
    $table->timestampsTz();
});
```

**Indexes:**
- `['tokenable_type', 'tokenable_id']` - Fast lookup by user
- `token` - Unique index for authentication
- `expires_at` - Efficient expiration checks

### 1.3 Token Generation Process

**File:** `app/Http/Controllers/Auth/AuthController.php`

#### Registration Flow
```php
public function register(Request $request) {
    // 1. Validate user data
    $validated = $request->validate([...]);
    
    // 2. Create user with hashed password
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => $validated['role'] ?? 'user',
    ]);
    
    // 3. Generate Sanctum token
    $token = $user->createToken('auth_token')->plainTextToken;
    
    // 4. Return plain text token (ONLY shown once)
    return response()->json([
        'access_token' => $token,  // Format: "1|xxxxxxxxxxx"
        'token_type' => 'Bearer',
    ], 201);
}
```

#### Login Flow
```php
public function login(Request $request) {
    // 1. Find user by email
    $user = User::where('email', $request->email)->first();
    
    // 2. Verify password
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([...]);
    }
    
    // 3. Optional: Revoke old tokens (commented out)
    // $user->tokens()->delete();
    
    // 4. Create new token
    $token = $user->createToken('auth_token')->plainTextToken;
    
    // 5. Return token
    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 200);
}
```

**Token Format:** `{token_id}|{plain_text_token}`
- Example: `1|abcdefghijklmnopqrstuvwxyz1234567890`
- Token ID: Used for database lookup
- Plain Text: 40-character random string
- **Stored as SHA-256 hash** in `personal_access_tokens.token`

#### Token Revocation
```php
// Logout (revoke current token only)
public function logout(Request $request) {
    $request->user()->currentAccessToken()->delete();
}

// Logout from all devices (revoke all tokens)
public function logoutAll(Request $request) {
    $request->user()->tokens()->delete();
}
```

### 1.4 Token Lifecycle

```
┌─────────────┐
│ User Logins │
└──────┬──────┘
       │
       ▼
┌──────────────────────────────────┐
│ createToken('auth_token')        │
│ - Generates 40-char random token │
│ - Hashes with SHA-256            │
│ - Stores in personal_access_     │
│   tokens table                    │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Plain text token returned ONCE   │
│ Format: {id}|{plain_text}        │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Client stores token              │
│ Sends in Authorization header:   │
│ Bearer {token}                    │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Sanctum validates on each request│
│ - Extracts token ID              │
│ - Looks up in database           │
│ - Verifies hash matches          │
│ - Checks expires_at (if set)     │
│ - Updates last_used_at           │
└──────────────────────────────────┘
```

---

## 2. User Model & Relationships

### 2.1 User Model Status

**⚠️ CRITICAL FINDING:** The `User` model file **DOES NOT EXIST** in `app/Models/User.php`

**Evidence:**
- Referenced in `AuthController.php`
- Referenced in `CreateToken.php` command
- Referenced in `Organization.php` model relationships
- Users table migration exists
- **But model file is missing**

**Required Implementation:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'organization_id',
        'name',
        'phone',
        'email',
        'role',
        'sex',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organization that the user belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
```

### 2.2 Users Table Schema

**Migration:** `2024_01_01_000002_create_users_table.php`

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')
          ->constrained('organizations')
          ->onDelete('cascade');  // User deleted when org deleted
    $table->string('name');
    $table->string('phone')->nullable();
    $table->string('email')->nullable();
    $table->string('role')->nullable();
    $table->char('sex', 1)->nullable();
    $table->timestampTz('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestampsTz();
    
    // Unique constraints
    $table->unique(['organization_id', 'email'], 'unique_org_email');
    $table->unique(['organization_id', 'phone'], 'unique_org_phone');
});
```

**Key Features:**
- ✅ **Multi-tenancy**: Users scoped to organizations
- ✅ **Unique Emails per Org**: Same email can exist in different organizations
- ✅ **Cascade Delete**: Users deleted when organization is deleted
- ✅ **Role-based Access**: Role field for permissions

---

## 3. Organization-User-Token Relationship

### 3.1 Entity Relationship Diagram

```
┌─────────────────────┐
│   Organizations     │
│                     │
│ - id                │
│ - name              │
│ - email             │
│ - phone             │
│ - currency          │
│ - country_id        │
│ - status            │
└──────────┬──────────┘
           │ 1
           │ has many
           │ N
           ▼
┌─────────────────────┐         ┌──────────────────────────┐
│   Users             │ 1    N  │ personal_access_tokens   │
│                     ├─────────┤                          │
│ - id                │         │ - id                     │
│ - organization_id ──┘ creates │ - tokenable_type (User)  │
│ - name                        │ - tokenable_id (user_id) │
│ - email                       │ - token (hashed)         │
│ - phone                       │ - name                   │
│ - role                        │ - abilities              │
│ - password                    │ - last_used_at           │
│                               │ - expires_at             │
└───────────────────────────────┴──────────────────────────┘

Polymorphic Relationship:
- tokenable_type = 'App\Models\User'
- tokenable_id = user.id
```

### 3.2 How Tokens Recognize Users & Organizations

#### Step 1: Token Authentication
```php
// Sanctum middleware extracts token from header
$token = $request->bearerToken();  // "1|abcd1234..."

// Splits token
[$id, $plainText] = explode('|', $token, 2);

// Looks up in database
$tokenModel = PersonalAccessToken::find($id);

// Verifies hash
hash_equals(hash('sha256', $plainText), $tokenModel->token);
```

#### Step 2: User Resolution
```php
// Sanctum retrieves the user
$user = $tokenModel->tokenable;  // Polymorphic relationship

// User ID: $user->id
// Organization ID: $user->organization_id
```

#### Step 3: Organization Context
```php
// After authentication, controllers can access:
$request->user();                    // User model
$request->user()->organization_id;   // Organization ID
$request->user()->organization;      // Organization model

// Example controller usage:
public function index(Request $request) {
    $organizationId = $request->user()->organization_id;
    
    $products = Product::where('organization_id', $organizationId)->get();
    
    return response()->json($products);
}
```

**✅ YES - Tokens CAN recognize users and organizations:**
- Token → User (via `tokenable_id`)
- User → Organization (via `organization_id`)
- Full organizational context available in every request

---

## 4. Token Expiration Strategy

### 4.1 Current Configuration

**Global Expiration: NONE**
```php
// config/sanctum.php
'expiration' => null,
```

This means:
- ❌ Tokens **NEVER** expire automatically
- ✅ Tokens remain valid until manually revoked
- ⚠️ Security risk for compromised tokens

### 4.2 Per-Token Expiration

The `personal_access_tokens` table has an `expires_at` column:

```php
$table->timestampTz('expires_at')->nullable()->index();
```

**How to set individual expiration:**

```php
// Create token that expires in 30 days
$token = $user->createToken('auth_token', ['*'], now()->addDays(30));

// Create token that expires in 7 days
$token = $user->createToken('auth_token', ['*'], now()->addWeek());

// Create permanent token (current behavior)
$token = $user->createToken('auth_token');
```

**Current Implementation:**
```php
// AuthController.php - NO EXPIRATION SET
$token = $user->createToken('auth_token')->plainTextToken;
```

### 4.3 Expiration Check Flow

```php
// Sanctum's HasApiTokens trait
protected function findToken($token) {
    $model = PersonalAccessToken::where('token', hash('sha256', $token))
                                ->first();
    
    // Automatic expiration check
    if ($model && $model->expires_at && $model->expires_at->isPast()) {
        return null;  // Expired token = authentication fails
    }
    
    return $model;
}
```

### 4.4 Recommended Expiration Strategy

```php
// For regular users (mobile apps, web apps)
$token = $user->createToken('auth_token', ['*'], now()->addDays(30));

// For admin sessions
$token = $user->createToken('admin_token', ['*'], now()->addHours(12));

// For API integrations (longer-lived)
$token = $user->createToken('api_token', ['*'], now()->addYear());

// For testing/development (short-lived)
$token = $user->createToken('test_token', ['*'], now()->addHour());
```

---

## 5. Dual Authentication System

### 5.1 Three Authentication Methods

The application supports **THREE** authentication methods:

#### Method 1: APP_ACCESS_TOKEN (Legacy)
```php
// File: .env
APP_ACCESS_TOKEN=your-secret-token-here

// Usage:
Authorization: Bearer your-secret-token-here
```

**Characteristics:**
- ❌ Single shared token for everyone
- ❌ No user/organization context
- ❌ No expiration
- ❌ No revocation
- ⚠️ Security risk

#### Method 2: Laravel Sanctum Personal Access Tokens
```php
// Generate via /auth/login
POST /api/auth/login
{ "email": "user@example.com", "password": "secret" }

// Response:
{ "access_token": "1|abcd1234..." }

// Usage:
Authorization: Bearer 1|abcd1234...
```

**Characteristics:**
- ✅ Tied to specific user
- ✅ Organization context via user->organization_id
- ✅ Can expire (if configured)
- ✅ Can be revoked
- ✅ Tracks last_used_at
- ✅ Secure (SHA-256 hashing)

#### Method 3: Organization API Keys
```php
// Model: OrganizationApiKey
// Format: org_{environment}_{40_random_chars}
// Example: org_live_abc123xyz789...

// Storage: SHA-256 hashed in organization_api_keys table
```

**Characteristics:**
- ✅ Organization-scoped
- ✅ Test/Live environments
- ✅ Can expire (expires_at field)
- ✅ Can be revoked (status field)
- ✅ Tracks last_used_at
- ✅ Name/description for management

### 5.2 Middleware Authentication Flow

**File:** `app/Http/Middleware/MultiAuthMiddleware.php`

```php
public function handle(Request $request, Closure $next) {
    $token = $request->bearerToken();
    
    // 1. Try APP_ACCESS_TOKEN (legacy)
    if (hash_equals(env('APP_ACCESS_TOKEN'), $token)) {
        return $next($request);
    }
    
    // 2. Try Sanctum authentication
    if ($request->user('sanctum')) {
        return $next($request);
    }
    
    // 3. Authentication failed
    return response()->json(['error' => 'invalid_access_token'], 401);
}
```

**Current Routes:**
```php
// routes/api.php
Route::middleware(['app.access.token', 'throttle:30,1'])->group(function () {
    // All protected API routes use AppAccessTokenMiddleware
    // NOT using Sanctum middleware
});
```

**⚠️ FINDING:** Routes currently use `app.access.token` middleware (APP_ACCESS_TOKEN only), not Sanctum.

---

## 6. Token Security Analysis

### 6.1 Security Strengths

✅ **SHA-256 Hashing**
- Plain text tokens never stored in database
- Computationally infeasible to reverse

✅ **Polymorphic Architecture**
- Tokens can belong to different model types
- Flexible authorization system

✅ **Token Abilities/Scopes**
- Fine-grained permissions possible
- Can limit token capabilities

✅ **Unique Constraints**
- Email unique per organization
- Token uniqueness enforced

✅ **Cascade Deletion**
- Users deleted when organization deleted
- Maintains referential integrity

### 6.2 Security Weaknesses

❌ **No Expiration by Default**
- Tokens valid forever
- Compromised tokens remain active indefinitely

❌ **APP_ACCESS_TOKEN Still Active**
- Single shared secret
- No user/org context
- Cannot track who used it
- Cannot revoke individually

❌ **No Token Refresh Mechanism**
- No short-lived access tokens with refresh tokens
- All-or-nothing approach

❌ **No Rate Limiting Per User**
- Rate limiting at route level only
- Cannot limit per-user API usage

❌ **No Token Rotation**
- Old tokens not automatically revoked on login
- Multiple active tokens per user

### 6.3 Recommended Security Improvements

1. **Set Token Expiration**
```php
// AuthController.php
$token = $user->createToken('auth_token', ['*'], now()->addDays(30))
              ->plainTextToken;
```

2. **Implement Token Rotation**
```php
public function login(Request $request) {
    $user = User::where('email', $request->email)->first();
    
    // Revoke old tokens
    $user->tokens()->delete();
    
    // Create new token
    $token = $user->createToken('auth_token', ['*'], now()->addDays(30))
                  ->plainTextToken;
}
```

3. **Remove APP_ACCESS_TOKEN**
```php
// Migrate all clients to Sanctum tokens
// Then remove from .env and middleware
```

4. **Add Token Abilities**
```php
// Create admin token
$token = $user->createToken('admin_token', ['admin:read', 'admin:write']);

// Create read-only token
$token = $user->createToken('readonly_token', ['read']);

// Check in controller
if (!$request->user()->tokenCan('admin:write')) {
    abort(403, 'Insufficient permissions');
}
```

5. **Implement Refresh Tokens**
```php
// Short-lived access token (1 hour)
$accessToken = $user->createToken('access', ['*'], now()->addHour());

// Long-lived refresh token (30 days)
$refreshToken = $user->createToken('refresh', ['refresh'], now()->addDays(30));

// Return both
return [
    'access_token' => $accessToken->plainTextToken,
    'refresh_token' => $refreshToken->plainTextToken,
    'expires_in' => 3600,
];
```

---

## 7. Organization API Keys System

### 7.1 OrganizationApiKey Model

**File:** `app/Models/OrganizationApiKey.php`

```php
class OrganizationApiKey extends Model {
    protected $fillable = [
        'organization_id',
        'name',              // "Production API Key"
        'key_prefix',        // "org_live_"
        'key_hash',          // SHA-256 hash
        'environment',       // "test" or "live"
        'last_used_at',
        'expires_at',
        'status',            // "active" or "revoked"
    ];
    
    public function isActive(): bool {
        return $this->status === 'active' 
            && (!$this->expires_at || !$this->expires_at->isPast());
    }
    
    public function revoke(): bool {
        $this->status = 'revoked';
        return $this->save();
    }
}
```

### 7.2 Key Format

```
org_{environment}_{40_random_chars}

Examples:
org_live_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8
org_test_z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2
```

**Parts:**
1. Prefix: `org_`
2. Environment: `live` or `test`
3. Random Key: 40 characters (base62: a-zA-Z0-9)

**Total Length:** 49 characters (vs Sanctum's variable length)

### 7.3 Comparison: Sanctum vs Organization API Keys

| Feature | Sanctum Tokens | Organization API Keys |
|---------|----------------|----------------------|
| **Tied to** | User | Organization |
| **Format** | `{id}\|{40chars}` | `org_{env}_{40chars}` |
| **Storage** | personal_access_tokens | organization_api_keys |
| **Hashing** | SHA-256 | SHA-256 |
| **Expiration** | Optional (expires_at) | Optional (expires_at) |
| **Revocation** | Delete record | Update status='revoked' |
| **Environment** | N/A | Test/Live separation |
| **Use Case** | User sessions | API integrations |
| **Context** | User + Organization | Organization only |

---

## 8. Complete Authentication Matrix

### 8.1 Authentication Methods Comparison

| Method | User Context | Org Context | Expiration | Revokable | Best For |
|--------|-------------|-------------|------------|-----------|----------|
| APP_ACCESS_TOKEN | ❌ | ❌ | ❌ | ❌ | **DEPRECATED** |
| Sanctum Token | ✅ | ✅ (via user) | ✅ Optional | ✅ | User sessions, mobile apps |
| Organization API Key | ❌ | ✅ | ✅ Optional | ✅ | B2B integrations, webhooks |

### 8.2 Recommended Usage Strategy

**For End Users (Web/Mobile Apps):**
```
Use: Sanctum Personal Access Tokens
Why: Full user context, secure, manageable
Expiration: 30 days
Rotation: Yes (revoke on login)
```

**For API Integrations (B2B):**
```
Use: Organization API Keys
Why: Organization-scoped, test/live separation
Expiration: 1 year (configurable)
Rotation: Manual
```

**For Internal Services:**
```
Use: Service-specific Sanctum tokens
Why: Trackable, revokable, scoped abilities
Expiration: No expiration or 1 year
```

---

## 9. Implementation Checklist

### 9.1 Critical Missing Components

- [ ] **Create User Model** (`app/Models/User.php`)
  - Must extend `Authenticatable`
  - Must use `HasApiTokens` trait
  - Must define organization relationship

- [ ] **Update Routes to Use Sanctum**
  ```php
  // routes/api.php
  Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
      // Protected routes
  });
  ```

- [ ] **Create Auth Routes**
  ```php
  Route::post('/auth/register', [AuthController::class, 'register']);
  Route::post('/auth/login', [AuthController::class, 'login']);
  Route::post('/auth/logout', [AuthController::class, 'logout'])
       ->middleware('auth:sanctum');
  ```

- [ ] **Set Token Expiration**
  ```php
  // AuthController.php
  $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
  ```

- [ ] **Remove APP_ACCESS_TOKEN**
  - Remove from `.env`
  - Remove `AppAccessTokenMiddleware`
  - Update all API clients

### 9.2 Security Enhancements

- [ ] Implement token rotation on login
- [ ] Add refresh token mechanism
- [ ] Set up token abilities/scopes
- [ ] Add per-user rate limiting
- [ ] Implement IP whitelisting for organization API keys
- [ ] Add audit logging for token usage
- [ ] Set up token expiration monitoring
- [ ] Implement automated token cleanup

---

## 10. Summary

### Current State
- ✅ Sanctum installed and configured
- ✅ Personal access tokens table exists
- ✅ Users table with organization foreign key
- ✅ AuthController with login/register logic
- ✅ Organization API keys system
- ❌ **User model missing** (critical)
- ❌ Token expiration not configured
- ❌ Routes use legacy APP_ACCESS_TOKEN
- ❌ No token rotation
- ⚠️ Mixed authentication methods

### Token Recognition Capabilities
- **YES** - Tokens can recognize users via `tokenable_id`
- **YES** - Tokens can recognize organizations via `user->organization_id`
- **YES** - Full context available: `$request->user()->organization`

### Expiration Status
- **Global:** No expiration (config: `null`)
- **Individual:** Can be set per token (not currently implemented)
- **Organization API Keys:** Support expires_at field
- **Recommendation:** Set 30-day expiration for user tokens

### Relationships
```
Organization (1) ──┬── (N) Users
                   ├── (N) Organization API Keys
                   └── (N) Customers, Products, etc.

User (1) ────── (N) Personal Access Tokens (Sanctum)
```

### Action Required
1. **CRITICAL:** Create `app/Models/User.php` with HasApiTokens trait
2. **HIGH:** Migrate routes from APP_ACCESS_TOKEN to Sanctum
3. **HIGH:** Set token expiration (30 days recommended)
4. **MEDIUM:** Implement token rotation
5. **MEDIUM:** Add refresh token mechanism
6. **LOW:** Remove APP_ACCESS_TOKEN after migration

---

## Appendix A: Token Generation Command

**File:** `app/Console/Commands/CreateToken.php`

```bash
# Create token for user ID 1
php artisan token:create 1

# Output
New token created for user user@example.com:
1|abcdefghijklmnopqrstuvwxyz1234567890
```

**Use Cases:**
- Testing API authentication
- Creating service account tokens
- Generating long-lived integration tokens
- Development/debugging

---

*Last Updated: March 11, 2026*
*Assessment Status: Complete*
*Next Review: After User model creation*