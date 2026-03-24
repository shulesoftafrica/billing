<?php

namespace App\Console\Commands;

use App\Models\CustomWebhook;
use App\Models\WebhookDelivery;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Product;
use App\Services\WebhookDispatchService;
use App\Services\PayloadBuilderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:dispatch 
                            {--delivery= : Resend a specific webhook delivery by ID}
                            {--product= : Product ID to dispatch webhooks for}
                            {--event= : Event type (e.g., payment.success, invoice.paid)}
                            {--payment= : Payment ID to build payload from}
                            {--invoice= : Invoice ID to build payload from}
                            {--subscription= : Subscription ID to build payload from}
                            {--failed : Only retry failed deliveries}
                            {--all : Dispatch to all webhooks for the product}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually dispatch/resend webhooks to third-party applications';

    protected WebhookDispatchService $webhookDispatchService;
    protected PayloadBuilderService $payloadBuilderService;

    /**
     * Create a new command instance.
     */
    public function __construct(
        WebhookDispatchService $webhookDispatchService,
        PayloadBuilderService $payloadBuilderService
    ) {
        parent::__construct();
        $this->webhookDispatchService = $webhookDispatchService;
        $this->payloadBuilderService = $payloadBuilderService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Option 1: Resend specific delivery
            if ($deliveryId = $this->option('delivery')) {
                return $this->resendDelivery($deliveryId);
            }

            // Option 2: Retry failed deliveries for a product
            if ($this->option('failed')) {
                return $this->retryFailedDeliveries();
            }

            // Option 3: Dispatch new webhook
            if ($productId = $this->option('product')) {
                return $this->dispatchToProduct($productId);
            }

            $this->error('❌ Please provide one of the following options:');
            $this->line('  --delivery=ID    : Resend a specific webhook delivery');
            $this->line('  --failed         : Retry all failed deliveries');
            $this->line('  --product=ID     : Dispatch webhooks for a product');
            $this->line('');
            $this->line('Examples:');
            $this->line('  php artisan webhooks:dispatch --delivery=123');
            $this->line('  php artisan webhooks:dispatch --failed');
            $this->line('  php artisan webhooks:dispatch --product=1 --event=payment.success --payment=456');

            return Command::FAILURE;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Webhook dispatch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Resend a specific webhook delivery
     */
    protected function resendDelivery(int $deliveryId): int
    {
        $this->info("🔄 Resending webhook delivery #{$deliveryId}...");

        $delivery = WebhookDelivery::with('webhook')->find($deliveryId);

        if (!$delivery) {
            $this->error("❌ Webhook delivery #{$deliveryId} not found");
            return Command::FAILURE;
        }

        $this->info("Webhook: {$delivery->webhook->name}");
        $this->info("URL: {$delivery->webhook->url}");
        $this->info("Event: {$delivery->event_type}");
        $this->info("Status: {$delivery->status}");
        $this->info("Attempts: {$delivery->attempts}");

        if (!$this->confirm('Do you want to resend this webhook?', true)) {
            $this->info('Cancelled');
            return Command::SUCCESS;
        }

        try {
            $response = $this->webhookDispatchService->retryDelivery($delivery);

            if ($response['success']) {
                $this->info("✅ Webhook resent successfully");
                $this->line("HTTP Status: {$response['http_status']}");
                $this->line("Response Time: {$response['response_time']}ms");
            } else {
                $this->error("❌ Webhook delivery failed");
                $this->line("Error: {$response['error_message']}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to resend webhook: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Retry all failed webhook deliveries
     */
    protected function retryFailedDeliveries(): int
    {
        $this->info('🔄 Retrying all failed webhook deliveries...');

        $productId = $this->option('product');
        
        $query = WebhookDelivery::where('status', 'failed')
            ->where('attempts', '<', 3)
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now());

        if ($productId) {
            $query->whereHas('webhook', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $failedDeliveries = $query->with('webhook')->get();

        if ($failedDeliveries->isEmpty()) {
            $this->info('ℹ️  No failed deliveries to retry');
            return Command::SUCCESS;
        }

        $this->info("Found {$failedDeliveries->count()} failed delivery(s) to retry");

        $bar = $this->output->createProgressBar($failedDeliveries->count());
        $bar->start();

        $successCount = 0;
        $failureCount = 0;

        foreach ($failedDeliveries as $delivery) {
            try {
                $response = $this->webhookDispatchService->retryDelivery($delivery);
                
                if ($response['success']) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } catch (\Exception $e) {
                $failureCount++;
                Log::error("Failed to retry delivery #{$delivery->id}", [
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Completed: {$successCount} succeeded, {$failureCount} failed");

        return Command::SUCCESS;
    }

    /**
     * Dispatch webhook to a product
     */
    protected function dispatchToProduct(int $productId): int
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->error("❌ Product #{$productId} not found");
            return Command::FAILURE;
        }

        $this->info("📦 Product: {$product->name} (ID: {$productId})");

        $eventType = $this->option('event');
        if (!$eventType) {
            $this->error('❌ --event option is required when using --product');
            $this->line('Example: --event=payment.success');
            return Command::FAILURE;
        }

        // Build payload based on provided resource
        $payload = $this->buildPayload($eventType);

        if (!$payload) {
            $this->error('❌ Failed to build payload. Provide --payment, --invoice, or --subscription option');
            return Command::FAILURE;
        }

        $this->info("Event: {$eventType}");
        $this->info("Payload keys: " . implode(', ', array_keys($payload)));

        if (!$this->confirm('Do you want to dispatch this webhook?', true)) {
            $this->info('Cancelled');
            return Command::SUCCESS;
        }

        try {
            $results = $this->webhookDispatchService->dispatchToProduct($product, $eventType, $payload);

            $this->info("✅ Dispatched to {$results['dispatched']} webhook(s)");
            
            if ($results['successful'] > 0) {
                $this->info("✅ {$results['successful']} succeeded");
            }
            
            if ($results['failed'] > 0) {
                $this->warn("⚠️  {$results['failed']} failed");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Failed to dispatch webhooks: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Build payload from provided resource
     */
    protected function buildPayload(string $eventType): ?array
    {
        // Payment-based events
        if ($paymentId = $this->option('payment')) {
            $payment = Payment::with(['invoice', 'customer', 'gateway'])->find($paymentId);
            
            if (!$payment) {
                $this->error("❌ Payment #{$paymentId} not found");
                return null;
            }

            $this->info("Payment: #{$payment->id} - {$payment->amount} {$payment->invoice->currency->code}");

            if (str_contains($eventType, 'payment.success')) {
                return $this->payloadBuilderService->buildPaymentSuccessPayload($payment);
            } elseif (str_contains($eventType, 'payment.failed')) {
                return $this->payloadBuilderService->buildPaymentFailedPayload($payment);
            }
        }

        // Invoice-based events
        if ($invoiceId = $this->option('invoice')) {
            $invoice = Invoice::with(['customer', 'items'])->find($invoiceId);
            
            if (!$invoice) {
                $this->error("❌ Invoice #{$invoiceId} not found");
                return null;
            }

            $this->info("Invoice: #{$invoice->invoice_number} - {$invoice->total_amount}");

            if (str_contains($eventType, 'invoice.created')) {
                return $this->payloadBuilderService->buildInvoiceCreatedPayload($invoice);
            } elseif (str_contains($eventType, 'invoice.paid')) {
                return $this->payloadBuilderService->buildInvoicePaidPayload($invoice);
            }
        }

        // Subscription-based events
        if ($subscriptionId = $this->option('subscription')) {
            $subscription = Subscription::with(['customer', 'pricePlan'])->find($subscriptionId);
            
            if (!$subscription) {
                $this->error("❌ Subscription #{$subscriptionId} not found");
                return null;
            }

            $this->info("Subscription: #{$subscription->id} - {$subscription->status}");

            if (str_contains($eventType, 'subscription.created')) {
                return $this->payloadBuilderService->buildSubscriptionCreatedPayload($subscription);
            } elseif (str_contains($eventType, 'subscription.cancelled')) {
                return $this->payloadBuilderService->buildSubscriptionCancelledPayload($subscription);
            }
        }

        return null;
    }
}
