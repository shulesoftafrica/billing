<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
