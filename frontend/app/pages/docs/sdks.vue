<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>SDKs & Libraries</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="sdks-libraries">SDKs & Libraries</h1>
      <p class="text-xl text-gray-600">
        Official and community-maintained client libraries for integrating the Billing API into your application.
      </p>
    </div>
    
    <!-- Official SDKs -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="official-sdks">Official SDKs</h2>
      <p class="text-gray-700 mb-6">
        We maintain official libraries for popular programming languages. These are actively supported and receive updates with new API features.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- PHP SDK -->
        <div class="border-2 border-primary-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-2xl">🐘</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">PHP</h3>
              <p class="text-sm text-gray-600">billing-php</p>
            </div>
          </div>
          
          <p class="text-gray-700 mb-4">Supports PHP 7.4+ and PHP 8.x</p>
          
          <div class="space-y-3 mb-4">
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">PSR-4 autoloading</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Composer installation</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Full type hints</span>
            </div>
          </div>
          
          <CodeBlock :code="phpInstallExample" />
          
          <div class="flex gap-3 mt-4">
            <a href="https://github.com/billing/billing-php" target="_blank" class="flex-1 text-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
              View on GitHub
            </a>
            <a href="#" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
              Documentation
            </a>
          </div>
        </div>
        
        <!-- Node.js SDK -->
        <div class="border-2 border-success-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center mr-4">
              <span class="text-2xl">📗</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Node.js</h3>
              <p class="text-sm text-gray-600">billing-node</p>
            </div>
          </div>
          
          <p class="text-gray-700 mb-4">Supports Node.js 14+ with TypeScript support</p>
          
          <div class="space-y-3 mb-4">
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Promise-based API</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">TypeScript definitions</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Zero dependencies</span>
            </div>
          </div>
          
          <CodeBlock :code="nodeInstallExample" />
          
          <div class="flex gap-3 mt-4">
            <a href="https://github.com/billing/billing-node" target="_blank" class="flex-1 text-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
              View on GitHub
            </a>
            <a href="#" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
              Documentation
            </a>
          </div>
        </div>
        
        <!-- Python SDK -->
        <div class="border-2 border-warning-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-lg bg-warning-100 flex items-center justify-center mr-4">
              <span class="text-2xl">🐍</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Python</h3>
              <p class="text-sm text-gray-600">billing-python</p>
            </div>
          </div>
          
          <p class="text-gray-700 mb-4">Supports Python 3.7+</p>
          
          <div class="space-y-3 mb-4">
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Async/await support</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Type hints included</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Pydantic models</span>
            </div>
          </div>
          
          <CodeBlock :code="pythonInstallExample" />
          
          <div class="flex gap-3 mt-4">
            <a href="https://github.com/billing/billing-python" target="_blank" class="flex-1 text-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
              View on GitHub
            </a>
            <a href="#" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
              Documentation
            </a>
          </div>
        </div>
        
        <!-- Ruby SDK -->
        <div class="border-2 border-error-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 rounded-lg bg-error-100 flex items-center justify-center mr-4">
              <span class="text-2xl">💎</span>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900">Ruby</h3>
              <p class="text-sm text-gray-600">billing-ruby</p>
            </div>
          </div>
          
          <p class="text-gray-700 mb-4">Supports Ruby 2.7+ and Ruby 3.x</p>
          
          <div class="space-y-3 mb-4">
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Rails integration</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Idiomatic Ruby API</span>
            </div>
            <div class="flex items-center text-sm">
              <svg class="w-4 h-4 text-success-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-gray-700">Full test coverage</span>
            </div>
          </div>
          
          <CodeBlock :code="rubyInstallExample" />
          
          <div class="flex gap-3 mt-4">
            <a href="https://github.com/billing/billing-ruby" target="_blank" class="flex-1 text-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
              View on GitHub
            </a>
            <a href="#" class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
              Documentation
            </a>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Community SDKs -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="community-sdks">Community Libraries</h2>
      <p class="text-gray-700 mb-6">
        These libraries are maintained by the community. While not officially supported, many are high-quality and actively maintained.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">☕</span>
            <div>
              <h3 class="font-bold text-gray-900">Java</h3>
              <p class="text-xs text-gray-600">billing-java</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">Java 11+ with Maven/Gradle support</p>
          <a href="https://github.com/community/billing-java" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">🔷</span>
            <div>
              <h3 class="font-bold text-gray-900">Go</h3>
              <p class="text-xs text-gray-600">billing-go</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">Go 1.18+ with generics support</p>
          <a href="https://github.com/community/billing-go" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">🦀</span>
            <div>
              <h3 class="font-bold text-gray-900">Rust</h3>
              <p class="text-xs text-gray-600">billing-rust</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">Async Rust with Tokio</p>
          <a href="https://github.com/community/billing-rust" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">🎯</span>
            <div>
              <h3 class="font-bold text-gray-900">.NET / C#</h3>
              <p class="text-xs text-gray-600">Billing.NET</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">.NET 6+ with async/await</p>
          <a href="https://github.com/community/billing-dotnet" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">🎼</span>
            <div>
              <h3 class="font-bold text-gray-900">Elixir</h3>
              <p class="text-xs text-gray-600">billing_ex</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">Elixir 1.13+ with Hex package</p>
          <a href="https://github.com/community/billing-elixir" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
        
        <div class="border border-gray-200 rounded-lg p-6">
          <div class="flex items-center mb-3">
            <span class="text-2xl mr-3">🐦</span>
            <div>
              <h3 class="font-bold text-gray-900">Swift</h3>
              <p class="text-xs text-gray-600">BillingKit</p>
            </div>
          </div>
          <p class="text-sm text-gray-700 mb-3">Swift 5.5+ for iOS/macOS</p>
          <a href="https://github.com/community/billing-swift" target="_blank" class="text-sm text-primary-600 hover:underline">
            View on GitHub →
          </a>
        </div>
      </div>
      
      <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-primary-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-primary-900">Want to Contribute?</p>
            <p class="text-sm text-primary-800 mt-1">
              Building a library for your favorite language? <a href="#" class="underline">Let us know</a> and we'll feature it here!
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Usage Examples -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="usage-examples">Quick Start Examples</h2>
      <p class="text-gray-700 mb-6">
        Here's how to get started with each SDK:
      </p>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">PHP Example</h3>
          <CodeBlock :code="phpUsageExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Node.js Example</h3>
          <CodeBlock :code="nodeUsageExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Python Example</h3>
          <CodeBlock :code="pythonUsageExample" />
        </div>
      </div>
    </div>
    
    <!-- REST API -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="rest-api">Direct REST API</h2>
      <p class="text-gray-700 mb-6">
        Don't see an SDK for your language? You can interact directly with our REST API using any HTTP client.
      </p>
      
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-3">Simple HTTP Request</h3>
        <CodeBlock :code="restApiExample" />
        
        <p class="text-sm text-gray-600 mt-4">
          Our API is RESTful and uses standard HTTP methods. All responses are JSON. 
          See the <NuxtLink to="/docs/api/customers" class="text-primary-600 hover:underline">API Reference</NuxtLink> for complete documentation.
        </p>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Next Steps</h2>
      <p class="text-gray-600 mb-6">Get started with your preferred SDK</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/quickstart" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Quick Start</h3>
          <p class="text-sm text-gray-600">Get your first API call working</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/authentication" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Authentication</h3>
          <p class="text-sm text-gray-600">Learn about API keys</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/api/customers" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">API Reference</h3>
          <p class="text-sm text-gray-600">Explore all endpoints</p>
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
  title: 'SDKs & Libraries'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'sdks-libraries', text: 'SDKs & Libraries', level: 1 },
      { id: 'official-sdks', text: 'Official SDKs', level: 2 },
      { id: 'community-sdks', text: 'Community Libraries', level: 2 },
      { id: 'usage-examples', text: 'Quick Start Examples', level: 2 },
      { id: 'rest-api', text: 'Direct REST API', level: 2 },
    ])
  }
})

