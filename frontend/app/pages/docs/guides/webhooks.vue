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
        <span>Webhooks</span>
      </div>
      
      <h1 class="text-4xl font-bold text-gray-900 mb-4" id="webhooks-guide">Webhooks Guide</h1>
      <p class="text-xl text-gray-600">
        Receive real-time notifications when events happen in your account. Learn how to set up and handle webhooks securely.
      </p>
    </div>
    
    <!-- Introduction -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="introduction">What are Webhooks?</h2>
      <p class="text-gray-700 mb-6">
        Webhooks are automated messages sent from our servers to your server when specific events occur. 
        Instead of polling our API for changes, webhooks push real-time notifications to your application, making your integration more efficient and responsive.
      </p>
      
      <div class="bg-gradient-to-r from-primary-50 to-success-50 rounded-lg p-8 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
          <div>
            <div class="text-4xl mb-2">⚡</div>
            <h3 class="font-semibold text-gray-900 mb-2">Real-Time</h3>
            <p class="text-sm text-gray-700">Get notified instantly when events occur</p>
          </div>
          <div>
            <div class="text-4xl mb-2">🔒</div>
            <h3 class="font-semibold text-gray-900 mb-2">Secure</h3>
            <p class="text-sm text-gray-700">Verify authenticity with signatures</p>
          </div>
          <div>
            <div class="text-4xl mb-2">🎯</div>
            <h3 class="font-semibold text-gray-900 mb-2">Reliable</h3>
            <p class="text-sm text-gray-700">Automatic retries for failed deliveries</p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Setting Up Webhooks -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="setup">Setting Up Webhooks</h2>
      
      <div class="space-y-6">
        <div class="flex items-start">
          <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-4">
            <span class="text-primary-700 font-bold">1</span>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Create an Endpoint</h3>
            <p class="text-gray-700 mb-4">
              Set up an HTTPS endpoint on your server that can receive POST requests. This endpoint will receive webhook events.
            </p>
            <CodeBlock :code="endpointExample" />
          </div>
        </div>
        
        <div class="flex items-start">
          <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-4">
            <span class="text-primary-700 font-bold">2</span>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Register Your Endpoint</h3>
            <p class="text-gray-700 mb-4">
              Add your endpoint URL in the dashboard under Settings → Webhooks. You can configure which events to receive.
            </p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
              <p class="text-sm font-mono text-gray-700">https://yourdomain.com/webhooks/billing</p>
            </div>
          </div>
        </div>
        
        <div class="flex items-start">
          <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-4">
            <span class="text-primary-700 font-bold">3</span>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Test Your Integration</h3>
            <p class="text-gray-700 mb-4">
              Use the dashboard to send test events and verify your endpoint is working correctly.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Event Types -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="event-types">Event Types</h2>
      <p class="text-gray-700 mb-6">
        Subscribe to the events that matter to your application:
      </p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="category in eventCategories" :key="category.name" class="border border-gray-200 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ category.name }}</h3>
          <ul class="space-y-2">
            <li v-for="event in category.events" :key="event" class="text-sm">
              <code class="bg-gray-100 px-2 py-1 rounded text-primary-600 text-xs">{{ event }}</code>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Webhook Payload -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="webhook-payload">Webhook Payload Structure</h2>
      <p class="text-gray-700 mb-6">
        All webhook events follow a consistent JSON structure:
      </p>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Payload Fields</h3>
          <div class="space-y-4">
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">id</code>
              <p class="text-sm text-gray-600 mt-1">Unique identifier for the event</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">type</code>
              <p class="text-sm text-gray-600 mt-1">Event type (e.g., customer.created)</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">created_at</code>
              <p class="text-sm text-gray-600 mt-1">Timestamp when the event occurred</p>
            </div>
            <div class="border-l-4 border-primary-200 pl-4">
              <code class="text-sm font-mono text-primary-600">data</code>
              <p class="text-sm text-gray-600 mt-1">The resource object related to the event</p>
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">Example Payload</h3>
          <CodeBlock :code="webhookPayloadExample" language="json" />
        </div>
      </div>
    </div>
    
    <!-- Verifying Signatures -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="verify-signatures">Verifying Webhook Signatures</h2>
      <p class="text-gray-700 mb-6">
        Always verify webhook signatures to ensure requests are from our servers and haven't been tampered with.
      </p>
      
      <div class="bg-error-50 border border-error-200 rounded-lg p-6 mb-6">
        <div class="flex">
          <svg class="w-5 h-5 text-error-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-error-900">Security Warning</p>
            <p class="text-sm text-error-800 mt-1">
              Never process webhooks without verifying signatures. This protects against replay attacks and forgery.
            </p>
          </div>
        </div>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Signature Verification</h3>
      <p class="text-gray-700 mb-4">
        Each webhook request includes a <code class="text-sm bg-gray-100 px-2 py-1 rounded">X-Webhook-Signature</code> header. 
        Verify it using your webhook signing secret from the dashboard.
      </p>
      
      <CodeBlock :code="signatureVerificationExample" />
    </div>
    
    <!-- Responding to Webhooks -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="responding">Responding to Webhooks</h2>
      
      <div class="space-y-6">
        <div class="bg-success-50 border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Return 200 Quickly</h3>
          <p class="text-gray-700">
            Acknowledge receipt by returning a 200 status code within 5 seconds. Process the event asynchronously to avoid timeouts.
          </p>
        </div>
        
        <div class="bg-success-50 border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Handle Duplicate Events</h3>
          <p class="text-gray-700">
            Make your webhook handler idempotent. The same event may be sent multiple times due to network issues or retries.
          </p>
        </div>
        
        <div class="bg-success-50 border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Use Event IDs</h3>
          <p class="text-gray-700">
            Store processed event IDs to detect and skip duplicates. Check if you've already processed an event before taking action.
          </p>
        </div>
      </div>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4 mt-8">Example Handler</h3>
      <CodeBlock :code="webhookHandlerExample" />
    </div>
    
    <!-- Retry Logic -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="retries">Retry Logic</h2>
      <p class="text-gray-700 mb-6">
        If your endpoint doesn't respond with a 2xx status code, we'll retry the webhook with exponential backoff:
      </p>
      
      <div class="bg-gray-50 rounded-lg p-6">
        <div class="space-y-3">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-primary-700 font-semibold text-sm">1</span>
            </div>
            <div class="flex-1">
              <span class="text-gray-700">Immediately after failure</span>
            </div>
          </div>
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-primary-700 font-semibold text-sm">2</span>
            </div>
            <div class="flex-1">
              <span class="text-gray-700">5 minutes later</span>
            </div>
          </div>
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-primary-700 font-semibold text-sm">3</span>
            </div>
            <div class="flex-1">
              <span class="text-gray-700">30 minutes later</span>
            </div>
          </div>
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-primary-700 font-semibold text-sm">4</span>
            </div>
            <div class="flex-1">
              <span class="text-gray-700">2 hours later</span>
            </div>
          </div>
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center mr-4">
              <span class="text-primary-700 font-semibold text-sm">5</span>
            </div>
            <div class="flex-1">
              <span class="text-gray-700">12 hours later (final attempt)</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mt-6">
        <div class="flex">
          <svg class="w-5 h-5 text-warning-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <div>
            <p class="text-sm font-medium text-warning-900">Monitor Failed Webhooks</p>
            <p class="text-sm text-warning-800 mt-1">
              You can view and manually retry failed webhooks in the dashboard under Developers → Webhooks → Logs.
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Testing Webhooks -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="testing">Testing Webhooks</h2>
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4">Local Testing with ngrok</h3>
      <p class="text-gray-700 mb-4">
        Use ngrok to expose your local server and test webhooks during development:
      </p>
      
      <CodeBlock :code="ngrokExample" />
      
      <h3 class="text-xl font-semibold text-gray-900 mb-4 mt-8">Send Test Events</h3>
      <p class="text-gray-700 mb-4">
        Use the dashboard or CLI to send test webhook events:
      </p>
      
      <CodeBlock :code="testWebhookExample" />
    </div>
    
    <!-- Best Practices -->
    <div class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4" id="best-practices">Best Practices</h2>
      
      <div class="space-y-4">
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Use HTTPS Only</h3>
          <p class="text-gray-700">
            Webhook endpoints must use HTTPS. We don't send webhooks to unsecured HTTP endpoints.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Queue Processing</h3>
          <p class="text-gray-700">
            Add webhook events to a queue and process them asynchronously. This ensures fast response times and better reliability.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Log All Webhooks</h3>
          <p class="text-gray-700">
            Keep detailed logs of received webhooks for debugging and auditing purposes.
          </p>
        </div>
        
        <div class="bg-white border-l-4 border-success-500 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">✅ Monitor Webhook Health</h3>
          <p class="text-gray-700">
            Set up alerts for webhook failures. Regular failures may indicate issues with your endpoint.
          </p>
        </div>
      </div>
    </div>
    
    <!-- Next Steps -->
    <div class="not-prose bg-gradient-to-br from-primary-50 to-success-50 rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Next Steps</h2>
      <p class="text-gray-600 mb-6">Continue learning about integrating the API</p>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <NuxtLink to="/docs/error-handling" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Error Handling</h3>
          <p class="text-sm text-gray-600">Learn how to handle API errors gracefully</p>
        </NuxtLink>
        
        <NuxtLink to="/dashboard/webhooks" class="block bg-white rounded-lg p-6 hover:shadow-lg transition-shadow">
          <h3 class="font-semibold text-gray-900 mb-2">Configure Webhooks</h3>
          <p class="text-sm text-gray-600">Set up webhooks in your dashboard</p>
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
  title: 'Webhooks Guide'
})

