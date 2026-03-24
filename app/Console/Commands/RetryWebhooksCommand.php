<?php

namespace App\Console\Commands;

use App\Services\WebhookDispatchService;
use Illuminate\Console\Command;

class RetryWebhooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed webhook deliveries that are due for retry';

    protected WebhookDispatchService $webhookDispatchService;

    /**
     * Create a new command instance.
     */
    public function __construct(WebhookDispatchService $webhookDispatchService)
    {
        parent::__construct();
        $this->webhookDispatchService = $webhookDispatchService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Retrying failed webhooks...');
        
        try {
            $retried = $this->webhookDispatchService->retryFailedWebhooks();
            
            if ($retried > 0) {
                $this->info("✅ Retried {$retried} webhook(s)");
            } else {
                $this->info('ℹ️  No webhooks pending retry');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to retry webhooks: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
