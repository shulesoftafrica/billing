<?php

namespace App\Services\Flutterwave\Models;

class FlutterwaveApiResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data,
        public readonly ?array $meta,
        public readonly int $httpStatus,
        public readonly array $raw
    ) {
    }

    public static function fromHttpResponse(int $httpStatus, array $payload): self
    {
        $status = strtolower((string) ($payload['status'] ?? 'failed'));

        return new self(
            success: $status === 'success' && $httpStatus >= 200 && $httpStatus < 300,
            message: (string) ($payload['message'] ?? 'No message returned from Flutterwave.'),
            data: $payload['data'] ?? null,
            meta: isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : null,
            httpStatus: $httpStatus,
            raw: $payload
        );
    }
}
