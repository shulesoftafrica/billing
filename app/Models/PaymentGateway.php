<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'type',
        'webhook_secret',
        'config',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'config' => 'array',
    ];
}
