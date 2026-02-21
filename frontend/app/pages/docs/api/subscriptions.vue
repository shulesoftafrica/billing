<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <NuxtLink to="/docs/api" class="hover:text-gray-700">API Reference</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Subscriptions</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="subscriptions-api">Subscriptions API</h1>
      <p class="text-xl text-gray-600">
        Create and manage recurring subscriptions. Subscriptions automatically bill customers on a regular schedule.
      </p>
    </div>
    
    <!-- Object Overview -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="subscription-object">The Subscription Object</h2>
      <p class="text-gray-600 mb-6">
        Subscription objects represent recurring billing arrangements. They link a customer to a price plan and handle automatic billing.
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Attributes</h3>
          <div class="space-y-4">
            <div v-for="attr in subscriptionAttributes" :key="attr.name" class="border-l-4 border-primary-200 pl-4">
              <div class="flex items-baseline">
                <code class="text-sm font-mono text-primary-600">{{ attr.name }}</code>
                <span class="ml-2 text-xs text-gray-500">{{ attr.type }}</span>
                <span v-if="attr.required" class="ml-2 text-xs px-2 py-0.5 bg-error-100 text-error-700 rounded">required</span>
              </div>
              <p class="text-sm text-gray-600 mt-1">{{ attr.description }}</p>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Example Object</h3>
          <CodeBlock :code="subscriptionObjectExample" language="json" filename="subscription.json" />
        </div>
      </div>
    </div>
    
    <!-- Create Subscription -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="create-subscription">Create a Subscription</h2>
      <p class="text-gray-600 mb-6">
        Creates a new subscription for a customer. The subscription will begin immediately unless a start date is specified.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-success-100 text-success-700 text-xs font-semibold rounded mr-3">POST</span>
          <code class="text-sm font-mono">/api/v1/subscriptions</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Parameters</h3>
      <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Required</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="param in createSubscriptionParams" :key="param.name">
              <td class="px-4 py-3 text-sm font-mono text-primary-600">{{ param.name }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.type }}</td>
              <td class="px-4 py-3 text-sm">
                <span v-if="param.required" class="text-error-600">Yes</span>
                <span v-else class="text-gray-500">No</span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="createSubscriptionExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="createSubscriptionResponse" language="json" />
    </div>
    
    <!-- Retrieve Subscription -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="retrieve-subscription">Retrieve a Subscription</h2>
      <p class="text-gray-600 mb-6">
        Retrieves the details of an existing subscription by its unique ID.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded mr-3">GET</span>
          <code class="text-sm font-mono">/api/v1/subscriptions/:id</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="retrieveSubscriptionExample" runnable />
    </div>
    
    <!-- Update Subscription -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="update-subscription">Update a Subscription</h2>
      <p class="text-gray-600 mb-6">
        Updates the specified subscription. You can update the price plan, quantity, or metadata.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-warning-100 text-warning-700 text-xs font-semibold rounded mr-3">PUT</span>
          <code class="text-sm font-mono">/api/v1/subscriptions/:id</code>
        </div>
      </div>
      
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-primary-900">Pro Tip</p>
            <p class="text-sm text-primary-800 mt-1">
              When changing plans, you can set proration_behavior to determine how billing is handled mid-cycle.
            </p>
          </div>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="updateSubscriptionExample" runnable />
    </div>
    
    <!-- Cancel Subscription -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="cancel-subscription">Cancel a Subscription</h2>
      <p class="text-gray-600 mb-6">
        Cancels a subscription. By default, the subscription remains active until the end of the billing period, then cancels.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-error-100 text-error-700 text-xs font-semibold rounded mr-3">POST</span>
          <code class="text-sm font-mono">/api/v1/subscriptions/:id/cancel</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Parameters</h3>
      <div class="border-l-4 border-primary-200 pl-4 mb-6">
        <div class="flex items-baseline">
          <code class="text-sm font-mono text-primary-600">cancel_at_period_end</code>
          <span class="ml-2 text-xs text-gray-500">boolean</span>
        </div>
        <p class="text-sm text-gray-600 mt-1">
          If true, cancel at the end of the billing period. If false, cancel immediately. Default: true
        </p>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="cancelSubscriptionExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="cancelSubscriptionResponse" language="json" />
    </div>
    
    <!-- List Subscriptions -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="list-subscriptions">List All Subscriptions</h2>
      <p class="text-gray-600 mb-6">
        Returns a list of subscriptions. The subscriptions are sorted by creation date, with the most recent first.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded mr-3">GET</span>
          <code class="text-sm font-mono">/api/v1/subscriptions</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Query Parameters</h3>
      <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="param in listSubscriptionsParams" :key="param.name">
              <td class="px-4 py-3 text-sm font-mono text-primary-600">{{ param.name }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.type }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.default }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="listSubscriptionsExample" runnable />
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Explore related APIs and guides</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/guides/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscription Guide</h3>
          <p class="text-sm text-gray-600">Best practices for managing subscriptions</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices API</h3>
          <p class="text-sm text-gray-600">View and manage subscription invoices</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/customers" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Customers API</h3>
          <p class="text-sm text-gray-600">Manage customer records</p>
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
  title: 'Subscriptions API Reference'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'subscriptions-api', text: 'Subscriptions API', level: 1 },
      { id: 'subscription-object', text: 'The Subscription Object', level: 2 },
      { id: 'create-subscription', text: 'Create a Subscription', level: 2 },
      { id: 'retrieve-subscription', text: 'Retrieve a Subscription', level: 2 },
      { id: 'update-subscription', text: 'Update a Subscription', level: 2 },
      { id: 'cancel-subscription', text: 'Cancel a Subscription', level: 2 },
      { id: 'list-subscriptions', text: 'List All Subscriptions', level: 2 },
    ])
  }
})

