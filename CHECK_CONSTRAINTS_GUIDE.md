# Check Constraints Implementation Guide for Laravel 12

## Problem
Laravel 12 Blueprint doesn't support the `check()` method. The error `BadMethodCallException: Method Illuminate\Database\Schema\Blueprint::check does not exist` occurs when trying to add database check constraints.

## Solution
Use raw SQL via `DB::statement()` to add check constraints after table creation.

## Implementation

### Option 1: Dedicated Migration File (RECOMMENDED)
Use the migration: `2026_01_23_000000_add_check_constraints.php`

This migration adds all check constraints using raw SQL statements:

```php
DB::statement('ALTER TABLE table_name ADD CONSTRAINT constraint_name CHECK (condition)');
```

**Advantages:**
- Keeps constraint logic separate and organized
- Runs after all tables are created
- Easy to modify or remove constraints
- Clear separation of concerns

**To run:**
```bash
php artisan migrate
```

### Option 2: Per-Table Approach
Add raw SQL in each table's migration file:

```php
Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
    $table->string('name');
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->string('status')->default('active');
    $table->timestampsTz();
});

// Add constraint after table creation in same migration
Schema::table('customers', function (Blueprint $table) {
    DB::statement('ALTER TABLE customers ADD CONSTRAINT customer_status_check CHECK (status IN (\'active\', \'inactive\', \'suspended\'))');
});
```

**Advantages:**
- Keeps constraint with table definition
- More intuitive for developers

**Disadvantages:**
- Requires modifying each migration file
- Can cause issues if table doesn't exist yet

### Option 3: Model Validation Layer
Implement validation in models using Laravel's validation rules:

```php
class Customer extends Model
{
    protected $fillable = ['organization_id', 'name', 'email', 'phone', 'status'];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $validator = Validator::make(['status' => $model->status], [
                'status' => 'required|in:active,inactive,suspended',
            ]);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        });
    }
}
```

**Advantages:**
- Application-level validation
- Provides user-friendly error messages
- Can include additional business logic

**Disadvantages:**
- Doesn't prevent direct SQL inserts
- More code to maintain

## Check Constraints Mapped

| Table | Constraint | Condition |
|-------|-----------|-----------|
| organizations | org_status_check | status IN ('active', 'inactive', 'suspended') |
| users | user_sex_check | sex IN ('M', 'F', 'O') |
| customers | customer_status_check | status IN ('active', 'inactive', 'suspended') |
| products | product_status_check | status IN ('active', 'inactive', 'archived') |
| price_plans | subscription_type_check | subscription_type IN ('daily','weekly','monthly','quarterly','semi_annually','yearly') |
| price_plans | pp_amount_check | amount >= 0 |
| subscriptions | subscription_status_check | status IN ('active', 'pending', 'cancelled', 'expired') |
| invoices | invoice_status_check | status IN ('draft', 'issued', 'paid', 'overdue', 'cancelled') |
| invoices | invoices_amounts_check | subtotal >= 0 AND tax_total >= 0 AND total >= 0 |
| invoice_items | quantity_check | quantity > 0 |
| invoice_items | prices_check | unit_price >= 0 AND total >= 0 |
| tax_rates | rate_check | rate >= 0 AND rate <= 100 |
| invoice_taxes | invoice_taxes_amount_check | amount >= 0 |
| payments | payment_status_check | notification_status IN ('pending', 'processing', 'completed', 'failed', 'cancelled') |
| payments | payments_amount_check | amount > 0 |
| refunds | refund_status_check | status IN ('pending', 'approved', 'rejected', 'processed') |
| refunds | refunds_amount_check | amount > 0 |
| organization_payment_gateway_integrations | opgi_status_check | status IN ('active', 'inactive', 'suspended') |
| configurations | env_check | env IN ('testing', 'production') |

## Running the Migration

```bash
# Run all pending migrations
php artisan migrate

# Run specific migration
php artisan migrate --path=database/migrations/2026_01_23_000000_add_check_constraints.php

# Rollback
php artisan migrate:rollback
```

## Testing Check Constraints

You can verify constraints are active in the database:

### PostgreSQL
```sql
SELECT constraint_name, check_clause
FROM information_schema.check_constraints
WHERE table_name = 'customers';
```

### MySQL
```sql
SELECT CONSTRAINT_NAME, CHECK_CLAUSE
FROM INFORMATION_SCHEMA.CHECK_CONSTRAINTS
WHERE TABLE_NAME = 'customers';
```

## Important Notes

1. **Database Support**: Check constraints are supported by PostgreSQL, MySQL 8.0.16+, and SQLite.

2. **Application Validation**: Always validate data at the application level as well, since:
   - Database constraints can fail with cryptic error messages
   - Not all applications connect directly to the database
   - Validation provides better UX

3. **Order of Migrations**: The dedicated constraint migration should run after all table migrations are completed.

4. **Performance**: Check constraints are validated on every INSERT/UPDATE, so complex constraints may impact performance on large datasets.

5. **Rollback Safety**: The down() method uses `DROP CONSTRAINT IF EXISTS` to safely handle removal.
