<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <NuxtLink to="/docs/guides/customers" class="hover:text-gray-700">Guides</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Invoices</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="invoices-guide">Invoice Management Guide</h1>
      <p class="text-xl text-gray-600">
        Best practices for creating, customizing, and managing invoices for your customers.
      </p>
    </div>
    
    <!-- Introduction -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="introduction">Understanding Invoices</h2>
      <p class="text-gray-700 mb-6">
        Invoices are generated automatically from subscriptions or can be created manually for one-time charges. This guide covers both scenarios.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 class="font-semibold text-blue-900 mb-3">Automatic Invoices</h3>
          <p class="text-sm text-blue-800 mb-3">Generated from subscriptions on billing cycle</p>
          <ul class="space-y-2 text-sm text-blue-800">
            <li>✓ Created automatically</li>
            <li>✓ Include subscription items</li>
            <li>✓ Charged on due date</li>
          </ul>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
          <h3 class="font-semibold text-green-900 mb-3">Manual Invoices</h3>
          <p class="text-sm text-green-800 mb-3">Created for one-time charges or services</p>
          <ul class="space-y-2 text-sm text-green-800">
            <li>✓ Created via API</li>
            <li>✓ Custom line items</li>
            <li>✓ Flexible due dates</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Invoice Lifecycle -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="lifecycle">Invoice Lifecycle</h2>
      <p class="text-gray-700 mb-6">
        Invoices move through different states from creation to payment:
      </p>
      
      <div class="space-y-4">
        <div class="flex items-center">
          <div class="w-32 flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">DRAFT</span>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">Invoice is being created. Can be edited before finalization.</p>
          </div>
        </div>
        
        <div class="flex items-center">
          <div class="w-8 h-8 flex items-center justify-center ml-12">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
          </div>
        </div>
        
        <div class="flex items-center">
          <div class="w-32 flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">OPEN</span>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">Invoice finalized and sent to customer. Awaiting payment.</p>
          </div>
        </div>
        
        <div class="flex items-center">
          <div class="w-8 h-8 flex items-center justify-center ml-12">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
          </div>
        </div>
        
        <div class="flex items-center">
          <div class="w-32 flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">PAID</span>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-700">Payment received. Invoice is complete.</p>
          </div>
        </div>
        
        <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
          <p class="text-sm font-medium text-gray-900 mb-2">Alternative States:</p>
          <div class="space-y-2">
            <div class="flex items-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-3">VOID</span>
              <p class="text-sm text-gray-600">Invoice was voided and will not be paid</p>
            </div>
            <div class="flex items-center">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 mr-3">UNCOLLECTIBLE</span>
              <p class="text-sm text-gray-600">Payment attempts failed; marked as uncollectible</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Creating Manual Invoices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="creating-invoices">Creating Manual Invoices</h2>
      <p class="text-gray-700 mb-6">
        Create invoices for one-time services, consulting work, or custom charges:
      </p>
      
      <CodeBlock :code="createInvoiceExample" />
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-900">Draft State</p>
            <p class="text-sm text-blue-800 mt-1">
              New invoices start in <code class="bg-blue-100 px-1 py-0.5 rounded">draft</code> status. Finalize them before sending to customers.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Customization -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="customization">Customizing Invoices</h2>
      <p class="text-gray-700 mb-6">
        Add custom fields, branding, and metadata to your invoices:
      </p>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Custom Metadata</h3>
          <p class="text-gray-700 mb-4">Store custom data with invoices for your internal tracking:</p>
          <CodeBlock :code="metadataExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Custom Due Dates</h3>
          <p class="text-gray-700 mb-4">Set payment terms (e.g., Net 30, Net 60):</p>
          <CodeBlock :code="dueDateExample" />
        </div>
      </div>
    </div>
    
    <!-- Sending Invoices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="sending">Sending Invoices to Customers</h2>
      <p class="text-gray-700 mb-6">
        After finalizing an invoice, send it to your customer via email:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">1. Finalize Invoice</h3>
          <CodeBlock :code="finalizeExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">2. Send Email</h3>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <p class="text-sm text-gray-700 mb-3">The API automatically sends an email when you finalize an invoice, or you can use your own email service:</p>
            <CodeBlock :code="sendEmailExample" />
          </div>
        </div>
      </div>
    </div>
    
    <!-- Payment Collection -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="payment-collection">Collecting Payment</h2>
      <p class="text-gray-700 mb-6">
        There are several ways to collect payment on an invoice:
      </p>
      
      <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-2">Automatic Collection</h3>
          <p class="text-sm text-gray-700 mb-3">
            For customers with saved payment methods, payments are automatically attempted on the due date.
          </p>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Recommended</span>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-2">Payment Links</h3>
          <p class="text-sm text-gray-700 mb-3">
            Include a payment link in the invoice email. Customer clicks to pay online.
          </p>
          <code class="text-xs text-gray-600">https://billing.com/pay/inv_1a2b3c4d5e</code>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-2">Manual Payment</h3>
          <p class="text-sm text-gray-700 mb-3">
            For offline payments (check, wire transfer), manually mark the invoice as paid.
          </p>
          <CodeBlock :code="manualPaymentExample" />
        </div>
      </div>
    </div>
    
    <!-- Failed Payments -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="failed-payments">Handling Failed Payments</h2>
      <p class="text-gray-700 mb-6">
        When automatic payment fails, listen for webhooks and take action:
      </p>
      
      <CodeBlock :code="failedPaymentWebhook" />
      
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">Best Practice</p>
            <p class="text-sm text-amber-800 mt-1">
              Retry failed payments automatically using smart retry logic. Don't give up after one failure.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="best-practices">Best Practices</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">📧 Clear Communication</h3>
          <p class="text-sm text-gray-700">
            Send invoice emails with clear payment instructions, due dates, and what the charge is for.
          </p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">💰 Payment Reminders</h3>
          <p class="text-sm text-gray-700">
            Send reminder emails 3 days before and on the due date to reduce late payments.
          </p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font font-semibold text-gray-900 mb-3">🔍 Detailed Line Items</h3>
          <p class="text-sm text-gray-700">
            Use descriptive line item descriptions so customers understand exactly what they're paying for.
          </p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">📊 Track Metrics</h3>
          <p class="text-sm text-gray-700">
            Monitor collection rates, average days to payment, and failed payment trends.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Learn more about invoicing</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/api/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices API</h3>
          <p class="text-sm text-gray-600">Complete API reference</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscriptions Guide</h3>
          <p class="text-sm text-gray-600">Automatic invoicing for subscriptions</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/payments" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Payments Guide</h3>
          <p class="text-sm text-gray-600">Process invoice payments</p>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import CodeBlock from '~/components/documentation/CodeBlock.vue'

