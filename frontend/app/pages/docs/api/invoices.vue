<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <NuxtLink to="/docs/api/customers" class="hover:text-gray-700">API Reference</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Invoices</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="invoices-api">Invoices API</h1>
      <p class="text-xl text-gray-600">
        Create, retrieve, and manage invoices for your customers. Invoices can be generated automatically from subscriptions or created manually.
      </p>
    </div>
    
    <!-- Invoice Object -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="invoice-object">The Invoice Object</h2>
      <p class="text-gray-700 mb-6">
        An invoice represents an itemized bill for a customer.
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Attributes</h3>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-mono text-gray-900">id <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Unique identifier for the invoice</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">customer_id <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">ID of the customer this invoice belongs to</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">subscription_id <span class="text-gray-500 font-sans">string | null</span></dt>
              <dd class="text-sm text-gray-600 mt-1">ID of the subscription if invoice was auto-generated</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">amount_due <span class="text-gray-500 font-sans">integer</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Total amount due in cents</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">amount_paid <span class="text-gray-500 font-sans">integer</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Amount already paid in cents</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">currency <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Three-letter ISO currency code</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">status <span class="text-gray-500 font-sans">enum</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Status: <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">draft</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">open</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">paid</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">void</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">uncollectible</code></dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">line_items <span class="text-gray-500 font-sans">array</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Array of line items on the invoice</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">due_date <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Date payment is due (ISO 8601)</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">paid_at <span class="text-gray-500 font-sans">string | null</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Date invoice was paid (ISO 8601)</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">created_at <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Timestamp of creation (ISO 8601)</dd>
            </div>
          </dl>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Example Object</h3>
          <CodeBlock :code="invoiceObjectExample" />
        </div>
      </div>
    </div>
    
    <!-- Create Invoice -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="create">Create an Invoice</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices</code>
      </p>
      <p class="text-gray-700 mb-6">
        Create a new invoice for a customer. Invoices start in <code class="text-sm bg-gray-100 px-1 py-0.5 rounded">draft</code> status and must be finalized before sending to customers.
      </p>
      
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Parameters</h3>
      <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameter</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">customer_id</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">The customer to invoice</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">line_items</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">array</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Array of line items</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">currency</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Three-letter ISO currency code (defaults to USD)</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">due_date</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Payment due date (ISO 8601)</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">metadata</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">object</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Set of key-value pairs for custom data</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Example Request</h3>
      <CodeBlock :code="createInvoiceExample" />
    </div>
    
    <!-- Retrieve Invoice -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="retrieve">Retrieve an Invoice</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">GET</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices/:id</code>
      </p>
      <p class="text-gray-700 mb-6">
        Retrieves the details of an existing invoice.
      </p>
      
      <CodeBlock :code="retrieveInvoiceExample" />
    </div>
    
    <!-- Finalize Invoice -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="finalize">Finalize an Invoice</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices/:id/finalize</code>
      </p>
      <p class="text-gray-700 mb-6">
        Finalize a draft invoice, changing its status to <code class="text-sm bg-gray-100 px-1 py-0.5 rounded">open</code>. An invoice cannot be edited after finalization.
      </p>
      
      <CodeBlock :code="finalizeInvoiceExample" />
    </div>
    
    <!-- Pay Invoice -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="pay">Pay an Invoice</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices/:id/pay</code>
      </p>
      <p class="text-gray-700 mb-6">
        Mark an invoice as paid. The invoice status will change to <code class="text-sm bg-gray-100 px-1 py-0.5 rounded">paid</code>.
      </p>
      
      <CodeBlock :code="payInvoiceExample" />
    </div>
    
    <!-- Void Invoice -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="void">Void an Invoice</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices/:id/void</code>
      </p>
      <p class="text-gray-700 mb-6">
        Mark an invoice as void. This cannot be undone.
      </p>
      
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">Warning</p>
            <p class="text-sm text-amber-800 mt-1">
              Voiding an invoice cannot be undone. Consider marking it as uncollectible instead if you may want to revert the action.
            </p>
          </div>
        </div>
      </div>
      
      <CodeBlock :code="voidInvoiceExample" />
    </div>
    
    <!-- List Invoices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="list">List Invoices</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">GET</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/invoices</code>
      </p>
      <p class="text-gray-700 mb-6">
        Returns a list of invoices. Supports filtering by customer, subscription, and status.
      </p>
      
      <CodeBlock :code="listInvoicesExample" />
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Learn more about invoices</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/guides/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices Guide</h3>
          <p class="text-sm text-gray-600">Best practices for managing invoices</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/payments" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Payments API</h3>
          <p class="text-sm text-gray-600">Process invoice payments</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks</h3>
          <p class="text-sm text-gray-600">Listen to invoice events</p>
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
  title: 'Invoices API Reference'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'invoices-api', text: 'Invoices API', level: 1 },
      { id: 'invoice-object', text: 'The Invoice Object', level: 2 },
      { id: 'create', text: 'Create an Invoice', level: 2 },
      { id: 'retrieve', text: 'Retrieve an Invoice', level: 2 },
      { id: 'finalize', text: 'Finalize an Invoice', level: 2 },
      { id: 'pay', text: 'Pay an Invoice', level: 2 },
      { id: 'void', text: 'Void an Invoice', level: 2 },
      { id: 'list', text: 'List Invoices', level: 2 },
    ])
  }
})

