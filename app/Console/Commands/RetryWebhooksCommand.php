<?php

namespace App\Console\Commands;

use App\Models\CustomWebhook;
use App\Models\Payment;
use App\Models\WebhookDelivery;
use App\Services\PayloadBuilderService;
use App\Services\WebhookDispatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryWebhooksCommand extends Command
{
    protected $signature = 'webhooks:retry
                            {--product= : Limit to a specific product ID}
                            {--dry-run  : Show what would be sent without actually sending}';

    protected $description = 'Retry failed webhook deliveries AND send payment.success for cleared payments that were never delivered';

    public function __construct(
        private readonly WebhookDispatchService $dispatchService,
        private readonly PayloadBuilderService  $payloadBuilder,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $productId = $this->option('product');

        $this->info('======================================================');
        $this->info('  Webhook Retry + Unsent-Payment Sweep');
        $this->info('  ' . now()->toDateTimeString() . ($dryRun ? '  [DRY RUN]' : ''));
        $this->info('======================================================');

        $exitCode  = Command::SUCCESS;
        $exitCode  = min($exitCode, $this->phaseRetryFailed($productId, $dryRun));
        $exitCode  = min($exitCode, $this->phaseUnsentPayments($productId, $dryRun));

        $this->info('');
        $this->info('✅ Done.');

        return $exitCode;
    }

    // ------------------------------------------------------------------
    // Phase 1 – retry existing webhook_deliveries that previously failed
    // ------------------------------------------------------------------
    private function phaseRetryFailed(?string $productId, bool $dryRun): int
    {
        $this->info('');
        $this->info('─── Phase 1: Retry failed/pending deliveries ───');

        $query = WebhookDelivery::pendingRetry()->with('customWebhook');

        if ($productId) {
            $query->whereHas('customWebhook', fn ($q) => $q->where('product_id', $productId));
        }

        $deliveries = $query->get();

        if ($deliveries->isEmpty()) {
            $this->line('  ℹ️  No deliveries pending retry.');
            return Command::SUCCESS;
        }

        $this->line("  Found {$deliveries->count()} delivery(s) pending retry.");

        $ok  = 0;
        $bad = 0;

        foreach ($deliveries as $delivery) {
            $label = "delivery #{$delivery->id} → webhook #{$delivery->custom_webhook_id} ({$delivery->event_type})";

            if ($dryRun) {
                $this->line("  [dry-run] would retry {$label}");
                continue;
            }

            try {
                // If we have a payment_id, rebuild the payload fresh so any
                // payload schema fixes are applied rather than replaying a
                // stale stored payload.
                if ($delivery->payment_id) {
                    $payment = Payment::find($delivery->payment_id);

                    if (!$payment) {
                        $bad++;
                        $this->warn("  ⚠️  Skipped {$label} — payment #{$delivery->payment_id} not found");
                        continue;
                    }

                    $freshPayload = $this->payloadBuilder->buildPaymentSuccessPayload($payment);

                    // Overwrite stored payload with the fresh one so future
                    // retries also have the correct structure.
                    $delivery->update(['payload' => json_encode($freshPayload)]);

                    $newDelivery = $this->dispatchService->dispatch(
                        $delivery->customWebhook,
                        $freshPayload,
                        $payment->id
                    );

                    // Mark the original delivery as superseded so it doesn't
                    // keep appearing in the retry queue.
                    $delivery->update(['status' => 'superseded', 'next_retry_at' => null]);

                    if ($newDelivery->status === 'sent') {
                        $ok++;
                        $this->line("  ✅ Retried {$label} (fresh payload) — HTTP {$newDelivery->http_status_code} ({$newDelivery->duration_ms}ms)");
                        Log::info('[webhooks:retry] Phase-1 delivery succeeded (fresh payload)', [
                            'original_delivery_id' => $delivery->id,
                            'new_delivery_id'      => $newDelivery->id,
                            'webhook_id'           => $delivery->custom_webhook_id,
                            'payment_id'           => $payment->id,
                            'http_status'          => $newDelivery->http_status_code,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("  ⚠️  Failed {$label} (fresh payload) — {$newDelivery->error_message}");
                        Log::warning('[webhooks:retry] Phase-1 delivery failed (fresh payload)', [
                            'original_delivery_id' => $delivery->id,
                            'new_delivery_id'      => $newDelivery->id,
                            'webhook_id'           => $delivery->custom_webhook_id,
                            'payment_id'           => $payment->id,
                            'error'                => $newDelivery->error_message,
                        ]);
                    }
                } else {
                    // No payment_id — fall back to resending stored payload.
                    $result = $this->dispatchService->retryDelivery($delivery);

                    if ($result['success']) {
                        $ok++;
                        $this->line("  ✅ Retried {$label} — HTTP {$result['http_status']} ({$result['response_time']}ms)");
                        Log::info('[webhooks:retry] Phase-1 delivery succeeded', [
                            'delivery_id'  => $delivery->id,
                            'webhook_id'   => $delivery->custom_webhook_id,
                            'http_status'  => $result['http_status'],
                            'duration_ms'  => $result['response_time'],
                        ]);
                    } else {
                        $bad++;
                        $this->warn("  ⚠️  Failed {$label} — {$result['error_message']}");
                        Log::warning('[webhooks:retry] Phase-1 delivery failed', [
                            'delivery_id'  => $delivery->id,
                            'webhook_id'   => $delivery->custom_webhook_id,
                            'error'        => $result['error_message'],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $bad++;
                $this->error("  ❌ Exception for {$label}: {$e->getMessage()}");
                Log::error('[webhooks:retry] Phase-1 exception', [
                    'delivery_id' => $delivery->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        $this->line("  Phase-1 result: {$ok} succeeded, {$bad} failed.");

        return Command::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Phase 2 – find cleared payments with no successful delivery record
    //           for each active product webhook and send them
    // ------------------------------------------------------------------
    private function phaseUnsentPayments(?string $productId, bool $dryRun): int
    {
        $this->info('');
        $this->info('─── Phase 2: Sweep cleared payments never delivered ───');

        // Load all active webhooks that listen to payment.success (or wildcard)
        $webhookQuery = CustomWebhook::with('product')
            ->where('status', 'active')
            ->where(function ($q) {
                // events is null/empty (trigger on all) OR contains payment.success / payment.* / *
                $q->whereNull('events')
                  ->orWhereJsonContains('events', 'payment.success')
                  ->orWhereJsonContains('events', 'payment.*')
                  ->orWhereJsonContains('events', '*');
            });

        if ($productId) {
            $webhookQuery->where('product_id', $productId);
        }

        $webhooks = $webhookQuery->get();

        if ($webhooks->isEmpty()) {
            $this->line('  ℹ️  No active payment.success webhooks found.');
            return Command::SUCCESS;
        }

        $this->line("  Checking {$webhooks->count()} active webhook(s)...");

        $totalDispatched = 0;
        $totalFailed     = 0;

        foreach ($webhooks as $webhook) {
            $product = $webhook->product;

            if (!$product) {
                $this->warn("  ⚠️  Webhook #{$webhook->id} has no product — skipping.");
                continue;
            }

            // IDs already successfully sent to THIS webhook
            $sentPaymentIds = WebhookDelivery::where('custom_webhook_id', $webhook->id)
                ->where('status', 'sent')
                ->whereNotNull('payment_id')
                ->pluck('payment_id')
                ->toArray();

            // Cleared payments for this product not yet sent to this webhook
            $payments = Payment::whereHas('customer', fn ($q) => $q->where('product_id', $product->id))
                ->where('status', 'cleared')
                ->whereNotIn('id', $sentPaymentIds)
                ->orderBy('paid_at')
                ->get();

            if ($payments->isEmpty()) {
                $this->line("  ✔  Webhook #{$webhook->id} '{$webhook->name}' — all payments already delivered.");
                continue;
            }

            $this->line("  📬 Webhook #{$webhook->id} '{$webhook->name}' ({$product->name}) — {$payments->count()} unsent payment(s).");

            $ok  = 0;
            $bad = 0;

            foreach ($payments as $payment) {
                $label = "payment #{$payment->id} (" . number_format($payment->amount, 2) . ") to webhook #{$webhook->id}";

                if ($dryRun) {
                    $this->line("    [dry-run] would send {$label}");
                    continue;
                }

                try {
                    $payload  = $this->payloadBuilder->buildPaymentSuccessPayload($payment);
                    $delivery = $this->dispatchService->dispatch($webhook, $payload, $payment->id);

                    if ($delivery->status === 'sent') {
                        $ok++;
                        $this->line("    ✅ Sent {$label} — HTTP {$delivery->http_status_code} ({$delivery->duration_ms}ms)");
                        Log::info('[webhooks:retry] Phase-2 payment delivered', [
                            'webhook_id'   => $webhook->id,
                            'webhook_name' => $webhook->name,
                            'payment_id'   => $payment->id,
                            'delivery_id'  => $delivery->id,
                            'http_status'  => $delivery->http_status_code,
                            'duration_ms'  => $delivery->duration_ms,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("    ⚠️  Failed {$label} — {$delivery->error_message}");
                        Log::warning('[webhooks:retry] Phase-2 payment delivery failed', [
                            'webhook_id'   => $webhook->id,
                            'payment_id'   => $payment->id,
                            'delivery_id'  => $delivery->id,
                            'error'        => $delivery->error_message,
                        ]);
                    }
                } catch (\Exception $e) {
                    $bad++;
                    $this->error("    ❌ Exception for {$label}: {$e->getMessage()}");
                    Log::error('[webhooks:retry] Phase-2 exception', [
                        'webhook_id' => $webhook->id,
                        'payment_id' => $payment->id,
                        'error'      => $e->getMessage(),
                        'trace'      => $e->getTraceAsString(),
                    ]);
                }
            }

            $totalDispatched += $ok;
            $totalFailed     += $bad;

            if (!$dryRun) {
                $this->line("    → {$ok} sent, {$bad} failed for webhook #{$webhook->id}.");
            }
        }

        $this->line("  Phase-2 result: {$totalDispatched} sent, {$totalFailed} failed.");

        return $totalFailed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
