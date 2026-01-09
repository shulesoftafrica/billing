<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'customer_id',
        'price_plan_id',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pricePlan(): BelongsTo
    {
        return $this->belongsTo(PricePlan::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