const subscriptionAttributes = [
  { name: 'id', type: 'string', description: 'Unique identifier for the subscription', required: false },
  { name: 'customer_id', type: 'string', description: 'ID of the customer being billed', required: true },
  { name: 'price_plan_id', type: 'string', description: 'ID of the price plan', required: true },
  { name: 'status', type: 'enum', description: 'Status: active, canceled, past_due, trialing', required: false },
  { name: 'quantity', type: 'integer', description: 'Quantity of the subscription (for metered billing)', required: false },
  { name: 'current_period_start', type: 'timestamp', description: 'Start of the current billing period', required: false },
  { name: 'current_period_end', type: 'timestamp', description: 'End of the current billing period', required: false },
  { name: 'cancel_at', type: 'timestamp', description: 'When the subscription will be canceled', required: false },
  { name: 'metadata', type: 'object', description: 'Key-value pairs for additional information', required: false },
]

const subscriptionObjectExample = `{
  "id": "sub_5k3j2l1m0n",
  "customer_id": "cus_9h3k2j1l0m",
  "price_plan_id": "plan_premium_monthly",
  "status": "active",
  "quantity": 1,
  "current_period_start": "2024-01-15T00:00:00Z",
  "current_period_end": "2024-02-15T00:00:00Z",
  "cancel_at": null,
  "metadata": {
    "feature_tier": "premium"
  },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T10:30:00Z"
}`

const createSubscriptionParams = [
  { name: 'customer_id', type: 'string', required: true, description: 'The ID of the customer to subscribe' },
  { name: 'price_plan_id', type: 'string', required: true, description: 'The ID of the price plan' },
  { name: 'quantity', type: 'integer', required: false, description: 'Quantity (default: 1)' },
  { name: 'trial_days', type: 'integer', required: false, description: 'Number of trial days before billing starts' },
  { name: 'start_date', type: 'timestamp', required: false, description: 'When to start the subscription (default: now)' },
  { name: 'metadata', type: 'object', required: false, description: 'Key-value pairs for additional data' },
]

const createSubscriptionExample = {
  bash: `curl -X POST https://api.billing.com/v1/subscriptions \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": "cus_9h3k2j1l0m",
    "price_plan_id": "plan_premium_monthly",
    "quantity": 1,
    "trial_days": 14
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$subscription = $client->subscriptions->create([
  'customer_id' => 'cus_9h3k2j1l0m',
  'price_plan_id' => 'plan_premium_monthly',
  'quantity' => 1,
  'trial_days' => 14
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const subscription = await billing.subscriptions.create({
  customer_id: 'cus_9h3k2j1l0m',
  price_plan_id: 'plan_premium_monthly',
  quantity: 1,
  trial_days: 14
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

subscription = billing.Subscription.create(
  customer_id='cus_9h3k2j1l0m',
  price_plan_id='plan_premium_monthly',
  quantity=1,
  trial_days=14
)`
}

