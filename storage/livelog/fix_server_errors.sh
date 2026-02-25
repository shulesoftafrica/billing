#!/bin/bash
# Script to fix Laravel server errors
# Run this on the remote server: ssh -p 4647 ephraim@144.91.101.154

echo "=== Laravel Server Error Fix Script ==="
echo "Server: 144.91.101.154"
echo "Path: /usr/share/nginx/html/billing"
echo ""

# Navigate to application directory
cd /usr/share/nginx/html/billing

echo "Step 1: Checking current .env configuration..."
echo "---"
grep -E "^DB_" .env
echo ""

echo "Step 2: Backup current .env file..."
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "Backup created!"
echo ""

echo "Step 3: Fix database configuration in .env"
echo "Current DB_DATABASE value:"
grep "^DB_DATABASE=" .env
echo ""
echo "Please verify the correct database name should be one of:"
echo "  - billing (if using database named 'billing')"
echo "  - shulesoft2024 (if using database named 'shulesoft2024' with schema 'billing')"
echo ""
echo "To fix manually, run:"
echo "  sudo sed -i 's/^DB_DATABASE=.*/DB_DATABASE=billing/' .env"
echo "  OR"
echo "  sudo nano .env  # and change DB_DATABASE value"
echo ""

echo "Step 4: Check current database and schema..."
psql -h 127.0.0.1 -U postgres -d billing -c "\dt" 2>/dev/null || echo "Cannot connect to database 'billing'"
echo ""

echo "Step 5: Run migrations..."
echo "First, check if migrations table exists:"
php artisan migrate:status
echo ""

echo "Step 6: Clear all Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ""

echo "Step 7: Run fresh migrations (this will create all tables)..."
echo "WARNING: This might ask for confirmation if tables exist"
php artisan migrate --force
echo ""

echo "Step 8: Verify cache table was created..."
php artisan cache:table
php artisan migrate --force
echo ""

echo "Step 9: Test cache functionality..."
php artisan cache:clear
echo "Cache cleared successfully!"
echo ""

echo "Step 10: Verify organizations table exists..."
php artisan tinker --execute="echo \App\Models\Organization::count() . ' organizations found';"
echo ""

echo "=== Fix Complete ==="
echo "Check the Laravel logs again: tail -f /usr/share/nginx/html/billing/storage/logs/laravel.log"