// Register headings for TOC
onMounted(() => {
  const registerHeadings = inject('registerHeadings')
  if (registerHeadings) {
    registerHeadings([
      { id: 'webhooks-guide', text: 'Webhooks Guide', level: 1 },
      { id: 'introduction', text: 'What are Webhooks?', level: 2 },
      { id: 'setup', text: 'Setting Up Webhooks', level: 2 },
      { id: 'event-types', text: 'Event Types', level: 2 },
      { id: 'webhook-payload', text: 'Webhook Payload Structure', level: 2 },
      { id: 'verify-signatures', text: 'Verifying Webhook Signatures', level: 2 },
      { id: 'responding', text: 'Responding to Webhooks', level: 2 },
      { id: 'retries', text: 'Retry Logic', level: 2 },
      { id: 'testing', text: 'Testing Webhooks', level: 2 },
      { id: 'best-practices', text: 'Best Practices', level: 2 },
    ])
  }
})

const eventCategories = [
  {
    name: 'Customer Events',
    events: ['customer.created', 'customer.updated', 'customer.deleted', 'customer.kyc_verified']
  },
  {
    name: 'Subscription Events',
    events: ['subscription.created', 'subscription.updated', 'subscription.canceled', 'subscription.trial_ending']
  },
  {
    name: 'Invoice Events',
    events: ['invoice.created', 'invoice.paid', 'invoice.payment_failed', 'invoice.finalized']
  },
  {
    name: 'Payment Events',
    events: ['payment.succeeded', 'payment.failed', 'payment.refunded', 'payment.disputed']
  }
]

