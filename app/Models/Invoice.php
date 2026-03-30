<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_number',
        'currency',
        'date',
        'status',
        'description',
        'subtotal',
        'tax_total',
        'total',
        'due_date',
        'issued_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'currency' => 'string',
        'date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function pricePlans(): HasManyThrough
    {
        return $this->hasManyThrough(PricePlan::class, InvoiceItem::class, 'invoice_id', 'id', 'id', 'price_plan_id');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'invoice_payments')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function invoiceTaxes(): HasMany
    {
        return $this->hasMany(InvoiceTaxes::class, 'invoice_id');
    }

    /**
     * Control numbers for this invoice's customer+product combination.
     * A control number is linked to customer_id on the control_numbers table;
     * we scope further to the products present in this invoice's items.
     */
    public function controlNumbers(): HasMany
    {
        return $this->hasMany(ControlNumber::class, 'customer_id', 'customer_id');
    }
}