definePageMeta({
  layout: 'docs'
})

useHead({
  title: 'Invoice Management Guide'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'invoices-guide', text: 'Invoice Management', level: 1 },
      { id: 'introduction', text: 'Understanding Invoices', level: 2 },
      { id: 'lifecycle', text: 'Invoice Lifecycle', level: 2 },
      { id: 'creating-invoices', text: 'Creating Manual Invoices', level: 2 },
      { id: 'customization', text: 'Customizing Invoices', level: 2 },
      { id: 'sending', text: 'Sending Invoices', level: 2 },
      { id: 'payment-collection', text: 'Collecting Payment', level: 2 },
      { id: 'failed-payments', text: 'Failed Payments', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
    ])
  }
})

const createInvoiceExample = {
  bash: `curl -X POST https://api.billing.com/v1/invoices \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": "cus_abc123",
    "line_items": [
      {
        "description": "Website Design Services",
        "amount": 250000,
        "quantity": 1
      },
      {
        "description": "Logo Design",
        "amount": 50000,
        "quantity": 1
      }
    ],
    "due_date": "2024-03-15T00:00:00Z"
  }'`,
  php: `$invoice = $client->invoices->create([
  'customer_id' => 'cus_abc123',
  'line_items' => [
    [
      'description' => 'Website Design Services',
      'amount' => 250000,
      'quantity' => 1
    ],
    [
      'description' => 'Logo Design',
      'amount' => 50000,
      'quantity' => 1
    ]
  ],
  'due_date' => '2024-03-15T00:00:00Z'
]);`
}

const metadataExample = {
  php: `$invoice = $client->invoices->create([
  'customer_id' => 'cus_abc123',
  'line_items' => [...],
  'metadata' => [
    'project_id' => 'proj_123',
    'purchase_order' => 'PO-2024-001',
    'department' => 'Marketing'
  ]
]);`
}

const dueDateExample = {
  php: `// Net 30 (due in 30 days)
$dueDate = now()->addDays(30);

$invoice = $client->invoices->create([
  'customer_id' => 'cus_abc123',
  'line_items' => [...],
  'due_date' => $dueDate->toIso8601String()
]);`
}

const finalizeExample = {
  php: `$invoice = $client->invoices->finalize('inv_1a2b3c4d5e');`
}

const sendEmailExample = {
  php: `// Using Laravel Mail
Mail::to($customer->email)->send(
  new InvoiceCreated($invoice)
);`
}

const manualPaymentExample = {
  php: `// Mark as paid after receiving offline payment
$invoice = $client->invoices->pay('inv_1a2b3c4d5e');`
}

const failedPaymentWebhook = {
  php: `public function handleWebhook(Request $request)
{
  $event = $request->all();
  
  if ($event['type'] === 'invoice.payment_failed') {
    $invoice = $event['data'];
    
    // Notify customer
    $customer = Customer::find($invoice['customer_id']);
    Mail::to($customer->email)->send(
      new PaymentFailedEmail($invoice)
    );
    
    // Schedule retry in 3 days
    RetryPayment::dispatch($invoice['id'])
      ->delay(now()->addDays(3));
  }
  
  return response()->json(['status' => 'success']);
}`
}
</script>
