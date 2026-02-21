<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700 dark:hover:text-gray-300">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Authentication</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4" id="authentication">Authentication</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">
        Learn how to authenticate your API requests securely using API keys and handle authentication errors.
      </p>
    </div>
    
    <!-- Overview -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="overview">Overview</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        The Billing API uses API keys to authenticate requests. Your API keys carry many privileges, so be sure to keep them secure! 
        Do not share your secret API keys in publicly accessible areas such as GitHub, client-side code, and so forth.
      </p>
      
      <div class="bg-error-50 dark:bg-error-900/20 border border-error-200 dark:border-error-800 rounded-lg p-6 mb-6">
        <div class="flex">
          <svg class="w-5 h-5 text-error-600 dark:text-error-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-error-900 dark:text-error-300">Keep Your API Keys Secure</p>
            <p class="text-sm text-error-800 dark:text-error-400 mt-1">
              Never expose your secret API keys in client-side code, public repositories, or logs. Always make API calls from your backend server.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- API Key Types -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="api-key-types">API Key Types</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        We provide two types of API keys for different environments:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="border-2 border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-900/20 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 rounded-full bg-warning-100 dark:bg-warning-900/40 flex items-center justify-center mr-3">
              <span class="text-xl">🧪</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Test Keys</h3>
          </div>
          <p class="text-gray-700 dark:text-gray-300 mb-3">
            Start with <code class="text-sm bg-white dark:bg-gray-900 px-2 py-1 rounded text-gray-900 dark:text-gray-200">sk_test_</code>
          </p>
          <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li class="flex items-start">
              <svg class="w-4 h-4 text-warning-600 dark:text-warning-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>For development and testing</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 text-warning-600 dark:text-warning-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>No real charges are made</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 text-warning-600 dark:text-warning-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Use test card numbers</span>
            </li>
          </ul>
        </div>
        
        <div class="border-2 border-success-200 dark:border-success-800 bg-success-50 dark:bg-success-900/20 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <div class="w-10 h-10 rounded-full bg-success-100 dark:bg-success-900/40 flex items-center justify-center mr-3">
              <span class="text-xl">🚀</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live Keys</h3>
          </div>
          <p class="text-gray-700 dark:text-gray-300 mb-3">
            Start with <code class="text-sm bg-white dark:bg-gray-900 px-2 py-1 rounded text-gray-900 dark:text-gray-200">sk_live_</code>
          </p>
          <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li class="flex items-start">
              <svg class="w-4 h-4 text-success-600 dark:text-success-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>For production use</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 text-success-600 dark:text-success-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Real charges are processed</span>
            </li>
            <li class="flex items-start">
              <svg class="w-4 h-4 text-success-600 dark:text-success-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span>Requires KYC verification</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Making Authenticated Requests -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="authenticated-requests">Making Authenticated Requests</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        Authentication to the API is performed via HTTP Bearer Authentication. Provide your API key as the bearer token value in the Authorization header.
      </p>
      
      <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-primary-900 dark:text-primary-300">Using Libraries</p>
            <p class="text-sm text-primary-800 dark:text-primary-400 mt-1">
              Our official SDKs handle authentication automatically. Just pass your API key when initializing the client.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Error Handling -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="error-handling">Authentication Errors</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        If authentication fails, you'll receive one of the following error responses:
      </p>
      
      <div class="space-y-4">
        <!-- 401 Unauthorized -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
          <div class="flex items-center mb-2">
            <span class="px-3 py-1 bg-error-100 dark:bg-error-900/30 text-error-700 dark:text-error-400 text-xs font-semibold rounded mr-3">401</span>
            <h3 class="font-semibold text-gray-900 dark:text-white">Unauthorized</h3>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300">No valid API key provided</p>
        </div>
        
        <!-- 403 Forbidden -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
          <div class="flex items-center mb-2">
            <span class="px-3 py-1 bg-error-100 dark:bg-error-900/30 text-error-700 dark:text-error-400 text-xs font-semibold rounded mr-3">403</span>
            <h3 class="font-semibold text-gray-900 dark:text-white">Forbidden</h3>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300">The API key doesn't have permissions for the requested resource</p>
        </div>
        
        <!-- 401 Invalid Key -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
          <div class="flex items-center mb-2">
            <span class="px-3 py-1 bg-error-100 dark:bg-error-900/30 text-error-700 dark:text-error-400 text-xs font-semibold rounded mr-3">401</span>
            <h3 class="font-semibold text-gray-900 dark:text-white">Invalid API Key</h3>
          </div>
          <p class="text-sm text-gray-700 dark:text-gray-300">The API key is invalid or has been revoked</p>
        </div>
      </div>
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="best-practices">Best Practices</h2>
      
      <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 border-l-4 border-success-500 dark:border-success-600 p-6 rounded">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">✅ Store in Environment Variables</h3>
          <p class="text-gray-700 dark:text-gray-300">
            Never hardcode API keys in your source code. Use environment variables instead.
          </p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 border-l-4 border-success-500 dark:border-success-600 p-6 rounded">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">✅ Use Different Keys per Environment</h3>
          <p class="text-gray-700 dark:text-gray-300">
            Use separate API keys for development, staging, and production. This prevents accidental charges during testing.
          </p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 border-l-4 border-success-500 dark:border-success-600 p-6 rounded">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">✅ Rotate Keys Regularly</h3>
          <p class="text-gray-700 dark:text-gray-300">
            Periodically rotate your API keys for security. When rotating, create a new key before deleting the old one to avoid downtime.
          </p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 border-l-4 border-error-500 dark:border-error-600 p-6 rounded">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">❌ Don't Use in Client-Side Code</h3>
          <p class="text-gray-700 dark:text-gray-300">
            Never embed API keys in mobile apps, frontend JavaScript, or any code that users can access. Always proxy requests through your backend.
          </p>
        </div>
        
        <div class="bg-white dark:bg-gray-800 border-l-4 border-error-500 dark:border-error-600 p-6 rounded">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">❌ Don't Commit to Version Control</h3>
          <p class="text-gray-700 dark:text-gray-300">
            Add your .env file to .gitignore. Never commit API keys to Git, even in private repositories.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Key Management -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4" id="key-management">Managing API Keys</h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        You can manage your API keys from the dashboard:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center p-6 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
            <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Create Keys</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Generate new API keys for different environments</p>
        </div>
        
        <div class="text-center p-6 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-warning-50 dark:bg-warning-900/20 flex items-center justify-center">
            <svg class="w-8 h-8 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">View Usage</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Monitor API key usage and request logs</p>
        </div>
        
        <div class="text-center p-6 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-error-50 dark:bg-error-900/20 flex items-center justify-center">
            <svg class="w-8 h-8 text-error-600 dark:text-error-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Revoke Keys</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Immediately invalidate compromised keys</p>
        </div>
      </div>
      
      <div class="mt-6 text-center">
        <NuxtLink to="/dashboard/api-keys" class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
          Go to API Keys Dashboard
          <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </NuxtLink>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 dark:from-primary-900/20 dark:to-success-900/20 rounded-2xl p-8 border border-primary-200 dark:border-primary-800">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Next Steps</h2>
      <p class="text-gray-600 dark:text-gray-400 mb-6">Now that you understand authentication, start building</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <NuxtLink to="/docs/making-requests" class="block bg-white dark:bg-gray-800 rounded-lg p-6 hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Making Requests</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Learn how to make your first API call</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/error-handling" class="block bg-white dark:bg-gray-800 rounded-lg p-6 hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Error Handling</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Learn how to handle API errors gracefully</p>
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
  title: 'Authentication'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'authentication', text: 'Authentication', level: 1 },
      { id: 'overview', text: 'Overview', level: 2 },
      { id: 'api-key-types', text: 'API Key Types', level: 2 },
      { id: 'authenticated-requests', text: 'Making Authenticated Requests', level: 2 },
      { id: 'error-handling', text: 'Authentication Errors', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
      { id: 'key-management', text: 'Managing API Keys', level: 2 },
    ])
  }
})

