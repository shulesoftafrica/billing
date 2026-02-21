<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Error Handling</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="error-handling">Error Handling</h1>
      <p class="text-xl text-gray-600">
        Learn how to handle API errors gracefully and build robust integrations with proper error handling.
      </p>
    </div>
    
    <!-- Overview -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="overview">Overview</h2>
      <p class="text-gray-700 mb-6">
        The Billing API uses conventional HTTP response codes to indicate the success or failure of an API request. 
        In general, codes in the 2xx range indicate success, codes in the 4xx range indicate an error that failed given the information provided, 
        and codes in the 5xx range indicate an error with our servers.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="border-l-4 border-success-500 bg-success-50 p-6 rounded-lg">
          <div class="text-3xl font-bold text-success-700 mb-2">2xx</div>
          <h3 class="font-semibold text-gray-900 mb-2">Success</h3>
          <p class="text-sm text-gray-700">Request completed successfully</p>
        </div>
        
        <div class="border-l-4 border-warning-500 bg-warning-50 p-6 rounded-lg">
          <div class="text-3xl font-bold text-warning-700 mb-2">4xx</div>
          <h3 class="font-semibold text-gray-900 mb-2">Client Error</h3>
          <p class="text-sm text-gray-700">Invalid request from your application</p>
        </div>
        
        <div class="border-l-4 border-error-500 bg-error-50 p-6 rounded-lg">
          <div class="text-3xl font-bold text-error-700 mb-2">5xx</div>
          <h3 class="font-semibold text-gray-900 mb-2">Server Error</h3>
          <p class="text-sm text-gray-700">Something went wrong on our end</p>
        </div>
      </div>
    </div>
    
    <!-- HTTP Status Codes -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="http-status-codes">HTTP Status Codes</h2>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="code in statusCodes" :key="code.code" :class="code.type === 'success' ? 'bg-success-50/30' : code.type === 'error' ? 'bg-error-50/30' : ''">
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'px-3 py-1 text-xs font-semibold rounded',
                  code.type === 'success' ? 'bg-success-100 text-success-700' : 
                  code.type === 'client' ? 'bg-warning-100 text-warning-700' : 
                  'bg-error-100 text-error-700'
                ]">
                  {{ code.code }}
                </span>
              </td>
              <td class="px-6 py-4 font-medium text-gray-900">{{ code.status }}</td>
              <td class="px-6 py-4 text-sm text-gray-700">{{ code.description }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Error Object Structure -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="error-object">Error Object Structure</h2>
      <p class="text-gray-700 mb-6">
        All error responses follow a consistent JSON structure to make parsing easier:
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Error Attributes</h3>
          <div class="space-y-4">
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">type</code>
              <p class="text-sm text-gray-600 mt-1">The type of error returned (authentication_error, validation_error, etc.)</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">code</code>
              <p class="text-sm text-gray-600 mt-1">Machine-readable error code for programmatic handling</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">message</code>
              <p class="text-sm text-gray-600 mt-1">Human-readable error message</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">param</code>
              <p class="text-sm text-gray-600 mt-1">(Optional) The parameter that caused the error</p>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Example Error Response</h3>
          <CodeBlock :code="errorObjectExample" language="json" />
        </div>
      </div>
    </div>
    
    <!-- Error Types -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="error-types">Error Types</h2>
      
      <div class="space-y-6">
        <!-- Authentication Error -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 rounded-full bg-error-100 flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">authentication_error</h3>
              <p class="text-sm text-gray-600">Authentication with the API failed</p>
            </div>
          </div>
          <CodeBlock :code="authErrorExample" language="json" />
        </div>
        
        <!-- Validation Error -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">validation_error</h3>
              <p class="text-sm text-gray-600">Parameters failed validation</p>
            </div>
          </div>
          <CodeBlock :code="validationErrorExample" language="json" />
        </div>
        
        <!-- Rate Limit Error -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 rounded-full bg-error-100 flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">rate_limit_error</h3>
              <p class="text-sm text-gray-600">Too many requests in a short period</p>
            </div>
          </div>
          <CodeBlock :code="rateLimitErrorExample" language="json" />
        </div>
        
        <!-- Resource Error -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 rounded-full bg-warning-100 flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">resource_error</h3>
              <p class="text-sm text-gray-600">Requested resource doesn't exist</p>
            </div>
          </div>
          <CodeBlock :code="resourceErrorExample" language="json" />
        </div>
        
        <!-- API Error -->
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-4">
            <div class="w-10 h-10 rounded-full bg-error-100 flex items-center justify-center mr-3">
              <svg class="w-6 h-6 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">api_error</h3>
              <p class="text-sm text-gray-600">Something went wrong on our servers</p>
            </div>
          </div>
          <CodeBlock :code="apiErrorExample" language="json" />
        </div>
      </div>
    </div>
    
    <!-- Handling Errors -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="handling-errors">Handling Errors in Code</h2>
      <p class="text-gray-700 mb-6">
        Here's how to properly handle errors in different programming languages:
      </p>
      
      <CodeBlock :code="errorHandlingExample" />
      
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-primary-900">Pro Tip</p>
            <p class="text-sm text-primary-800 mt-1">
              Always check the error code property for programmatic handling. Error messages may change, but codes remain stable.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Rate Limiting -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="rate-limiting">Rate Limiting</h2>
      <p class="text-gray-700 mb-6">
        The API limits requests to prevent abuse. Rate limit headers are included in every response:
      </p>
      
      <div class="bg-gray-50 rounded-lg p-6 mb-6 font-mono text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <span class="text-gray-500">X-RateLimit-Limit:</span>
            <span class="text-gray-900"> 1000</span>
          </div>
          <div>
            <span class="text-gray-500">X-RateLimit-Remaining:</span>
            <span class="text-gray-900"> 998</span>
          </div>
          <div>
            <span class="text-gray-500">X-RateLimit-Reset:</span>
            <span class="text-gray-900"> 1708185600</span>
          </div>
        </div>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Rate Limit Tiers</h3>
      <div class="overflow-x-auto mb-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limit</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Window</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr>
              <td class="px-6 py-4 font-medium text-gray-900">Test Mode</td>
              <td class="px-6 py-4 text-gray-700">100 requests</td>
              <td class="px-6 py-4 text-gray-700">Per minute</td>
            </tr>
            <tr>
              <td class="px-6 py-4 font-medium text-gray-900">Live Mode (Standard)</td>
              <td class="px-6 py-4 text-gray-700">1,000 requests</td>
              <td class="px-6 py-4 text-gray-700">Per minute</td>
            </tr>
            <tr>
              <td class="px-6 py-4 font-medium text-gray-900">Live Mode (Premium)</td>
              <td class="px-6 py-4 text-gray-700">5,000 requests</td>
              <td class="px-6 py-4 text-gray-700">Per minute</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Handling Rate Limits</h3>
      <CodeBlock :code="rateLimitHandlingExample" />
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="best-practices">Best Practices</h2>
      
      <div class="space-y-4">
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Log Errors with Context</h3>
          <p class="text-gray-700">
            Always log the full error object, request ID, and relevant context. This helps with debugging production issues.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Implement Exponential Backoff</h3>
          <p class="text-gray-700">
            When retrying failed requests, use exponential backoff to avoid overwhelming the API during outages.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Show User-Friendly Messages</h3>
          <p class="text-gray-700">
            Display helpful messages to users based on error types. Don't expose technical error details.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Monitor Error Rates</h3>
          <p class="text-gray-700">
            Track error rates in your monitoring system. Sudden spikes may indicate integration issues.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Next Steps</h2>
      <p class="text-gray-600 mb-6">Continue learning about the API</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks Guide</h3>
          <p class="text-sm text-gray-600">Receive real-time notifications for events</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/customers" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">API Reference</h3>
          <p class="text-sm text-gray-600">Explore complete API documentation</p>
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
  title: 'Error Handling'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'error-handling', text: 'Error Handling', level: 1 },
      { id: 'overview', text: 'Overview', level: 2 },
      { id: 'http-status-codes', text: 'HTTP Status Codes', level: 2 },
      { id: 'error-object', text: 'Error Object Structure', level: 2 },
      { id: 'error-types', text: 'Error Types', level: 2 },
      { id: 'handling-errors', text: 'Handling Errors in Code', level: 2 },
      { id: 'rate-limiting', text: 'Rate Limiting', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
    ])
  }
})

