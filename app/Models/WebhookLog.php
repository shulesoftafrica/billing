<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webhook_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'request_id',
        'webhook_type',
        'status',
        'event_type',
        'payload',
        'response_data',
        'error_message',
        'ip_address',
        'user_agent',
        'duration_ms',
        'http_status_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payload' => 'array',
        'response_data' => 'array',
        'duration_ms' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mark webhook as completed
     */
    public function markAsCompleted(array $responseData = null, int $httpStatusCode = null, float $durationMs = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'response_data' => $responseData,
            'http_status_code' => $httpStatusCode,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Mark webhook as error
     */
    public function markAsError(string $errorMessage, int $httpStatusCode = null, float $durationMs = null): bool
    {
        return $this->update([
            'status' => 'error',
            'error_message' => $errorMessage,
            'http_status_code' => $httpStatusCode,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Update duration
     */
    public function updateDuration(float $durationMs): bool
    {
        return $this->update(['duration_ms' => $durationMs]);
    }

    /**
     * Scope to filter by webhook type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('webhook_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get recent webhooks
     */
    public function scopeRecent($query, int $limit = 100)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope to get failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope to get pending webhooks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'in_progress');
    }
}
