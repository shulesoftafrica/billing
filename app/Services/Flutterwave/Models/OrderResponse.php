<?php

namespace App\Services\Flutterwave\Models;

class OrderResponse
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly ?string $reference,
        public readonly ?array $nextAction,
        public readonly array $attributes
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            reference: isset($data['reference']) ? (string) $data['reference'] : null,
            nextAction: isset($data['next_action']) && is_array($data['next_action']) ? $data['next_action'] : null,
            attributes: $data
        );
    }

    public function redirectUrl(): ?string
    {
        if (!is_array($this->nextAction)) {
            return null;
        }

        if (($this->nextAction['type'] ?? null) === 'redirect_url') {
            return $this->nextAction['redirect_url']['url'] ?? $this->nextAction['url'] ?? null;
        }

        return $this->attributes['redirect_url'] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
