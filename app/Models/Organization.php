<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'currency',
        'country_id',
        'status',
    ];

    protected $casts = [
        'currency' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the country that this organization belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get all users for this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all customers for this organization.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get all products for this organization.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all bank accounts for this organization.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get all payment gateway integrations for this organization.
     */
    public function paymentGatewayIntegrations(): HasMany
    {
        return $this->hasMany(OrganizationPaymentGatewayIntegration::class);
    }

    /**
     * Get all configurations for this organization.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }
}