const phpInstallExample = {
  bash: `composer require billing/billing-php`
}

const nodeInstallExample = {
  bash: `npm install billing-node
# or
yarn add billing-node`
}

const pythonInstallExample = {
  bash: `pip install billing-python`
}

const rubyInstallExample = {
  bash: `gem install billing-ruby
# or add to Gemfile
gem 'billing-ruby'`
}

const phpUsageExample = {
  php: `<?php
require_once('vendor/autoload.php');

$client = new \\Billing\\BillingClient('sk_test_abc123');

// Create a customer
$customer = $client->customers->create([
  'email' => 'john@example.com',
  'name' => 'John Doe'
]);

// Create a subscription
$subscription = $client->subscriptions->create([
  'customer_id' => $customer->id,
  'price_plan_id' => 'plan_premium_monthly'
]);

echo "Subscription created: {$subscription->id}\\n";`
}

const nodeUsageExample = {
  javascript: `const Billing = require('billing-node');
const billing = new Billing('sk_test_abc123');

// Create a customer
const customer = await billing.customers.create({
  email: 'john@example.com',
  name: 'John Doe'
});

// Create a subscription
const subscription = await billing.subscriptions.create({
  customer_id: customer.id,
  price_plan_id: 'plan_premium_monthly'
});

console.log(\`Subscription created: \${subscription.id}\`);`
}

const pythonUsageExample = {
  python: `import billing

billing.api_key = 'sk_test_abc123'

# Create a customer
customer = billing.Customer.create(
    email='john@example.com',
    name='John Doe'
)

# Create a subscription
subscription = billing.Subscription.create(
    customer_id=customer.id,
    price_plan_id='plan_premium_monthly'
)

print(f"Subscription created: {subscription.id}")`
}

const restApiExample = {
  bash: `curl -X POST https://api.billing.com/v1/customers \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "email": "john@example.com",
    "name": "John Doe"
  }'`
}
</script>
