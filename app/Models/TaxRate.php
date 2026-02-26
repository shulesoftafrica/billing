<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\InvoiceTaxes;

class TaxRate extends Model
{
    protected $fillable = [
        'country',
        'name',
        'rate',
        'active',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function invoiceTaxes(): HasMany
    {
        return $this->hasMany(InvoiceTaxes::class);
    }
}
