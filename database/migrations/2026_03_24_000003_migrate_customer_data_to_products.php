<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration populates customers.product_id by:
     * 1. Finding product via customer's subscriptions -> price_plan -> product
     * 2. Finding product via customer's invoices -> invoice_items -> price_plan -> product
     * 3. Creating a default product if no relationship found
     */
    public function up(): void
    {
        // Strategy 1: Map via subscriptions
        DB::statement("
            UPDATE customers c
            INNER JOIN subscriptions s ON s.customer_id = c.id
            INNER JOIN price_plans pp ON pp.id = s.price_plan_id
            SET c.product_id = pp.product_id
            WHERE c.product_id IS NULL
            AND s.id = (
                SELECT MIN(id) FROM subscriptions WHERE customer_id = c.id LIMIT 1
            )
        ");

        // Strategy 2: Map via invoices for customers still without product_id
        DB::statement("
            UPDATE customers c
            INNER JOIN invoices i ON i.customer_id = c.id
            INNER JOIN invoice_items ii ON ii.invoice_id = i.id
            INNER JOIN price_plans pp ON pp.id = ii.price_plan_id
            SET c.product_id = pp.product_id
            WHERE c.product_id IS NULL
            AND i.id = (
                SELECT MIN(id) FROM invoices WHERE customer_id = c.id LIMIT 1
            )
        ");

        // Strategy 3: For remaining customers, create/assign default product per organization
        $orphanedCustomers = DB::table('customers')
            ->whereNull('product_id')
            ->get();

        foreach ($orphanedCustomers as $customer) {
            // Check if organization has a default product
            $defaultProduct = DB::table('products')
                ->where('organization_id', $customer->organization_id)
                ->where('name', 'LIKE', '%Default%')
                ->orWhere('product_code', 'DEFAULT')
                ->first();

            if (!$defaultProduct) {
                // Create default product for this organization
                $productId = DB::table('products')->insertGetId([
                    'organization_id' => $customer->organization_id,
                    'product_type_id' => DB::table('product_types')->first()?->id ?? 1,
                    'name' => 'Default Product',
                    'product_code' => 'DEFAULT-' . $customer->organization_id,
                    'description' => 'Auto-generated default product for legacy customers',
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $productId = $defaultProduct->id;
            }

            // Assign customer to product
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['product_id' => $productId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all product_id back to null
        DB::table('customers')->update(['product_id' => null]);
        
        // Optionally delete auto-generated default products
        DB::table('products')
            ->where('product_code', 'LIKE', 'DEFAULT-%')
            ->delete();
    }
};
