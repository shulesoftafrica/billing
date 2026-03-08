<?php

namespace App\Services\Flutterwave\Models;

class CreatePaymentMethodRequest
{
    public function __construct(
        public readonly string $type,
        public readonly array $paymentData,
        public readonly ?string $customerId = null,
        public readonly array $meta = []
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'type' => $this->type,
            $this->type => $this->paymentData,
        ];

        if ($this->customerId !== null) {
            $payload['customer_id'] = $this->customerId;
        }

        if (!empty($this->meta)) {
            $payload['meta'] = $this->meta;
        }

        return $payload;
    }
}
