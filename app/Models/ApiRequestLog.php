<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiRequestLog extends Model
{
    protected $fillable = [
        'organization_id',
        'client_id',
        'method',
        'endpoint',
        'status_code',
        'success',
        'request_payload',
        'response_summary',
        'response_time_ms',
        'ip_address',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_summary' => 'array',
        'success'          => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
