<?php

namespace App\Console\Commands;

use App\Models\ControlNumber;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Console\Command;

class DiagnoseControlNumbers extends Command
{
    protected $signature = 'diagnose:control-numbers {invoice_id}';
    protected $description = 'Diagnose control numbers for a specific invoice';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        $invoice = Invoice::with([
            'customer',
            'invoiceItems.pricePlan.product.productType',
        ])->find($invoiceId);

        if (!$invoice) {
            $this->error("Invoice {$invoiceId} not found");
            return 1;
        }

        $this->info("=== Invoice Details ===");
        $this->line("Invoice ID: {$invoice->id}");
        $this->line("Invoice Number: {$invoice->invoice_number}");
        $this->line("Customer ID: {$invoice->customer_id}");
        $this->line("Total: {$invoice->total}");
        $this->line("Status: {$invoice->status}");
        
        $this->info("\n=== Invoice Items ===");
        foreach ($invoice->invoiceItems as $item) {
            $product = $item->pricePlan->product;
            $productType = $product->productType;
            
            $this->line("  Item ID: {$item->id}");
            $this->line("  Product ID: {$product->id}");
            $this->line("  Product Name: {$product->name}");
            $this->line("  Product Type: {$productType->name} (ID: {$product->product_type_id})");
            $this->line("  Subscription ID: " . ($item->subscription_id ?: 'N/A'));
            $this->line("");
        }

        // Get all control numbers for this customer
        $productIds = $invoice->invoiceItems->pluck('pricePlan.product.id')->filter()->unique();
        
        $this->info("=== Control Numbers (Customer {$invoice->customer_id}, Products: " . $productIds->implode(', ') . ") ===");
        
        $controlNumbers = ControlNumber::with('organizationPaymentGatewayIntegration.paymentGateway')
            ->where('customer_id', $invoice->customer_id)
            ->whereIn('product_id', $productIds)
            ->get();

        if ($controlNumbers->isEmpty()) {
            $this->warn("No control numbers found!");
            return 0;
        }

        foreach ($controlNumbers as $cn) {
            $metadata = json_decode($cn->metadata, true);
            $gateway = $cn->organizationPaymentGatewayIntegration?->paymentGateway;
            
            $this->line("  ID: {$cn->id}");
            $this->line("  Reference: {$cn->reference}");
            $this->line("  Product ID: {$cn->product_id}");
            $this->line("  Gateway: " . ($gateway?->name ?: 'N/A'));
            
            // Extract invoice_id from metadata
            $metadataInvoiceId = $metadata['meta']['invoice_id'] ?? $metadata['invoice_id'] ?? 'NOT FOUND';
            $this->line("  Metadata Invoice ID: {$metadataInvoiceId}");
            
            // Check if it matches current invoice
            if ((int)$metadataInvoiceId === (int)$invoiceId) {
                $this->info("  ✓ Matches current invoice");
            } else {
                $this->error("  ✗ Does NOT match current invoice (expected: {$invoiceId})");
            }
            
            // Show payment URLs
            if (isset($metadata['payment_link'])) {
                $this->line("  Flutterwave Link: {$metadata['payment_link']}");
            }
            if (isset($metadata['client_secret'])) {
                $this->line("  Stripe Secret: {$metadata['client_secret']}");
            }
            
            $this->line("  Created: {$cn->created_at}");
            $this->line("");
        }

        return 0;
    }
}
