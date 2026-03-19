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
        'subscription_type',
        'amount',
        'currency_id',
        'metadata',
        'plan_code',
        'feature_code',
        'trial_period_days',
        'setup_fee',
        'active',
        'rate',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'metadata' => 'array',
        'trial_period_days' => 'integer',
        'active' => 'boolean',
        'rate' => 'integer',
    ];

    protected $appends = ['currency', 'rate', 'subscription_type'];

    /**
     * Get the currency code for the price plan.
     * Returns the currency code from the related Currency model.
     */
    public function getCurrencyAttribute()
    {
        // If currency relationship is loaded, return its code
        if ($this->relationLoaded('currency') && $this->currency) {
            return $this->currency->code;
        }

        // Otherwise, load the relationship and return the code
        $currency = $this->currency()->first();
        return $currency ? $currency->code : 'TZS'; // Default fallback
    }

    /**
     * Get the rate attribute.
     * Returns the rate value from the database column.
     */
    public function getRateAttribute()
    {
        return $this->attributes['rate'] ?? 1;
    }

    /**
     * Get the subscription type attribute.
     * Returns the billing_interval as subscription_type for backward compatibility.
     */
    public function getSubscriptionTypeAttribute()
    {
        return $this->attributes['billing_interval'] ?? $this->attributes['subscription_type'] ?? null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
