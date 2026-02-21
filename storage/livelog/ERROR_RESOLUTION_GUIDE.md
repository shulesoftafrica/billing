# Laravel Server Error Resolution Guide
**Server**: 144.91.101.154:4647  
**Application Path**: /usr/share/nginx/html/billing  
**Date**: 2026-02-21  

## 🔴 Critical Errors Found

### Error 1: Wrong Database Connection
```
ERROR: relation "organizations" does not exist
Connection: pgsql, Host: 127.0.0.1, Port: 5996, Database: other_app
```

**Problem**: Server `.env` file has `DB_DATABASE=other_app` but should be `billing`

**Solution**:
```bash
# SSH into server
ssh -p 4647 ephraim@144.91.101.154

# Navigate to app directory
cd /usr/share/nginx/html/billing

# Check current database setting
grep "^DB_DATABASE=" .env

# Backup .env
sudo cp .env .env.backup

# Fix database name (choose one based on your setup)
sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=billing/' .env

# Or if using schema approach:
sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=shulesoft2024/' .env

# Verify the change
grep "^DB_DATABASE=" .env

# Clear config cache
php artisan config:clear
```

---

### Error 2: Cache Table Missing
```
ERROR: relation "cache" does not exist
```

**Problem**: Cache table not created in database

**Solution**:
```bash
# Create cache table migration
php artisan cache:table

# Run the migration
php artisan migrate --force

# Test cache
php artisan cache:clear
```

---

### Error 3: Missing Database Tables (organizations, etc.)
```
ERROR: relation "organizations" does not exist
```

**Problem**: Database migrations haven't been run

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Run all pending migrations
php artisan migrate --force

# If migrations fail, check database connection first:
php artisan tinker
# Then in tinker:
DB::connection()->getPdo();
exit
```

---

### Error 4: Schema "constant" Issue (Already Fixed Locally)
```
ERROR: schema "constant" does not exist
```

**Problem**: Old migration file on server references cross-schema foreign key

**Solution**: Deploy updated migration file
```bash
# On local machine, upload fixed migration
scp -P 4647 database/migrations/2024_01_01_000016_create_bank_accounts_table.php ephraim@144.91.101.154:/usr/share/nginx/html/billing/database/migrations/

# Then on server, reset this specific migration
php artisan migrate:refresh --path=/database/migrations/2024_01_01_000016_create_bank_accounts_table.php --force
```

---

## ✅ Complete Fix Procedure

### Step 1: Check Current Configuration
```bash
ssh -p 4647 ephraim@144.91.101.154
cd /usr/share/nginx/html/billing

# Check all DB settings
grep "^DB_" .env

# Check current database connection
php artisan tinker --execute="echo 'Connected to: ' . config('database.connections.pgsql.database');"
```

### Step 2: Fix Database Configuration
```bash
# Backup .env
sudo cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# Update database name
sudo nano .env
# Change: DB_DATABASE=other_app
# To:     DB_DATABASE=billing

# Clear cached config
php artisan config:clear
```

### Step 3: Verify Database Exists
```bash
# List all PostgreSQL databases
sudo -u postgres psql -l

# If 'billing' database doesn't exist, create it:
sudo -u postgres psql -c "CREATE DATABASE billing;"

# Check schema
sudo -u postgres psql -d billing -c "\dn"

# If using schema approach, verify 'billing' schema exists:
sudo -u postgres psql -d shulesoft2024 -c "\dn"
```

### Step 4: Deploy Updated Files
```bash
# On local machine - upload fixed migration
scp -P 4647 database/migrations/2024_01_01_000016_create_bank_accounts_table.php ephraim@144.91.101.154:/usr/share/nginx/html/billing/database/migrations/
```

### Step 5: Run Migrations
```bash
# On server
cd /usr/share/nginx/html/billing

# Check migration status
php artisan migrate:status

# Create cache table
php artisan cache:table

# Run all migrations
php artisan migrate --force

# Verify tables exist
php artisan tinker --execute="
echo 'Organizations table exists: ' . (Schema::hasTable('organizations') ? 'YES' : 'NO') . PHP_EOL;
echo 'Cache table exists: ' . (Schema::hasTable('cache') ? 'YES' : 'NO') . PHP_EOL;
echo 'Bank accounts table exists: ' . (Schema::hasTable('bank_accounts') ? 'YES' : 'NO') . PHP_EOL;
"
```

### Step 6: Clear All Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 7: Verify Fixes
```bash
# Test database connection and queries
php artisan tinker --execute="
echo 'Testing database connection...' . PHP_EOL;
echo 'Organizations count: ' . \App\Models\Organization::count() . PHP_EOL;
echo 'Users count: ' . \App\Models\User::count() . PHP_EOL;
echo 'SUCCESS: All tables accessible!' . PHP_EOL;
"

# Monitor logs for new errors
tail -f storage/logs/laravel.log
```

---

## 🔍 Troubleshooting

### If migrations fail:
```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Check database connection
sudo -u postgres psql -d billing -c "SELECT version();"

# Check permissions
sudo -u postgres psql -d billing -c "\du"
```

### If cache errors persist:
```bash
# Use database cache driver instead
sudo nano .env
# Change: CACHE_STORE=file
# To:     CACHE_STORE=database

# Clear and rebuild
php artisan config:clear
php artisan cache:clear
```

### If organization errors persist:
```bash
# Verify the table exists in correct location
php artisan tinker --execute="
echo 'Current connection: ' . config('database.default') . PHP_EOL;
echo 'Database: ' . config('database.connections.pgsql.database') . PHP_EOL;
echo 'Schema: ' . config('database.connections.pgsql.schema') . PHP_EOL;
DB::select('SELECT * FROM information_schema.tables WHERE table_name = ?', ['organizations']);
"
```

---

## 📊 Verification Checklist

After running all fixes, verify:

- [ ] `.env` has correct `DB_DATABASE` value
- [ ] Can connect to database: `php artisan tinker --execute="DB::connection()->getPdo();"`
- [ ] All migrations ran: `php artisan migrate:status`
- [ ] Organizations table exists and accessible
- [ ] Cache table exists and working: `php artisan cache:clear`
- [ ] No errors in CustomerController: Test API endpoint
- [ ] Application runs without errors: `tail -f storage/logs/laravel.log`

---

## 🚀 Quick Command Summary

```bash
# SSH to server
ssh -p 4647 ephraim@144.91.101.154
cd /usr/share/nginx/html/billing

# Fix database config
sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=billing/' .env
php artisan config:clear

# Run migrations
php artisan cache:table
php artisan migrate --force

# Clear caches
php artisan optimize:clear

# Test
php artisan tinker --execute="echo \App\Models\Organization::count();"
```

---

## 📝 Notes

1. The local codebase already has the fix for the `constant.refer_banks` issue
2. Make sure to deploy updated migration files to the server
3. The server was connecting to wrong database `other_app` - this is the root cause
4. After fixing, monitor logs: `tail -f /usr/share/nginx/html/billing/storage/logs/laravel.log`

---

## Need Help?

If errors persist after following this guide:
1. Download the latest log: `scp -P 4647 ephraim@144.91.101.154:/usr/share/nginx/html/billing/storage/logs/laravel.log storage/livelog/laravel-after-fix.log`
2. Check what changed in the error messages
3. Verify database schema and connection settings
