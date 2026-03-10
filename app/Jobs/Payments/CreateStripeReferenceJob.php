<?php

namespace App\Jobs\Payments;

use App\Http\Controllers\Api\InvoiceController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\Product;
use Illuminate\Http\Request;

class CreateStripeReferenceJob extends ProcessGatewayReferenceJob
{
    protected function process(
        InvoiceController $controller,
        Invoice $invoice,
        Product $product,
        Customer $customer,
        OrganizationPaymentGatewayIntegration $orgGateway
    ): array {
        $request = Request::create('/', 'POST', [
            'success_url' => $this->successUrl,
        ]);

        return $controller->createStripeReference($invoice, $product, $customer, $request, $orgGateway);
    }
}
