<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class CurrencyService
{
    /**
     * Convert amount between currencies
     */
    public function convertAmount($amount, $fromCurrencyCode, $toCurrencyCode, $useBuffer = true)
    {
        try {
            if ($fromCurrencyCode === $toCurrencyCode) {
                return [
                    'success' => true,
                    'original_amount' => $amount,
                    'converted_amount' => $amount,
                    'from_currency' => $fromCurrencyCode,
                    'to_currency' => $toCurrencyCode,
                    'rate' => 1.0000,
                    'effective_rate' => 1.0000
                ];
            }

            $rate = $this->getExchangeRate($fromCurrencyCode, $toCurrencyCode, $useBuffer);
            
            if (!$rate['success']) {
                return $rate;
            }

            $convertedAmount = $amount * $rate['effective_rate'];

            return [
                'success' => true,
                'original_amount' => $amount,
                'converted_amount' => round($convertedAmount, 2),
                'from_currency' => $fromCurrencyCode,
                'to_currency' => $toCurrencyCode,
                'rate' => $rate['rate'],
                'effective_rate' => $rate['effective_rate'],
                'buffer_applied' => $rate['buffer_applied']
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Currency conversion failed'
            ];
        }
    }

    /**
     * Get exchange rate between two currencies
     */
    public function getExchangeRate($fromCurrencyCode, $toCurrencyCode, $useBuffer = true)
    {
        try {
            // Get currency IDs
            $fromCurrency = Currency::where('code', $fromCurrencyCode)->first();
            $toCurrency = Currency::where('code', $toCurrencyCode)->first();

            if (!$fromCurrency || !$toCurrency) {
                return [
                    'success' => false,
                    'error' => 'currency_not_found',
                    'message' => 'One or both currencies not found'
                ];
            }

            // Check for direct exchange rate
            $exchangeRate = DB::table('currency_exchange_rates')
                ->where('from_currency_id', $fromCurrency->id)
                ->where('to_currency_id', $toCurrency->id)
                ->first();

            if ($exchangeRate) {
                $rateToUse = $useBuffer ? $exchangeRate->effective_rate : $exchangeRate->rate;
                
                return [
                    'success' => true,
                    'rate' => $exchangeRate->rate,
                    'effective_rate' => $rateToUse,
                    'buffer_percentage' => $exchangeRate->buffer_percentage,
                    'buffer_applied' => $useBuffer,
                    'last_updated' => $exchangeRate->updated_at
                ];
            }

            // Try reverse rate (divide by reverse rate)
            $reverseRate = DB::table('currency_exchange_rates')
                ->where('from_currency_id', $toCurrency->id)
                ->where('to_currency_id', $fromCurrency->id)
                ->first();

            if ($reverseRate) {
                $rate = 1 / $reverseRate->rate;
                $effectiveRate = $useBuffer ? (1 / $reverseRate->effective_rate) : $rate;
                
                return [
                    'success' => true,
                    'rate' => $rate,
                    'effective_rate' => $effectiveRate,
                    'buffer_percentage' => $reverseRate->buffer_percentage,
                    'buffer_applied' => $useBuffer,
                    'last_updated' => $reverseRate->updated_at,
                    'note' => 'Calculated from reverse rate'
                ];
            }

            // Use base currency conversion if available
            $baseCurrency = Currency::where('is_base_currency', true)->first();
            
            if ($baseCurrency && $baseCurrency->id !== $fromCurrency->id && $baseCurrency->id !== $toCurrency->id) {
                $fromToBase = $this->getExchangeRate($fromCurrencyCode, $baseCurrency->code, $useBuffer);
                $baseToTo = $this->getExchangeRate($baseCurrency->code, $toCurrencyCode, $useBuffer);
                
                if ($fromToBase['success'] && $baseToTo['success']) {
                    $combinedRate = $fromToBase['effective_rate'] * $baseToTo['effective_rate'];
                    
                    return [
                        'success' => true,
                        'rate' => $combinedRate,
                        'effective_rate' => $combinedRate,
                        'buffer_applied' => $useBuffer,
                        'note' => 'Calculated via base currency: ' . $baseCurrency->code
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'exchange_rate_not_found',
                'message' => "Exchange rate not found for {$fromCurrencyCode} to {$toCurrencyCode}"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to get exchange rate'
            ];
        }
    }

    /**
     * Get price preview in multiple currencies
     */
    public function getPricePreview($amount, $baseCurrencyCode, $targetCurrencies = [])
    {
        $previews = [];
        $baseCurrency = Currency::where('code', $baseCurrencyCode)->first();
        
        if (!$baseCurrency) {
            return [
                'success' => false,
                'error' => 'base_currency_not_found',
                'message' => 'Base currency not found'
            ];
        }

        // If no target currencies specified, get all active currencies
        if (empty($targetCurrencies)) {
            $targetCurrencies = Currency::where('active', true)
                ->where('code', '!=', $baseCurrencyCode)
                ->pluck('code')
                ->toArray();
        }

        foreach ($targetCurrencies as $targetCurrencyCode) {
            $conversion = $this->convertAmount($amount, $baseCurrencyCode, $targetCurrencyCode);
            
            if ($conversion['success']) {
                $previews[$targetCurrencyCode] = [
                    'currency_code' => $targetCurrencyCode,
                    'amount' => $conversion['converted_amount'],
                    'rate' => $conversion['rate'],
                    'effective_rate' => $conversion['effective_rate']
                ];
            } else {
                $previews[$targetCurrencyCode] = [
                    'currency_code' => $targetCurrencyCode,
                    'error' => $conversion['error'],
                    'message' => $conversion['message']
                ];
            }
        }

        return [
            'success' => true,
            'base_currency' => $baseCurrencyCode,
            'base_amount' => $amount,
            'previews' => $previews
        ];
    }

    /**
     * Update exchange rates (for admin/scheduled updates)
     */
    public function updateExchangeRate($fromCurrencyCode, $toCurrencyCode, $rate, $bufferPercentage = 0)
    {
        try {
            $fromCurrency = Currency::where('code', $fromCurrencyCode)->first();
            $toCurrency = Currency::where('code', $toCurrencyCode)->first();

            if (!$fromCurrency || !$toCurrency) {
                return [
                    'success' => false,
                    'error' => 'currency_not_found',
                    'message' => 'One or both currencies not found'
                ];
            }

            $effectiveRate = $rate * (1 + ($bufferPercentage / 100));

            DB::table('currency_exchange_rates')->updateOrInsert(
                [
                    'from_currency_id' => $fromCurrency->id,
                    'to_currency_id' => $toCurrency->id
                ],
                [
                    'rate' => $rate,
                    'buffer_percentage' => $bufferPercentage,
                    'effective_rate' => $effectiveRate,
                    'updated_at' => now()
                ]
            );

            // Update currency table last_updated
            $fromCurrency->update(['last_updated' => now()]);

            return [
                'success' => true,
                'from_currency' => $fromCurrencyCode,
                'to_currency' => $toCurrencyCode,
                'rate' => $rate,
                'buffer_percentage' => $bufferPercentage,
                'effective_rate' => $effectiveRate,
                'message' => 'Exchange rate updated successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update exchange rate'
            ];
        }
    }

    /**
     * Get all current exchange rates
     */
    public function getCurrentRates()
    {
        return DB::table('currency_exchange_rates as cer')
            ->join('currencies as fc', 'cer.from_currency_id', '=', 'fc.id')
            ->join('currencies as tc', 'cer.to_currency_id', '=', 'tc.id')
            ->select([
                'fc.code as from_currency',
                'tc.code as to_currency',
                'cer.rate',
                'cer.buffer_percentage',
                'cer.effective_rate',
                'cer.updated_at'
            ])
            ->orderBy('fc.code')
            ->get();
    }
}