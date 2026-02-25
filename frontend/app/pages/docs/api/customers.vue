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
        <span>Customers</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="customers-api">Customers API</h1>
      <p class="text-xl text-gray-600">
        Create and manage customer records. Customers represent your end-users who will be billed for subscriptions or one-time payments.
      </p>
    </div>
    
    <!-- Object Overview -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="customer-object">The Customer Object</h2>
      <p class="text-gray-600 mb-6">
        Customer objects contain all information about an end-user, including their KYC status, payment methods, and subscription history.
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Attributes</h3>
          <div class="space-y-4">
            <div v-for="attr in customerAttributes" :key="attr.name" class="border-l-4 border-primary-200 pl-4">
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
          <CodeBlock :code="customerObjectExample" language="json" filename="customer.json" />
        </div>
      </div>
    </div>
    
    <!-- Create Customer -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="create-customer">Create a Customer</h2>
      <p class="text-gray-600 mb-6">
        Creates a new customer object with the provided details. Email is required and must be unique.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-success-100 text-success-700 text-xs font-semibold rounded mr-3">POST</span>
          <code class="text-sm font-mono">/api/v1/customers</code>
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
            <tr v-for="param in createCustomerParams" :key="param.name">
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
      <CodeBlock :code="createCustomerExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="createCustomerResponse" language="json" />
    </div>
    
    <!-- Retrieve Customer -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="retrieve-customer">Retrieve a Customer</h2>
      <p class="text-gray-600 mb-6">
        Retrieves the details of an existing customer by their unique ID.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded mr-3">GET</span>
          <code class="text-sm font-mono">/api/v1/customers/:id</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Path Parameters</h3>
      <div class="border-l-4 border-primary-200 pl-4 mb-6">
        <div class="flex items-baseline">
          <code class="text-sm font-mono text-primary-600">id</code>
          <span class="ml-2 text-xs text-gray-500">string</span>
          <span class="ml-2 text-xs px-2 py-0.5 bg-error-100 text-error-700 rounded">required</span>
        </div>
        <p class="text-sm text-gray-600 mt-1">The unique identifier of the customer</p>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="retrieveCustomerExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="retrieveCustomerResponse" language="json" />
    </div>
    
    <!-- Update Customer -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="update-customer">Update a Customer</h2>
      <p class="text-gray-600 mb-6">
        Updates the specified customer by setting the values of the parameters passed. Any parameters not provided will be left unchanged.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-warning-100 text-warning-700 text-xs font-semibold rounded mr-3">PUT</span>
          <code class="text-sm font-mono">/api/v1/customers/:id</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="updateCustomerExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="updateCustomerResponse" language="json" />
    </div>
    
    <!-- List Customers -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="list-customers">List All Customers</h2>
      <p class="text-gray-600 mb-6">
        Returns a list of customers. The customers are returned sorted by creation date, with the most recent customers appearing first.
      </p>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-primary-100 text-primary-700 text-xs font-semibold rounded mr-3">GET</span>
          <code class="text-sm font-mono">/api/v1/customers</code>
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
            <tr v-for="param in listCustomersParams" :key="param.name">
              <td class="px-4 py-3 text-sm font-mono text-primary-600">{{ param.name }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.type }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.default }}</td>
              <td class="px-4 py-3 text-sm text-gray-600">{{ param.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="listCustomersExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="listCustomersResponse" language="json" />
    </div>
    
    <!-- Delete Customer -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="delete-customer">Delete a Customer</h2>
      <p class="text-gray-600 mb-6">
        Permanently deletes a customer. This action cannot be undone. All active subscriptions will be cancelled.
      </p>
      
      <div class="bg-error-50 rounded-lg p-4 mb-6 border border-error-200">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-error-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <span class="text-sm font-medium text-error-800">Warning: This action is irreversible</span>
        </div>
      </div>
      
      <div class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
        <div class="flex items-center">
          <span class="px-3 py-1 bg-error-100 text-error-700 text-xs font-semibold rounded mr-3">DELETE</span>
          <code class="text-sm font-mono">/api/v1/customers/:id</code>
        </div>
      </div>
      
      <h3 class="font-semibold text-gray-900 mb-3">Request Example</h3>
      <CodeBlock :code="deleteCustomerExample" runnable />
      
      <h3 class="font-semibold text-gray-900 mb-3 mt-6">Response Example</h3>
      <CodeBlock :code="deleteCustomerResponse" language="json" />
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Learn more about working with customers in our guides</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <NuxtLink to="/docs/guides/customers" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Customer Management Guide</h3>
          <p class="text-sm text-gray-600">Best practices for managing customer data and KYC</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscriptions API</h3>
          <p class="text-sm text-gray-600">Create recurring subscriptions for customers</p>
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
  title: 'Customers API Reference'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'customers-api', text: 'Customers API', level: 1 },
      { id: 'customer-object', text: 'The Customer Object', level: 2 },
      { id: 'create-customer', text: 'Create a Customer', level: 2 },
      { id: 'retrieve-customer', text: 'Retrieve a Customer', level: 2 },
      { id: 'update-customer', text: 'Update a Customer', level: 2 },
      { id: 'list-customers', text: 'List All Customers', level: 2 },
      { id: 'delete-customer', text: 'Delete a Customer', level: 2 },
    ])
  }
})

