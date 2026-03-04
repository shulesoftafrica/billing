<?php

namespace App\Services\Stripe;

class StripeAmountHelper
{
    /**
     * Zero-decimal currencies that Stripe handles without multiplying by 100
     */
    private const ZERO_DECIMAL_CURRENCIES = [
        'bif',
        'clp',
        'djf',
        'gnf',
        'jpy',
        'kmf',
        'krw',
        'mga',
        'pyg',
        'rwf',
        'vnd',
        'vuu',
        'xaf',
        'xof',
        'xpf'
    ];

    /**
     * Convert amount to Stripe-compatible format
     *
     * @param float|int $amount The amount in standard currency units
     * @param string $currency The ISO 4217 currency code
     * @return int The amount in cents (or smallest unit for zero-decimal currencies)
     */
    public static function toStripeAmount($amount, string $currency): int
    {
        $currency = strtolower($currency);
        if (self::isZeroDecimalCurrency($currency)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    /**
     * Convert Stripe amount back to standard currency units
     *
     * @param int $amount The amount from Stripe
     * @param string $currency The ISO 4217 currency code
     * @return float The amount in standard currency units
     */
    public static function fromStripeAmount(int $amount, string $currency): float
    {
        $currency = strtolower($currency);

        if (self::isZeroDecimalCurrency($currency)) {
            return (float) $amount;
        }

        return $amount / 100;
    }

    /**
     * Check if currency is zero-decimal
     *
     * @param string $currency The ISO 4217 currency code
     * @return bool
     */
    public static function isZeroDecimalCurrency(string $currency): bool
    {
        return in_array(strtolower($currency), self::ZERO_DECIMAL_CURRENCIES, true);
    }
    public static function countDigits($number): int
    {
        return strlen((string) abs((int) $number));
    }
}
