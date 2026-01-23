<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationPaymentGatewayIntegration extends Model
{
    protected $fillable = [
        'bank_account_id',
        'payment_gateway_id',
        'organization_id',
        'status',
        'attachment',
    ];

    protected $casts = [
        'attachment' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bank account associated with this integration.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Get the payment gateway associated with this integration.
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    /**
     * Get the organization associated with this integration.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all merchants for this integration.
     */
    public function merchants(): HasMany
    {
        return $this->hasMany(Merchant::class);
    }

    /**
     * Get all control numbers for this integration.
     */
    public function controlNumbers(): HasMany
    {
        return $this->hasMany(ControlNumber::class);
    }
}
