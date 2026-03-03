<?php

namespace App\Services\Flutterwave\Models;

class PaymentMethodResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly array $attributes
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            type: (string) ($data['type'] ?? ''),
            attributes: $data
        );
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
