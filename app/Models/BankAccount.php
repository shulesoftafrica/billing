<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'name',
        'account_number',
        'branch',
        'refer_bank_id',
        'organization_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organization that owns this bank account.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get all payment gateway integrations for this bank account.
     */
    public function paymentGatewayIntegrations(): HasMany
    {
        return $this->hasMany(OrganizationPaymentGatewayIntegration::class);
    }
}
