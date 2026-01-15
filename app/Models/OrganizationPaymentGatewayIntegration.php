<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationPaymentGatewayIntegration extends Model
{
    protected $fillable = [
        'bank_account_id',
        'payment_gateway_id',
        'organization_id',
        'status',
        'attachment',
        'attachment_url',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }

    public function controlNumbers()
    {
        return $this->hasMany(ControlNumber::class);
    }
}
