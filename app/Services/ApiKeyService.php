<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationApiKey;
use Illuminate\Support\Str;

class ApiKeyService
{
    /**
     * Generate a new API key for an organization.
     *
     * @param Organization $organization
     * @param string $environment 'test' or 'live'
     * @param string|null $name Optional name/label for the key
     * @return array Contains 'key' (plain text - show once) and 'model' (OrganizationApiKey)
     */
    public function generateKey(Organization $organization, string $environment = 'test', ?string $name = null): array
    {
        // Validate environment
        if (!in_array($environment, ['test', 'live'])) {
            throw new \InvalidArgumentException('Environment must be either "test" or "live"');
        }

        // Generate secure random token (40 characters)
        $randomPart = Str::random(40);
        
        // Create the full key: org_{environment}_{random}
        $fullKey = "org_{$environment}_{$randomPart}";
        
        // Create the prefix (first 14 chars for indexing): org_test_XXXXX or org_live_XXXXX
        $keyPrefix = substr($fullKey, 0, 14);
        
        // Hash the full key for secure storage
        $keyHash = hash('sha256', $fullKey);

        // Create the API key record
        $apiKey = OrganizationApiKey::create([
            'organization_id' => $organization->id,
            'name' => $name,
            'key_prefix' => $keyPrefix,
            'key_hash' => $keyHash,
            'environment' => $environment,
            'status' => 'active',
        ]);

        return [
            'key' => $fullKey,  // Return plain text key ONCE - never stored
            'model' => $apiKey,  // Return the model for additional info
        ];
    }

    /**
     * Validate an API key and return the associated organization.
     *
     * @param string $key The API key to validate
     * @return Organization|null Returns organization if valid, null otherwise
     */
    public function validateKey(string $key): ?Organization
    {
        // Check key format
        if (!$this->isValidKeyFormat($key)) {
            return null;
        }

        // Hash the provided key
        $keyHash = hash('sha256', $key);

        // Find the API key by hash
        $apiKey = OrganizationApiKey::where('key_hash', $keyHash)->first();

        if (!$apiKey) {
            return null;
        }

        // Check if key is active
        if (!$apiKey->isActive()) {
            return null;
        }

        // Update last used timestamp asynchronously (don't block response)
        $apiKey->updateLastUsed();

        // Return the organization
        return $apiKey->organization;
    }

    /**
     * Validate key format without database lookup.
     *
     * @param string $key
     * @return bool
     */
    public function isValidKeyFormat(string $key): bool
    {
        // Must start with org_test_ or org_live_
        // Total length should be 49 characters (org_ + test/live + _ + 40 random chars)
        return preg_match('/^org_(test|live)_[a-zA-Z0-9]{40}$/', $key) === 1;
    }

    /**
     * Revoke an API key.
     *
     * @param string $keyPrefixOrId The key prefix or ID to revoke
     * @param Organization|null $organization Optional organization to scope the revocation
     * @return bool
     */
    public function revokeKey(string $keyPrefixOrId, ?Organization $organization = null): bool
    {
        $query = OrganizationApiKey::query();

        // Try to find by ID first, then by prefix
        if (is_numeric($keyPrefixOrId)) {
            $query->where('id', $keyPrefixOrId);
        } else {
            $query->where('key_prefix', $keyPrefixOrId);
        }

        // Scope to organization if provided
        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        $apiKey = $query->first();

        if (!$apiKey) {
            return false;
        }

        return $apiKey->revoke();
    }

    /**
     * Get all active API keys for an organization.
     *
     * @param Organization $organization
     * @param string|null $environment Filter by environment ('test' or 'live')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrganizationKeys(Organization $organization, ?string $environment = null)
    {
        $query = $organization->apiKeys()->where('status', 'active');

        if ($environment) {
            $query->where('environment', $environment);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Extract environment from API key.
     *
     * @param string $key
     * @return string|null 'test' or 'live' or null if invalid
     */
    public function getKeyEnvironment(string $key): ?string
    {
        if (preg_match('/^org_(test|live)_/', $key, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
