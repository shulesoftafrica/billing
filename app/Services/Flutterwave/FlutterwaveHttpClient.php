<?php

namespace App\Services\Flutterwave;

use App\Models\PaymentGateway;
use App\Services\Flutterwave\Models\FlutterwaveApiResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class FlutterwaveHttpClient
{
    private const DEFAULT_TIMEOUT = 30;

    public function __construct(
        private readonly ?PaymentGateway $gateway = null
    ) {
    }

    public function request(
        string $method,
        string $url,
        array $query = [],
        ?array $body = null,
        array $headers = []
    ): FlutterwaveApiResponse {
        // Build the final endpoint URL per request method (no constructor-level base URL).
        $requestUrl = $this->buildRequestUrl($url, $query);
        $requestHeaders = array_merge([
            'Authorization' => 'Bearer ' . $this->resolveBearerToken(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Trace-Id' => (string) Str::uuid(),
        ], $headers);

        $encodedBody = null;
        if ($body !== null) {
            // Encode request payload as JSON before sending.
            $encodedBody = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($encodedBody === false) {
                throw new RuntimeException('Failed to encode Flutterwave request body as JSON: ' . json_last_error_msg());
            }
        }

        try {
            [$statusCode, $rawBody] = $this->executeCurlRequest(
                method: $method,
                url: $requestUrl,
                headers: $requestHeaders,
                body: $encodedBody
            );

            // Decode and normalize the API response payload.
            $decoded = json_decode($rawBody, true);

            if (!is_array($decoded)) {
                throw new RuntimeException('Invalid JSON response from Flutterwave.');
            }

            $apiResponse = FlutterwaveApiResponse::fromHttpResponse($statusCode, $decoded);

            if (!$apiResponse->success) {
                Log::error('Flutterwave request failed', [
                    'method' => strtoupper($method),
                    'url' => $requestUrl,
                    'status_code' => $statusCode,
                    'message' => $apiResponse->message,
                    'response' => $apiResponse->raw,
                ]);
            }

            return $apiResponse;
        } catch (\Throwable $exception) {
            Log::error('Flutterwave HTTP request exception', [
                'method' => strtoupper($method),
                'url' => $requestUrl,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Flutterwave API request failed: ' . $exception->getMessage(), previous: $exception);
        }
    }

    private function executeCurlRequest(string $method, string $url, array $headers, ?string $body): array
    {
        $curlHandle = curl_init();
        if ($curlHandle === false) {
            throw new RuntimeException('Failed to initialize cURL for Flutterwave request.');
        }

        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            if (is_int($key)) {
                $formattedHeaders[] = (string) $value;
                continue;
            }

            $formattedHeaders[] = $key . ': ' . (string) $value;
        }

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $formattedHeaders,
        ];

        if ($body !== null) {
            $curlOptions[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curlHandle, $curlOptions);
        $rawResponse = curl_exec($curlHandle);

        if ($rawResponse === false) {
            $curlError = curl_error($curlHandle);
            curl_close($curlHandle);

            throw new RuntimeException('cURL error: ' . $curlError);
        }

        $statusCode = (int) curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        return [$statusCode, (string) $rawResponse];
    }

    private function buildRequestUrl(string $url, array $query): string
    {
        $trimmedUrl = trim($url);

        if (empty($query)) {
            return $trimmedUrl;
        }

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        if ($queryString === '') {
            return $trimmedUrl;
        }

        return str_contains($trimmedUrl, '?')
            ? $trimmedUrl . '&' . $queryString
            : $trimmedUrl . '?' . $queryString;
    }

    private function resolveBearerToken(): string
    {
        $token = $this->resolveGatewayConfigValue('secret_key')
            ?? $this->resolveGatewayConfigValue('bearer_token')
            ?? config('services.flutterwave.secret_key')
            ?? env('FLUTTERWAVE_SECRET_KEY')
            ?? env('FLW_SECRET_KEY');

        if (!is_string($token) || trim($token) === '') {
            throw new RuntimeException('Flutterwave Bearer token is not configured.');
        }

        return $token;
    }

    private function resolveGatewayConfigValue(string $key): mixed
    {
        $config = $this->gateway?->config;

        if (is_string($config)) {
            $decoded = json_decode($config, true);
            $config = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($config)) {
            return null;
        }

        $value = $config[$key] ?? null;

        return is_string($value) ? trim($value) : $value;
    }
}
