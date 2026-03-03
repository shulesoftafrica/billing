<?php

namespace App\Services\Flutterwave\Models;

class CreateOrderRequest
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $reference,
        public readonly string $customerId,
        public readonly string $paymentMethodId,
        public readonly ?string $redirectUrl = null,
        public readonly array $meta = [],
        public readonly ?array $authorization = null,
        public readonly ?float $merchantVatAmount = null
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'customer_id' => $this->customerId,
            'payment_method_id' => $this->paymentMethodId,
        ];

        if ($this->redirectUrl !== null) {
            $payload['redirect_url'] = $this->redirectUrl;
        }

        if (!empty($this->meta)) {
            $payload['meta'] = $this->meta;
        }

        if ($this->authorization !== null) {
            $payload['authorization'] = $this->authorization;
        }

        if ($this->merchantVatAmount !== null) {
            $payload['merchant_vat_amount'] = $this->merchantVatAmount;
        }

        return $payload;
    }
}