const statusCodes = [
  { code: 200, status: 'OK', description: 'Request succeeded', type: 'success' },
  { code: 201, status: 'Created', description: 'Resource successfully created', type: 'success' },
  { code: 400, status: 'Bad Request', description: 'Invalid request parameters', type: 'client' },
  { code: 401, status: 'Unauthorized', description: 'Missing or invalid API key', type: 'client' },
  { code: 403, status: 'Forbidden', description: 'API key lacks permissions', type: 'client' },
  { code: 404, status: 'Not Found', description: 'Resource doesn\'t exist', type: 'client' },
  { code: 409, status: 'Conflict', description: 'Resource already exists', type: 'client' },
  { code: 422, status: 'Unprocessable', description: 'Validation failed', type: 'client' },
  { code: 429, status: 'Too Many Requests', description: 'Rate limit exceeded', type: 'client' },
  { code: 500, status: 'Internal Server Error', description: 'Something went wrong', type: 'error' },
  { code: 503, status: 'Service Unavailable', description: 'Temporary outage', type: 'error' },
]

const errorObjectExample = `{
  "error": {
    "type": "validation_error",
    "code": "invalid_email",
    "message": "The email address provided is invalid.",
    "param": "email"
  }
}`

const authErrorExample = `{
  "error": {
    "type": "authentication_error",
    "code": "invalid_api_key",
    "message": "Invalid API key provided."
  }
}`

