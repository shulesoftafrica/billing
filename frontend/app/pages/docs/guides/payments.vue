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
        <span>Payments</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="payments-guide">Payment Processing Guide</h1>
      <p class="text-xl text-gray-600">
        Best practices for accepting payments, handling refunds, and managing payment methods securely.
      </p>
    </div>
    
    <!-- Introduction -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="introduction">Payment Methods</h2>
      <p class="text-gray-700 mb-6">
        Our platform supports multiple payment methods to accommodate your customers worldwide:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div class="text-3xl mb-3">💳</div>
          <h3 class="font-semibold text-blue-900 mb-2">Credit & Debit Cards</h3>
          <p class="text-sm text-blue-800">Visa, Mastercard, American Express, and more</p>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
          <div class="text-3xl mb-3">🏦</div>
          <h3 class="font-semibold text-green-900 mb-2">Bank Transfers</h3>
          <p class="text-sm text-green-800">ACH, SEPA, and wire transfers</p>
        </div>
        
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
          <div class="text-3xl mb-3">📱</div>
          <h3 class="font-semibold text-purple-900 mb-2">Mobile Money</h3>
          <p class="text-sm text-purple-800">M-Pesa, Mobile Money, and digital wallets</p>
        </div>
      </div>
    </div>
    
    <!-- Processing Payments -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="processing">Processing Payments</h2>
      
      <div class="space-y-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">One-Time Payment</h3>
          <p class="text-gray-700 mb-4">Process a single payment from a customer:</p>
          <CodeBlock :code="oneTimePaymentExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Saving Payment Methods</h3>
          <p class="text-gray-700 mb-4">Save a payment method for future use (subscriptions):</p>
          <CodeBlock :code="savePaymentMethodExample" />
        </div>
      </div>
    </div>
    
    <!-- Payment Confirmation -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="confirmation">Payment Confirmation</h2>
      <p class="text-gray-700 mb-6">
        Always verify payment status before providing services or products:
      </p>
      
      <CodeBlock :code="paymentConfirmationExample" />
      
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-blue-900">Webhook Verification</p>
            <p class="text-sm text-blue-800 mt-1">
              Never rely solely on client-side payment status. Always verify using webhooks or server-side API calls.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Refunds -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="refunds">Handling Refunds</h2>
      <p class="text-gray-700 mb-6">
        Refund payments fully or partially when needed:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Full Refund</h3>
          <CodeBlock :code="fullRefundExample" />
        </div>
        
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Partial Refund</h3>
          <CodeBlock :code="partialRefundExample" />
        </div>
      </div>
      
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-amber-900">Refund Timing</p>
            <p class="text-sm text-amber-800 mt-1">
              Refunds typically take 5-10 business days to appear in the customer's account.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Disputes -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="disputes">Managing Disputes</h2>
      <p class="text-gray-700 mb-6">
        When a customer disputes a charge with their bank, you'll receive a webhook notification:
      </p>
      
      <CodeBlock :code="disputeWebhookExample" />
      
      <div class="bg-red-50 border border-red-200 rounded-lg p-6 mt-6">
        <h3 class="font-semibold text-red-900 mb-3">Responding to Disputes</h3>
        <ol class="space-y-2 text-sm text-red-800">
          <li class="flex items-start">
            <span class="font-semibold mr-2">1.</span>
            <span>Review the dispute reason and gather evidence (receipts, communication, delivery proof)</span>
          </li>
          <li class="flex items-start">
            <span class="font-semibold mr-2">2.</span>
            <span>Respond within 7 days through the dashboard</span>
          </li>
          <li class="flex items-start">
            <span class="font-semibold mr-2">3.</span>
            <span>The bank reviews evidence and makes a final decision</span>
          </li>
        </ol>
      </div>
    </div>
    
    <!-- Security -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="security">Security Best Practices</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border-2 border-primary-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">🔒 PCI Compliance</h3>
          <p class="text-sm text-gray-700 mb-2">Never store raw card data</p>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>• Use our hosted payment page</li>
            <li>• Or tokenize cards via API</li>
            <li>• Store only tokens, never card numbers</li>
          </ul>
        </div>
        
        <div class="bg-white border-2 border-success-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">✅ Verify Webhooks</h3>
          <p class="text-sm text-gray-700 mb-2">Always validate webhook signatures</p>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>• Check HMAC signature</li>
            <li>• Use timing-safe comparison</li>
            <li>• Reject invalid signatures</li>
          </ul>
        </div>
        
        <div class="bg-white border-2 border-warning-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">🔐 Secure Keys</h3>
          <p class="text-sm text-gray-700 mb-2">Protect your API keys</p>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>• Never commit to version control</li>
            <li>• Use environment variables</li>
            <li>• Rotate keys regularly</li>
          </ul>
        </div>
        
        <div class="bg-white border-2 border-error-200 rounded-lg p-6">
          <h3 class="font-semibold text-gray-900 mb-3">🛡️ Fraud Prevention</h3>
          <p class="text-sm text-gray-700 mb-2">Monitor for suspicious activity</p>
          <ul class="text-sm text-gray-600 space-y-1">
            <li>• Check billing/shipping mismatch</li>
            <li>• Monitor velocity (multiple attempts)</li>
            <li>• Use 3D Secure for high-risk transactions</li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Reconciliation -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="reconciliation">Payment Reconciliation</h2>
      <p class="text-gray-700 mb-6">
        Regularly reconcile payments with your accounting system:
      </p>
      
      <CodeBlock :code="reconciliationExample" />
      
      <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-6">
        <h3 class="font-semibold text-gray-900 mb-3">Reconciliation Checklist</h3>
        <ul class="space-y-2 text-sm text-gray-700">
          <li class="flex items-start">
            <svg class="w-5 h-5 text-success-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Export payment data daily or weekly</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-success-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Match against bank deposits</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-success-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Account for refunds and chargebacks</span>
          </li>
          <li class="flex items-start">
            <svg class="w-5 h-5 text-success-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Investigate discrepancies immediately</span>
          </li>
        </ul>
      </div>
    </div>
    
    <!-- Related Resources -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Related Resources</h2>
      <p class="text-gray-600 mb-6">Learn more about payments</p>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <NuxtLink to="/docs/api/payments" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Payments API</h3>
          <p class="text-sm text-gray-600">Complete API reference</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/invoices" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Invoices Guide</h3>
          <p class="text-sm text-gray-600">Invoice payment collection</p>
        </NuxtLink>
        
        <NuxtLink to="/docs/guides/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Webhooks</h3>
          <p class="text-sm text-gray-600">Listen to payment events</p>
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
  title: 'Payment Processing Guide'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'payments-guide', text: 'Payment Processing', level: 1 },
      { id: 'introduction', text: 'Payment Methods', level: 2 },
      { id: 'processing', text: 'Processing Payments', level: 2 },
      { id: 'confirmation', text: 'Payment Confirmation', level: 2 },
      { id: 'refunds', text: 'Handling Refunds', level: 2 },
      { id: 'disputes', text: 'Managing Disputes', level: 2 },
      { id: 'security', text: 'Security Best Practices', level: 2 },
      { id: 'reconciliation', text: 'Payment Reconciliation', level: 2 },
    ])
  }
})

