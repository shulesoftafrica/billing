# 🚀 Sanctum Implementation Checklist

## ✅ Implementation Status

All tasks completed successfully! Here's what was implemented:

### 1. Foundation ✓

- [x] Created [User.php](app/Models/User.php) model with `HasApiTokens` trait
- [x] Defined `belongsTo` relationship to Organization
- [x] Added `organization_id` to `$fillable` array
- [x] Added organization query scope

### 2. Legacy Auth Deprecation ✓

- [x] Created new `/api/v1/*` routes with Sanctum authentication
- [x] Applied `organization.scope` middleware to all v1 routes
- [x] Increased rate limiting to 60 requests/minute
- [x] Kept legacy routes for backward compatibility
- [x] Added public authentication routes (`/api/auth/*`)

### 3. Organization-Scoped Tokens ✓

- [x] Implemented token rotation on login (revokes old tokens)
- [x] Set 30-day expiration on all tokens
- [x] Added IP address and User Agent audit logging
- [x] Created [BaseApiController.php](app/Http/Controllers/BaseApiController.php) with helper methods:
  - `getOrganization()` - Get user's organization
  - `getOrganizationId()` - Get organization ID
  - `verifyOrganizationAccess()` - Validate organization access
  - `scopeToOrganization()` - Scope queries to organization
  - `hasAbility()` / `requireAbility()` - Check token abilities

### 4. Advanced Sanctum Configuration ✓

- [x] Updated [sanctum.php](config/sanctum.php):
  - Set global expiration to 43200 minutes (30 days)
  - Set token prefix to `shulesoft_` for secret scanning
- [x] Created migration to add `ip_address` and `user_agent` columns

### 5. Security Hardening ✓

- [x] Created [EnsureOrganizationScope.php](app/Http/Middleware/EnsureOrganizationScope.php) middleware
- [x] Automatic organization validation on all requests
- [x] Auto-injection of `organization_id` for create/update operations
- [x] Updated [PaymentController.php](app/Http/Controllers/Api/PaymentController.php) with:
  - `payments:read` ability checks
  - Organization-scoped queries
  - Extended from `BaseApiController`

---

## 🔧 Required Actions

### 1. Run Database Migration

```bash
php artisan migrate
```

This adds `ip_address` and `user_agent` columns to `personal_access_tokens` table.

### 2. Clear Configuration Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 3. Update Environment Variables (Optional)

Add to `.env`:

```env
# Sanctum Configuration
SANCTUM_EXPIRATION=43200          # 30 days in minutes
SANCTUM_TOKEN_PREFIX=shulesoft_    # Token prefix for GitHub secret scanning
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,yourdomain.com
```

---

## 🧪 Testing Checklist

### Test 1: User Registration

```bash
POST /api/auth/register
{
  "organization_id": 1,
  "name": "Test User",
  "email": "test@example.com",
  "password": "Test123!",
  "password_confirmation": "Test123!"
}

✓ Expected: 201 Created with access_token
✓ Token format: shulesoft_{id}|{random_string}
✓ Token has 30-day expiration
```

### Test 2: User Login

```bash
POST /api/auth/login
{
  "email": "test@example.com",
  "password": "Test123!"
}

✓ Expected: 200 OK with new access_token
✓ Old tokens should be revoked (token rotation)
✓ Check personal_access_tokens table for ip_address and user_agent
```

### Test 3: Protected Route Access

```bash
GET /api/v1/products
Authorization: Bearer {token}

✓ Expected: 200 OK with products from user's organization only
✓ Should not see products from other organizations
```

### Test 4: Organization Isolation

```bash
# As user from Organization 1, try to create product for Organization 2
POST /api/v1/products
Authorization: Bearer {org1_token}
{
  "organization_id": 2,
  "name": "Product",
  "product_type_id": 1
}

✓ Expected: 403 Forbidden
✓ Error: "You cannot create or modify resources for other organizations"
```

### Test 5: Payment Ability Check

```bash
GET /api/v1/payments?date_from=2026-01-01&date_to=2026-03-31
Authorization: Bearer {token_without_payments_read}

✓ Expected: 403 Forbidden (if token doesn't have 'payments:read' ability)
✓ With wildcard (*) ability: 200 OK
```

