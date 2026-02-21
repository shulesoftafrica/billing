<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <NuxtLink to="/docs/guides" class="hover:text-gray-700">Guides</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Customer Management</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="customer-management">Customer Management Guide</h1>
      <p class="text-xl text-gray-600">
        Learn how to effectively manage customers, handle KYC verification, and maintain customer data in your billing system.
      </p>
    </div>
    
    <!-- Introduction -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="introduction">Introduction</h2>
      <p class="text-gray-700 mb-4">
        Customers are at the heart of your billing system. A customer object represents an individual or business that you bill for products or services. 
        This guide will walk you through the complete customer lifecycle.
      </p>
      
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-6 mb-6">
        <h3 class="font-semibold text-primary-900 mb-2">What You'll Learn</h3>
        <ul class="space-y-2 text-primary-800">
          <li class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Creating and managing customer records</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>KYC verification process and requirements</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Using metadata for custom integrations</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Best practices for data management</span>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Creating Customers -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="creating-customers">Creating Customers</h2>
      <p class="text-gray-700 mb-6">
        Creating a customer is the first step before you can bill them. At minimum, you need an email address, but providing more information helps with fraud prevention and compliance.
      </p>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Basic Customer Creation</h3>
      <p class="text-gray-700 mb-4">
        Here's how to create a customer with the required fields:
      </p>
      
      <CodeBlock :code="basicCustomerExample" />
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4 mt-8">Complete Customer Record</h3>
      <p class="text-gray-700 mb-4">
        For better fraud detection and compliance, include additional information:
      </p>
      
      <CodeBlock :code="completeCustomerExample" />
      
      <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-warning-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-warning-900">Email Uniqueness</p>
            <p class="text-sm text-warning-800 mt-1">
              Each email can only be used once. If you try to create a customer with an existing email, you'll receive a 409 Conflict error.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- KYC Verification -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="kyc-verification">KYC Verification</h2>
      <p class="text-gray-700 mb-6">
        Know Your Customer (KYC) verification is required for compliance with financial regulations. Verified customers can access higher transaction limits.
      </p>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">KYC Status Flow</h3>
      <div class="bg-gray-50 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-gray-200 flex items-center justify-center mb-2">
              <span class="text-2xl">📝</span>
            </div>
            <p class="font-semibold text-gray-900">Pending</p>
            <p class="text-xs text-gray-600">Initial state</p>
          </div>
          
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
          
          <div class="text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-warning-100 flex items-center justify-center mb-2">
              <span class="text-2xl">⏳</span>
            </div>
            <p class="font-semibold text-gray-900">Under Review</p>
            <p class="text-xs text-gray-600">Processing</p>
          </div>
          
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
          
          <div class="text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-success-100 flex items-center justify-center mb-2">
              <span class="text-2xl">✅</span>
            </div>
            <p class="font-semibold text-gray-900">Verified</p>
            <p class="text-xs text-gray-600">Approved</p>
          </div>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-200">
          <div class="flex items-center justify-center">
            <div class="text-center">
              <div class="w-24 h-24 mx-auto rounded-full bg-error-100 flex items-center justify-center mb-2">
                <span class="text-2xl">❌</span>
              </div>
              <p class="font-semibold text-gray-900">Rejected</p>
              <p class="text-xs text-gray-600">Failed verification</p>
            </div>
          </div>
        </div>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Required Documents</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="border border-gray-200 rounded-lg p-6">
          <h4 class="font-semibold text-gray-900 mb-3">Individual Customers</h4>
          <ul class="space-y-2 text-gray-700">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Government-issued ID (passport, driver's license)</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Proof of address (utility bill, bank statement)</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Selfie for identity verification</span>
            </li>
          </ul>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <h4 class="font-semibold text-gray-900 mb-3">Business Customers</h4>
          <ul class="space-y-2 text-gray-700">
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Business registration certificate</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Tax identification number</span>
            </li>
            <li class="flex items-start">
              <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Authorized representative ID</span>
            </li>
          </ul>
        </div>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Checking KYC Status</h3>
      <CodeBlock :code="kycStatusExample" />
    </div>
    
    <!-- Using Metadata -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="using-metadata">Using Metadata</h2>
      <p class="text-gray-700 mb-6">
        Metadata allows you to attach custom key-value data to customer objects. This is useful for linking customers to records in your own system.
      </p>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Common Use Cases</h3>
      <div class="space-y-4 mb-6">
        <div class="border-l-4 border-primary-500 pl-4">
          <h4 class="font-semibold text-gray-900 mb-1">User ID Mapping</h4>
          <p class="text-gray-700 text-sm">Store your internal user ID to link billing data with your user database</p>
          <CodeBlock :code="`metadata: { user_id: '12345', source: 'mobile_app' }`" language="json" class="mt-2" />
        </div>
        
        <div class="border-l-4 border-success-500 pl-4">
          <h4 class="font-semibold text-gray-900 mb-1">Feature Tracking</h4>
          <p class="text-gray-700 text-sm">Track which features or experiments a customer is enrolled in</p>
          <CodeBlock :code="`metadata: { feature_tier: 'premium', ab_test: 'variant_b' }`" language="json" class="mt-2" />
        </div>
        
        <div class="border-l-4 border-warning-500 pl-4">
          <h4 class="font-semibold text-gray-900 mb-1">Referral Information</h4>
          <p class="text-gray-700 text-sm">Store referral source for attribution</p>
          <CodeBlock :code="`metadata: { referral_code: 'FRIEND2024', campaign: 'summer_promo' }`" language="json" class="mt-2" />
        </div>
      </div>
      
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-primary-900">Metadata Limits</p>
            <p class="text-sm text-primary-800 mt-1">
              You can store up to 50 key-value pairs per customer. Keys can be up to 40 characters and values up to 500 characters.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="best-practices">Best Practices</h2>
      
      <div class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Validate Email Addresses</h3>
              <p class="text-gray-700">
                Always validate email addresses on your frontend before sending to the API. This prevents user errors and ensures better data quality.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Handle Duplicate Emails Gracefully</h3>
              <p class="text-gray-700">
                If a user tries to sign up with an existing email, consider offering "Sign In" instead of showing an error. This improves user experience.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Use Webhooks for KYC Updates</h3>
              <p class="text-gray-700">
                Set up webhooks to receive real-time notifications when KYC status changes. This allows you to automatically enable features for verified users.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Keep Customer Data Updated</h3>
              <p class="text-gray-700">
                Regularly prompt users to update their information. Outdated contact details can cause payment failures and compliance issues.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Next Steps</h2>
      <p class="text-gray-600 mb-6">Continue learning about billing</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <NuxtLink to="/docs/guides/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscription Management</h3>
          <p class="text-sm text-gray-600">Learn how to create and manage subscriptions</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks</h3>
          <p class="text-sm text-gray-600">Receive real-time notifications for events</p>
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
  title: 'Customer Management Guide'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'customer-management', text: 'Customer Management Guide', level: 1 },
      { id: 'introduction', text: 'Introduction', level: 2 },
      { id: 'creating-customers', text: 'Creating Customers', level: 2 },
      { id: 'kyc-verification', text: 'KYC Verification', level: 2 },
      { id: 'using-metadata', text: 'Using Metadata', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
    ])
  }
})

const basicCustomerExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "customer@example.com",
    "name": "Jane Doe"
  }'`,
  php: `$client = new BillingClient('sk_test_abc123');

$customer = $client->customers->create([
  'email' => 'customer@example.com',
  'name' => 'Jane Doe'
]);`,
  javascript: `const billing = require('billing-node')('sk_test_abc123');

const customer = await billing.customers.create({
  email: 'customer@example.com',
  name: 'Jane Doe'
});`,
  python: `import billing
billing.api_key = 'sk_test_abc123'

customer = billing.Customer.create(
  email='customer@example.com',
  name='Jane Doe'
)`
}

const completeCustomerExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "customer@example.com",
    "name": "Jane Doe",
    "phone": "+1234567890",
    "address": {
      "line1": "123 Main St",
      "city": "San Francisco",
      "state": "CA",
      "postal_code": "94102",
      "country": "US"
    },
    "metadata": {
      "user_id": "67890",
      "source": "website"
    }
  }'`,
  php: `$customer = $client->customers->create([
  'email' => 'customer@example.com',
  'name' => 'Jane Doe',
  'phone' => '+1234567890',
  'address' => [
    'line1' => '123 Main St',
    'city' => 'San Francisco',
    'state' => 'CA',
    'postal_code' => '94102',
    'country' => 'US'
  ],
  'metadata' => [
    'user_id' => '67890',
    'source' => 'website'
  ]
]);`,
  javascript: `const customer = await billing.customers.create({
  email: 'customer@example.com',
  name: 'Jane Doe',
  phone: '+1234567890',
  address: {
    line1: '123 Main St',
    city: 'San Francisco',
    state: 'CA',
    postal_code: '94102',
    country: 'US'
  },
  metadata: {
    user_id: '67890',
    source: 'website'
  }
});`,
  python: `customer = billing.Customer.create(
  email='customer@example.com',
  name='Jane Doe',
  phone='+1234567890',
  address={
    'line1': '123 Main St',
    'city': 'San Francisco',
    'state': 'CA',
    'postal_code': '94102',
    'country': 'US'
  },
  metadata={
    'user_id': '67890',
    'source': 'website'
  }
)`
}

const kycStatusExample = {
  bash: `curl https://api.billing.com/v1/customers/cus_9h3k2j1l0m \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `$customer = $client->customers->retrieve('cus_9h3k2j1l0m');
$kycStatus = $customer->kyc_status;

if ($kycStatus === 'verified') {
  // Enable premium features
}`,
  javascript: `const customer = await billing.customers.retrieve('cus_9h3k2j1l0m');

if (customer.kyc_status === 'verified') {
  // Enable premium features
}`,
  python: `customer = billing.Customer.retrieve('cus_9h3k2j1l0m')

if customer.kyc_status == 'verified':
    # Enable premium features
    pass`
}
</script>