const createSubscriptionResponse = `{
  "id": "sub_5k3j2l1m0n",
  "customer_id": "cus_9h3k2j1l0m",
  "price_plan_id": "plan_premium_monthly",
  "status": "trialing",
  "quantity": 1,
  "current_period_start": "2024-01-15T00:00:00Z",
  "current_period_end": "2024-02-15T00:00:00Z",
  "trial_end": "2024-01-29T00:00:00Z",
  "cancel_at": null,
  "created_at": "2024-01-15T10:30:00Z"
}`

const retrieveSubscriptionExample = {
  bash: `curl https://api.billing.com/v1/subscriptions/sub_5k3j2l1m0n \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client = new BillingClient('sk_test_abc123');
$subscription = $client->subscriptions->retrieve('sub_5k3j2l1m0n');`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');
const subscription = await billing.subscriptions.retrieve('sub_5k3j2l1m0n');`,
  python: `import billing
billing.api_key = 'sk_test_abc123'
subscription = billing.Subscription.retrieve('sub_5k3j2l1m0n')`
}

const updateSubscriptionExample = {
  bash: `curl -X PUT https://api.billing.com/v1/subscriptions/sub_5k3j2l1m0n \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "price_plan_id": "plan_enterprise_monthly",
    "proration_behavior": "create_prorations"
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$subscription = $client->subscriptions->update('sub_5k3j2l1m0n', [
  'price_plan_id' => 'plan_enterprise_monthly',
  'proration_behavior' => 'create_prorations'
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const subscription = await billing.subscriptions.update('sub_5k3j2l1m0n', {
  price_plan_id: 'plan_enterprise_monthly',
  proration_behavior: 'create_prorations'
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

subscription = billing.Subscription.modify('sub_5k3j2l1m0n',
  price_plan_id='plan_enterprise_monthly',
  proration_behavior='create_prorations'
)`
}

const cancelSubscriptionExample = {
  bash: `curl -X POST https://api.billing.com/v1/subscriptions/sub_5k3j2l1m0n/cancel \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "cancel_at_period_end": true
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$subscription = $client->subscriptions->cancel('sub_5k3j2l1m0n', [
  'cancel_at_period_end' => true
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const subscription = await billing.subscriptions.cancel('sub_5k3j2l1m0n', {
  cancel_at_period_end: true
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

subscription = billing.Subscription.cancel('sub_5k3j2l1m0n',
  cancel_at_period_end=True
)`
}

const cancelSubscriptionResponse = `{
  "id": "sub_5k3j2l1m0n",
  "customer_id": "cus_9h3k2j1l0m",
  "price_plan_id": "plan_premium_monthly",
  "status": "active",
  "cancel_at": "2024-02-15T00:00:00Z",
  "cancel_at_period_end": true,
  "current_period_end": "2024-02-15T00:00:00Z"
}`

const listSubscriptionsParams = [
  { name: 'limit', type: 'integer', default: '10', description: 'Number of subscriptions to return (max 100)' },
  { name: 'customer_id', type: 'string', default: 'null', description: 'Filter by customer ID' },
  { name: 'status', type: 'string', default: 'null', description: 'Filter by status (active, canceled, etc.)' },
]

const listSubscriptionsExample = {
  bash: `curl "https://api.billing.com/v1/subscriptions?customer_id=cus_9h3k2j1l0m" \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client = new BillingClient('sk_test_abc123');
$subscriptions = $client->subscriptions->all(['customer_id' => 'cus_9h3k2j1l0m']);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');
const subscriptions = await billing.subscriptions.list({ 
  customer_id: 'cus_9h3k2j1l0m' 
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'
subscriptions = billing.Subscription.list(customer_id='cus_9h3k2j1l0m')`
}
</script>
