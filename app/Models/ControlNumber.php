<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlNumber extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'organization_payment_gateway_integration_id',
        'product_id',
        'type_id',
        'header_response',
        'qr_code',
        'notified',
    ];

    protected $casts = [
        'header_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organizationPaymentGatewayIntegration()
    {
        return $this->belongsTo(OrganizationPaymentGatewayIntegration::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
