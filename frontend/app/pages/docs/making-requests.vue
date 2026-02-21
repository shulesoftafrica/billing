<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Making Requests</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="making-requests">Making Requests</h1>
      <p class="text-xl text-gray-600">
        Learn how to make HTTP requests to the Billing API, handle responses, and work with our REST endpoints.
      </p>
    </div>
    
    <!-- Base URL -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="base-url">Base URL</h2>
      <p class="text-gray-700 mb-4">
        All API requests should be made to the following base URL:
      </p>
      
      <div class="bg-gray-900 rounded-lg p-4 mb-4">
        <code class="text-green-400">https://api.billing.com/v1</code>
      </div>
      
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">HTTPS Required</p>
            <p class="text-sm text-amber-800 mt-1">
              All API requests must be made over HTTPS. Requests made over plain HTTP will fail.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- HTTP Methods -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="http-methods">HTTP Methods</h2>
      <p class="text-gray-700 mb-6">
        The API follows REST conventions and uses standard HTTP methods:
      </p>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Example</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">GET</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">Retrieve resources</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-600">GET /v1/customers/:id</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">POST</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">Create new resources</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-600">POST /v1/customers</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">PUT</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">Update existing resources</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-600">PUT /v1/customers/:id</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">DELETE</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">Delete resources</td>
              <td class="px-6 py-4 text-sm font-mono text-gray-600">DELETE /v1/customers/:id</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Request Headers -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="request-headers">Request Headers</h2>
      <p class="text-gray-700 mb-6">
        Include these headers with every request:
      </p>
      
      <div class="space-y-4">
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex items-start">
            <code class="text-sm font-semibold text-gray-900 mr-4">Authorization</code>
            <div class="flex-1">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mb-2">Required</span>
              <p class="text-sm text-gray-700 mb-2">Bearer token authentication using your API key</p>
              <code class="text-xs text-gray-600">Authorization: Bearer sk_test_abc123...</code>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex items-start">
            <code class="text-sm font-semibold text-gray-900 mr-4">Content-Type</code>
            <div class="flex-1">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mb-2">Required for POST/PUT</span>
              <p class="text-sm text-gray-700 mb-2">Specify the content type of the request body</p>
              <code class="text-xs text-gray-600">Content-Type: application/json</code>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
          <div class="flex items-start">
            <code class="text-sm font-semibold text-gray-900 mr-4">Accept</code>
            <div class="flex-1">
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mb-2">Optional</span>
              <p class="text-sm text-gray-700 mb-2">Specify the desired response format (defaults to JSON)</p>
              <code class="text-xs text-gray-600">Accept: application/json</code>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Request Examples -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="request-examples">Request Examples</h2>
      
      <div class="space-y-8">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">GET Request</h3>
          <p class="text-gray-700 mb-4">Retrieve a customer by ID:</p>
          <CodeBlock :code="getRequestExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">POST Request</h3>
          <p class="text-gray-700 mb-4">Create a new customer:</p>
          <CodeBlock :code="postRequestExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">PUT Request</h3>
          <p class="text-gray-700 mb-4">Update an existing customer:</p>
          <CodeBlock :code="putRequestExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">DELETE Request</h3>
          <p class="text-gray-700 mb-4">Delete a customer:</p>
          <CodeBlock :code="deleteRequestExample" />
        </div>
      </div>
    </div>
    
    <!-- Response Format -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="response-format">Response Format</h2>
      <p class="text-gray-700 mb-6">
        All responses are returned as JSON and follow a consistent structure:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
          <h3 class="font-semibold text-green-900 mb-3">Success Response</h3>
          <CodeBlock :code="successResponseExample" />
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
          <h3 class="font-semibold text-red-900 mb-3">Error Response</h3>
          <CodeBlock :code="errorResponseExample" />
        </div>
      </div>
    </div>
    
    <!-- Pagination -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="pagination">Pagination</h2>
      <p class="text-gray-700 mb-6">
        List endpoints return paginated results. Use these query parameters to control pagination:
      </p>
      
      <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameter</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">limit</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">integer</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">10</td>
              <td class="px-6 py-4 text-sm text-gray-700">Number of objects to return (1-100)</td>
            </tr>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm text-gray-900">page</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">integer</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">1</td>
              <td class="px-6 py-4 text-sm text-gray-700">Page number to retrieve</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <CodeBlock :code="paginationExample" />
    </div>
    
    <!-- Idempotency -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="idempotency">Idempotency</h2>
      <p class="text-gray-700 mb-4">
        The API supports idempotency for safely retrying requests without accidentally performing the same operation twice. 
        Include an <code class="text-sm bg-gray-100 px-2 py-1 rounded">Idempotency-Key</code> header with a unique value:
      </p>
      
      <CodeBlock :code="idempotencyExample" />
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
        <div class="flex">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-900">Best Practice</p>
            <p class="text-sm text-blue-800 mt-1">
              Use a UUID v4 for idempotency keys. Keys are valid for 24 hours.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Rate Limiting -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="rate-limiting">Rate Limiting</h2>
      <p class="text-gray-700 mb-4">
        API requests are rate-limited. See <NuxtLink to="/docs/error-handling#rate-limiting" class="text-primary-600 hover:underline">Error Handling</NuxtLink> for details on handling rate limit errors.
      </p>
      
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-3">Rate Limit Headers</h3>
        <p class="text-sm text-gray-700 mb-3">Every response includes rate limit information:</p>
        <div class="space-y-2 font-mono text-sm text-gray-600">
          <div><span class="text-gray-900">X-RateLimit-Limit:</span> 1000</div>
          <div><span class="text-gray-900">X-RateLimit-Remaining:</span> 998</div>
          <div><span class="text-gray-900">X-RateLimit-Reset:</span> 1640000000</div>
        </div>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Next Steps</h2>
      <p class="text-gray-600 mb-6">Now that you understand how requests work, explore specific resources</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/api/customers" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Customers API</h3>
          <p class="text-sm text-gray-600">Manage customer records</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscriptions API</h3>
          <p class="text-sm text-gray-600">Handle recurring billing</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/error-handling" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Error Handling</h3>
          <p class="text-sm text-gray-600">Handle errors gracefully</p>
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
  title: 'Making Requests'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'making-requests', text: 'Making Requests', level: 1 },
      { id: 'base-url', text: 'Base URL', level: 2 },
      { id: 'http-methods', text: 'HTTP Methods', level: 2 },
      { id: 'request-headers', text: 'Request Headers', level: 2 },
      { id: 'request-examples', text: 'Request Examples', level: 2 },
      { id: 'response-format', text: 'Response Format', level: 2 },
      { id: 'pagination', text: 'Pagination', level: 2 },
      { id: 'idempotency', text: 'Idempotency', level: 2 },
      { id: 'rate-limiting', text: 'Rate Limiting', level: 2 },
    ])
  }
})

