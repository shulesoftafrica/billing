<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\ControlNumber;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PricePlan;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-time backfill/reconciliation for a school that was billed OUTSIDE this
 * platform before it existed — already fully paid, partially paid, invoiced
 * but unpaid, or with nothing on file at all. Creates a real subscription +
 * invoice for the school (the same way POST /v1/subscriptions does, via
 * SubscriptionService::createSubscriptionsWithInvoice()), and optionally
 * records a payment already received against it.
 *
 * Designed to be run ONE SCHOOL AT A TIME, reviewed via --dry-run first,
 * and reconciled by a human against real records before committing — not a
 * batch/bulk tool. See resources/documentation/billing/
 * SCHOOL_SUBSCRIPTION_CONFIGURATION_GUIDE.md (shulesoft_newversion repo) for
 * the full explanation of when to use each --status value.
 *
 * IMPORTANT — how payment recording actually works here: SubscriptionService
 * ::enableSubscription() does NOT activate a subscription just because you
 * pass it a Payment with some amount. It only counts a payment toward the
 * invoice balance if that Payment's payment_reference matches a real
 * ControlNumber already generated for this customer+product (created
 * automatically the moment the subscription/invoice above is created). So
 * this command looks up that real ControlNumber and reuses its reference —
 * inventing an arbitrary reference would silently be ignored and the
 * subscription would never activate, even for a "paid" backfill. This was
 * confirmed by reading enableSubscription()'s full body, not assumed.
 *
 * Full vs. partial is NOT something you choose here — it's whatever
 * enableSubscription() itself decides by comparing --amount to the invoice
 * total: exactly equal (or more) activates the subscription and sets a real
 * end_date (required for subscriptions:renew to ever pick this school up
 * next year); anything less leaves it 'pending' with the partial amount
 * correctly recorded against the invoice (no proration — this system does
 * not shorten/recalculate the paid period from a partial amount).
 *
 * This command has NO access to shulesoft_newversion's database (separate
 * Postgres server/app) — it prints the exact values you need to apply to
 * shulesoft.setting yourself once you're satisfied with the result.
 */
class BackfillSchoolSubscription extends Command
{
    protected $signature = 'billing:backfill-subscription
        {username : School schema/customer username — matches customers.username}
        {name : Display name for the customer record}
        {--email= : Contact email}
        {--phone= : Contact phone}
        {--quantity=1 : Student count (per-student pricing multiplier)}
        {--plan= : Price plan id — REQUIRED, no default (differs between local/dev and production, verify the real id first)}
        {--status=unpaid : unpaid|paid — whether to record a payment already received. "Partial vs full" is not a choice: it is decided automatically by comparing --amount to the invoice total once recorded, matching real enableSubscription() behavior}
        {--amount= : Amount actually received. Required with --status=paid. May be less than the full invoice total — the subscription only activates if it is not less}
        {--reference= : A human note identifying the real-world payment (e.g. a deposit slip number), stored for your own records — NOT what enableSubscription() matches on}
        {--dry-run : Show exactly what would happen without creating or changing anything}';

    protected $description = 'Backfill a real subscription+invoice (and optionally a payment already received) for one school billed outside this platform. Run one school at a time.';

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $username = $this->argument('username');
        $name = $this->argument('name');
        $email = $this->option('email');
        $phone = $this->option('phone');
        $quantity = max(1, (int) $this->option('quantity'));
        $planId = $this->option('plan');
        $status = $this->option('status');
        $amountOption = $this->option('amount');
        $reference = $this->option('reference');
        $isDry = (bool) $this->option('dry-run');

        if (!$planId) {
            $this->error('--plan is required. The Tanzania per-student plan id differs between environments (local dev vs. production) — look it up and pass it explicitly, do not assume a default.');
            return self::FAILURE;
        }
        if (!in_array($status, ['unpaid', 'paid'], true)) {
            $this->error('--status must be "unpaid" or "paid". There is no separate "partial" flag — see the class docblock: whether a paid backfill fully activates or stays partially-paid is decided by comparing --amount to the real invoice total, the same way a real payment would be.');
            return self::FAILURE;
        }
        if ($status === 'paid' && !$amountOption) {
            $this->error('--amount is required with --status=paid.');
            return self::FAILURE;
        }

        $plan = PricePlan::with('product')->find($planId);
        if (!$plan) {
            $this->error("Price plan #{$planId} not found in this database.");
            return self::FAILURE;
        }

        $organizationId = $plan->product->organization_id;
        $invoiceTotal = (float) $plan->amount * $quantity;

