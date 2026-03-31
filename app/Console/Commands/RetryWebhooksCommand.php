<?php

namespace App\Console\Commands;

use App\Models\CustomWebhook;
use App\Models\Payment;
use App\Models\Subscription;
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

    protected $description = 'Retry failed webhook deliveries AND sweep all unsent events (payments + subscriptions) for every active webhook';

    /** Microseconds to sleep between consecutive requests to the same endpoint. */
    private const INTER_REQUEST_DELAY_US = 500_000; // 500 ms

    /** Extra sleep (seconds) when a 429 is received before moving to the next webhook. */
    private const RATE_LIMIT_BACKOFF_S = 10;

    /** Track webhook IDs that returned 429 this run — skip remaining items for those. */
    private array $rateLimitedWebhooks = [];

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
        $this->info('  Webhook Retry + Full Event Sweep');
        $this->info('  ' . now()->toDateTimeString() . ($dryRun ? '  [DRY RUN]' : ''));
        $this->info('======================================================');

        $exitCode  = Command::SUCCESS;
        $exitCode  = min($exitCode, $this->phaseRetryFailed($productId, $dryRun));
        $exitCode  = min($exitCode, $this->phaseUnsentPayments($productId, $dryRun));
        $exitCode  = min($exitCode, $this->phaseUnsentSubscriptionEvents($productId, $dryRun));

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

            // Skip webhooks that already hit a rate-limit this run
            $webhookId = $delivery->custom_webhook_id;
            if (isset($this->rateLimitedWebhooks[$webhookId])) {
                $this->warn("  ⏭  Skipping {$label} — webhook #{$webhookId} is rate-limited this run.");
                $bad++;
                continue;
            }

            try {
                $freshPayload = $this->rebuildPayload($delivery);

                if ($freshPayload !== null) {
                    // Update stored payload so future retries also get the correct schema.
                    $delivery->update(['payload' => json_encode($freshPayload)]);

                    $newDelivery = $this->dispatchService->dispatch(
                        $delivery->customWebhook,
                        $freshPayload,
                        $delivery->payment_id,
                        $delivery->subscription_id,
                    );

                    $delivery->update(['status' => 'superseded', 'next_retry_at' => null]);

                    if ($newDelivery->http_status_code === 429) {
                        $bad++;
                        $this->handleRateLimit($webhookId, $label);
                        continue;
                    }

                    if ($newDelivery->status === 'sent') {
                        $ok++;
                        $this->line("  ✅ Retried {$label} (fresh) — HTTP {$newDelivery->http_status_code} ({$newDelivery->duration_ms}ms)");
                        Log::info('[webhooks:retry] Phase-1 succeeded (fresh)', [
                            'original_delivery_id' => $delivery->id,
                            'new_delivery_id'      => $newDelivery->id,
                            'event_type'           => $delivery->event_type,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("  ⚠️  Failed {$label} (fresh) — {$newDelivery->error_message}");
                        Log::warning('[webhooks:retry] Phase-1 failed (fresh)', [
                            'original_delivery_id' => $delivery->id,
                            'new_delivery_id'      => $newDelivery->id,
                            'event_type'           => $delivery->event_type,
                            'error'                => $newDelivery->error_message,
                        ]);
                    }
                } else {
                    // Cannot rebuild from DB — replay the stored payload as-is.
                    $result = $this->dispatchService->retryDelivery($delivery);

                    if (($result['http_status'] ?? null) === 429) {
                        $bad++;
                        $this->handleRateLimit($webhookId, $label);
                        continue;
                    }

                    if ($result['success']) {
                        $ok++;
                        $this->line("  ✅ Retried {$label} (stored) — HTTP {$result['http_status']} ({$result['response_time']}ms)");
                        Log::info('[webhooks:retry] Phase-1 succeeded (stored)', [
                            'delivery_id' => $delivery->id,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("  ⚠️  Failed {$label} (stored) — {$result['error_message']}");
                        Log::warning('[webhooks:retry] Phase-1 failed (stored)', [
                            'delivery_id' => $delivery->id,
                            'error'       => $result['error_message'],
                        ]);
                    }
                }

                usleep(self::INTER_REQUEST_DELAY_US);
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
    // Phase 2 – sweep cleared/failed payments never successfully delivered
    // ------------------------------------------------------------------
    private function phaseUnsentPayments(?string $productId, bool $dryRun): int
    {
        $this->info('');
        $this->info('─── Phase 2: Sweep unsent payment events ───');

        /** @var array<string, string> event_type => payment status to match */
        $eventMap = [
            'payment.success' => 'cleared',
            'payment.failed'  => 'failed',
        ];

        $totalDispatched = 0;
        $totalFailed     = 0;

        foreach ($eventMap as $eventType => $paymentStatus) {
            $webhooks = $this->activeWebhooksForProducts($eventType, $productId);

            if ($webhooks->isEmpty()) {
                $this->line("  ℹ️  No active {$eventType} webhooks found.");
                continue;
            }

            $this->line("  Checking {$webhooks->count()} webhook(s) for {$eventType}...");

            [$ok, $bad] = $this->dispatchPayments($webhooks, $eventType, $paymentStatus, $dryRun);
            $totalDispatched += $ok;
            $totalFailed     += $bad;
        }

        $this->line("  Phase-2 result: {$totalDispatched} sent, {$totalFailed} failed.");

        return $totalFailed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Phase 3 – sweep subscription events never successfully delivered
    // ------------------------------------------------------------------
    private function phaseUnsentSubscriptionEvents(?string $productId, bool $dryRun): int
    {
        $this->info('');
        $this->info('─── Phase 3: Sweep unsent subscription events ───');

        /**
         * event_type => closure that further filters the Subscription query
         * @var array<string, \Closure>
         */
        $sweepMap = [
            'subscription.created'   => fn ($q) => $q,
            'subscription.cancelled' => fn ($q) => $q->where('status', 'cancelled'),
            'subscription.expired'   => fn ($q) => $q->where('status', 'expired'),
            'subscription.upgraded'  => fn ($q) => $q->whereNotNull('previous_plan_id'),
        ];

        $totalDispatched = 0;
        $totalFailed     = 0;

        foreach ($sweepMap as $eventType => $queryFilter) {
            $webhooks = $this->activeWebhooksForProducts($eventType, $productId);

            if ($webhooks->isEmpty()) {
                $this->line("  ℹ️  No active {$eventType} webhooks found.");
                continue;
            }

            $this->line("  Checking {$webhooks->count()} webhook(s) for {$eventType}...");

            [$ok, $bad] = $this->dispatchSubscriptions($webhooks, $eventType, $queryFilter, $dryRun);
            $totalDispatched += $ok;
            $totalFailed     += $bad;
        }

        $this->line("  Phase-3 result: {$totalDispatched} sent, {$totalFailed} failed.");

        return $totalFailed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Rebuild a fresh payload for a delivery based on its event_type.
     * Returns null if the source record (payment / subscription) is missing
     * or the event_type is unrecognised — caller falls back to stored payload.
     */
    private function rebuildPayload(WebhookDelivery $delivery): ?array
    {
        $eventType = $delivery->event_type;

        if (in_array($eventType, ['payment.success', 'payment.failed'], true) && $delivery->payment_id) {
            $payment = Payment::find($delivery->payment_id);
            if (!$payment) {
                return null;
            }
            return $eventType === 'payment.failed'
                ? $this->payloadBuilder->buildPaymentFailedPayload($payment)
                : $this->payloadBuilder->buildPaymentSuccessPayload($payment);
        }

        if (str_starts_with($eventType, 'subscription.') && $delivery->subscription_id) {
            $subscription = Subscription::with([
                'customer.organization',
                'pricePlan.product.organization',
            ])->find($delivery->subscription_id);

            if (!$subscription) {
                return null;
            }

            return match ($eventType) {
                'subscription.created'   => $this->payloadBuilder->buildSubscriptionCreatedPayload($subscription),
                'subscription.renewed'   => $this->payloadBuilder->buildSubscriptionRenewedPayload($subscription),
                'subscription.cancelled' => $this->payloadBuilder->buildSubscriptionCancelledPayload($subscription),
                'subscription.expired'   => $this->payloadBuilder->buildSubscriptionExpiredPayload($subscription),
                'subscription.upgraded'  => $this->payloadBuilder->buildSubscriptionUpgradedPayload($subscription),
                default                  => null,
            };
        }

        return null;
    }

    /**
     * Loop through a webhook collection and send unsent payments of the
     * given status/event_type. Returns [dispatched, failed].
     *
     * @param  \Illuminate\Support\Collection $webhooks
     * @return array{int, int}
     */
    private function dispatchPayments(
        \Illuminate\Support\Collection $webhooks,
        string $eventType,
        string $paymentStatus,
        bool $dryRun
    ): array {
        $totalOk  = 0;
        $totalBad = 0;

        foreach ($webhooks as $webhook) {
            $product = $webhook->product;

            if (!$product) {
                $this->warn("  ⚠️  Webhook #{$webhook->id} has no product — skipping.");
                continue;
            }

            // IDs already successfully sent for this event to THIS webhook
            $sentPaymentIds = WebhookDelivery::where('custom_webhook_id', $webhook->id)
                ->where('event_type', $eventType)
                ->where('status', 'sent')
                ->whereNotNull('payment_id')
                ->pluck('payment_id')
                ->toArray();

            // Payments belong to a product through invoice → invoice_items → price_plan.
            // Scope to the organization that owns the webhook's product so we never
            // send another org's customer data to this webhook endpoint.
            $payments = Payment::whereHas('invoice.invoiceItems.pricePlan', fn ($q) => $q->where('product_id', $product->id))
                ->whereHas('customer', fn ($q) => $q->where('organization_id', $product->organization_id))
                ->where('status', $paymentStatus)
                ->whereNotIn('id', $sentPaymentIds)
                ->orderBy('paid_at')
                ->get();

            if ($payments->isEmpty()) {
                $this->line("  ✔  Webhook #{$webhook->id} '{$webhook->name}' — all {$eventType} already delivered.");
                continue;
            }

            $this->line("  📬 Webhook #{$webhook->id} '{$webhook->name}' ({$product->name}) — {$payments->count()} unsent {$eventType}.");

            $ok  = 0;
            $bad = 0;

            foreach ($payments as $payment) {
                $label = "{$eventType} payment #{$payment->id} (" . number_format($payment->amount, 2) . ") → webhook #{$webhook->id}";

                if ($dryRun) {
                    $this->line("    [dry-run] would send {$label}");
                    continue;
                }

                // Stop sending to this webhook if it already hit rate-limit
                if (isset($this->rateLimitedWebhooks[$webhook->id])) {
                    $this->warn("    ⏭  Skipping {$label} — rate-limited this run.");
                    $bad++;
                    continue;
                }

                try {
                    $payload = $eventType === 'payment.failed'
                        ? $this->payloadBuilder->buildPaymentFailedPayload($payment)
                        : $this->payloadBuilder->buildPaymentSuccessPayload($payment);

                    $delivery = $this->dispatchService->dispatch($webhook, $payload, $payment->id);

                    if ($delivery->http_status_code === 429) {
                        $bad++;
                        $this->handleRateLimit($webhook->id, $label);
                        continue;
                    }

                    if ($delivery->status === 'sent') {
                        $ok++;
                        $this->line("    ✅ Sent {$label} — HTTP {$delivery->http_status_code} ({$delivery->duration_ms}ms)");
                        Log::info('[webhooks:retry] Phase-2 payment delivered', [
                            'event_type'  => $eventType,
                            'webhook_id'  => $webhook->id,
                            'payment_id'  => $payment->id,
                            'delivery_id' => $delivery->id,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("    ⚠️  Failed {$label} — {$delivery->error_message}");
                        Log::warning('[webhooks:retry] Phase-2 payment failed', [
                            'event_type'  => $eventType,
                            'webhook_id'  => $webhook->id,
                            'payment_id'  => $payment->id,
                            'error'       => $delivery->error_message,
                        ]);
                    }

                    usleep(self::INTER_REQUEST_DELAY_US);
                } catch (\Exception $e) {
                    $bad++;
                    $this->error("    ❌ Exception for {$label}: {$e->getMessage()}");
                    Log::error('[webhooks:retry] Phase-2 exception', [
                        'event_type' => $eventType,
                        'webhook_id' => $webhook->id,
                        'payment_id' => $payment->id,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            $totalOk  += $ok;
            $totalBad += $bad;

            if (!$dryRun) {
                $this->line("    → {$ok} sent, {$bad} failed for webhook #{$webhook->id}.");
            }
        }

        return [$totalOk, $totalBad];
    }

    /**
     * Loop through a webhook collection and send unsent subscription events.
     * Returns [dispatched, failed].
     *
     * @param  \Illuminate\Support\Collection $webhooks
     * @param  \Closure                       $queryFilter  Extra where clauses on Subscription query
     * @return array{int, int}
     */
    private function dispatchSubscriptions(
        \Illuminate\Support\Collection $webhooks,
        string $eventType,
        \Closure $queryFilter,
        bool $dryRun
    ): array {
        $totalOk  = 0;
        $totalBad = 0;

        foreach ($webhooks as $webhook) {
            $product = $webhook->product;

            if (!$product) {
                $this->warn("  ⚠️  Webhook #{$webhook->id} has no product — skipping.");
                continue;
            }

            // Subscription IDs already successfully delivered for this event to THIS webhook
            $sentSubscriptionIds = WebhookDelivery::where('custom_webhook_id', $webhook->id)
                ->where('event_type', $eventType)
                ->where('status', 'sent')
                ->whereNotNull('subscription_id')
                ->pluck('subscription_id')
                ->toArray();

            // Subscriptions belong to a product through price_plan.
            // Scope to the organization that owns the webhook's product so we never
            // send another org's customer data to this webhook endpoint.
            $subscriptionQuery = Subscription::with(['customer.organization', 'pricePlan'])
                ->whereHas('pricePlan', fn ($q) => $q->where('product_id', $product->id))
                ->whereHas('customer', fn ($q) => $q->where('organization_id', $product->organization_id))
                ->whereNotIn('id', $sentSubscriptionIds)
                ->orderBy('created_at');

            // Apply event-specific filter (e.g. where status = 'cancelled')
            $subscriptions = $queryFilter($subscriptionQuery)->get();

            if ($subscriptions->isEmpty()) {
                $this->line("  ✔  Webhook #{$webhook->id} '{$webhook->name}' — all {$eventType} already delivered.");
                continue;
            }

            $this->line("  📬 Webhook #{$webhook->id} '{$webhook->name}' ({$product->name}) — {$subscriptions->count()} unsent {$eventType}.");

            $ok  = 0;
            $bad = 0;

            foreach ($subscriptions as $subscription) {
                $label = "{$eventType} subscription #{$subscription->id} → webhook #{$webhook->id}";

                if ($dryRun) {
                    $this->line("    [dry-run] would send {$label}");
                    continue;
                }

                // Stop sending to this webhook if it already hit rate-limit
                if (isset($this->rateLimitedWebhooks[$webhook->id])) {
                    $this->warn("    ⏭  Skipping {$label} — rate-limited this run.");
                    $bad++;
                    continue;
                }

                try {
                    $payload = match ($eventType) {
                        'subscription.created'   => $this->payloadBuilder->buildSubscriptionCreatedPayload($subscription),
                        'subscription.renewed'   => $this->payloadBuilder->buildSubscriptionRenewedPayload($subscription),
                        'subscription.cancelled' => $this->payloadBuilder->buildSubscriptionCancelledPayload($subscription),
                        'subscription.expired'   => $this->payloadBuilder->buildSubscriptionExpiredPayload($subscription),
                        'subscription.upgraded'  => $this->payloadBuilder->buildSubscriptionUpgradedPayload($subscription),
                        default                  => null,
                    };

                    if ($payload === null) {
                        $this->warn("    ⚠️  No payload builder for {$eventType} — skipping.");
                        continue;
                    }

                    $delivery = $this->dispatchService->dispatch($webhook, $payload, null, $subscription->id);

                    if ($delivery->http_status_code === 429) {
                        $bad++;
                        $this->handleRateLimit($webhook->id, $label);
                        continue;
                    }

                    if ($delivery->status === 'sent') {
                        $ok++;
                        $this->line("    ✅ Sent {$label} — HTTP {$delivery->http_status_code} ({$delivery->duration_ms}ms)");
                        Log::info('[webhooks:retry] Phase-3 subscription delivered', [
                            'event_type'      => $eventType,
                            'webhook_id'      => $webhook->id,
                            'subscription_id' => $subscription->id,
                            'delivery_id'     => $delivery->id,
                        ]);
                    } else {
                        $bad++;
                        $this->warn("    ⚠️  Failed {$label} — {$delivery->error_message}");
                        Log::warning('[webhooks:retry] Phase-3 subscription failed', [
                            'event_type'      => $eventType,
                            'webhook_id'      => $webhook->id,
                            'subscription_id' => $subscription->id,
                            'error'           => $delivery->error_message,
                        ]);
                    }

                    usleep(self::INTER_REQUEST_DELAY_US);
                } catch (\Exception $e) {
                    $bad++;
                    $this->error("    ❌ Exception for {$label}: {$e->getMessage()}");
                    Log::error('[webhooks:retry] Phase-3 exception', [
                        'event_type'      => $eventType,
                        'webhook_id'      => $webhook->id,
                        'subscription_id' => $subscription->id,
                        'error'           => $e->getMessage(),
                    ]);
                }
            }

            $totalOk  += $ok;
            $totalBad += $bad;

            if (!$dryRun) {
                $this->line("    → {$ok} sent, {$bad} failed for webhook #{$webhook->id}.");
            }
        }

        return [$totalOk, $totalBad];
    }

    /**
     * Mark a webhook as rate-limited, back off, and log the event.
     */
    private function handleRateLimit(int $webhookId, string $label): void
    {
        $this->rateLimitedWebhooks[$webhookId] = true;
        $this->warn("    🚫 429 Too Many Requests for {$label} — webhook #{$webhookId} paused for this run. Remaining items will retry next scheduled run.");
        Log::warning('[webhooks:retry] Rate limited (429)', [
            'webhook_id' => $webhookId,
            'label'      => $label,
        ]);
        // Back off before continuing to the next webhook
        sleep(self::RATE_LIMIT_BACKOFF_S);
    }

    /**
     * Return all active CustomWebhooks (with their product) that listen to
     * $eventType, optionally filtered to a single product.
     *
     * @return \Illuminate\Support\Collection
     */
    private function activeWebhooksForProducts(string $eventType, ?string $productId): \Illuminate\Support\Collection
    {
        $query = CustomWebhook::with('product')
            ->where('status', 'active')
            ->where(function ($q) use ($eventType) {
                $q->whereNull('events')
                  ->orWhereJsonContains('events', $eventType)
                  ->orWhereJsonContains('events', '*');

                // e.g. "payment.*" covers both payment.success and payment.failed
                $parts = explode('.', $eventType);
                if (count($parts) >= 2) {
                    $q->orWhereJsonContains('events', $parts[0] . '.*');
                }
            });

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->get();
    }
}