### Test 6: Auto Organization Injection

```bash
POST /api/v1/products
Authorization: Bearer {token}
{
  "name": "New Product",
  "product_type_id": 1
  # organization_id NOT provided
}

✓ Expected: 201 Created
✓ Product should have organization_id automatically set to user's organization
```

### Test 7: Legacy Compatibility

```bash
GET /api/products
Authorization: Bearer {APP_ACCESS_TOKEN}

✓ Expected: 200 OK (legacy routes still work)
✓ Works with old APP_ACCESS_TOKEN
```

### Test 8: Token Expiration

```bash
# Create a token with 1-minute expiration (for testing)
$token = $user->createToken('test', ['*'], now()->addMinute());

# Wait 2 minutes
# Try to use expired token
GET /api/v1/products
Authorization: Bearer {expired_token}

✓ Expected: 401 Unauthenticated
```

---

## 📋 Post-Implementation Checklist

### Database Verification

- [ ] Run `php artisan migrate`
- [ ] Verify `personal_access_tokens` has `ip_address` column
- [ ] Verify `personal_access_tokens` has `user_agent` column
- [ ] Check indexes on `organization_id` in all relevant tables

### Code Verification

- [ ] No syntax errors: `php artisan route:list` runs successfully
- [ ] User model exists at `app/Models/User.php`
- [ ] BaseApiController exists at `app/Http/Controllers/BaseApiController.php`
- [ ] EnsureOrganizationScope middleware registered in `bootstrap/app.php`
- [ ] AuthController has token rotation enabled
- [ ] PaymentController extends BaseApiController

### Route Verification

```bash
# List all routes to verify structure
php artisan route:list --path=api

# Should see:
# - POST /api/auth/register
# - POST /api/auth/login
# - GET  /api/v1/products (auth:sanctum, organization.scope)
# - GET  /api/products (app.access.token) - legacy
```

### Configuration Verification

- [ ] `config/sanctum.php` has expiration: 43200
- [ ] `config/sanctum.php` has token_prefix: 'shulesoft_'
- [ ] `.env` has SANCTUM_STATEFUL_DOMAINS configured (if using SPA)

---

## 🎯 Next Steps (Recommended)

### Immediate (Week 1)

1. **Test all authentication endpoints**
   - Register new users
   - Login/logout functionality
   - Token refresh behavior

2. **Migrate first API client to v1 routes**
   - Use `/api/v1/*` instead of `/api/*`
   - Update authentication to use Sanctum tokens
   - Test organization isolation

3. **Monitor token creation**
   - Check `personal_access_tokens` table
   - Verify `ip_address` and `user_agent` are being logged
   - Confirm tokens have `expires_at` set

### Short-term (Month 1)

4. **Create tokens with specific abilities**
   - Modify AuthController to accept `abilities` parameter
   - Create read-only tokens for reporting tools
   - Create write tokens for admin users

5. **Update other controllers**
   - Extend from `BaseApiController`
   - Add ability checks where appropriate
   - Use organization helper methods

6. **Document for developers**
   - API documentation with new v1 endpoints
   - Token abilities reference
   - Migration guide for existing clients

### Medium-term (Month 2-3)

7. **Performance optimization**
   - Add database indexes on `organization_id`
   - Implement query result caching
   - Monitor slow queries

8. **Security enhancements**
   - Implement token refresh mechanism
   - Add IP whitelisting for sensitive operations
   - Set up token usage monitoring

9. **Deprecation planning**
   - Notify clients about legacy route deprecation
   - Set deprecation date (e.g., 6 months)
   - Create migration timeline

### Long-term (Month 4+)

10. **Complete migration**
    - All clients using v1 routes
    - Remove legacy `APP_ACCESS_TOKEN` routes
    - Remove `AppAccessTokenMiddleware`

11. **Advanced features**
    - Organization admin dashboard
    - Token management UI
    - API usage analytics
    - Multi-factor authentication

---

## 🐛 Common Issues & Solutions

### Issue: "Class 'App\Models\User' not found"

