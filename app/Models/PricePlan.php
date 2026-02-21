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
        'active',
        'plan_code',
        'feature_code',
        'trial_period_days',
        'setup_fee',
    ];

    protected $casts = [
        'active' => 'boolean',
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

    /**
     * Get the currency code.
     */
    public function getCurrencyAttribute()
    {
        if ($this->relationLoaded('currency')) {
            $currencyModel = $this->getRelation('currency');
            return $currencyModel ? str_pad($currencyModel->code, 5) : null;
        }
        return null;
    }

    /**
     * Get the currency exchange rate.
     */
    public function getRateAttribute()
    {
        if ($this->relationLoaded('currency')) {
            $currencyModel = $this->getRelation('currency');
            return $currencyModel ? $currencyModel->exchange_rate : 1;
        }
        return 1;
    }

    /**
     * Get the subscription type (alias for billing_interval).
     */
    public function getSubscriptionTypeAttribute()
    {
        return $this->billing_interval;
    }
}
