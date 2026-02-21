<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <NuxtLink to="/docs/guides/customers" class="hover:text-gray-700">Guides</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Subscriptions</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="subscriptions-guide">Subscription Management Guide</h1>
      <p class="text-xl text-gray-600">
        Learn best practices for managing subscription lifecycles, handling upgrades/downgrades, and recovering from failed payments.
      </p>
    </div>
    
    <!-- Introduction -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="introduction">Introduction</h2>
      <p class="text-gray-700 mb-4">
        Subscriptions are the core of recurring billing. This guide covers common patterns and best practices for managing subscription lifecycles effectively.
      </p>
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="font-semibold text-blue-900 mb-3">What You'll Learn</h3>
        <ul class="space-y-2 text-sm text-blue-800">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Creating subscriptions with trials</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Handling plan upgrades and downgrades</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Managing cancellations gracefully</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Recovering from failed payments</span>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Creating Subscriptions -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="creating-subscriptions">Creating Subscriptions</h2>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Basic Subscription</h3>
          <p class="text-gray-700 mb-4">Create a simple subscription without a trial:</p>
          <CodeBlock :code="basicSubscriptionExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Subscription with Trial</h3>
          <p class="text-gray-700 mb-4">Offer a trial period before charging:</p>
          <CodeBlock :code="trialSubscriptionExample" />
          
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
            <div class="flex">
              <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
              <div>
                <p class="text-sm font-medium text-blue-900">Trial Ending Webhook</p>
                <p class="text-sm text-blue-800 mt-1">
                  Listen for <code class="bg-blue-100 px-1 py-0.5 rounded">subscription.trial_ending</code> events to notify customers 3 days before their trial ends.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Upgrading and Downgrading -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="changing-plans">Upgrading and Downgrading Plans</h2>
      <p class="text-gray-700 mb-6">
        When customers change plans mid-cycle, you need to handle proration to ensure fair billing.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
          <h3 class="font-semibold text-green-900 mb-3">Upgrade (Immediate)</h3>
          <p class="text-sm text-green-800 mb-3">Customer gets immediate access to new features. Prorated credit applied.</p>
          <CodeBlock :code="upgradeExample" />
        </div>
        
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-6">
          <h3 class="font-semibold text-amber-900 mb-3">Downgrade (End of Period)</h3>
          <p class="text-sm text-amber-800 mb-3">Changes take effect at period end. Customer keeps current features until then.</p>
          <CodeBlock :code="downgradeExample" />
        </div>
      </div>
      
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-3">Proration Behavior Options</h3>
        <dl class="space-y-4">
          <div>
            <dt class="text-sm font-mono text-gray-900">create_prorations</dt>
            <dd class="text-sm text-gray-600 mt-1">Creates invoice items for the difference. Recommended for upgrades.</dd>
          </div>
          <div>
            <dt class="text-sm font-mono text-gray-900">none</dt>
            <dd class="text-sm text-gray-600 mt-1">No proration. Change takes effect immediately. Good for equal-price plan switches.</dd>
          </div>
          <div>
            <dt class="text-sm font-mono text-gray-900">always_invoice</dt>
            <dd class="text-sm text-gray-600 mt-1">Creates and immediately finalizes an invoice for the prorated amount.</dd>
          </div>
        </dl>
      </div>
    </div>
    
    <!-- Cancellation -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="cancellation">Handling Cancellations</h2>
      <p class="text-gray-700 mb-6">
        There are two ways to cancel a subscription: immediately or at period end.
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Cancel at Period End</h3>
          <p class="text-gray-700 mb-3 text-sm">Customer retains access until the current billing period ends. This is the recommended approach.</p>
          <CodeBlock :code="cancelAtPeriodEndExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Immediate Cancellation</h3>
          <p class="text-gray-700 mb-3 text-sm">Access is revoked immediately. Use for exceptional cases (fraud, TOS violations).</p>
          <CodeBlock :code="immediateCancelExample" />
        </div>
      </div>
      
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <div class="flex">
          <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">Retention Opportunity</p>
            <p class="text-sm text-amber-800 mt-1">
              Before canceling, consider offering a discount, pause option, or survey to understand why they're leaving.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Failed Payments -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="failed-payments">Recovering from Failed Payments</h2>
      <p class="text-gray-700 mb-6">
        Payment failures happen. Having a recovery strategy is crucial for reducing churn.
      </p>
      
      <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <h3 class="font-semibold text-red-900 mb-3">Dunning Process</h3>
        <p class="text-sm text-red-800 mb-4">
          Dunning is the automated process of retrying failed payments and communicating with customers.
        </p>
        
        <div class="space-y-3">
          <div class="flex items-start bg-white rounded-lg p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-semibold text-red-700">1</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Payment Fails</p>
              <p class="text-xs text-gray-600">Customer receives immediate notification</p>
            </div>
          </div>
          
          <div class="flex items-start bg-white rounded-lg p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-semibold text-red-700">2</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Automatic Retry (Day 3)</p>
              <p class="text-xs text-gray-600">First automated retry attempt</p>
            </div>
          </div>
          
          <div class="flex items-start bg-white rounded-lg p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-semibold text-red-700">3</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Second Retry (Day 5)</p>
              <p class="text-xs text-gray-600">Warning: subscription will be canceled soon</p>
            </div>
          </div>
          
          <div class="flex items-start bg-white rounded-lg p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-semibold text-red-700">4</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Final Retry (Day 7)</p>
              <p class="text-xs text-gray-600">Last attempt before cancellation</p>
            </div>
          </div>
          
          <div class="flex items-start bg-white rounded-lg p-3">
            <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-semibold text-red-700">5</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Cancel Subscription (Day 10)</p>
              <p class="text-xs text-gray-600">Subscription canceled if still unpaid</p>
            </div>
          </div>
        </div>
      </div>
      
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Handling Payment Failures</h3>
        <p class="text-gray-700 mb-4">Listen for the <code class="text-sm bg-gray-100 px-1 py-0.5 rounded">invoice.payment_failed</code> event:</p>
        <CodeBlock :code="paymentFailedWebhookExample" />
      </div>
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="best-practices">Best Practices</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0 w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Proactive Communication</h3>
              <p class="text-sm text-gray-600">
                Notify customers before trials end, before renewals, and when payments fail. Transparency builds trust.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0 w-10 h-10 bg-success-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Grace Periods</h3>
              <p class="text-sm text-gray-600">
                Don't immediately revoke access after a failed payment. Give customers time to update their payment method.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0 w-10 h-10 bg-warning-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Flexible Terms</h3>
              <p class="text-sm text-gray-600">
                Offer monthly and annual options. Annual subscriptions reduce churn and improve cash flow.
              </p>
            </div>
          </div>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0 w-10 h-10 bg-error-100 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-error-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900 mb-2">Easy Plan Changes</h3>
              <p class="text-sm text-gray-600">
                Make it simple for customers to upgrade or downgrade. Friction leads to cancellations.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Explore more subscription topics</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/api/subscriptions" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Subscriptions API</h3>
          <p class="text-sm text-gray-600">Complete API reference</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices Guide</h3>
          <p class="text-sm text-gray-600">Manage subscription invoices</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks</h3>
          <p class="text-sm text-gray-600">Listen to subscription events</p>
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
  title: 'Subscription Management Guide'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'subscriptions-guide', text: 'Subscription Management', level: 1 },
      { id: 'introduction', text: 'Introduction', level: 2 },
      { id: 'creating-subscriptions', text: 'Creating Subscriptions', level: 2 },
      { id: 'changing-plans', text: 'Upgrading and Downgrading', level: 2 },
      { id: 'cancellation', text: 'Handling Cancellations', level: 2 },
      { id: 'failed-payments', text: 'Failed Payment Recovery', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
    ])
  }
})

