<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationApiService
{
    private string $endpoint;
    private string $apiKey;
    private string $schemaName;

    public function __construct()
    {
        $this->endpoint   = config('services.notification.endpoint');
        $this->apiKey     = config('services.notification.api_key');
        $this->schemaName = config('services.notification.schema_name');
    }

    /**
     * Send an email notification via the notification API.
     */
    public function sendEmail(string $to, string $subject, string $message): bool
    {
        return $this->dispatch([
            'channel'     => 'email',
            'provider'    => 'sendgrid',
            'to'          => $to,
            'subject'     => $subject,
            'message'     => $message,
            'priority'    => 'high',
        ]);
    }

    /**
     * Send a WhatsApp notification via the notification API.
     */
    public function sendWhatsApp(string $to, string $message): bool
    {
        // Normalise number: strip spaces, ensure it starts with +
        $phone = preg_replace('/\s+/', '', $to);
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . ltrim($phone, '0');
        }

        return $this->dispatch([
            'channel'  => 'whatsapp',
            'provider' => 'wasender',
            'to'       => $phone,
            'message'  => $message,
            'priority' => 'high',
        ]);
    }

    /**
     * Core dispatcher — adds common fields and calls the API.
     */
    private function dispatch(array $payload): bool
    {
        $payload['schema_name'] = $this->schemaName;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-API-Key'    => $this->apiKey,
            ])->timeout(10)->post($this->endpoint, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Notification API returned non-success response', [
                'channel'  => $payload['channel'] ?? 'unknown',
                'to'       => $payload['to'] ?? 'unknown',
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('Notification API request failed', [
                'channel' => $payload['channel'] ?? 'unknown',
                'to'      => $payload['to'] ?? 'unknown',
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }
}
