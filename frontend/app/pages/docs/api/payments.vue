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
        <span>Payments</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="payments-api">Payments API</h1>
      <p class="text-xl text-gray-600">
        Process and manage payments from your customers. Accept various payment methods including cards, bank transfers, and mobile money.
      </p>
    </div>
    
    <!-- Payment Object -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="payment-object">The Payment Object</h2>
      <p class="text-gray-700 mb-6">
        A payment represents a financial transaction from a customer.
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Attributes</h3>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-mono text-gray-900">id <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Unique identifier for the payment</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">amount <span class="text-gray-500 font-sans">integer</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Payment amount in cents</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">currency <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Three-letter ISO currency code</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">status <span class="text-gray-500 font-sans">enum</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Status: <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">pending</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">succeeded</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">failed</code>, <code class="text-xs bg-gray-100 px-1 py-0.5 rounded">refunded</code></dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">payment_method <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Payment method: card, bank_transfer, mobile_money</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">customer_id <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">ID of the customer making the payment</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">invoice_id <span class="text-gray-500 font-sans">string | null</span></dt>
              <dd class="text-sm text-gray-600 mt-1">ID of the invoice being paid</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">description <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Description of the payment</dd>
            </div>
            <div>
              <dt class="text-sm font-mono text-gray-900">created_at <span class="text-gray-500 font-sans">string</span></dt>
              <dd class="text-sm text-gray-600 mt-1">Timestamp of creation (ISO 8601)</dd>
            </div>
          </dl>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Example Object</h3>
          <CodeBlock :code="paymentObjectExample" />
        </div>
      </div>
    </div>
    
    <!-- Create Payment -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="create">Create a Payment</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/payments</code>
      </p>
      <p class="text-gray-700 mb-6">
        Create a new payment to charge a customer.
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
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">amount</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">integer</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Amount to charge in cents</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">currency</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Three-letter ISO currency code</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">customer_id</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">The customer to charge</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">payment_method</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Yes</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Payment method ID or type</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">invoice_id</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Invoice this payment is for</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">description</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Description for the payment</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="text-lg font-semibold text-gray-900 mb-3">Example Request</h3>
      <CodeBlock :code="createPaymentExample" />
    </div>
    
    <!-- Retrieve Payment -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="retrieve">Retrieve a Payment</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">GET</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/payments/:id</code>
      </p>
      <p class="text-gray-700 mb-6">
        Retrieves the details of an existing payment.
      </p>
      
      <CodeBlock :code="retrievePaymentExample" />
    </div>
    
    <!-- Refund Payment -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="refund">Refund a Payment</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">POST</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/payments/:id/refund</code>
      </p>
      <p class="text-gray-700 mb-6">
        Refund a payment, either fully or partially.
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
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">amount</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">integer</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Amount to refund (omit for full refund)</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">reason</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">string</td>
              <td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">No</span></td>
              <td class="px-6 py-4 text-sm text-gray-700">Reason for the refund</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <CodeBlock :code="refundPaymentExample" />
    </div>
    
    <!-- List Payments -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="list">List Payments</h2>
      <p class="text-gray-700 mb-4">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">GET</span>
        <code class="text-sm bg-gray-100 px-2 py-1 rounded">/v1/payments</code>
      </p>
      <p class="text-gray-700 mb-6">
        Returns a list of payments. Supports filtering by customer, invoice, and status.
      </p>
      
      <CodeBlock :code="listPaymentsExample" />
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Learn more about payments</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/guides/payments" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Payments Guide</h3>
          <p class="text-sm text-gray-600">Best practices for handling payments</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices API</h3>
          <p class="text-sm text-gray-600">Manage customer invoices</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks</h3>
          <p class="text-sm text-gray-600">Listen to payment events</p>
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
  title: 'Payments API Reference'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'payments-api', text: 'Payments API', level: 1 },
      { id: 'payment-object', text: 'The Payment Object', level: 2 },
      { id: 'create', text: 'Create a Payment', level: 2 },
      { id: 'retrieve', text: 'Retrieve a Payment', level: 2 },
      { id: 'refund', text: 'Refund a Payment', level: 2 },
      { id: 'list', text: 'List Payments', level: 2 },
    ])
  }
})

const paymentObjectExample = {
  json: `{
  "id": "pay_1a2b3c4d5e",
  "amount": 5000,
  "currency": "usd",
  "status": "succeeded",
  "payment_method": "card",
  "customer_id": "cus_abc123",
  "invoice_id": "inv_xyz789",
  "description": "Payment for Premium Plan",
  "created_at": "2024-01-15T10:30:00Z"
}`
}

const createPaymentExample = {
  bash: `curl -X POST https://api.billing.com/v1/payments \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "amount": 5000,
    "currency": "usd",
    "customer_id": "cus_abc123",
    "payment_method": "card",
    "description": "Premium subscription payment"
  }'`,
  php: `$payment = $client->payments->create([
  'amount' => 5000,
  'currency' => 'usd',
  'customer_id' => 'cus_abc123',
  'payment_method' => 'card',
  'description' => 'Premium subscription payment'
]);`,
  javascript: `const payment = await billing.payments.create({
  amount: 5000,
  currency: 'usd',
  customer_id: 'cus_abc123',
  payment_method: 'card',
  description: 'Premium subscription payment'
});`,
  python: `payment = billing.Payment.create(
  amount=5000,
  currency='usd',
  customer_id='cus_abc123',
  payment_method='card',
  description='Premium subscription payment'
)`
}

const retrievePaymentExample = {
  bash: `curl https://api.billing.com/v1/payments/pay_1a2b3c4d5e \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$payment = $client->payments->retrieve('pay_1a2b3c4d5e');`,
  javascript: `const payment = await billing.payments.retrieve('pay_1a2b3c4d5e');`,
  python: `payment = billing.Payment.retrieve('pay_1a2b3c4d5e')`
}

const refundPaymentExample = {
  bash: `curl -X POST https://api.billing.com/v1/payments/pay_1a2b3c4d5e/refund \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "amount": 2500,
    "reason": "Customer request"
  }'`,
  php: `$payment = $client->payments->refund('pay_1a2b3c4d5e', [
  'amount' => 2500,
  'reason' => 'Customer request'
]);`,
  javascript: `const payment = await billing.payments.refund('pay_1a2b3c4d5e', {
  amount: 2500,
  reason: 'Customer request'
});`,
  python: `payment = billing.Payment.refund(
  'pay_1a2b3c4d5e',
  amount=2500,
  reason='Customer request'
)`
}

const listPaymentsExample = {
  bash: `curl "https://api.billing.com/v1/payments?customer_id=cus_abc123&status=succeeded&limit=10" \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$payments = $client->payments->list([
  'customer_id' => 'cus_abc123',
  'status' => 'succeeded',
  'limit' => 10
]);`,
  javascript: `const payments = await billing.payments.list({
  customer_id: 'cus_abc123',
  status: 'succeeded',
  limit: 10
});`,
  python: `payments = billing.Payment.list(
  customer_id='cus_abc123',
  status='succeeded',
  limit=10
)`
}
</script>
