<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomWebhook extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'url',
        'secret',
        'status',
        'events',
        'http_method',
        'headers',
        'timeout',
        'retry_count',
        'verify_ssl',
        'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'verify_ssl' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    protected $hidden = [
        'secret', // Hide secret from JSON responses by default
    ];

    /**
     * Get the product that owns this webhook
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all deliveries for this webhook
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * Check if webhook should be triggered for a given event type
     */
    public function shouldTrigger(string $eventType): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // If no events specified, trigger for all events
        if (empty($this->events)) {
            return true;
        }

        // Check for exact match or wildcard match
        foreach ($this->events as $pattern) {
            // Exact match
            if ($pattern === $eventType) {
                return true;
            }
            
            // Wildcard support: payment.* matches payment.success, payment.failed, etc.
            $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';
            if (preg_match($regex, $eventType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate HMAC signature for payload
     */
    public function generateSignature(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), $this->secret);
    }

    /**
     * Scope: Get active webhooks
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Get webhooks for specific event
     */
    public function scopeForEvent($query, string $eventType)
    {
        return $query->where(function ($q) use ($eventType) {
            // Webhooks with no events (listen to all)
            $q->whereNull('events')
              ->orWhereRaw('JSON_LENGTH(events) = 0')
              // Webhooks with this specific event
              ->orWhereRaw('JSON_CONTAINS(events, ?)', [json_encode($eventType)])
              // Webhooks with wildcard matching (e.g., payment.*)
              ->orWhere(function ($subQuery) use ($eventType) {
                  $eventPrefix = explode('.', $eventType)[0];
                  $subQuery->whereRaw('JSON_CONTAINS(events, ?)', [json_encode($eventPrefix . '.*')]);
              });
        });
    }
}
