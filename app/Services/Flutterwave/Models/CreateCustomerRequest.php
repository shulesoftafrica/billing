<?php

namespace App\Services\Flutterwave\Models;

class CreateCustomerRequest
{
    public function __construct(
        public readonly string $email,
        public readonly array $name = [],
        public readonly ?array $phone = null,
        public readonly ?array $address = null,
        public readonly array $meta = []
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'email' => $this->email,
            'name' => $this->name,
        ];

        if (!empty($this->phone)) {
            $payload['phone'] = $this->phone;
        }

        if (!empty($this->address)) {
            $payload['address'] = $this->address;
        }

        if (!empty($this->meta)) {
            $payload['meta'] = $this->meta;
        }

        return $payload;
    }
}
