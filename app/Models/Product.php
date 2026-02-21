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
     * Get the product status.
     */
    public function getStatusAttribute()
    {
        return $this->active ? 'active' : 'inactive';
    }
}
