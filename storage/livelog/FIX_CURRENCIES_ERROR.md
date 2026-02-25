# Fix Missing Currencies Table Error

## 🔴 Error
```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "currencies" does not exist
```

## ✅ Solution

The currencies table hasn't been created yet. Run migrations on the server.

---

## 🚀 Quick Fix (3 Commands)

### Option 1: SSH and Run Commands
```bash
# SSH into server
ssh -p 4647 ephraim@144.91.101.154

# Navigate to app
cd /usr/share/nginx/html/billing

# Run migrations
php artisan migrate --force
```

### Option 2: Use Fix Script
```bash
# SSH into server
ssh -p 4647 ephraim@144.91.101.154

# Pull latest code (if fix_currencies_table.sh was pushed)
cd /usr/share/nginx/html/billing
git pull origin main

# Run fix script
chmod +x storage/livelog/fix_currencies_table.sh
./storage/livelog/fix_currencies_table.sh
```

---

## 📋 What Gets Fixed

Running `php artisan migrate --force` will create:
- ✅ currencies table
- ✅ countries table  
- ✅ organizations table
- ✅ cache table
- ✅ All other missing tables

---

## 🔍 Verify Fix Worked

```bash
# Check currencies table exists
php artisan tinker --execute="echo Schema::hasTable('currencies') ? 'YES' : 'NO';"

# Count currency records
php artisan tinker --execute="echo \App\Models\Currency::count();"
```

---

## 📊 Seed Currency Data (If Needed)

If currencies table is empty, you need to add currencies:

### Method 1: Via API (Recommended)
```bash
# Create USD currency
curl -X POST https://your-domain.com/api/currencies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "United States Dollar",
    "code": "USD",
    "symbol": "$"
  }'

# Create TZS currency
curl -X POST https://your-domain.com/api/currencies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tanzanian Shilling",
    "code": "TZS",
    "symbol": "TSh"
  }'
```

### Method 2: Via Tinker
```bash
php artisan tinker

# Then inside tinker:
\App\Models\Currency::create([
    'name' => 'United States Dollar',
    'code' => 'USD',
    'symbol' => '$'
]);

\App\Models\Currency::create([
    'name' => 'Tanzanian Shilling',
    'code' => 'TZS',
    'symbol' => 'TSh'
]);

\App\Models\Currency::create([
    'name' => 'Euro',
    'code' => 'EUR',
    'symbol' => '€'
]);

exit
```

### Method 3: Create Seeder
```bash
# Create seeder
php artisan make:seeder CurrencySeeder

# Edit database/seeders/CurrencySeeder.php
# Add currencies in the run() method

# Run seeder
php artisan db:seed --class=CurrencySeeder
```

---

## 🔄 Common Currencies to Add

| Currency | Code | Symbol | Name |
|----------|------|--------|------|
| US Dollar | USD | $ | United States Dollar |
| Euro | EUR | € | Euro |
| Shilling | TZS | TSh | Tanzanian Shilling |
| Shilling | KES | KSh | Kenyan Shilling |
| Pound | GBP | £ | British Pound |
| Rand | ZAR | R | South African Rand |

---

## 🆘 If Still Having Issues

### Check Database Connection
```bash
php artisan tinker --execute="var_dump(DB::connection()->getDatabaseName());"
```

### Check Migration Status
```bash
php artisan migrate:status
```

### Check .env Database Settings
```bash
grep "^DB_" .env
```

Should see:
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=billing
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

---

## 💡 Root Cause

The error occurs when:
1. Migrations haven't been run after deployment
2. Database is empty/new
3. Wrong database is being used

**Solution**: Always run `php artisan migrate` after deploying code!

---

## ✅ Success Checklist

After fix, verify:
- [ ] No "relation currencies does not exist" errors
- [ ] Can query currencies: `php artisan tinker --execute="echo \App\Models\Currency::count();"`
- [ ] At least one currency exists in the database
- [ ] Products/Price Plans can reference currencies
- [ ] Application loads without errors

---

## 📝 Prevention

Add to deployment script:
```bash
# Always run migrations on deployment
php artisan migrate --force

# Clear caches
php artisan optimize:clear

# Verify critical tables exist
php artisan tinker --execute="
echo 'Currencies: ' . \App\Models\Currency::count() . PHP_EOL;
echo 'Organizations: ' . \App\Models\Organization::count() . PHP_EOL;
"
```

---

**Quick command**: 
```bash
ssh -p 4647 ephraim@144.91.101.154 "cd /usr/share/nginx/html/billing && php artisan migrate --force && php artisan cache:clear"
```
