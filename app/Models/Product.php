<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'organization_id',
        'product_type_id',
        'name',
        'product_code',
        'description',
        'active',
        'unit',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $appends = ['status'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    public function pricePlans()
    {
        return $this->hasMany(PricePlan::class);
    }

    /**
     * Customers who have at least one subscription to a price plan of this product.
     *
     * There is intentionally no product_id on the customers table — a customer can
     * subscribe to many products.  Use this query scope when you need to list them:
     *
     *   $product->customers()  →  Customer query builder
     *
     * Example: $product->customers()->where('status', 'active')->get()
     */
    public function customers()
    {
        return Customer::whereHas('subscriptions', function ($q) {
            $q->whereHas('pricePlan', fn ($pp) => $pp->where('product_id', $this->id));
        });
    }

    public function webhooks()
    {
        return $this->hasMany(CustomWebhook::class);
    }

    /**
     * Get active webhooks for a specific event type
     */
    public function getActiveWebhooksForEvent(string $eventType)
    {
        return $this->webhooks()
            ->where('status', 'active')
            ->get()
            ->filter(fn($webhook) => $webhook->shouldTrigger($eventType));
    }

    /**
     * Get the product status.
     */
    public function getStatusAttribute()
    {
        return $this->active ? 'active' : 'inactive';
    }
}
