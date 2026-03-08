<?php

namespace App\Exceptions;

use RuntimeException;

class StripePaymentException extends RuntimeException
{
    public function __construct(
        private readonly string $type,
        private readonly ?string $codeValue,
        private readonly ?string $declineCode,
        string $message,
        private readonly int $httpStatus
    ) {
        parent::__construct($message);
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'code' => $this->codeValue,
            'decline_code' => $this->declineCode,
            'message' => $this->getMessage(),
            'http_status' => $this->httpStatus,
        ];
    }
}