const endpointExample = {
  php: `// webhook-handler.php
<?php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

// Verify signature (see verification section)
if (!verifySignature($payload, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}

$event = json_decode($payload, true);

// Return 200 immediately
http_response_code(200);

// Process event asynchronously
// Queue::push(new ProcessWebhook($event));`,
  javascript: `// webhook-handler.js
const express = require('express');
const app = express();

app.post('/webhooks/billing', express.raw({type: 'application/json'}), (req, res) => {
  const signature = req.headers['x-webhook-signature'];
  const payload = req.body.toString();
  
  // Verify signature
  if (!verifySignature(payload, signature)) {
    return res.status(401).send('Invalid signature');
  }
  
  const event = JSON.parse(payload);
  
  // Return 200 immediately
  res.status(200).send('OK');
  
  // Process event asynchronously
  processWebhook(event);
});`,
  python: `# webhook_handler.py
from flask import Flask, request
import hmac
import hashlib

app = Flask(__name__)

@app.route('/webhooks/billing', methods=['POST'])
def webhook_handler():
    signature = request.headers.get('X-Webhook-Signature')
    payload = request.data
    
    # Verify signature
    if not verify_signature(payload, signature):
        return 'Invalid signature', 401
    
    event = request.json
    
    # Return 200 immediately
    response = make_response('OK', 200)
    
    # Process event asynchronously
    # queue.enqueue(process_webhook, event)
    
    return response`
}

