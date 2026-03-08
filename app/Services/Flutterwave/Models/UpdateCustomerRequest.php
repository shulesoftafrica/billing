<?php

namespace App\Services\Flutterwave\Models;

class UpdateCustomerRequest
{
    public function __construct(
        public readonly ?array $name = null,
        public readonly ?array $phone = null,
        public readonly ?array $address = null,
        public readonly ?array $meta = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'meta' => $this->meta,
        ], static fn ($value) => $value !== null);
    }
}