**Cause:** User model not properly created or autoload cache outdated

**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Issue: Tokens don't have `shulesoft_` prefix

**Cause:** Config cache not cleared after updating sanctum.php

**Solution:**
```bash
php artisan config:clear
# Recreate tokens after clearing cache
```

### Issue: Organization validation always fails

**Cause:** User doesn't have `organization_id` set

**Solution:**
```sql
-- Check user's organization_id
SELECT id, email, organization_id FROM users WHERE email = 'user@example.com';

-- Update if missing
UPDATE users SET organization_id = 1 WHERE email = 'user@example.com';
```

### Issue: "Column 'ip_address' not found"

**Cause:** Migration not run

**Solution:**
```bash
php artisan migrate
# If already run:
php artisan migrate:status
```

### Issue: Ability checks fail with wildcard token

**Cause:** Using wrong method to check abilities

**Solution:**
```php
// ❌ Wrong
if ($token->abilities === ['*']) { ... }

// ✅ Correct
if ($token->can('payments:read')) { ... }
if ($request->user()->tokenCan('payments:read')) { ... }
```

---

## 📊 Monitoring & Metrics

### Key Metrics to Track

1. **Token Creation Rate**
   ```sql
   SELECT DATE(created_at) as date, COUNT(*) as tokens_created
   FROM personal_access_tokens
   GROUP BY DATE(created_at)
   ORDER BY date DESC;
   ```

2. **Active Tokens per Organization**
   ```sql
   SELECT u.organization_id, COUNT(pat.id) as active_tokens
   FROM personal_access_tokens pat
   JOIN users u ON pat.tokenable_id = u.id
   WHERE pat.tokenable_type = 'App\\Models\\User'
     AND (pat.expires_at IS NULL OR pat.expires_at > NOW())
   GROUP BY u.organization_id;
   ```

3. **Token Usage by IP Address**
   ```sql
   SELECT ip_address, COUNT(*) as token_count
   FROM personal_access_tokens
   WHERE ip_address IS NOT NULL
   GROUP BY ip_address
   ORDER BY token_count DESC
   LIMIT 20;
   ```

4. **Expired Tokens Cleanup**
   ```sql
   -- Find expired tokens
   SELECT COUNT(*) FROM personal_access_tokens
   WHERE expires_at IS NOT NULL AND expires_at < NOW();
   
   -- Delete expired tokens (add to scheduled job)
   DELETE FROM personal_access_tokens
   WHERE expires_at IS NOT NULL AND expires_at < NOW();
   ```

---

## 🎓 Training Resources

### For Developers

1. Read [SANCTUM_IMPLEMENTATION.md](SANCTUM_IMPLEMENTATION.md)
2. Review [authorization.md](resources/requirements/authorization.md)
3. Study [BaseApiController.php](app/Http/Controllers/BaseApiController.php) examples
4. Test with Postman/Insomnia collection

### For API Consumers

1. Migration guide from legacy to v1 routes
2. Token generation documentation
3. Ability-based permissions reference
4. Rate limiting guidelines

---

## ✅ Sign-off Checklist

Before marking implementation complete:

- [ ] All migrations run successfully
- [ ] No errors when running `php artisan route:list`
- [ ] User registration works via `/api/auth/register`
- [ ] User login works via `/api/auth/login`
- [ ] Token has correct prefix: `shulesoft_`
- [ ] Token rotation confirmed (old tokens deleted on login)
- [ ] Organization isolation tested and working
- [ ] Payment ability checks working
- [ ] IP address and user agent logged in database
- [ ] Legacy routes still functional (backward compatibility)
- [ ] Documentation updated and reviewed

---

## 📞 Support

If you encounter issues:

1. Check this checklist first
2. Review error logs: `storage/logs/laravel.log`
3. Verify database schema matches expected structure
4. Test with simple cURL requests before complex integrations
5. Check Laravel Sanctum documentation: https://laravel.com/docs/11.x/sanctum

---

**Implementation Date:** March 11, 2026
**Status:** ✅ Complete and Ready for Testing
**Next Review:** After first production deployment

---

*Good luck with your high-scale payment API! 🚀*
