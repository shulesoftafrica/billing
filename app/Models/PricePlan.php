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
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
