<?php

namespace App\Services\Stripe;

use App\Exceptions\StripePaymentException;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\RateLimitException;

class StripeErrorHandler
{
    public static function handle(ApiErrorException $e): never
    {
        $error = $e->getError();

        Log::error('Stripe API error', [
            'exception' => class_basename($e),
            'request_id' => method_exists($e, 'getRequestId') ? $e->getRequestId() : null,
            'message' => $e->getMessage(),
        ]);

        if ($e instanceof CardException) {
            $message = (string) ($error?->message ?: 'Card was declined.');
            $declineCode = $error?->decline_code;

            if ($declineCode) {
                $message .= ' (decline_code: ' . $declineCode . ')';
            }

            throw new StripePaymentException(
                type: 'CardException',
                codeValue: $error?->code,
                declineCode: $declineCode,
                message: $message,
                httpStatus: 402
            );
        }

        if ($e instanceof RateLimitException) {
            throw new StripePaymentException(
                type: 'RateLimitException',
                codeValue: $error?->code,
                declineCode: $error?->decline_code,
                message: 'Too many requests. Retry shortly.',
                httpStatus: 429
            );
        }

        if ($e instanceof InvalidRequestException) {
            throw new StripePaymentException(
                type: 'InvalidRequestException',
                codeValue: $error?->code,
                declineCode: $error?->decline_code,
                message: 'Invalid payment request.',
                httpStatus: 400
            );
        }

        if ($e instanceof AuthenticationException) {
            throw new StripePaymentException(
                type: 'AuthenticationException',
                codeValue: $error?->code,
                declineCode: $error?->decline_code,
                message: 'Payment service configuration error.',
                httpStatus: 500
            );
        }

        if ($e instanceof ApiConnectionException) {
            throw new StripePaymentException(
                type: 'ApiConnectionException',
                codeValue: $error?->code,
                declineCode: $error?->decline_code,
                message: 'Unable to reach payment provider.',
                httpStatus: 503
            );
        }

        throw new StripePaymentException(
            type: 'ApiErrorException',
            codeValue: $error?->code,
            declineCode: $error?->decline_code,
            message: 'Unexpected payment error.',
            httpStatus: 502
        );
    }
}
