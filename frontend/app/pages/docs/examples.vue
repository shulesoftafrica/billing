<template>
  <div>
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex items-center text-sm text-gray-500 mb-4">
        <NuxtLink to="/docs" class="hover:text-gray-700">Documentation</NuxtLink>
        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span>Code Examples</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="code-examples">Code Examples</h1>
      <p class="text-xl text-gray-600">
        Complete code examples demonstrating common integration patterns and use cases.
      </p>
    </div>
    
    <!-- Complete Subscription Flow -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="subscription-flow">Complete Subscription Flow</h2>
      <p class="text-gray-700 mb-6">
        This example shows how to create a customer, add a payment method, and start a subscription:
      </p>
      
      <CodeBlock :code="subscriptionFlowExample" />
    </div>
    
    <!-- Customer Onboarding -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="onboarding">Customer Onboarding</h2>
      <p class="text-gray-700 mb-6">
        A complete onboarding flow with customer creation, KYC verification, and subscription setup:
      </p>
      
      <CodeBlock :code="onboardingExample" />
    </div>
    
    <!-- Invoice Payment -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="invoice-payment">Invoice Payment Flow</h2>
      <p class="text-gray-700 mb-6">
        Create an invoice, send it to the customer, and process payment:
      </p>
      
      <CodeBlock :code="invoicePaymentExample" />
    </div>
    
    <!-- Webhook Handler -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="webhook-handler">Complete Webhook Handler</h2>
      <p class="text-gray-700 mb-6">
        A production-ready webhook handler with signature verification and event processing:
      </p>
      
      <CodeBlock :code="webhookHandlerExample" />
    </div>
    
    <!-- Failed Payment Recovery -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="payment-recovery">Failed Payment Recovery</h2>
      <p class="text-gray-700 mb-6">
        Implement smart retry logic for failed payments:
      </p>
      
      <CodeBlock :code="paymentRecoveryExample" />
    </div>
    
    <!-- Usage Metering -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="usage-metering">Usage-Based Billing</h2>
      <p class="text-gray-700 mb-6">
        Track usage and bill customers based on consumption:
      </p>
      
      <CodeBlock :code="usageMeteringExample" />
    </div>
    
    <!-- Multi-Tenant Setup -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="multi-tenant">Multi-Tenant Setup</h2>
      <p class="text-gray-700 mb-6">
        Manage multiple organizations with separate billing:
      </p>
      
      <CodeBlock :code="multiTenantExample" />
    </div>
    
    <!-- Sample Applications -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="sample-apps">Sample Applications</h2>
      <p class="text-gray-700 mb-6">
        Full sample applications demonstrating complete integrations:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">SaaS Starter (Laravel + Vue)</h3>
          <p class="text-sm text-gray-700 mb-4">
            Complete SaaS application with authentication, subscription management, and billing dashboard.
          </p>
          <a href="https://github.com/billing/saas-starter-laravel" target="_blank" class="inline-flex items-center text-sm text-primary-600 hover:underline">
            View on GitHub 
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">E-Commerce (Node.js + React)</h3>
          <p class="text-sm text-gray-700 mb-4">
            Online store with product catalog, cart, checkout, and order management.
          </p>
          <a href="https://github.com/billing/ecommerce-starter-node" target="_blank" class="inline-flex items-center text-sm text-primary-600 hover:underline">
            View on GitHub 
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Marketplace (Python + Django)</h3>
          <p class="text-sm text-gray-700 mb-4">
            Two-sided marketplace with seller onboarding, split payments, and marketplace fees.
          </p>
          <a href="https://github.com/billing/marketplace-starter-python" target="_blank" class="inline-flex items-center text-sm text-primary-600 hover:underline">
            View on GitHub 
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">Mobile App (React Native)</h3>
          <p class="text-sm text-gray-700 mb-4">
            Mobile subscription app with in-app purchases and billing management.
          </p>
          <a href="https://github.com/billing/mobile-starter-react-native" target="_blank" class="inline-flex items-center text-sm text-primary-600 hover:underline">
            View on GitHub 
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Need Help?</h2>
      <p class="text-gray-600 mb-6">We're here to help you integrate successfully</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/quickstart" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Quick Start</h3>
          <p class="text-sm text-gray-600">Get started in minutes</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/sdks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">SDKs & Libraries</h3>
          <p class="text-sm text-gray-600">Use our official SDKs</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/support" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Get Support</h3>
          <p class="text-sm text-gray-600">Contact our team</p>
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
  title: 'Code Examples'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'code-examples', text: 'Code Examples', level: 1 },
      { id: 'subscription-flow', text: 'Subscription Flow', level: 2 },
      { id: 'onboarding', text: 'Customer Onboarding', level: 2 },
      { id: 'invoice-payment', text: 'Invoice Payment', level: 2 },
      { id: 'webhook-handler', text: 'Webhook Handler', level: 2 },
      { id: 'payment-recovery', text: 'Payment Recovery', level: 2 },
      { id: 'usage-metering', text: 'Usage-Based Billing', level: 2 },
      { id: 'multi-tenant', text: 'Multi-Tenant Setup', level: 2 },
      { id: 'sample-apps', text: 'Sample Applications', level: 2 },
    ])
  }
})