const invoiceObjectExample = {
  json: `{
  "id": "inv_1a2b3c4d5e",
  "customer_id": "cus_abc123",
  "subscription_id": "sub_xyz789",
  "amount_due": 5000,
  "amount_paid": 5000,
  "currency": "usd",
  "status": "paid",
  "line_items": [
    {
      "description": "Premium Plan",
      "amount": 5000,
      "quantity": 1
    }
  ],
  "due_date": "2024-02-15T00:00:00Z",
  "paid_at": "2024-01-20T14:30:00Z",
  "created_at": "2024-01-15T10:30:00Z"
}`
}

const createInvoiceExample = {
  bash: `curl -X POST https://api.billing.com/v1/invoices \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": "cus_abc123",
    "line_items": [
      {
        "description": "Consulting Services",
        "amount": 15000,
        "quantity": 1
      }
    ],
    "due_date": "2024-02-28T00:00:00Z"
  }'`,
  php: `$invoice = $client->invoices->create([
  'customer_id' => 'cus_abc123',
  'line_items' => [
    [
      'description' => 'Consulting Services',
      'amount' => 15000,
      'quantity' => 1
    ]
  ],
  'due_date' => '2024-02-28T00:00:00Z'
]);`,
  javascript: `const invoice = await billing.invoices.create({
  customer_id: 'cus_abc123',
  line_items: [
    {
      description: 'Consulting Services',
      amount: 15000,
      quantity: 1
    }
  ],
  due_date: '2024-02-28T00:00:00Z'
});`,
  python: `invoice = billing.Invoice.create(
  customer_id='cus_abc123',
  line_items=[
    {
      'description': 'Consulting Services',
      'amount': 15000,
      'quantity': 1
    }
  ],
  due_date='2024-02-28T00:00:00Z'
)`
}

const retrieveInvoiceExample = {
  bash: `curl https://api.billing.com/v1/invoices/inv_1a2b3c4d5e \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$invoice = $client->invoices->retrieve('inv_1a2b3c4d5e');`,
  javascript: `const invoice = await billing.invoices.retrieve('inv_1a2b3c4d5e');`,
  python: `invoice = billing.Invoice.retrieve('inv_1a2b3c4d5e')`
}

const finalizeInvoiceExample = {
  bash: `curl -X POST https://api.billing.com/v1/invoices/inv_1a2b3c4d5e/finalize \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$invoice = $client->invoices->finalize('inv_1a2b3c4d5e');`,
  javascript: `const invoice = await billing.invoices.finalize('inv_1a2b3c4d5e');`,
  python: `invoice = billing.Invoice.finalize('inv_1a2b3c4d5e')`
}

const payInvoiceExample = {
  bash: `curl -X POST https://api.billing.com/v1/invoices/inv_1a2b3c4d5e/pay \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$invoice = $client->invoices->pay('inv_1a2b3c4d5e');`,
  javascript: `const invoice = await billing.invoices.pay('inv_1a2b3c4d5e');`,
  python: `invoice = billing.Invoice.pay('inv_1a2b3c4d5e')`
}

const voidInvoiceExample = {
  bash: `curl -X POST https://api.billing.com/v1/invoices/inv_1a2b3c4d5e/void \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$invoice = $client->invoices->void('inv_1a2b3c4d5e');`,
  javascript: `const invoice = await billing.invoices.void('inv_1a2b3c4d5e');`,
  python: `invoice = billing.Invoice.void('inv_1a2b3c4d5e')`
}

const listInvoicesExample = {
  bash: `curl "https://api.billing.com/v1/invoices?customer_id=cus_abc123&status=paid&limit=10" \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$invoices = $client->invoices->list([
  'customer_id' => 'cus_abc123',
  'status' => 'paid',
  'limit' => 10
]);`,
  javascript: `const invoices = await billing.invoices.list({
  customer_id: 'cus_abc123',
  status: 'paid',
  limit: 10
});`,
  python: `invoices = billing.Invoice.list(
  customer_id='cus_abc123',
  status='paid',
  limit=10
)`
}
</script>
