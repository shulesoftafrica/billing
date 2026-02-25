<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Changelog</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="changelog">Changelog</h1>
      <p class="text-xl text-gray-600">
        Track new features, improvements, and bug fixes to the Billing API and SDKs.
      </p>
    </div>
    
    <!-- Version 2.1.0 -->
    <div class="mb-12 border-l-4 border-primary-500 pl-6">
      <div class="flex items-center mb-2">
        <h2 class="text-2xl font-semibold text-gray-900" id="v2-1-0">Version 2.1.0</h2>
        <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">Latest</span>
      </div>
      <p class="text-sm text-gray-500 mb-4">February 15, 2026</p>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Usage-Based Billing
          </h3>
          <p class="text-gray-700 mb-2">
            Track and bill customers based on their usage with new metering endpoints.
          </p>
          <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 ml-4">
            <li>Record usage events via API</li>
            <li>Automatic invoice generation based on usage</li>
            <li>Support for tiered and volume pricing</li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">Improved</span>
            Webhooks Retry Logic
          </h3>
          <p class="text-gray-700 text-sm">
            Enhanced retry logic with exponential backoff: 5 attempts over 12 hours with automatic disable after 5 consecutive failures.
          </p>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mr-2">Fixed</span>
            Invoice Proration Edge Cases
          </h3>
          <p class="text-gray-700 text-sm">
            Fixed calculation errors when upgrading subscriptions mid-cycle with prorated amounts.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Version 2.0.0 -->
    <div class="mb-12 border-l-4 border-gray-300 pl-6">
      <div class="flex items-center mb-2">
        <h2 class="text-2xl font-semibold text-gray-900" id="v2-0-0">Version 2.0.0</h2>
      </div>
      <p class="text-sm text-gray-500 mb-4">January 10, 2026</p>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">Breaking</span>
            API v2 Release
          </h3>
          <p class="text-gray-700 mb-2">
            Major version upgrade with improved consistency and new features.
          </p>
          <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 ml-4">
            <li>Unified error response format</li>
            <li>Consistent ID prefixes (cus_, sub_, inv_, etc.)</li>
            <li>Pagination standardized across all list endpoints</li>
          </ul>
          
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4">
            <p class="text-sm font-medium text-amber-900">Migration Guide</p>
            <p class="text-sm text-amber-800 mt-1">
              See our <a href="#" class="underline">v1 to v2 migration guide</a> for detailed upgrade instructions. v1 API will be supported until July 2026.
            </p>
          </div>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Multi-Currency Support
          </h3>
          <p class="text-gray-700 text-sm">
            Accept payments in 135+ currencies with automatic conversion and settlement.
          </p>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Smart Retry for Failed Payments
          </h3>
          <p class="text-gray-700 text-sm">
            Automatic retry logic with machine learning optimization to recover failed payments.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Version 1.8.0 -->
    <div class="mb-12 border-l-4 border-gray-300 pl-6">
      <div class="flex items-center mb-2">
        <h2 class="text-2xl font-semibold text-gray-900" id="v1-8-0">Version 1.8.0</h2>
      </div>
      <p class="text-sm text-gray-500 mb-4">December 5, 2025</p>
      
      <div class="space-y-4">
        <div>
          <h3 class="text-md font-semibold text-gray-900 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Customer Portal
          </h3>
          <p class="text-sm text-gray-700">
            Embeddable customer portal for self-service billing management.
          </p>
        </div>
        
        <div>
          <h3 class="text-md font-semibold text-gray-900 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mr-2">Improved</span>
            Dashboard Analytics
          </h3>
          <p class="text-sm text-gray-700">
            Enhanced revenue analytics with MRR, churn rate, and cohort analysis.
          </p>
        </div>
        
        <div>
          <h3 class="text-md font-semibold text-gray-900 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mr-2">Fixed</span>
            Webhook Delivery Issues
          </h3>
          <p class="text-sm text-gray-700">
            Resolved intermittent webhook delivery failures during high traffic.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Version 1.7.0 -->
    <div class="mb-12 border-l-4 border-gray-300 pl-6">
      <div class="flex items-center mb-2">
        <h2 class="text-2xl font-semibold text-gray-900" id="v1-7-0">Version 1.7.0</h2>
      </div>
      <p class="text-sm text-gray-500 mb-4">November 1, 2025</p>
      
      <div class="space-y-4">
        <div>
          <h3 class="text-md font-semibold text-gray-900 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Mobile Money Support
          </h3>
          <p class="text-sm text-gray-700">
            Accept payments via M-Pesa and other mobile money providers in Africa.
          </p>
        </div>
        
        <div>
          <h3 class="text-md font-semibold text-gray-900 flex items-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">New</span>
            Invoice Templates
          </h3>
          <p class="text-sm text-gray-700">
            Customizable invoice templates with your branding and logo.
          </p>
        </div>
      </div>
    </div>
    
    <!-- SDK Updates -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-6" id="sdk-updates">Recent SDK Updates</h2>
      
      <div class="space-y-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">billing-php v3.2.0</h3>
            <span class="text-sm text-gray-500">February 10, 2026</span>
          </div>
          <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>Added support for PHP 8.3</li>
            <li>Improved type hints for better IDE support</li>
            <li>Performance improvements for bulk operations</li>
          </ul>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">billing-node v2.5.0</h3>
            <span class="text-sm text-gray-500">February 5, 2026</span>
          </div>
          <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>Full TypeScript 5.0 support</li>
            <li>Added streaming for large list responses</li>
            <li>Fixed memory leaks in webhook handler</li>
          </ul>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-gray-900">billing-python v1.9.0</h3>
            <span class="text-sm text-gray-500">January 28, 2026</span>
          </div>
          <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>Python 3.12 compatibility</li>
            <li>Async/await support for all operations</li>
            <li>Improved error messages and debugging</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Subscribe to Updates -->
    <div class="bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Stay Updated</h2>
      <p class="text-gray-600 mb-6">Get notified about new releases and important updates</p>
      
      <div class="flex gap-4">
        <input
          type="email"
          placeholder="your@email.com"
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <button class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
          Subscribe
        </button>
      </div>
      
      <div class="flex gap-4 mt-6">
        <a href="#" class="text-sm text-primary-600 hover:underline">RSS Feed</a>
        <span class="text-gray-400">•</span>
        <a href="#" class="text-sm text-primary-600 hover:underline">API Status</a>
        <span class="text-gray-400">•</span>
        <a href="https://twitter.com/billing" target="_blank" class="text-sm text-primary-600 hover:underline">Follow on Twitter</a>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'docs'
})

useHead({
  title: 'Changelog'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'changelog', text: 'Changelog', level: 1 },
      { id: 'v2-1-0', text: 'Version 2.1.0', level: 2 },
      { id: 'v2-0-0', text: 'Version 2.0.0', level: 2 },
      { id: 'v1-8-0', text: 'Version 1.8.0', level: 2 },
      { id: 'v1-7-0', text: 'Version 1.7.0', level: 2 },
      { id: 'sdk-updates', text: 'SDK Updates', level: 2 },
    ])
  }
})
</script>
