<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricePlan extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'subscription_type',
        'amount',
        'currency',
        'rate',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
