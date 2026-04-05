<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'currency_code')) {
                $table->string('currency_code', 3)->nullable()->after('code');
            }
        });

        // Seed ISO 4217 currency codes for the 10 seeded African countries
        $map = [
            'TZ' => 'TZS',   // Tanzania Shilling
            'KE' => 'KES',   // Kenya Shilling
            'UG' => 'UGX',   // Uganda Shilling
            'RW' => 'RWF',   // Rwanda Franc
            'BI' => 'BIF',   // Burundi Franc
            'ZA' => 'ZAR',   // South African Rand
            'NG' => 'NGN',   // Nigerian Naira
            'GH' => 'GHS',   // Ghana Cedi
            'EG' => 'EGP',   // Egyptian Pound
            'MA' => 'MAD',   // Moroccan Dirham
        ];

        foreach ($map as $countryCode => $currencyCode) {
            DB::table('countries')
                ->where('code', $countryCode)
                ->update(['currency_code' => $currencyCode]);
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'currency_code')) {
                $table->dropColumn('currency_code');
            }
        });
    }
};