const webhookPayloadExample = `{
  "id": "evt_1a2b3c4d5e",
  "type": "customer.created",
  "created_at": "2024-01-15T10:30:00Z",
  "data": {
    "id": "cus_9h3k2j1l0m",
    "email": "john@example.com",
    "name": "John Doe",
    "kyc_status": "pending",
    "created_at": "2024-01-15T10:30:00Z"
  }
}`

const signatureVerificationExample = {
  php: `function verifySignature($payload, $signature) {
    $secret = getenv('WEBHOOK_SECRET');
    $computed = hash_hmac('sha256', $payload, $secret);
    
    return hash_equals($computed, $signature);
}

// Usage in your webhook handler
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if (!verifySignature($payload, $signature)) {
    http_response_code(401);
    exit('Invalid signature');
}`,
  javascript: `const crypto = require('crypto');

function verifySignature(payload, signature) {
  const secret = process.env.WEBHOOK_SECRET;
  const computed = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
  
  return crypto.timingSafeEqual(
    Buffer.from(computed),
    Buffer.from(signature)
  );
}

// Usage in your webhook handler
const signature = req.headers['x-webhook-signature'];
const payload = req.body.toString();

if (!verifySignature(payload, signature)) {
  return res.status(401).send('Invalid signature');
}`,
  python: `import hmac
import hashlib
import os

def verify_signature(payload, signature):
    secret = os.getenv('WEBHOOK_SECRET').encode()
    computed = hmac.new(secret, payload, hashlib.sha256).hexdigest()
    
    return hmac.compare_digest(computed, signature)

# Usage in your webhook handler
signature = request.headers.get('X-Webhook-Signature')
payload = request.data

if not verify_signature(payload, signature):
    return 'Invalid signature', 401`
}

const webhookHandlerExample = {
  php: `<?php
// Store processed event IDs to handle duplicates
function hasProcessedEvent($eventId) {
    return Redis::exists("webhook:processed:{$eventId}");
}

function markEventProcessed($eventId) {
    Redis::setex("webhook:processed:{$eventId}", 86400, 1); // 24 hours
}

$event = json_decode($payload, true);

// Check for duplicates
if (hasProcessedEvent($event['id'])) {
    http_response_code(200);
    exit('Already processed');
}

// Handle different event types
switch ($event['type']) {
    case 'customer.created':
        handleCustomerCreated($event['data']);
        break;
    case 'subscription.canceled':
        handleSubscriptionCanceled($event['data']);
        break;
    // ... other event types
}

// Mark as processed
markEventProcessed($event['id']);
http_response_code(200);`,
  javascript: `const processedEvents = new Set();

async function handleWebhook(event) {
  // Check for duplicates
  if (processedEvents.has(event.id)) {
    console.log('Event already processed:', event.id);
    return;
  }
  
  // Handle different event types
  switch (event.type) {
    case 'customer.created':
      await handleCustomerCreated(event.data);
      break;
    case 'subscription.canceled':
      await handleSubscriptionCanceled(event.data);
      break;
    // ... other event types
  }
  
  // Mark as processed
  processedEvents.add(event.id);
}`,
  python: `processed_events = set()

def handle_webhook(event):
    # Check for duplicates
    if event['id'] in processed_events:
        print(f"Event already processed: {event['id']}")
        return
    
    # Handle different event types
    event_type = event['type']
    
    if event_type == 'customer.created':
        handle_customer_created(event['data'])
    elif event_type == 'subscription.canceled':
        handle_subscription_canceled(event['data'])
    # ... other event types
    
    # Mark as processed
    processed_events.add(event['id'])`
}

const ngrokExample = {
  bash: `# Install ngrok
# Download from https://ngrok.com/download

# Start your local server
php -S localhost:8000

# In another terminal, start ngrok
ngrok http 8000

# Use the HTTPS URL provided by ngrok as your webhook endpoint
# Example: https://abc123.ngrok.io/webhooks/billing`
}

const testWebhookExample = {
  bash: `# Using CLI
billing webhooks test \\
  --endpoint https://yourdomain.com/webhooks/billing \\
  --event customer.created

# Or trigger from your code
curl -X POST https://api.billing.com/v1/webhooks/test \\
  -H "Authorization: Bearer sk_test_abc123" \\
  -d "event_type=customer.created" \\
  -d "endpoint=https://yourdomain.com/webhooks/billing"`
}
</script>
