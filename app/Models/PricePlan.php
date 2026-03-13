<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricePlan extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'billing_type',
        'billing_interval',
        'amount',
        'currency_id',
        'metadata',
        'plan_code',
        'feature_code',
        'trial_period_days',
        'setup_fee',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'metadata' => 'array',
        'trial_period_days' => 'integer',
    ];

    protected $appends = ['currency', 'rate', 'subscription_type'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