const basicSubscriptionExample = {
  bash: `curl -X POST https://api.billing.com/v1/subscriptions \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": "cus_abc123",
    "price_plan_id": "plan_premium_monthly"
  }'`,
  php: `$subscription = $client->subscriptions->create([
  'customer_id' => 'cus_abc123',
  'price_plan_id' => 'plan_premium_monthly'
]);`
}

const trialSubscriptionExample = {
  bash: `curl -X POST https://api.billing.com/v1/subscriptions \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "customer_id": "cus_abc123",
    "price_plan_id": "plan_premium_monthly",
    "trial_days": 14
  }'`,
  php: `$subscription = $client->subscriptions->create([
  'customer_id' => 'cus_abc123',
  'price_plan_id' => 'plan_premium_monthly',
  'trial_days' => 14
]);`
}

const upgradeExample = {
  php: `$subscription = $client->subscriptions->update(
  'sub_xyz789',
  [
    'price_plan_id' => 'plan_enterprise',
    'proration_behavior' => 'create_prorations'
  ]
);`
}

const downgradeExample = {
  php: `$subscription = $client->subscriptions->update(
  'sub_xyz789',
  [
    'price_plan_id' => 'plan_basic',
    'proration_behavior' => 'none',
    'cancel_at_period_end' => false
  ]
);`
}

const cancelAtPeriodEndExample = {
  php: `$subscription = $client->subscriptions->cancel(
  'sub_xyz789',
  ['cancel_at_period_end' => true]
);`
}

const immediateCancelExample = {
  php: `$subscription = $client->subscriptions->cancel(
  'sub_xyz789',
  ['cancel_at_period_end' => false]
);`
}

const paymentFailedWebhookExample = {
  php: `public function handleWebhook(Request $request)
{
  $event = $request->all();
  
  if ($event['type'] === 'invoice.payment_failed') {
    $invoice = $event['data'];
    $customer = Customer::find($invoice['customer_id']);
    
    // Send email to customer
    Mail::to($customer->email)->send(
      new PaymentFailedNotification($invoice)
    );
    
    // Update payment method page link
    $updateUrl = route('billing.payment-method');
    
    // Log for follow-up
    Log::warning('Payment failed', [
      'customer' => $customer->id,
      'invoice' => $invoice['id'],
      'amount' => $invoice['amount_due']
    ]);
  }
  
  return response()->json(['status' => 'success']);
}`
}
</script>
