<template>
  <div class="prose prose-lg max-w-none">
    <!-- Page Header -->
    <div class="not-prose mb-12">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">Quick Start Guide</h1>
      <p class="text-xl text-gray-600">
        Get your first API call working in under 5 minutes. No credit card required.
      </p>
    </div>
    
    <!-- Steps -->
    <section id="step-1" class="mb-16">
      <h2 id="get-api-key" class="text-3xl font-bold text-gray-900 mb-4 flex items-center">
        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white text-lg mr-4">1</span>
        Get Your API Key
      </h2>
      
      <p class="text-gray-600 mb-6">
        Sign up for a free account and get your API keys instantly. No waiting, no approval process.
      </p>
      
      <div class="not-prose space-y-4">
        <div class="flex items-start space-x-3">
          <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="font-medium text-gray-900">Create Account</p>
            <p class="text-sm text-gray-600">Visit the <NuxtLink to="/auth/signup" class="text-primary-600 hover:underline">Sign Up page</NuxtLink> and create your account in 30 seconds.</p>
          </div>
        </div>
        
        <div class="flex items-start space-x-3">
          <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="font-medium text-gray-900">Navigate to API Keys</p>
            <p class="text-sm text-gray-600">Go to <NuxtLink to="/dashboard/api-keys" class="text-primary-600 hover:underline">Dashboard → API Keys</NuxtLink></p>
          </div>
        </div>
        
        <div class="flex items-start space-x-3">
          <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="font-medium text-gray-900">Copy Your Key</p>
            <p class="text-sm text-gray-600">Click "Create New Key" and copy your test API key (starts with <code class="text-xs">pk_test_</code>)</p>
          </div>
        </div>
      </div>
    </section>
    
    <section id="step-2" class="mb-16">
      <h2 id="make-first-request" class="text-3xl font-bold text-gray-900 mb-4 flex items-center">
        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white text-lg mr-4">2</span>
        Make Your First Request
      </h2>
      
      <p class="text-gray-600 mb-6">
        Let's create a customer using the API. Choose your preferred language below.
      </p>
      
      <CodeBlock
        :code="customerCreateCode"
        language="bash"
        :runnable="true"
        @run="handleRunCode"
      />
      
      <div class="not-prose bg-blue-50 border-l-4 border-primary-600 p-4 my-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-primary-700">
              <strong>Pro tip:</strong> Click the "Run" button above to execute this request directly from the docs and see the live response!
            </p>
          </div>
        </div>
      </div>
    </section>
    
    <section id="step-3" class="mb-16">
      <h2 id="handle-response" class="text-3xl font-bold text-gray-900 mb-4 flex items-center">
        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white text-lg mr-4">3</span>
        Handle the Response
      </h2>
      
      <p class="text-gray-600 mb-6">
        The API returns a JSON response with the created customer data. Here's what a successful response looks like:
      </p>
      
      <CodeBlock
        :code="responseExample"
        language="json"
        filename="Response (201 Created)"
      />
      
      <p class="text-gray-600 mt-6">
        The response includes:
      </p>
      
      <ul class="list-disc list-inside text-gray-600 space-y-2 mt-4">
        <li><strong>success</strong>: Boolean indicating if the request succeeded</li>
        <li><strong>message</strong>: Human-readable message</li>
        <li><strong>data</strong>: The created customer object with all fields</li>
      </ul>
    </section>
    
    <section id="step-4" class="mb-16">
      <h2 id="create-subscription" class="text-3xl font-bold text-gray-900 mb-4 flex items-center">
        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-primary-600 text-white text-lg mr-4">4</span>
        Create a Subscription
      </h2>
      
      <p class="text-gray-600 mb-6">
        Now that you have a customer, let's create a subscription for them:
      </p>
      
      <CodeBlock
        :code="subscriptionCreateCode"
        language="bash"
        :runnable="true"
      />
      
      <p class="text-gray-600 mt-6">
        This will create a subscription and automatically generate an invoice that the customer needs to pay.
      </p>
    </section>
    
    <section id="next-steps" class="mb-16">
      <h2 id="whats-next" class="text-3xl font-bold text-gray-900 mb-4">What's Next?</h2>
      
      <div class="not-prose grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <Card hover>
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-primary-50">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Read the Guides</h3>
              <p class="text-sm text-gray-600 mb-4">Deep dive into customers, subscriptions, invoices, and more.</p>
              <NuxtLink to="/docs/guides/customers" class="text-sm text-primary-600 hover:underline">
                Explore Guides →
              </NuxtLink>
            </div>
          </div>
        </Card>
        
        <Card hover>
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-success-50">
                <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">API Reference</h3>
              <p class="text-sm text-gray-600 mb-4">Complete reference for all API endpoints and parameters.</p>
              <NuxtLink to="/docs/api/customers" class="text-sm text-primary-600 hover:underline">
                View API Docs →
              </NuxtLink>
            </div>
          </div>
        </Card>
        
        <Card hover>
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-warning-50">
                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Try the Playground</h3>
              <p class="text-sm text-gray-600 mb-4">Interactive environment to test API calls with your real keys.</p>
              <NuxtLink to="/playground" class="text-sm text-primary-600 hover:underline">
                Open Playground →
              </NuxtLink>
            </div>
          </div>
        </Card>
        
        <Card hover>
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-error-50">
                <svg class="w-6 h-6 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Get Help</h3>
              <p class="text-sm text-gray-600 mb-4">Join our community or contact support for assistance.</p>
              <NuxtLink to="/docs/support" class="text-sm text-primary-600 hover:underline">
                Contact Support →
              </NuxtLink>
            </div>
          </div>
        </Card>
      </div>
    </section>
  </div>