        $this->info('=== Backfill preview ===' . ($isDry ? ' [DRY RUN]' : ''));
        $this->line("Customer: {$name} (username: {$username})");
        $this->line("Organization: #{$organizationId} | Plan: {$plan->name} (#{$plan->id})");
        $this->line("Quantity: {$quantity} x " . number_format((float) $plan->amount) . " = " . number_format($invoiceTotal));
        $this->line('Status: ' . $status . ($amountOption ? " (amount to record: " . number_format((float) $amountOption) . ")" : ''));

        if ($status === 'paid' && (float) $amountOption < $invoiceTotal) {
            $this->warn('--amount is less than the invoice total — this will be recorded as a PARTIAL payment. The subscription will stay pending, not active, and will not be picked up by the renewal cron until the balance is paid.');
        }

        if ($isDry) {
            $this->warn('Dry run — nothing created. Re-run without --dry-run to apply.');
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $customer = Customer::where('organization_id', $organizationId)->where('username', $username)->first();
            if ($customer) {
                $this->line("Using existing customer #{$customer->id}");
            } else {
                $customer = Customer::create([
                    'organization_id' => $organizationId,
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'status' => 'active',
                ]);
                $this->line("Created customer #{$customer->id}");
            }

            $invoice = $this->subscriptionService->createSubscriptionsWithInvoice($customer->id, [$plan->id], [$plan->id => $quantity]);
            $invoiceItem = $invoice->invoiceItems->first();
            $subscription = $invoiceItem?->subscription;
            if (!$subscription) {
                throw new \Exception('Invoice created but no linked subscription was found on it — aborting, nothing should be trusted from this run.');
            }
            $this->line("Created subscription #{$subscription->id}, invoice #{$invoice->id} ({$invoice->invoice_number}), total " . number_format((float) $invoice->total) . ", due {$invoice->due_date}");

            if ($status === 'paid') {
                $payment = $this->recordMatchedPayment($customer, $subscription, (float) $amountOption, $reference);
                $this->subscriptionService->enableSubscription($invoice->id, $subscription, (float) $invoiceItem->total, $payment);
                $subscription->refresh();
                $this->info("Payment recorded. Subscription status is now: {$subscription->status}" . ($subscription->status === 'active' ? " (end_date: {$subscription->end_date})" : ' (still pending — amount recorded was less than the invoice total).'));
            } else {
                $this->info('No payment recorded — invoice left open, subscription remains pending.');
            }

            DB::commit();

            $dueDate = \Carbon\Carbon::parse($invoice->due_date)->toDateString();
            $billingStatus = $subscription->status === 'active' ? 'ACTIVE' : 'PENDING_INVOICE';
            $this->newLine();
            $this->warn('=== Apply this on the shulesoft_newversion side — this command cannot reach that database ===');
            $this->line("UPDATE shulesoft.setting SET");
            $this->line("  billing_subscription_id = {$subscription->id},");
            $this->line("  billing_customer_id = {$customer->id},");
            $this->line("  billing_status = '{$billingStatus}',");
            $this->line("  has_invoice = 1,");
            $this->line("  invoice_date = '" . \Carbon\Carbon::parse($invoice->issued_at)->toDateString() . "',");
            $this->line("  next_payment_date = '{$dueDate}'");
            $this->line("WHERE schema_name = '{$username}';");
            $this->warn("Also confirm ai_lifecycle_status = 'live' for this school, or this data will be invisible to the gate/reminders.");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Backfill failed — nothing was committed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Creates a Payment that enableSubscription() will actually recognize —
     * reusing a real ControlNumber's reference for this customer+product
     * (auto-generated when the subscription/invoice above was created),
     * NOT an arbitrary made-up reference. See class docblock.
     */
    private function recordMatchedPayment(Customer $customer, $subscription, float $amount, ?string $reference): Payment
    {
        $productId = $subscription->pricePlan->product_id;

        $controlNumber = ControlNumber::where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->first();

        if (!$controlNumber) {
            throw new \Exception('No control number was generated for this customer/product — a payment gateway integration must be configured for this organization before a payment can be recorded here.');
        }

        $gateway = PaymentGateway::firstOrCreate(
            ['name' => 'Manual / Bank Deposit'],
            ['type' => 'bank', 'active' => true, 'webhook_secret' => Str::random(64)]
        );

        return Payment::create([
            'gateway_id' => $gateway->id,
            'customer_id' => $customer->id,
            'amount' => $amount,
            'notification_status' => 'n/a',
            'gateway_reference' => 'MANUAL-' . now()->timestamp . '-' . Str::random(8),
            'status' => 'pending', // enableSubscription() flips this to 'cleared' itself once matched
            'payment_reference' => $controlNumber->reference,
            'paid_at' => now(),
        ]);
    }
}
