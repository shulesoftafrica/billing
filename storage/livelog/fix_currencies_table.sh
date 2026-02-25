#!/bin/bash
# =============================================================================
# Quick Fix for Missing Currencies Table Error
# =============================================================================
# Run on server: ssh -p 4647 ephraim@144.91.101.154
# =============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== Quick Fix for Missing Currencies Table ===${NC}"
echo "Started at: $(date)"
echo ""

# Navigate to application directory
cd /usr/share/nginx/html/billing

# 1. Check current database configuration
echo -e "${YELLOW}Step 1: Current database configuration${NC}"
grep "^DB_DATABASE=" .env
echo ""

# 2. Test database connection
echo -e "${YELLOW}Step 2: Testing database connection${NC}"
php artisan tinker --execute="
try {
    \$db = DB::connection()->getDatabaseName();
    echo '✓ Connected to database: ' . \$db . PHP_EOL;
} catch (Exception \$e) {
    echo '✗ Database connection failed: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"
echo ""

# 3. Check if currencies table exists
echo -e "${YELLOW}Step 3: Checking currencies table${NC}"
php artisan tinker --execute="
\$exists = Schema::hasTable('currencies');
echo 'Currencies table exists: ' . (\$exists ? '✓ YES' : '✗ NO') . PHP_EOL;
if (!\$exists) {
    echo 'Need to run migrations!' . PHP_EOL;
}
"
echo ""

# 4. Run migrations
echo -e "${YELLOW}Step 4: Running all migrations${NC}"
echo "This will create all missing tables including currencies..."
php artisan migrate --force
echo ""

# 5. Verify currencies table now exists
echo -e "${YELLOW}Step 5: Verifying currencies table${NC}"
php artisan tinker --execute="
\$exists = Schema::hasTable('currencies');
echo 'Currencies table exists: ' . (\$exists ? '✓ YES' : '✗ NO') . PHP_EOL;

if (\$exists) {
    \$count = DB::table('currencies')->count();
    echo 'Currency records: ' . \$count . PHP_EOL;
    
    if (\$count == 0) {
        echo PHP_EOL . '⚠ Warning: currencies table is empty!' . PHP_EOL;
        echo 'You need to seed currency data or create currencies via API' . PHP_EOL;
    }
}
"
echo ""

# 6. Clear caches
echo -e "${YELLOW}Step 6: Clearing caches${NC}"
php artisan config:clear
php artisan cache:clear
echo "✓ Caches cleared"
echo ""

echo -e "${GREEN}=== Fix Complete ===${NC}"
echo ""
echo "If currencies table is empty, you can:"
echo "1. Run a seeder: php artisan db:seed --class=CurrencySeeder"
echo "2. Or create currencies via POST /api/currencies endpoint"
echo ""
echo "Example currencies to create:"
echo "  - USD (United States Dollar)"
echo "  - EUR (Euro)"
echo "  - TZS (Tanzanian Shilling)"
echo "  - KES (Kenyan Shilling)"
echo ""
