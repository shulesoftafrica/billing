#!/bin/bash
# =============================================================================
# Laravel Server Error Fix - One-Step Solution
# =============================================================================
# This script fixes all errors found in laravel.log
# Run on server: ssh -p 4647 ephraim@144.91.101.154
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Laravel Server Error Fix ===${NC}"
echo "Started at: $(date)"
echo ""

# Navigate to application directory
APP_DIR="/usr/share/nginx/html/billing"
cd "$APP_DIR" || { echo -e "${RED}Error: Cannot access $APP_DIR${NC}"; exit 1; }

echo -e "${YELLOW}Current directory: $(pwd)${NC}"
echo ""

# =============================================================================
# STEP 1: Check and backup .env
# =============================================================================
echo -e "${GREEN}STEP 1: Backing up .env file...${NC}"
if [ -f .env ]; then
    sudo cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    echo "✓ Backup created"
else
    echo -e "${RED}✗ .env file not found!${NC}"
    exit 1
fi
echo ""

# =============================================================================
# STEP 2: Check current database configuration
# =============================================================================
echo -e "${GREEN}STEP 2: Checking current database configuration...${NC}"
echo "Current DB settings:"
grep "^DB_" .env || echo "No DB_ settings found"
echo ""

CURRENT_DB=$(grep "^DB_DATABASE=" .env | cut -d= -f2)
echo -e "Current database: ${YELLOW}$CURRENT_DB${NC}"

if [ "$CURRENT_DB" = "other_app" ]; then
    echo -e "${RED}✗ ERROR: Database is set to 'other_app' - This is WRONG!${NC}"
    echo -e "${YELLOW}Fixing database name to 'billing'...${NC}"
    sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=billing/' .env
    echo "✓ Fixed: DB_DATABASE=billing"
    
    # Clear config cache
    php artisan config:clear
    echo "✓ Config cache cleared"
elif [ "$CURRENT_DB" = "billing" ] || [ "$CURRENT_DB" = "shulesoft2024" ]; then
    echo -e "${GREEN}✓ Database name looks correct: $CURRENT_DB${NC}"
else
    echo -e "${YELLOW}⚠ Warning: Unexpected database name: $CURRENT_DB${NC}"
    echo "Please verify this is correct before continuing."
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted by user"
        exit 1
    fi
fi
echo ""