const customerAttributes = [
  { name: 'id', type: 'string', description: 'Unique identifier for the customer', required: false },
  { name: 'email', type: 'string', description: 'Customer email address', required: true },
  { name: 'name', type: 'string', description: 'Full name of the customer', required: true },
  { name: 'phone', type: 'string', description: 'Phone number with country code', required: false },
  { name: 'kyc_status', type: 'enum', description: 'KYC verification status: pending, verified, rejected', required: false },
  { name: 'metadata', type: 'object', description: 'Set of key-value pairs for storing additional information', required: false },
  { name: 'created_at', type: 'timestamp', description: 'Timestamp when the customer was created', required: false },
  { name: 'updated_at', type: 'timestamp', description: 'Timestamp when the customer was last updated', required: false },
]

const customerObjectExample = `{
  "id": "cus_9h3k2j1l0m",
  "email": "john@example.com",
  "name": "John Doe",
  "phone": "+1234567890",
  "kyc_status": "verified",
  "metadata": {
    "user_id": "12345",
    "source": "web"
  },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T10:30:00Z"
}`

const createCustomerParams = [
  { name: 'email', type: 'string', required: true, description: 'Customer email address (must be unique)' },
  { name: 'name', type: 'string', required: true, description: 'Full name of the customer' },
  { name: 'phone', type: 'string', required: false, description: 'Phone number with country code' },
  { name: 'metadata', type: 'object', required: false, description: 'Key-value pairs for additional data' },
]

const createCustomerExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "john@example.com",
    "name": "John Doe",
    "phone": "+1234567890",
    "metadata": {
      "user_id": "12345"
    }
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$customer = $client->customers->create([
  'email' => 'john@example.com',
  'name' => 'John Doe',
  'phone' => '+1234567890',
  'metadata' => [
    'user_id' => '12345'
  ]
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const customer = await billing.customers.create({
  email: 'john@example.com',
  name: 'John Doe',
  phone: '+1234567890',
  metadata: {
    user_id: '12345'
  }
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

customer = billing.Customer.create(
  email='john@example.com',
  name='John Doe',
  phone='+1234567890',
  metadata={'user_id': '12345'}
)`
}

const createCustomerResponse = `{
  "id": "cus_9h3k2j1l0m",
  "email": "john@example.com",
  "name": "John Doe",
  "phone": "+1234567890",
  "kyc_status": "pending",
  "metadata": {
    "user_id": "12345"
  },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T10:30:00Z"
}`

const retrieveCustomerExample = {
  bash: `curl https://api.billing.com/v1/customers/cus_9h3k2j1l0m \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client = new BillingClient('sk_test_abc123');
$customer = $client->customers->retrieve('cus_9h3k2j1l0m');`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');
const customer = await billing.customers.retrieve('cus_9h3k2j1l0m');`,
  python: `import billing
billing.api_key = 'sk_test_abc123'
customer = billing.Customer.retrieve('cus_9h3k2j1l0m')`
}

const retrieveCustomerResponse = customerObjectExample

const updateCustomerExample = {
  bash: `curl -X PUT https://api.billing.com/v1/customers/cus_9h3k2j1l0m \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "name": "John Smith",
    "metadata": {
      "user_id": "12345",
      "verified": "true"
    }
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$customer = $client->customers->update('cus_9h3k2j1l0m', [
  'name' => 'John Smith',
  'metadata' => [
    'user_id' => '12345',
    'verified' => 'true'
  ]
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const customer = await billing.customers.update('cus_9h3k2j1l0m', {
  name: 'John Smith',
  metadata: {
    user_id: '12345',
    verified: 'true'
  }
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

customer = billing.Customer.modify('cus_9h3k2j1l0m',
  name='John Smith',
  metadata={'user_id': '12345', 'verified': 'true'}
)`
}

const updateCustomerResponse = `{
  "id": "cus_9h3k2j1l0m",
  "email": "john@example.com",
  "name": "John Smith",
  "phone": "+1234567890",
  "kyc_status": "verified",
  "metadata": {
    "user_id": "12345",
    "verified": "true"
  },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T11:45:00Z"
}`

const listCustomersParams = [
  { name: 'limit', type: 'integer', default: '10', description: 'Number of customers to return (max 100)' },
  { name: 'starting_after', type: 'string', default: 'null', description: 'Cursor for pagination' },
  { name: 'email', type: 'string', default: 'null', description: 'Filter by email address' },
]

const listCustomersExample = {
  bash: `curl "https://api.billing.com/v1/customers?limit=10" \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client = new BillingClient('sk_test_abc123');
$customers = $client->customers->all(['limit' => 10]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');
const customers = await billing.customers.list({ limit: 10 });`,
  python: `import billing
billing.api_key = 'sk_test_abc123'
customers = billing.Customer.list(limit=10)`
}

const listCustomersResponse = `{
  "object": "list",
  "data": [
    {
      "id": "cus_9h3k2j1l0m",
      "email": "john@example.com",
      "name": "John Doe",
      "phone": "+1234567890",
      "kyc_status": "verified",
      "created_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": "cus_8g2j1k0l9n",
      "email": "jane@example.com",
      "name": "Jane Smith",
      "phone": "+1234567891",
      "kyc_status": "pending",
      "created_at": "2024-01-14T09:20:00Z"
    }
  ],
  "has_more": true,
  "url": "/v1/customers"
}`

const deleteCustomerExample = {
  bash: `curl -X DELETE https://api.billing.com/v1/customers/cus_9h3k2j1l0m \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client = new BillingClient('sk_test_abc123');
$customer = $client->customers->delete('cus_9h3k2j1l0m');`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');
const deleted = await billing.customers.del('cus_9h3k2j1l0m');`,
  python: `import billing
billing.api_key = 'sk_test_abc123'
customer = billing.Customer.delete('cus_9h3k2j1l0m')`
}

const deleteCustomerResponse = `{
  "id": "cus_9h3k2j1l0m",
  "object": "customer",
  "deleted": true
}`
</script>
