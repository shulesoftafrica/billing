<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'custom_webhook_id',
        'payment_id',
        'event_type',
        'payload',
        'status',
        'attempt_count',
        'http_status_code',
        'response_body',
        'error_message',
        'duration_ms',
        'sent_at',
        'next_retry_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    /**
     * Get the webhook this delivery belongs to
     */
    public function customWebhook(): BelongsTo
    {
        return $this->belongsTo(CustomWebhook::class);
    }

    /**
     * Mark delivery as sent successfully
     */
    public function markAsSent(int $statusCode, ?string $responseBody, int $durationMs): void
    {
        $this->update([
            'status' => 'sent',
            'http_status_code' => $statusCode,
            'response_body' => $responseBody ? substr($responseBody, 0, 1000) : null, // Limit to 1KB
            'duration_ms' => $durationMs,
            'sent_at' => now(),
            'next_retry_at' => null,
        ]);
    }

    /**
     * Mark delivery as failed and schedule retry
     */
    public function markAsFailed(string $error, ?int $statusCode, int $durationMs): void
    {
        $this->increment('attempt_count');
        
        $maxRetries = $this->customWebhook->retry_count ?? 3;
        $shouldRetry = $this->attempt_count < $maxRetries;

        // Exponential backoff: 5min, 15min, 45min
        $retryDelayMinutes = $shouldRetry ? (5 * pow(3, $this->attempt_count - 1)) : null;

        $this->update([
            'status' => $shouldRetry ? 'pending' : 'failed',
            'http_status_code' => $statusCode,
            'error_message' => substr($error, 0, 1000),
            'duration_ms' => $durationMs,
            'next_retry_at' => $shouldRetry ? now()->addMinutes($retryDelayMinutes) : null,
        ]);
    }

    /**
     * Check if delivery should be retried
     */
    public function shouldRetry(): bool
    {
        return $this->status === 'pending' 
            && $this->next_retry_at 
            && $this->next_retry_at->isPast();
    }

    /**
     * Scope: Get deliveries pending retry
     */
    public function scopePendingRetry($query)
    {
        return $query->where('status', 'pending')
            ->where('next_retry_at', '<=', now())
            ->whereNotNull('next_retry_at');
    }

    /**
     * Scope: Get failed deliveries
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Get successful deliveries
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Get recent deliveries
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope: Get deliveries for specific event type
     */
    public function scopeOfEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
