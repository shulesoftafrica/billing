<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Services\WebhookDispatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Detects subscriptions whose paid period has ended and creates the next
 * period's subscription + invoice, reusing SubscriptionService::
 * createSubscriptionsWithInvoice() (the same method POST /v1/subscriptions
 * uses) rather than duplicating its invoice/subscription-creation logic.
 *
 * end_date is the one real, populated field marking period-end —
 * next_billing_date/current_period_end are declared in the schema but never
 * written by any code path today, confirmed by reading the whole of
 * SubscriptionService.php.
 *
 * Reuses the SAME quantity as the expiring subscription (e.g. student
 * count) — this command has no way to know if that number changed since
 * the prior period; a fresher count would need to be pushed in before this
 * runs. Out of scope here.
 */
class RenewExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew {--dry-run : Show what would be renewed without creating anything}';

    protected $description = 'Create the next period\'s subscription + invoice for every active subscription whose end_date has passed.';

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly WebhookDispatchService $webhookDispatchService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDry = (bool) $this->option('dry-run');
        $this->info('Checking for expiring subscriptions' . ($isDry ? ' [DRY RUN]' : ''));

        $expiring = Subscription::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', today())
            ->with(['customer', 'pricePlan'])
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('No subscriptions due for renewal.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiring->count()} subscription(s) due for renewal.");

        $renewed = 0;
        $failed = 0;

        foreach ($expiring as $subscription) {
            $label = "subscription #{$subscription->id} (customer #{$subscription->customer_id}, plan #{$subscription->price_plan_id}, qty {$subscription->quantity})";

            if ($isDry) {
                $this->line("  [DRY] Would renew {$label}, ended {$subscription->end_date->toDateString()}.");
                continue;
            }

            try {
                $invoice = $this->subscriptionService->createSubscriptionsWithInvoice(
                    $subscription->customer_id,
                    [$subscription->price_plan_id],
                    [$subscription->price_plan_id => $subscription->quantity]
                );

                $newSubscription = $invoice->invoiceItems->first()?->subscription;
                if (!$newSubscription) {
                    throw new \Exception('New invoice created but no linked subscription found on it.');
                }

                $subscription->update(['status' => 'expired']);
                $this->webhookDispatchService->dispatchSubscriptionRenewed($newSubscription);

                Log::info('RenewExpiringSubscriptions: renewed', [
                    'old_subscription_id' => $subscription->id,
                    'new_subscription_id' => $newSubscription->id,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $subscription->customer_id,
                ]);

                $this->line("  Renewed {$label} -> new subscription #{$newSubscription->id}, invoice #{$invoice->id}.");
                $renewed++;
            } catch (\Throwable $e) {
                Log::warning('RenewExpiringSubscriptions: failed to renew', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Failed to renew {$label}: {$e->getMessage()}");
                $failed++;
            }
        }

        if ($isDry) {
            $this->warn('Dry run — nothing created.');
        } else {
            $this->info("{$renewed} renewed, {$failed} failed.");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
