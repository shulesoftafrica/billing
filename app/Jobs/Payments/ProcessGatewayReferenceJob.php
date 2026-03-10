<?php

namespace App\Jobs\Payments;

use App\Http\Controllers\Api\InvoiceController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class ProcessGatewayReferenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        protected int $invoiceId,
        protected int $productId,
        protected int $customerId,
        protected int $organizationGatewayId,
        protected ?string $successUrl = null
    ) {
        $this->onQueue('payments');
    }

    public function backoff(): array
    {
        return [60, 180, 600];
    }

    abstract protected function process(
        InvoiceController $controller,
        Invoice $invoice,
        Product $product,
        Customer $customer,
        OrganizationPaymentGatewayIntegration $orgGateway
    ): array;

    public function handle(): void
    {
        $invoice = Invoice::find($this->invoiceId);
        $product = Product::find($this->productId);
        $customer = Customer::find($this->customerId);
        $orgGateway = OrganizationPaymentGatewayIntegration::with(['paymentGateway', 'merchants'])
            ->find($this->organizationGatewayId);

        if (!$invoice || !$product || !$customer || !$orgGateway) {
            Log::warning('Skipping payment reference job due to missing dependency', [
                'invoice_id' => $this->invoiceId,
                'product_id' => $this->productId,
                'customer_id' => $this->customerId,
                'organization_gateway_id' => $this->organizationGatewayId,
                'job' => static::class,
            ]);
            $this->markFailure('Missing invoice/product/customer/gateway dependency');
            return;
        }

        $controller = app(InvoiceController::class);
        $result = $this->process($controller, $invoice, $product, $customer, $orgGateway);

        if (($result['success'] ?? false) === true) {
            $this->markSuccess();
            return;
        }

        $this->markFailure((string) ($result['message'] ?? 'Reference creation failed'));
    }

    protected function markSuccess(): void
    {
        Log::info('Payment reference created successfully', [
            'invoice_id' => $this->invoiceId,
            'product_id' => $this->productId,
            'customer_id' => $this->customerId,
            'organization_gateway_id' => $this->organizationGatewayId,
            'job' => static::class,
        ]);
    }

    protected function markFailure(string $error): void
    {
        Log::error('Payment reference creation failed', [
            'invoice_id' => $this->invoiceId,
            'product_id' => $this->productId,
            'customer_id' => $this->customerId,
            'organization_gateway_id' => $this->organizationGatewayId,
            'job' => static::class,
            'error' => mb_substr($error, 0, 1000),
        ]);
    }
}
