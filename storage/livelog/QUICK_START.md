# Quick Fix Guide - Laravel Server Errors

## 🎯 Quick Start

### Option 1: Automatic Fix (Recommended)
```bash
# 1. Upload files
scp -P 4647 database/migrations/2024_01_01_000016_create_bank_accounts_table.php ephraim@144.91.101.154:/tmp/
scp -P 4647 storage/livelog/fix_all_errors.sh ephraim@144.91.101.154:/tmp/

# 2. SSH into server and run
ssh -p 4647 ephraim@144.91.101.154
chmod +x /tmp/fix_all_errors.sh
sudo /tmp/fix_all_errors.sh
```

### Option 2: Manual Fix (5 Commands)
```bash
# SSH into server
ssh -p 4647 ephraim@144.91.101.154
cd /usr/share/nginx/html/billing

# 1. Fix database name in .env
sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=billing/' .env
php artisan config:clear

# 2. Run migrations
php artisan cache:table
php artisan migrate --force

# 3. Clear caches
php artisan optimize:clear

# 4. Verify
php artisan tinker --execute="echo \App\Models\Organization::count();"
```

---

## 📋 Errors Fixed

### ✅ Error 1: Wrong Database Connection
- **Was**: `DB_DATABASE=other_app` 
- **Fixed to**: `DB_DATABASE=billing`

### ✅ Error 2: Missing Cache Table
- **Solution**: Created cache table via migration

### ✅ Error 3: Missing Organizations Table  
- **Solution**: Ran all pending migrations

### ✅ Error 4: Schema "constant" Issue
- **Solution**: Updated migration file (removed cross-schema FK)

---

## 📁 Files Created

All files are in: `storage/livelog/`

1. **fix_all_errors.sh** - Automated fix script (recommended)
2. **ERROR_RESOLUTION_GUIDE.md** - Detailed step-by-step guide
3. **fix_server_errors.sh** - Alternative fix script
4. **verify_database.sql** - SQL queries for verification
5. **QUICK_START.md** - This file

---

## 🔍 Verify Fix Worked

```bash
ssh -p 4647 ephraim@144.91.101.154
cd /usr/share/nginx/html/billing

# Check no recent errors
tail -50 storage/logs/laravel.log | grep ERROR

# Should see no new "relation does not exist" errors
```

---

## ⚡ One-Line Command

If you trust me completely:
```bash
scp -P 4647 database/migrations/2024_01_01_000016_create_bank_accounts_table.php storage/livelog/fix_all_errors.sh ephraim@144.91.101.154:/tmp/ && ssh -p 4647 ephraim@144.91.101.154 "chmod +x /tmp/fix_all_errors.sh && sudo /tmp/fix_all_errors.sh"
```

---

## 🆘 If Still Having Issues

1. **Download latest log**:
   ```bash
   scp -P 4647 ephraim@144.91.101.154:/usr/share/nginx/html/billing/storage/logs/laravel.log storage/livelog/laravel-after-fix.log
   ```

2. **Check what errors remain**:
   ```bash
   grep "ERROR" storage/livelog/laravel-after-fix.log | tail -20
   ```

3. **Common issues**:
   - PostgreSQL not running: `sudo systemctl status postgresql`
   - Wrong permissions: `sudo chown -R www-data:www-data /usr/share/nginx/html/billing/storage`
   - Wrong user in DB: Check .env has correct DB_USERNAME

---

## 📞 Need More Help?

Send me:
1. Output of: `ssh -p 4647 ephraim@144.91.101.154 "cd /usr/share/nginx/html/billing && grep ^DB_ .env"`
2. Latest log: `storage/livelog/laravel-after-fix.log`
3. Migration status: Output of `php artisan migrate:status`