const subscriptionFlowExample = {
  php: `<?php
// Complete subscription flow
$client = new \\Billing\\BillingClient('sk_test_abc123');

// 1. Create customer
$customer = $client->customers->create([
  'email' => 'john@example.com',
  'name' => 'John Doe',
  'phone' => '+1234567890'
]);

// 2. Add payment method
$paymentMethod = $client->paymentMethods->create([
  'customer_id' => $customer->id,
  'type' => 'card',
  'card' => [
    'number' => '4242424242424242',
    'exp_month' => 12,
    'exp_year' => 2025,
    'cvc' => '123'
  ]
]);

// 3. Create subscription
$subscription = $client->subscriptions->create([
  'customer_id' => $customer->id,
  'price_plan_id' => 'plan_premium_monthly',
  'payment_method' => $paymentMethod->id,
  'trial_days' => 14
]);

echo "Subscription created: {$subscription->id}";
echo "Trial ends: {$subscription->trial_end}";`
}

const onboardingExample = {
  php: `<?php
// Complete customer onboarding with KYC
public function onboardCustomer(Request $request)
{
  DB::beginTransaction();
  
  try {
    // 1. Create customer
    $customer = $this->billingClient->customers->create([
      'email' => $request->email,
      'name' => $request->name,
      'phone' => $request->phone,
      'address' => $request->address,
      'metadata' => [
        'user_id' => auth()->id(),
        'signup_source' => 'web'
      ]
    ]);
    
    // 2. Upload KYC documents
    if ($request->hasFile('id_document')) {
      $document = $request->file('id_document');
      $this->billingClient->customers->uploadDocument(
        $customer->id,
        'id_document',
        $document
      );
    }
    
    // 3. Link to local user
    auth()->user()->update([
      'billing_customer_id' => $customer->id
    ]);
    
    // 4. Send welcome email
    Mail::to($customer->email)->send(
      new WelcomeEmail($customer)
    );
    
    DB::commit();
    
    return response()->json([
      'customer_id' => $customer->id,
      'kyc_status' => $customer->kyc_status
    ]);
    
  } catch (\\Exception $e) {
    DB::rollBack();
    throw $e;
  }
}`
}

const invoicePaymentExample = {
  php: `<?php
// Create and process invoice payment
public function createAndPayInvoice($customerId, $items)
{
  // 1. Create draft invoice
  $invoice = $this->billingClient->invoices->create([
    'customer_id' => $customerId,
    'line_items' => $items,
    'due_date' => now()->addDays(30)->toIso8601String()
  ]);
  
  // 2. Finalize invoice
  $invoice = $this->billingClient->invoices->finalize($invoice->id);
  
  // 3. Send to customer
  Mail::to($invoice->customer_email)->send(
    new InvoiceEmail($invoice)
  );
  
  // 4. If customer has saved payment method, auto-charge
  if ($invoice->customer->default_payment_method) {
    try {
      $payment = $this->billingClient->invoices->pay($invoice->id);
      
      // Send receipt
      Mail::to($invoice->customer_email)->send(
        new PaymentReceiptEmail($payment)
      );
    } catch (\\Exception $e) {
      // Payment failed, customer will need to pay manually
      Log::error('Auto-payment failed', [
        'invoice_id' => $invoice->id,
        'error' => $e->getMessage()
      ]);
    }
  }
  
  return $invoice;
}`
}

const webhookHandlerExample = {
  php: `<?php
// Production-ready webhook handler
public function handleWebhook(Request $request)
{
  // 1. Verify signature
  $signature = $request->header('X-Webhook-Signature');
  $payload = $request->getContent();
  $secret = config('billing.webhook_secret');
  
  $expectedSignature = hash_hmac('sha256', $payload, $secret);
  
  if (!hash_equals($expectedSignature, $signature)) {
    return response()->json(['error' => 'Invalid signature'], 401);
  }
  
  // 2. Parse event
  $event = $request->all();
  $eventId = $event['id'];
  
  // 3. Check for duplicate
  if (WebhookEvent::where('event_id', $eventId)->exists()) {
    return response()->json(['status' => 'duplicate']);
  }
  
  // 4. Store event
  WebhookEvent::create([
    'event_id' => $eventId,
    'type' => $event['type'],
    'data' => $event['data']
  ]);
  
  // 5. Process event
  try {
    switch ($event['type']) {
      case 'customer.created':
        $this->handleCustomerCreated($event['data']);
        break;
        
      case 'subscription.created':
        $this->handleSubscriptionCreated($event['data']);
        break;
        
      case 'invoice.paid':
        $this->handleInvoicePaid($event['data']);
        break;
        
      case 'invoice.payment_failed':
        $this->handlePaymentFailed($event['data']);
        break;
        
      case 'subscription.canceled':
        $this->handleSubscriptionCanceled($event['data']);
        break;
    }
  } catch (\\Exception $e) {
    Log::error('Webhook processing failed', [
      'event_id' => $eventId,
      'type' => $event['type'],
      'error' => $e->getMessage()
    ]);
  }
  
  return response()->json(['status' => 'success']);
}`
}

