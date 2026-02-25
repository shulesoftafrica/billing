<template>
  <div>
    <!-- Hero Section -->
    <div class="mb-12">
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Billing API Documentation</h1>
      <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl">
        Everything you need to integrate billing and subscription management into your application. 
        Get started in minutes with our comprehensive API.
      </p>
    </div>
    
    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
      <NuxtLink 
        to="/docs/authentication" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Authentication</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Learn how to authenticate your API requests using API keys.</p>
      </NuxtLink>
      
      <NuxtLink 
        to="/docs/guides/customers" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Customers</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Create and manage customer accounts and billing information.</p>
      </NuxtLink>
      
      <NuxtLink 
        to="/docs/guides/subscriptions" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Subscriptions</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Set up recurring billing with flexible subscription plans.</p>
      </NuxtLink>
      
      <NuxtLink 
        to="/docs/guides/invoices" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Invoices</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Generate, send, and manage invoices programmatically.</p>
      </NuxtLink>
      
      <NuxtLink 
        to="/docs/guides/payments" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Payments</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Process payments securely with multiple payment methods.</p>
      </NuxtLink>
      
      <NuxtLink 
        to="/docs/guides/webhooks" 
        class="block p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-primary-500 dark:hover:border-primary-500 transition-colors group"
      >
        <div class="w-12 h-12 mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center group-hover:scale-110 transition-transform">
          <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Webhooks</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Receive real-time notifications about events in your account.</p>
      </NuxtLink>
    </div>

    <!-- Getting Started -->
    <div class="border-t border-gray-200 dark:border-gray-800 pt-12">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Getting Started</h2>
      
      <div class="prose dark:prose-invert max-w-none">
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          The Billing API is organized around REST. Our API has predictable resource-oriented URLs, 
          accepts JSON-encoded request bodies, returns JSON-encoded responses, and uses standard HTTP response codes, 
          authentication, and verbs.
        </p>
        
        <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Base URL</h3>
          <code class="text-sm bg-white dark:bg-gray-950 px-3 py-2 rounded border border-gray-200 dark:border-gray-800 text-primary-600 dark:text-primary-400">
            https://api.billingplatform.com/v1
          </code>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Example</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Here's a quick example to create a customer:
        </p>
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
  title: 'API Documentation - Billing Platform',
  meta: [
    { name: 'description', content: 'Complete API reference and guides for the Billing Platform API' }
  ]
})

const curlExample = `curl https://api.billingplatform.com/v1/customers \\
  -u YOUR_API_KEY: \\
  -d name='John Doe' \\
  -d email='john@example.com'`

const responseExample = `{
  "id": "cus_NffrFeUfNV2Hib",
  "object": "customer",
  "name": "John Doe",
  "email": "john@example.com",
  "created": 1680893116
}`
</script>
