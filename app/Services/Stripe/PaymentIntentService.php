<?php

namespace App\Services\Stripe;

use InvalidArgumentException;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class PaymentIntentService
{
    public function create(array $params): PaymentIntent
    {
        $amount = (int) ($params['amount'] ?? 0);
        $currency = strtolower((string) ($params['currency'] ?? ''));

        if ($amount <= 0) {
            throw new InvalidArgumentException('The amount must be a positive integer in the smallest currency unit.');
        }

        if ($currency === '' || strlen($currency) !== 3) {
            throw new InvalidArgumentException('The currency must be a valid 3-letter ISO code.');
        }

        $metadata = is_array($params['metadata'] ?? null) ? $params['metadata'] : [];
        $idempotencySeed = (string) ($metadata['order_id'] ?? uniqid('pi_', true));
        $idempotencyKey = 'pi_' . hash('sha256', $idempotencySeed);

        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => ['enabled' => true],
        ];

        foreach ([
            'customer',
            'description',
            'metadata',
            'receipt_email',
            'capture_method',
            'statement_descriptor',
        ] as $optionalField) {
            if (array_key_exists($optionalField, $params) && $params[$optionalField] !== null && $params[$optionalField] !== '') {
                $payload[$optionalField] = $params[$optionalField];
            }
        }
        try {
            return PaymentIntent::create($payload);
        } catch (ApiErrorException $e) {
            StripeErrorHandler::handle($e);
        }
    }
}
