<?php

namespace App\Http\Middleware;

use App\Models\ApiRequestLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestLogger
{
    /**
     * Fields to sanitize from request payloads before logging.
     */
    private const SENSITIVE_FIELDS = [
        'password', 'password_confirmation', 'client_secret',
        'secret', 'card_number', 'cvv', 'cvc', 'pin',
        'token', 'access_token', 'refresh_token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        try {
            $this->logRequest($request, $response, $startTime);
        } catch (\Throwable $e) {
            // Never break the API response if logging fails
            \Illuminate\Support\Facades\Log::error('ApiRequestLogger failed: ' . $e->getMessage());
        }

        return $response;
    }

    private function logRequest(Request $request, Response $response, float $startTime): void
    {
        // Resolve the authenticated organization
        $organizationId = null;

        // Try Sanctum user
        if ($user = $request->user()) {
            $organizationId = $user->organization_id;
        }

        // Try OAuth client via request attribute set by token resolution
        if (!$organizationId) {
            $organizationId = $request->attributes->get('organization_id');
        }

        if (!$organizationId) {
            return; // Cannot scope log without an organization
        }

        $clientId = $request->attributes->get('oauth_client_id');

        $statusCode    = $response->getStatusCode();
        $responseMs    = (int) round((microtime(true) - $startTime) * 1000);

        // Sanitize request payload
        $payload = $request->except(self::SENSITIVE_FIELDS);
        $payload = $this->deepSanitize($payload);

        // Summarize response (first 500 chars of body)
        $rawContent    = $response->getContent();
        $responseSummary = [
            'status' => $statusCode,
            'body'   => mb_substr((string) $rawContent, 0, 500),
        ];

        ApiRequestLog::create([
            'organization_id'  => $organizationId,
            'client_id'        => $clientId,
            'method'           => $request->method(),
            'endpoint'         => '/' . ltrim($request->path(), '/'),
            'status_code'      => $statusCode,
            'success'          => $statusCode < 400,
            'request_payload'  => empty($payload) ? null : $payload,
            'response_summary' => $responseSummary,
            'response_time_ms' => $responseMs,
            'ip_address'       => $request->ip(),
        ]);
    }

    private function deepSanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string)$key), self::SENSITIVE_FIELDS, true)) {
                $data[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->deepSanitize($value);
            }
        }

        return $data;
    }
}
