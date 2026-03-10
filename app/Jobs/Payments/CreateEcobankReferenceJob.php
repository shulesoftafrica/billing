<?php

namespace App\Jobs\Payments;

use App\Http\Controllers\Api\InvoiceController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrganizationPaymentGatewayIntegration;
use App\Models\Product;

class CreateEcobankReferenceJob extends ProcessGatewayReferenceJob
{
    protected function process(
        InvoiceController $controller,
        Invoice $invoice,
        Product $product,
        Customer $customer,
        OrganizationPaymentGatewayIntegration $orgGateway
    ): array {
        $merchant = $orgGateway->merchants()->first();

        if (!$merchant) {
            return [
                'success' => false,
                'message' => 'Merchant not found for Universal Control Number gateway',
            ];
        }

        return $controller->createControlNumber($merchant, $product, $customer, $orgGateway);
    }
}
