<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'organization_id',
        'product_type_id',
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

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
}
