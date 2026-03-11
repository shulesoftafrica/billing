<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationApiKey extends Model
{
    protected $fillable = [
        'organization_id',
        'name',
        'key_prefix',
        'key_hash',
        'environment',
        'last_used_at',
        'expires_at',
        'status',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the organization that owns this API key.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if the API key is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the API key is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Revoke the API key.
     */
    public function revoke(): bool
    {
        $this->status = 'revoked';
        return $this->save();
    }

    /**
     * Update the last used timestamp.
     */
    public function updateLastUsed(): bool
    {
        $this->last_used_at = now();
        return $this->save();
    }
}