const getRequestExample = {
  bash: `curl -X GET https://api.billing.com/v1/customers/cus_abc123 \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Accept: application/json"`,
  php: `$client->customers->retrieve('cus_abc123');`,
  javascript: `await billing.customers.retrieve('cus_abc123');`,
  python: `billing.Customer.retrieve('cus_abc123')`
}

const postRequestExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "john@example.com",
    "name": "John Doe"
  }'`,
  php: `$client->customers->create([
  'email' => 'john@example.com',
  'name' => 'John Doe'
]);`,
  javascript: `await billing.customers.create({
  email: 'john@example.com',
  name: 'John Doe'
});`,
  python: `billing.Customer.create(
  email='john@example.com',
  name='John Doe'
)`
}

const putRequestExample = {
  bash: `curl -X PUT https://api.billing.com/v1/customers/cus_abc123 \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "name": "John Smith"
  }'`,
  php: `$client->customers->update('cus_abc123', [
  'name' => 'John Smith'
]);`,
  javascript: `await billing.customers.update('cus_abc123', {
  name: 'John Smith'
});`,
  python: `billing.Customer.update(
  'cus_abc123',
  name='John Smith'
)`
}

const deleteRequestExample = {
  bash: `curl -X DELETE https://api.billing.com/v1/customers/cus_abc123 \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$client->customers->delete('cus_abc123');`,
  javascript: `await billing.customers.delete('cus_abc123');`,
  python: `billing.Customer.delete('cus_abc123')`
}

const successResponseExample = {
  json: `{
  "id": "cus_abc123",
  "email": "john@example.com",
  "name": "John Doe",
  "created_at": "2024-01-15T10:30:00Z"
}`
}

const errorResponseExample = {
  json: `{
  "error": {
    "type": "validation_error",
    "code": "invalid_parameter",
    "message": "Invalid email address"
  }
}`
}

const paginationExample = {
  bash: `curl -X GET "https://api.billing.com/v1/customers?limit=20&page=2" \\
  -H "Authorization: Bearer sk_test_abc123"`,
  json: `{
  "data": [...],
  "pagination": {
    "total": 150,
    "count": 20,
    "per_page": 20,
    "current_page": 2,
    "total_pages": 8
  }
}`
}

const idempotencyExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -H "Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000" \\
  -d '{
    "email": "john@example.com",
    "name": "John Doe"
  }'`
}
</script>