</template>

<script setup>
import { inject, onMounted } from 'vue'
import CodeBlock from '~/components/documentation/CodeBlock.vue'
import Card from '~/components/shared/Card.vue'

definePageMeta({
  layout: 'docs'
})

useHead({
  title: 'Quick Start Guide'
})

const registerHeadings = inject('registerHeadings')

const customerCreateCode = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -H "Content-Type: application/json" \\
  -d '{
    "name": "Acme Corporation",
    "email": "billing@acme.com",
    "phone": "+255712345678",
    "type": "business"
  }'`,
  
  php: `<?php
$client = new BillingAPI('YOUR_API_KEY');

$customer = $client->customers->create([
    'name' => 'Acme Corporation',
    'email' => 'billing@acme.com',
    'phone' => '+255712345678',
    'type' => 'business'
]);

echo $customer->id;`,
  
  javascript: `const billing = require('@billing-api/node');
const client = new billing.Client('YOUR_API_KEY');

const customer = await client.customers.create({
  name: 'Acme Corporation',
  email: 'billing@acme.com',
  phone: '+255712345678',
  type: 'business'
});

console.log(customer.id);`,
  
  python: `from billing_api import Client

client = Client('YOUR_API_KEY')

customer = client.customers.create(
    name='Acme Corporation',
    email='billing@acme.com',
    phone='+255712345678',
    type='business'
)

print(customer.id)`
}

const responseExample = `{
  "success": true,
  "message": "Customer created successfully",
  "data": {
    "id": 123,
    "name": "Acme Corporation",
    "email": "billing@acme.com",
    "phone": "+255712345678",
    "type": "business",
    "status": "active",
    "created_at": "2026-02-17T14:30:00Z",
    "updated_at": "2026-02-17T14:30:00Z"
  }
}`

const subscriptionCreateCode = {
  bash: `curl -X POST https://api.billing.com/v1/subscriptions \\
  -H "Authorization: Bearer YOUR_API_KEY" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": 123,
    "plan_ids": [1, 2]
  }'`,
  
  php: `<?php
$subscription = $client->subscriptions->create([
    'customer_id' => 123,
    'plan_ids' => [1, 2]
]);`,
  
  javascript: `const subscription = await client.subscriptions.create({
  customer_id: 123,
  plan_ids: [1, 2]
});`,
  
  python: `subscription = client.subscriptions.create(
    customer_id=123,
    plan_ids=[1, 2]
)`
}

const handleRunCode = (code) => {
  console.log('Running code:', code)
  // TODO: Send actual API request
}

onMounted(() => {
  if (registerHeadings) {
    registerHeadings([
      { id: 'get-api-key', text: 'Get Your API Key', level: 2 },
      { id: 'make-first-request', text: 'Make Your First Request', level: 2 },
      { id: 'handle-response', text: 'Handle the Response', level: 2 },
      { id: 'create-subscription', text: 'Create a Subscription', level: 2 },
      { id: 'whats-next', text: "What's Next?", level: 2 },
    ])
  }
})
</script>
