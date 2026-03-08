<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Subscription;

class WebhookPaymentProcessingService
{
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
    }

    public function processByProductAndCustomer(?Product $product, ?Customer $customer, Payment $payment): void
    {
        
        if (!$product || !$customer) {
            return;
        }

        if ((int) $product->product_type_id === 2) {
            $pricePlanIds = $product->pricePlans()->pluck('id');

            $subscription = Subscription::where('customer_id', $customer->id)
                ->whereIn('price_plan_id', $pricePlanIds)
                ->where('status', 'pending')
                ->first();

            if (!$subscription) {
                return;
            }

            $invoiceItem = InvoiceItem::where('price_plan_id', $subscription->price_plan_id)
                ->where('subscription_id', $subscription->id)
                ->first();

            if ($invoiceItem) {
                $this->subscriptionService->enableSubscription(
                    $invoiceItem->invoice_id,
                    $subscription,
                    (float) $invoiceItem->total,
                    $payment
                );
            }

            return;
        }

        if ((int) $product->product_type_id === 1) {
            $this->subscriptionService->getOneTimePendingInvoice($product->id, $customer->id, $payment);
            return;
        }

        if ((int) $product->product_type_id === 3) {
            $this->subscriptionService->createProductPurchase($product->id, $customer->id, $payment);
        }
    }

    public function processByInvoice(Invoice $invoice, Payment $payment): void
    {
        $invoice->loadMissing('customer', 'invoiceItems.pricePlan.product');

        $customer = $invoice->customer;
        if (!$customer) {
            return;
        }

        $products = $invoice->invoiceItems
            ->map(fn (InvoiceItem $item) => $item->pricePlan?->product)
            ->filter()
            ->unique('id');

        foreach ($products as $product) {
            $this->processByProductAndCustomer($product, $customer, $payment);
        }
    }
}