const oneTimePaymentExample = {
  bash: `curl -X POST https://api.billing.com/v1/payments \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -H "Content-Type: application/json" \\
  -d '{
    "amount": 5000,
    "currency": "usd",
    "customer_id": "cus_abc123",
    "payment_method": "card",
    "description": "Website hosting - Annual"
  }'`,
  php: `$payment = $client->payments->create([
  'amount' => 5000,
  'currency' => 'usd',
  'customer_id' => 'cus_abc123',
  'payment_method' => 'card',
  'description' => 'Website hosting - Annual'
]);`
}

const savePaymentMethodExample = {
  php: `// Save payment method for future use
$paymentMethod = $client->paymentMethods->create([
  'customer_id' => 'cus_abc123',
  'type' => 'card',
  'card' => [
    'number' => '4242424242424242',
    'exp_month' => 12,
    'exp_year' => 2025,
    'cvc' => '123'
  ]
]);

// Use saved method
$payment = $client->payments->create([
  'amount' => 5000,
  'customer_id' => 'cus_abc123',
  'payment_method' => $paymentMethod->id
]);`
}

const paymentConfirmationExample = {
  php: `// After creating payment, check status
$payment = $client->payments->retrieve('pay_1a2b3c4d5e');

if ($payment->status === 'succeeded') {
  // Fulfill order, grant access, etc.
  $order->markAsPaid();
  $user->grantAccess();
} elseif ($payment->status === 'pending') {
  // Payment is processing
  $order->markAsPending();
} else {
  // Payment failed
  $order->markAsFailed();
}`
}

const fullRefundExample = {
  php: `// Refund entire payment
$refund = $client->payments->refund(
  'pay_1a2b3c4d5e'
);`
}

const partialRefundExample = {
  php: `// Refund $25 of a $50 payment
$refund = $client->payments->refund(
  'pay_1a2b3c4d5e',
  [
    'amount' => 2500,
    'reason' => 'Customer returned one item'
  ]
);`
}

const disputeWebhookExample = {
  php: `public function handleWebhook(Request $request)
{
  $event = $request->all();
  
  if ($event['type'] === 'payment.disputed') {
    $payment = $event['data'];
    
    // Notify admin team
    Mail::to('disputes@company.com')->send(
      new DisputeNotification($payment)
    );
    
    // Flag order for review
    Order::where('payment_id', $payment['id'])
      ->update(['status' => 'disputed']);
  }
  
  return response()->json(['status' => 'success']);
}`
}

const reconciliationExample = {
  php: `// Get all payments for a date range
$payments = $client->payments->list([
  'created_after' => '2024-01-01',
  'created_before' => '2024-01-31',
  'status' => 'succeeded',
  'limit' => 100
]);

$totalRevenue = 0;
$totalRefunds = 0;

foreach ($payments->data as $payment) {
  $totalRevenue += $payment->amount;
  
  // Check for refunds
  if ($payment->refunded) {
    $totalRefunds += $payment->amount_refunded;
  }
}

$netRevenue = $totalRevenue - $totalRefunds;`
}
</script>
