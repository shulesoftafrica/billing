<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OAuthClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'client_id',
        'client_secret_hash',
        'client_secret_prefix',
        'environment',
        'status',
        'allowed_scopes',
        'last_used_at',
        'last_used_ip',
        'expires_at',
    ];

    protected $casts = [
        'allowed_scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'client_secret_hash',
    ];

    /**
     * Get the organization that owns the client.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if the client is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // Check if client has expired
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the client has a specific scope.
     */
    public function hasScope(string $scope): bool
    {
        if (empty($this->allowed_scopes)) {
            return true; // No scope restrictions
        }

        return in_array($scope, $this->allowed_scopes) || in_array('*', $this->allowed_scopes);
    }

    /**
     * Update the last used timestamp and IP.
     */
    public function updateLastUsed(string $ipAddress = null): void
    {
        $this->last_used_at = now();
        if ($ipAddress) {
            $this->last_used_ip = $ipAddress;
        }
        $this->save();
    }

    /**
     * Generate a new client ID and secret pair.
     * Returns array with 'client_id' and 'client_secret' (plain text - show once only).
     */
    public static function generateCredentials(string $environment = 'test'): array
    {
        // Generate client_id: org_test_client_xxxxxxxx or org_live_client_xxxxxxxx
        $randomId = Str::random(32);
        $clientId = "org_{$environment}_client_{$randomId}";

        // Generate client_secret: org_test_secret_xxxxxxxx or org_live_secret_xxxxxxxx
        $randomSecret = Str::random(40);
        $clientSecret = "org_{$environment}_secret_{$randomSecret}";

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'client_secret_hash' => hash('sha256', $clientSecret),
            'client_secret_prefix' => substr($clientSecret, 0, 12),
        ];
    }

    /**
     * Verify a client secret against the stored hash.
     */
    public function verifySecret(string $clientSecret): bool
    {
        return hash_equals($this->client_secret_hash, hash('sha256', $clientSecret));
    }

    /**
     * Revoke the client.
     */
    public function revoke(): bool
    {
        $this->status = 'revoked';
        return $this->save();
    }

    /**
     * Suspend the client.
     */
    public function suspend(): bool
    {
        $this->status = 'suspended';
        return $this->save();
    }

    /**
     * Activate the client.
     */
    public function activate(): bool
    {
        $this->status = 'active';
        return $this->save();
    }
}
