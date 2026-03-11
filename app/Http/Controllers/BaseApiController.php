<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Get the authenticated user's organization
     *
     * @return Organization|null
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getOrganization(): ?Organization
    {
        // Ensure user is authenticated
        if (!auth()->check() || !auth()->user()) {
            abort(401, 'Unauthenticated');
        }

        // Load organization relationship if not already loaded
        if (!auth()->user()->relationLoaded('organization')) {
            auth()->user()->load('organization');
        }

        return auth()->user()->organization;
    }

    /**
     * Get the authenticated user's organization ID
     *
     * @return int|null
     */
    protected function getOrganizationId(): ?int
    {
        return auth()->check() ? auth()->user()->organization_id : null;
    }

    /**
     * Verify that a given organization ID matches the authenticated user's organization
     *
     * @param int $organizationId
     * @return bool
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function verifyOrganizationAccess(int $organizationId): bool
    {
        if ($this->getOrganizationId() !== $organizationId) {
            abort(403, 'Access denied: You do not have access to this organization\'s resources');
        }

        return true;
    }

    /**
     * Scope query to authenticated user's organization
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeToOrganization($query)
    {
        return $query->where('organization_id', $this->getOrganizationId());
    }

    /**
     * Check if the authenticated user has a specific token ability
     *
     * @param string $ability
     * @return bool
     */
    protected function hasAbility(string $ability): bool
    {
        if (!auth()->check() || !auth()->user()) {
            return false;
        }

        $token = auth()->user()->currentAccessToken();

        if (!$token) {
            return false;
        }

        return $token->can($ability);
    }

    /**
     * Require a specific token ability or abort with 403
     *
     * @param string $ability
     * @param string|null $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function requireAbility(string $ability, ?string $message = null): void
    {
        if (!$this->hasAbility($ability)) {
            abort(403, $message ?? "This action requires the '{$ability}' permission");
        }
    }
}
