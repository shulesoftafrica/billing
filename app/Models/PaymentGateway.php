<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all organization integrations for this gateway.
     */
    public function organizationIntegrations(): HasMany
    {
        return $this->hasMany(OrganizationPaymentGatewayIntegration::class);
    }

    /**
     * Get all configurations for this gateway.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }
}
