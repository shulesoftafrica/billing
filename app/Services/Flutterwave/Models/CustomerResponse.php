<?php

namespace App\Services\Flutterwave\Models;

class CustomerResponse
{
    public function __construct(
        public readonly string $id,
        public readonly array $attributes
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            attributes: $data
        );
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