# =============================================================================
# STEP 3: Test database connection
# =============================================================================
echo -e "${GREEN}STEP 3: Testing database connection...${NC}"
php artisan tinker --execute="
try {
    \$pdo = DB::connection()->getPdo();
    echo '✓ Database connection successful' . PHP_EOL;
    echo 'Database: ' . config('database.connections.pgsql.database') . PHP_EOL;
    echo 'Schema: ' . config('database.connections.pgsql.schema') . PHP_EOL;
} catch (Exception \$e) {
    echo '✗ Database connection failed: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
" || { echo -e "${RED}✗ Cannot connect to database${NC}"; exit 1; }
echo ""

# =============================================================================
# STEP 4: Move fixed migration file
# =============================================================================
echo -e "${GREEN}STEP 4: Updating migration file...${NC}"
if [ -f /tmp/2024_01_01_000016_create_bank_accounts_table.php ]; then
    sudo cp /tmp/2024_01_01_000016_create_bank_accounts_table.php database/migrations/
    sudo chown www-data:www-data database/migrations/2024_01_01_000016_create_bank_accounts_table.php
    echo "✓ Migration file updated"
else
    echo -e "${YELLOW}⚠ Fixed migration file not found in /tmp/${NC}"
    echo "  Continuing with existing migration file..."
fi
echo ""

# =============================================================================
# STEP 5: Check migration status
# =============================================================================
echo -e "${GREEN}STEP 5: Checking migration status...${NC}"
php artisan migrate:status || echo "Migration table might not exist yet"
echo ""

# =============================================================================
# STEP 6: Create cache table migration
# =============================================================================
echo -e "${GREEN}STEP 6: Creating cache table...${NC}"
php artisan cache:table
echo "✓ Cache table migration created"
echo ""

# =============================================================================
# STEP 7: Run all migrations
# =============================================================================
echo -e "${GREEN}STEP 7: Running migrations...${NC}"
echo "This will create all missing tables..."

php artisan migrate --force 2>&1 | tee /tmp/migration_output.log

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migrations completed successfully${NC}"
else
    echo -e "${YELLOW}⚠ Some migrations may have failed. Check output above.${NC}"
    echo "Continuing with verification..."
fi
echo ""

# =============================================================================
# STEP 8: Verify critical tables exist
# =============================================================================
echo -e "${GREEN}STEP 8: Verifying tables...${NC}"
php artisan tinker --execute="
\$tables = ['organizations', 'cache', 'bank_accounts', 'users', 'customers'];
foreach (\$tables as \$table) {
    \$exists = Schema::hasTable(\$table);
    echo sprintf('%-20s: %s', ucfirst(\$table), \$exists ? '✓ EXISTS' : '✗ MISSING') . PHP_EOL;
}
"
echo ""

# =============================================================================
# STEP 9: Clear all caches
# =============================================================================
echo -e "${GREEN}STEP 9: Clearing all caches...${NC}"
php artisan config:clear
echo "  ✓ Config cache cleared"

php artisan route:clear
echo "  ✓ Route cache cleared"

php artisan view:clear
echo "  ✓ View cache cleared"

php artisan cache:clear
echo "  ✓ Application cache cleared"

php artisan optimize:clear 2>&1 | grep -v "ERROR" || echo "  ✓ Optimization cache cleared"
echo ""

# =============================================================================
# STEP 10: Test application
# =============================================================================
echo -e "${GREEN}STEP 10: Testing application...${NC}"

# Test organizations table
echo "Testing organizations table..."
php artisan tinker --execute="
try {
    \$count = \App\Models\Organization::count();
    echo '✓ Organizations table accessible: ' . \$count . ' records' . PHP_EOL;
} catch (Exception \$e) {
    echo '✗ Cannot access organizations table: ' . \$e->getMessage() . PHP_EOL;
}
"

# Test cache
echo "Testing cache functionality..."
php artisan tinker --execute="
try {
    Cache::put('test_key', 'test_value', 60);
    \$value = Cache::get('test_key');
    echo '✓ Cache working: ' . \$value . PHP_EOL;
    Cache::forget('test_key');
} catch (Exception \$e) {
    echo '✗ Cache not working: ' . \$e->getMessage() . PHP_EOL;
}
"
echo ""

# =============================================================================
# STEP 11: Check for remaining errors
# =============================================================================
echo -e "${GREEN}STEP 11: Checking recent log entries...${NC}"
if [ -f storage/logs/laravel.log ]; then
    echo "Last 20 lines of laravel.log:"
    tail -20 storage/logs/laravel.log
    echo ""
    
    # Count recent errors
    RECENT_ERRORS=$(grep -c "ERROR" storage/logs/laravel.log | tail -100 || echo "0")
    echo -e "Recent errors in log: ${YELLOW}$RECENT_ERRORS${NC}"
else
    echo "No log file found (this might be good - fresh start!)"
fi
echo ""

# =============================================================================
# SUMMARY
# =============================================================================
echo -e "${GREEN}=============================================${NC}"
echo -e "${GREEN}          FIX COMPLETED${NC}"
echo -e "${GREEN}=============================================${NC}"
echo ""
echo "Completed at: $(date)"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Test your API endpoints"
echo "2. Monitor logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "3. Check application in browser"
echo ""
echo -e "${YELLOW}If errors persist:${NC}"
echo "- Check database connectivity: php artisan tinker"
echo "- Verify .env settings: cat .env | grep DB_"
echo "- Review migration status: php artisan migrate:status"
echo "- Check permissions: ls -la storage/"
echo ""
echo -e "${GREEN}Done!${NC}"
