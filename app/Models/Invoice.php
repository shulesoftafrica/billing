<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'subscription_id',
        'invoice_number',
        'invoice_type',
        'status',
        'description',
        'subtotal',
        'tax_total',
        'proration_credit',
        'total',
        'due_date',
        'issued_at',
        'metadata',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'proration_credit' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // Alias for invoiceItems to match API expectations
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceTaxes(): HasMany
    {
        return $this->hasMany(InvoiceTaxes::class, 'invoice_id');
    }
}
