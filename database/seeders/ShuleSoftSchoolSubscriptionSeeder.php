<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-time provisioning for ShuleSoft's own school-subscription product on
 * this billing platform. Organization id 6 ("ShuleSoft LTD") is ShuleSoft's
 * real account here (confirmed against the live organizations table — not
 * id 1, which the older, stale ProductSeeder assumed).
 *
 * Only the Tanzania per-student rate (TSh10,000/student/year) is seeded.
 * The international/curriculum-based rate ($9.9/student/year) is
 * deliberately NOT seeded yet: nothing in the staff CRM currently records a
 * school's curriculum type or country, so there's no reliable way to pick
 * the right plan for a school at Go Live time. Add that plan once a real
 * data source for curriculum/country exists.
 *
 * Run explicitly (not wired into DatabaseSeeder, matching this repo's
 * existing convention for ProductSeeder): php artisan db:seed --class=Database\\Seeders\\ShuleSoftSchoolSubscriptionSeeder
 *
 * Idempotent: safe to re-run, does nothing if the product/plan already exist.
 */
class ShuleSoftSchoolSubscriptionSeeder extends Seeder
{
    public const ORGANIZATION_ID = 6; // ShuleSoft LTD
    public const PRODUCT_CODE = 'SHULESOFT-SCHOOL-SUB';
    public const TANZANIA_PLAN_CODE = 'SHULESOFT-SCHOOL-TZ-ANNUAL';
    public const TANZANIA_PER_STUDENT_AMOUNT = 10000; // TSh, per student, per year

    public function run(): void
    {
        $productId = DB::table('products')
            ->where('organization_id', self::ORGANIZATION_ID)
            ->where('product_code', self::PRODUCT_CODE)
            ->value('id');

        if (!$productId) {
            $productId = DB::table('products')->insertGetId([
                'organization_id' => self::ORGANIZATION_ID,
                'product_type_id' => 2, // Subscription Product
                'name' => 'ShuleSoft School Subscription',
                'description' => 'Annual, per-student-priced ShuleSoft school management subscription.',
                'active' => true,
                'product_code' => self::PRODUCT_CODE,
                'unit' => 'student',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $exists = DB::table('price_plans')->where('plan_code', self::TANZANIA_PLAN_CODE)->exists();
        if (!$exists) {
            DB::table('price_plans')->insert([
                'product_id' => $productId,
                'name' => 'ShuleSoft School Subscription - Annual (Tanzania)',
                'billing_type' => 'recurring',
                'billing_interval' => 'yearly',
                'amount' => self::TANZANIA_PER_STUDENT_AMOUNT,
                'currency_id' => 1, // TZS
                'active' => true,
                'plan_code' => self::TANZANIA_PLAN_CODE,
                'trial_period_days' => 0,
                'setup_fee' => 0.00,
                'rate' => 1,
                'metadata' => json_encode([
                    'pricing_model' => 'per_student',
                    'per_student_rate' => self::TANZANIA_PER_STUDENT_AMOUNT,
                    'currency' => 'TZS',
                    'country' => 'Tanzania',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