const authRequestExample = {
  bash: `curl https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123"`,
  php: `// Initialize client with API key
$client = new BillingClient('sk_test_abc123');

// All requests are automatically authenticated
$customers = $client->customers->all();`,
  javascript: `// Initialize client with API key
const billing = require('billing-node')('sk_test_abc123');

// All requests are automatically authenticated
const customers = await billing.customers.list();`,
  python: `# Set API key once
import billing
billing.api_key = 'sk_test_abc123'

# All requests are automatically authenticated
customers = billing.Customer.list()`
}

const error401Example = `{
  "error": {
    "type": "authentication_error",
    "code": "missing_api_key",
    "message": "No API key provided. Include your API key in the Authorization header."
  }
}`

const error403Example = `{
  "error": {
    "type": "permission_error",
    "code": "insufficient_permissions",
    "message": "This API key does not have permission to access this resource."
  }
}`

const errorInvalidKeyExample = `{
  "error": {
    "type": "authentication_error",
    "code": "invalid_api_key",
    "message": "Invalid API key provided. Check your API key and try again."
  }
}`

const envExample = {
  bash: `# .env file
BILLING_API_KEY=sk_test_abc123

# Use in your application
export BILLING_API_KEY`,
  php: `// .env file
BILLING_API_KEY=sk_test_abc123

// Load in your application
$apiKey = getenv('BILLING_API_KEY');
$client = new BillingClient($apiKey);`,
  javascript: `// .env file
BILLING_API_KEY=sk_test_abc123

// Load in your application
require('dotenv').config();
const billing = require('billing-node')(process.env.BILLING_API_KEY);`,
  python: `# .env file
BILLING_API_KEY=sk_test_abc123

# Load in your application
import os
from dotenv import load_dotenv
load_dotenv()

billing.api_key = os.getenv('BILLING_API_KEY')`
}
</script>
