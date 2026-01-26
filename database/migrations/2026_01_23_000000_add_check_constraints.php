<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add check constraints using raw SQL for each table
        
        // Organizations table - status check
        DB::statement('ALTER TABLE organizations ADD CONSTRAINT org_status_check CHECK (status IN (\'active\', \'inactive\', \'suspended\'))');
        
        // Users table - sex check
        DB::statement('ALTER TABLE users ADD CONSTRAINT user_sex_check CHECK (sex IN (\'M\', \'F\', \'O\'))');
        
        // Customers table - status check
        DB::statement('ALTER TABLE customers ADD CONSTRAINT customer_status_check CHECK (status IN (\'active\', \'inactive\', \'suspended\'))');
        
        // Products table - status check
        DB::statement('ALTER TABLE products ADD CONSTRAINT product_status_check CHECK (status IN (\'active\', \'inactive\', \'archived\'))');
        
        // Price Plans table - subscription type and amount checks
        DB::statement('ALTER TABLE price_plans ADD CONSTRAINT subscription_type_check CHECK (subscription_type IN (\'daily\',\'weekly\',\'monthly\',\'quarterly\',\'semi_annually\',\'yearly\'))');
        DB::statement('ALTER TABLE price_plans ADD CONSTRAINT pp_amount_check CHECK (amount >= 0)');
        
        // Subscriptions table - status check
        DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscription_status_check CHECK (status IN (\'active\', \'pending\', \'cancelled\', \'expired\'))');
        
        // Invoices table - status and amounts checks
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoice_status_check CHECK (status IN (\'draft\', \'issued\', \'paid\', \'overdue\', \'cancelled\'))');
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_amounts_check CHECK (subtotal >= 0 AND tax_total >= 0 AND total >= 0)');
        
        // Invoice Items table - quantity and prices checks
        DB::statement('ALTER TABLE invoice_items ADD CONSTRAINT quantity_check CHECK (quantity > 0)');
        DB::statement('ALTER TABLE invoice_items ADD CONSTRAINT prices_check CHECK (unit_price >= 0 AND total >= 0)');
        
        // Tax Rates table - rate check
        DB::statement('ALTER TABLE tax_rates ADD CONSTRAINT rate_check CHECK (rate >= 0 AND rate <= 100)');
        
        // Invoice Taxes table - amount check
        DB::statement('ALTER TABLE invoice_taxes ADD CONSTRAINT invoice_taxes_amount_check CHECK (amount >= 0)');
        
        // Payments table - status and amount checks
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payment_status_check CHECK (notification_status IN (\'pending\', \'processing\', \'completed\', \'failed\', \'cancelled\'))');
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount > 0)');
        
        // Refunds table - status and amount checks
        DB::statement('ALTER TABLE refunds ADD CONSTRAINT refund_status_check CHECK (status IN (\'pending\', \'approved\', \'rejected\', \'processed\'))');
        DB::statement('ALTER TABLE refunds ADD CONSTRAINT refunds_amount_check CHECK (amount > 0)');
        
        // Organization Payment Gateway Integrations table - status check
        DB::statement('ALTER TABLE organization_payment_gateway_integrations ADD CONSTRAINT opgi_status_check CHECK (status IN (\'active\', \'inactive\', \'suspended\', \'pending\'))');
        
        // Configurations table - env check
        DB::statement('ALTER TABLE configurations ADD CONSTRAINT env_check CHECK (env IN (\'testing\', \'production\'))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all check constraints
        $constraints = [
            'organizations' => 'org_status_check',
            'users' => 'user_sex_check',
            'customers' => 'customer_status_check',
            'products' => 'product_status_check',
            'price_plans' => ['subscription_type_check', 'pp_amount_check'],
            'subscriptions' => 'subscription_status_check',
            'invoices' => ['invoice_status_check', 'invoices_amounts_check'],
            'invoice_items' => ['quantity_check', 'prices_check'],
            'tax_rates' => 'rate_check',
            'invoice_taxes' => 'invoice_taxes_amount_check',
            'payments' => ['payment_status_check', 'payments_amount_check'],
            'refunds' => ['refund_status_check', 'refunds_amount_check'],
            'organization_payment_gateway_integrations' => 'opgi_status_check',
            'configurations' => 'env_check',
        ];

        foreach ($constraints as $table => $constraint) {
            $constraintNames = is_array($constraint) ? $constraint : [$constraint];
            foreach ($constraintNames as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            }
        }
    }
};
