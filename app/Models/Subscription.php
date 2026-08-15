<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    protected $fillable = [
        'customer_id',
        'price_plan_id',
        'quantity',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'current_period_start',
        'current_period_end',
        'previous_plan_id',
        'last_upgrade_proration',
        'subscription_number',
        'trial_ends_at',
        'canceled_at',
        'pause_starts_at',
        'pause_ends_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'last_upgrade_proration' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'pause_starts_at' => 'date',
        'pause_ends_at' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pricePlan(): BelongsTo
    {
        return $this->belongsTo(PricePlan::class);
    }

    /**
     * invoices has no subscription_id column — a subscription's invoices are
     * only reachable through invoice_items (invoice_items.subscription_id /
     * invoice_items.invoice_id). A plain hasMany(Invoice::class) here would
     * assume a subscription_id FK on invoices that doesn't exist and always
     * return empty; go through invoice_items instead.
     */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items', 'subscription_id', 'invoice_id')
            ->withPivot('quantity', 'unit_price', 'total');
    }
    
    public function invoice_item(): HasOne
    {
        return $this->hasOne(InvoiceItem::class);
    }

   
}