const paymentRecoveryExample = {
  php: `<?php
// Smart retry logic for failed payments
public function retryFailedPayment($invoiceId, $attemptNumber = 1)
{
  $invoice = $this->billingClient->invoices->retrieve($invoiceId);
  
  if ($invoice->status === 'paid') {
    return; // Already paid
  }
  
  // Exponential backoff: 1, 3, 7, 14 days
  $retrySchedule = [1, 3, 7, 14];
  
  if ($attemptNumber > count($retrySchedule)) {
    $this->handleFinalFailure($invoice);
    return;
  }
  
  try {
    // Attempt payment
    $payment = $this->billingClient->invoices->pay($invoiceId);
    
    // Success!
    $this->handlePaymentSuccess($invoice, $payment);
    
  } catch (\\Billing\\Exception\\PaymentException $e) {
    // Payment failed, schedule next retry
    $nextAttempt = $attemptNumber + 1;
    $nextRetryDays = $retrySchedule[$attemptNumber] ?? 14;
    
    RetryPaymentJob::dispatch($invoiceId, $nextAttempt)
      ->delay(now()->addDays($nextRetryDays));
    
    // Notify customer
    $customer = Customer::where(
      'billing_customer_id',
      $invoice->customer_id
    )->first();
    
    Mail::to($customer->email)->send(
      new PaymentRetryEmail($invoice, $nextAttempt)
    );
  }
}`
}

const usageMeteringExample = {
  php: `<?php
// Usage-based billing
public function recordUsage($customerId, $metric, $quantity)
{
  // 1. Record usage
  UsageRecord::create([
    'customer_id' => $customerId,
    'metric' => $metric,
    'quantity' => $quantity,
    'timestamp' => now()
  ]);
  
  return response()->json(['recorded' => true]);
}

// At end of billing period
public function billForUsage($customerId, $billingPeriodStart, $billingPeriodEnd)
{
  // 1. Calculate usage
  $apiCalls = UsageRecord::where('customer_id', $customerId)
    ->where('metric', 'api_calls')
    ->whereBetween('timestamp', [$billingPeriodStart, $billingPeriodEnd])
    ->sum('quantity');
  
  $storage = UsageRecord::where('customer_id', $customerId)
    ->where('metric', 'storage_gb')
    ->whereBetween('timestamp', [$billingPeriodStart, $billingPeriodEnd])
    ->avg('quantity');
  
  // 2. Calculate charges
  $apiCallsCharge = max(0, ($apiCalls - 10000)) * 0.001; // $0.001 per call after 10k
  $storageCharge = max(0, ($storage - 10)) * 0.10; // $0.10 per GB after 10GB
  
  // 3. Create invoice
  $invoice = $this->billingClient->invoices->create([
    'customer_id' => $customerId,
    'line_items' => [
      [
        'description' => "API Calls ({$apiCalls} calls)",
        'amount' => $apiCallsCharge * 100,
        'quantity' => 1
      ],
      [
        'description' => "Storage ({$storage} GB)",
        'amount' => $storageCharge * 100,
        'quantity' => 1
      ]
    ]
  ]);
  
  return $invoice;
}`
}

const multiTenantExample = {
  php: `<?php
// Multi-tenant billing setup
class OrganizationBillingService
{
  public function setupOrganizationBilling($organization)
  {
    // 1. Create billing customer for organization
    $customer = $this->billingClient->customers->create([
      'email' => $organization->billing_email,
      'name' => $organization->name,
      'metadata' => [
        'organization_id' => $organization->id,
        'plan_type' => 'organization'
      ]
    ]);
    
    // 2. Link to organization
    $organization->update([
      'billing_customer_id' => $customer->id
    ]);
    
    // 3. Create subscription for organization
    $subscription = $this->billingClient->subscriptions->create([
      'customer_id' => $customer->id,
      'price_plan_id' => $organization->selected_plan,
      'quantity' => $organization->users->count() // Seat-based pricing
    ]);
    
    return $subscription;
  }
  
  // Update subscription when users are added/removed
  public function updateSeats($organization)
  {
    $userCount = $organization->users->count();
    
    $this->billingClient->subscriptions->update(
      $organization->subscription_id,
      ['quantity' => $userCount]
    );
  }
}`
}
</script>
