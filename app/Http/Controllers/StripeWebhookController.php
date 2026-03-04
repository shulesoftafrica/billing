<?php

namespace App\Http\Controllers;

use App\Models\ControlNumber;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                (string) config('services.stripe.webhook_secret')
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            Log::warning('Invalid Stripe webhook signature', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Invalid webhook signature',
            ], 400);
        }

        app()->terminating(function () use ($event): void {
            $this->processEvent($event->type, $event->data->object);
        });

        return response()->json([
            'success' => true,
        ], 200);
    }

    private function processEvent(string $eventType, mixed $object): void
    {
        if (!$object instanceof PaymentIntent) {
            Log::info('Ignoring Stripe event without PaymentIntent payload', [
                'event' => $eventType,
            ]);

            return;
        }

        match ($eventType) {
            'payment_intent.succeeded' => $this->handleSucceeded($object),
            'payment_intent.payment_failed' => $this->handleFailed($object),
            'payment_intent.canceled' => $this->handleCanceled($object),
            'payment_intent.requires_action' => $this->handleRequiresAction($object),
            'payment_intent.processing' => $this->handleProcessing($object),
            default => Log::info('Unhandled Stripe webhook event', ['event' => $eventType]),
        };
    }

    private function handleSucceeded(PaymentIntent $intent): void
    {
        $invoice = $this->resolveBillingRecord($intent);

        if (!$invoice) {
            Log::warning('Stripe succeeded event with unknown billing record', [
                'payment_intent_id' => $intent->id,
                'metadata' => $intent->metadata->toArray(),
            ]);

            return;
        }

        $invoice->status = 'paid';
        $invoice->save();

        Log::info('Stripe payment succeeded', [
            'invoice_id' => $invoice->id,
            'payment_intent_id' => $intent->id,
        ]);
    }

    private function handleFailed(PaymentIntent $intent): void
    {
        $invoice = $this->resolveBillingRecord($intent);

        Log::warning('Stripe payment failed', [
            'invoice_id' => $invoice?->id,
            'payment_intent_id' => $intent->id,
            'last_payment_error' => $intent->last_payment_error?->message,
        ]);

        if ($invoice) {
            $invoice->status = 'failed';
            $invoice->save();
        }
    }

    private function handleCanceled(PaymentIntent $intent): void
    {
        $invoice = $this->resolveBillingRecord($intent);

        if ($invoice) {
            $invoice->status = 'cancelled';
            $invoice->save();
        }
    }

    private function handleRequiresAction(PaymentIntent $intent): void
    {
        Log::info('Stripe payment requires additional action (3DS)', [
            'payment_intent_id' => $intent->id,
            'status' => $intent->status,
        ]);
    }

    private function handleProcessing(PaymentIntent $intent): void
    {
        $invoice = $this->resolveBillingRecord($intent);

        if ($invoice) {
            $invoice->status = 'processing';
            $invoice->save();
        }
    }

    private function resolveBillingRecord(PaymentIntent $intent): ?Invoice
    {
        $metadata = $intent->metadata->toArray();
        $orderId = $metadata['order_id'] ?? null;
        $userId = $metadata['user_id'] ?? null;

        if (is_numeric($orderId)) {
            $invoice = Invoice::find((int) $orderId);

            if ($invoice) {
                return $invoice;
            }
        }

        if (is_string($orderId) && $orderId !== '') {
            $invoice = Invoice::where('invoice_number', $orderId)->first();

            if ($invoice) {
                return $invoice;
            }

            $controlNumber = ControlNumber::where('reference', $orderId)->first();
            if ($controlNumber) {
                $metadata = is_string($controlNumber->metadata)
                    ? (json_decode($controlNumber->metadata, true) ?: [])
                    : ((array) $controlNumber->metadata);

                $invoiceId = data_get($metadata, 'invoice_id') ?? data_get($metadata, 'meta.invoice_id');

                if (is_numeric($invoiceId)) {
                    return Invoice::find((int) $invoiceId);
                }
            }
        }

        if (is_numeric($userId)) {
            return Invoice::where('customer_id', (int) $userId)
                ->orderByDesc('id')
                ->first();
        }

        return null;
     }
 }