const validationErrorExample = `{
  "error": {
    "type": "validation_error",
    "code": "missing_required_field",
    "message": "Missing required parameter: customer_id",
    "param": "customer_id"
  }
}`

const rateLimitErrorExample = `{
  "error": {
    "type": "rate_limit_error",
    "code": "rate_limit_exceeded",
    "message": "Too many requests. Please wait before trying again."
  }
}`

const resourceErrorExample = `{
  "error": {
    "type": "resource_error",
    "code": "resource_not_found",
    "message": "No customer found with id: cus_invalid123"
  }
}`

const apiErrorExample = `{
  "error": {
    "type": "api_error",
    "code": "internal_error",
    "message": "An unexpected error occurred. Our team has been notified."
  }
}`

const errorHandlingExample = {
  php: `try {
  $customer = $client->customers->create([
    'email' => 'invalid-email',
    'name' => 'John Doe'
  ]);
} catch (\\Billing\\Exception\\ValidationException $e) {
  // Handle validation errors
  $errorCode = $e->getCode();
  $errorMessage = $e->getMessage();
  $param = $e->getParam();
  
  error_log("Validation error on {$param}: {$errorMessage}");
  
} catch (\\Billing\\Exception\\AuthenticationException $e) {
  // Handle auth errors
  error_log("Authentication failed: " . $e->getMessage());
  
} catch (\\Billing\\Exception\\ApiException $e) {
  // Handle generic API errors
  error_log("API error: " . $e->getMessage());
}`,
  javascript: `try {
  const customer = await billing.customers.create({
    email: 'invalid-email',
    name: 'John Doe'
  });
} catch (error) {
  if (error.type === 'validation_error') {
    // Handle validation errors
    console.error(\`Validation error on \${error.param}: \${error.message}\`);
    
  } else if (error.type === 'authentication_error') {
    // Handle auth errors
    console.error('Authentication failed:', error.message);
    
  } else if (error.type === 'rate_limit_error') {
    // Handle rate limiting
    console.error('Rate limit exceeded. Retry after:', error.retry_after);
    
  } else {
    // Handle other errors
    console.error('API error:', error.message);
  }
}`,
  python: `try:
    customer = billing.Customer.create(
        email='invalid-email',
        name='John Doe'
    )
except billing.error.ValidationError as e:
    # Handle validation errors
    print(f"Validation error on {e.param}: {e.message}")
    
except billing.error.AuthenticationError as e:
    # Handle auth errors
    print(f"Authentication failed: {e.message}")
    
except billing.error.RateLimitError as e:
    # Handle rate limiting
    print(f"Rate limit exceeded. Retry after {e.retry_after}s")
    
except billing.error.APIError as e:
    # Handle generic API errors
    print(f"API error: {e.message}")`
}

const rateLimitHandlingExample = {
  php: `function makeRequestWithRetry($callable, $maxRetries = 3) {
  $retries = 0;
  
  while ($retries < $maxRetries) {
    try {
      return $callable();
    } catch (\\Billing\\Exception\\RateLimitException $e) {
      $retries++;
      if ($retries >= $maxRetries) throw $e;
      
      $waitTime = pow(2, $retries); // Exponential backoff
      sleep($waitTime);
    }
  }
}

// Usage
$customer = makeRequestWithRetry(function() use ($client) {
  return $client->customers->create(['email' => 'john@example.com']);
});`,
  javascript: `async function makeRequestWithRetry(fn, maxRetries = 3) {
  let retries = 0;
  
  while (retries < maxRetries) {
    try {
      return await fn();
    } catch (error) {
      if (error.type === 'rate_limit_error') {
        retries++;
        if (retries >= maxRetries) throw error;
        
        const waitTime = Math.pow(2, retries) * 1000; // Exponential backoff
        await new Promise(resolve => setTimeout(resolve, waitTime));
      } else {
        throw error;
      }
    }
  }
}

// Usage
const customer = await makeRequestWithRetry(() => 
  billing.customers.create({ email: 'john@example.com' })
);`,
  python: `import time

def make_request_with_retry(callable_fn, max_retries=3):
    retries = 0
    
    while retries < max_retries:
        try:
            return callable_fn()
        except billing.error.RateLimitError as e:
            retries += 1
            if retries >= max_retries:
                raise e
            
            wait_time = 2 ** retries  # Exponential backoff
            time.sleep(wait_time)

# Usage
customer = make_request_with_retry(
    lambda: billing.Customer.create(email='john@example.com')
)`
}
</script>
