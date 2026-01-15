<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function organizationPaymentGatewayIntegration()
    {
        return $this->belongsTo(OrganizationPaymentGatewayIntegration::class);
    }
}
