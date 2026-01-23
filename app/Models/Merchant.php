<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Merchant extends Model
{
    protected $fillable = [
        'organization_payment_gateway_integration_id',
        'header_response',
        'merchant_code',
        'qr_code',
        'terminal_id',
        'terminal_name',
        'secret_key',
    ];

    protected $casts = [
        'header_response' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organization payment gateway integration this merchant belongs to.
     */
    public function organizationPaymentGatewayIntegration(): BelongsTo
    {
        return $this->belongsTo(OrganizationPaymentGatewayIntegration::class);
    }
}
