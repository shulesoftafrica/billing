<section class="api-section" id="webhooks-section">
    <h2>🔔 Webhooks</h2>
    <p>Configure webhook endpoints to receive <strong>real-time event notifications FROM this billing platform TO your application</strong> when billing events occur.</p>

    {{-- Overview Section --}}
    <div id="webhook-overview" style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-bottom: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">📋 How Webhooks Work</h3>
        
        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">🎯 What Are Webhooks?</h4>
            <p>Webhooks are HTTP callbacks that the billing platform sends <strong>TO your application</strong> when specific events occur. They allow you to receive real-time notifications about payments, invoices, and subscriptions.</p>
            <ul>
                <li><strong>Product-Level Isolation:</strong> Each webhook is configured per product (e.g., separate webhooks for Hospital Management vs Hotel Management)</li>
                <li><strong>Event-Driven:</strong> Webhooks fire immediately when events occur (no polling required)</li>
                <li><strong>Automatic Retry:</strong> Failed deliveries are automatically retried with exponential backoff</li>
                <li><strong>Secure:</strong> All webhook deliveries include HMAC SHA256 signatures for verification</li>
            </ul>
        </div>

        <div style="background: rgba(23, 162, 184, 0.1); border-left: 4px solid #17a2b8; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
            <h4 style="margin-top: 0;">💡 Understanding URL Parameters</h4>
            <p style="margin-bottom: 8px;"><strong>What does <code>{product}</code> mean in the URL?</strong></p>
            <p style="margin-bottom: 12px;">In all webhook endpoints like <code>/api/v1/products/{product}/webhooks</code>, the <code>{product}</code> placeholder refers to the <strong>Product ID</strong> (numeric value).</p>
            <p style="margin-bottom: 8px;"><strong>Example:</strong></p>
            <ul style="margin: 0; padding-left: 20px;">
                <li>If your product has ID = <code>1</code>, use: <code>/api/v1/products/1/webhooks</code></li>
                <li>If your product has ID = <code>42</code>, use: <code>/api/v1/products/42/webhooks</code></li>
            </ul>
            <p style="margin-top: 12px; margin-bottom: 0;"><strong>How to get your Product ID:</strong> Use the <a href="#list-all-products" style="text-decoration: underline;">List Products endpoint</a> to retrieve all your products with their IDs.</p>
        </div>

        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">📅 Available Event Types</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-primary);">
                        <th style="text-align: left; padding: 8px;">Event Type</th>
                        <th style="text-align: left; padding: 8px;">Description</th>
                        <th style="text-align: left; padding: 8px;">When It Fires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>payment.success</code></td>
                        <td style="padding: 8px;">Payment completed</td>
                        <td style="padding: 8px;">When payment is successfully processed via any gateway</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>payment.failed</code></td>
                        <td style="padding: 8px;">Payment failed</td>
                        <td style="padding: 8px;">When payment attempt fails</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>invoice.created</code></td>
                        <td style="padding: 8px;">New invoice</td>
                        <td style="padding: 8px;">When an invoice is generated</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>invoice.paid</code></td>
                        <td style="padding: 8px;">Invoice paid</td>
                        <td style="padding: 8px;">When invoice payment is completed</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.created</code></td>
                        <td style="padding: 8px;">New subscription</td>
                        <td style="padding: 8px;">When customer subscribes</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;"><code>subscription.cancelled</code></td>
                        <td style="padding: 8px;">Subscription cancelled</td>
                        <td style="padding: 8px;">When subscription is terminated</td>
                    </tr>
                </tbody>
            </table>
            <p style="margin-top: 12px;"><strong>💡 Wildcard Support:</strong> Use <code>payment.*</code> to subscribe to all payment events, <code>invoice.*</code> for all invoice events, etc.</p>
        </div>

        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">⏱️ Retry Logic</h4>
            <p>Failed webhook deliveries are automatically retried with exponential backoff:</p>
            <ul>
                <li><strong>Attempt 1:</strong> Immediate</li>
                <li><strong>Attempt 2:</strong> 5 minutes later</li>
                <li><strong>Attempt 3:</strong> 15 minutes later (20 minutes total)</li>
                <li><strong>Attempt 4:</strong> 45 minutes later (65 minutes total)</li>
            </ul>
            <p>A scheduled job runs every 5 minutes to retry failed webhooks automatically.</p>
        </div>

        <div>
            <h4 style="color: var(--text-primary);">✅ What Acknowledgement is Expected?</h4>
            <p>Your webhook endpoint must:</p>
            <ul>
                <li><strong>Return HTTP 2xx status code</strong> (200, 201, 202, 204) within 30 seconds to acknowledge receipt</li>
                <li><strong>Verify the HMAC signature</strong> in the <code>X-Webhook-Signature</code> header before processing</li>
                <li><strong>Process events idempotently</strong> using the delivery ID to prevent duplicate processing</li>
                <li><strong>Respond quickly</strong> - Process events asynchronously; don't keep the connection open</li>
            </ul>
            <p><strong>⚠️ Non-2xx responses</strong> (4xx, 5xx) or timeouts trigger automatic retry.</p>
        </div>
    </div>

    {{-- Security Example --}}
    <div style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107; padding: 16px; margin-bottom: 32px; border-radius: 4px;">
        <h4 style="margin-top: 0;">🔐 Security: Verify Webhook Signatures</h4>
        <p style="margin-bottom: 12px;">Always verify the HMAC SHA256 signature before processing webhooks:</p>
        <x-docs.code-block language="php">
// PHP Example
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$payload = file_get_contents('php://input');
$secret = 'whsec_...'; // Your webhook secret from creation

$expectedSignature = hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $expectedSignature)) {
    http_response_code(401);
    exit('Invalid signature');
}

// Process the webhook
$event = json_decode($payload, true);
// Return 200 OK
http_response_code(200);
        </x-docs.code-block>
    </div>

    {{-- List Webhooks --}}
    <x-docs.endpoint
        id="list-webhooks"
        method="GET"
        url="/api/v1/products/{product}/webhooks"
        title="List Webhooks"
        description="Get all webhook configurations for a specific product">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "name": "Production Payment Webhook",
      "url": "https://your-app.com/webhooks/billing",
      "status": "active",
      "events": ["payment.success", "invoice.paid"],
      "http_method": "POST",
      "timeout": 30,
      "retry_count": 3,
      "verify_ssl": true,
      "last_triggered_at": "2026-03-24T14:00:00+00:00",
      "delivery_stats": {
        "total": 150,
        "successful": 145,
        "failed": 5
      },
      "created_at": "2026-03-20T10:00:00+00:00",
      "updated_at": "2026-03-24T15:30:00+00:00"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Create Webhook --}}
    <x-docs.endpoint
        id="create-webhook"
        method="POST"
        url="/api/v1/products/{product}/webhooks"
        title="Create Webhook"
        description="Register a new webhook endpoint to receive event notifications for a product">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Production Payment Webhook",
  "url": "https://your-app.com/webhooks/billing",
  "events": ["payment.success", "invoice.paid"],
  "status": "active",
  "http_method": "POST",
  "headers": {
    "X-Custom-Header": "custom-value",
    "X-API-Key": "your-internal-api-key"
  },
  "timeout": 30,
  "retry_count": 3,
  "verify_ssl": true
}
            </x-docs.code-block>
            <h4>Request Parameters</h4>
            <table>
                <tr>
                    <td><code>name</code></td>
                    <td>string</td>
                    <td>required</td>
                    <td>Descriptive name for the webhook</td>
                </tr>
                <tr>
                    <td><code>url</code></td>
                    <td>string</td>
                    <td>required</td>
                    <td>Your endpoint URL (HTTPS recommended)</td>
                </tr>
                <tr>
                    <td><code>events</code></td>
                    <td>array</td>
                    <td>required</td>
                    <td>Event types to subscribe to</td>
                </tr>
                <tr>
                    <td><code>status</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>active or inactive (default: active)</td>
                </tr>
                <tr>
                    <td><code>http_method</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>POST or PUT (default: POST)</td>
                </tr>
                <tr>
                    <td><code>headers</code></td>
                    <td>object</td>
                    <td>optional</td>
                    <td>Custom headers to include</td>
                </tr>
                <tr>
                    <td><code>timeout</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Request timeout in seconds (default: 30)</td>
                </tr>
                <tr>
                    <td><code>retry_count</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Number of retry attempts (default: 3)</td>
                </tr>
                <tr>
                    <td><code>verify_ssl</code></td>
                    <td>boolean</td>
                    <td>optional</td>
                    <td>Verify SSL certificates (default: true)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook created successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "name": "Production Payment Webhook",
    "url": "https://your-app.com/webhooks/billing",
    "secret": "whsec_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "status": "active",
    "events": ["payment.success", "invoice.paid"],
    "http_method": "POST",
    "headers": {
      "X-Custom-Header": "custom-value"
    },
    "timeout": 30,
    "retry_count": 3,
    "verify_ssl": true,
    "created_at": "2026-03-24T15:30:00+00:00"
  }
}
            </x-docs.code-block>
            <div class="response-head">
                <span class="response-title">Error Response</span>
                <span class="status-badge status-4xx">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json">
{
  "errors": {
    "url": ["The url field must be a valid URL."],
    "events": ["The events field must contain valid event types."]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Get Webhook --}}
    <x-docs.endpoint
        id="get-webhook"
        method="GET"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Get Webhook Details"
        description="Get details of a specific webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "data": {
    "id": 1,
    "product_id": 1,
    "name": "Production Payment Webhook",
    "url": "https://your-app.com/webhooks/billing",
    "secret": "whsec_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "status": "active",
    "events": ["payment.success", "invoice.paid"],
    "http_method": "POST",
    "headers": {"X-Custom-Header": "custom-value"},
    "timeout": 30,
    "retry_count": 3,
    "verify_ssl": true,
    "last_triggered_at": "2026-03-24T14:00:00+00:00",
    "created_at": "2026-03-20T10:00:00+00:00",
    "updated_at": "2026-03-24T15:30:00+00:00"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Update Webhook --}}
    <x-docs.endpoint
        id="update-webhook"
        method="PUT"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Update Webhook"
        description="Update webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Updated Webhook Name",
  "url": "https://your-app.com/webhooks/billing-v2",
  "events": ["payment.*", "invoice.*"],
  "status": "active"
}
            </x-docs.code-block>
            <p style="margin-top: 8px; color: var(--text-secondary);">All fields are optional. Only send fields you want to update.</p>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Webhook Name",
    "url": "https://your-app.com/webhooks/billing-v2",
    "events": ["payment.*", "invoice.*"],
    "updated_at": "2026-03-24T16:00:00+00:00"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Delete Webhook --}}
    <x-docs.endpoint
        id="delete-webhook"
        method="DELETE"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Delete Webhook"
        description="Delete a webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook deleted successfully"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Test Webhook --}}
    <x-docs.endpoint
        id="test-webhook"
        method="POST"
        url="/api/v1/products/{product}/webhooks/{webhook}/test"
        title="Test Webhook"
        description="Send a test webhook delivery to verify your endpoint is working correctly">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Test webhook sent successfully",
  "delivery": {
    "id": 1234,
    "status": "sent",
    "http_status_code": 200,
    "response_body": "{\"received\": true}",
    "duration_ms": 145,
    "sent_at": "2026-03-24T16:30:00+00:00"
  }
}
            </x-docs.code-block>
            <h4 style="margin-top: 16px;">Test Payload Sent to Your Endpoint:</h4>
            <x-docs.code-block language="json">
{
  "event": "webhook.test",
  "timestamp": "2026-03-24T16:30:00+00:00",
  "webhook_id": 1,
  "message": "This is a test webhook from the billing platform"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Webhook Deliveries --}}
    <x-docs.endpoint
        id="webhook-deliveries"
        method="GET"
        url="/api/v1/products/{product}/webhooks/{webhook}/deliveries"
        title="Webhook Delivery History"
        description="View delivery history and retry status for a webhook">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
            <h4>Query Parameters</h4>
            <table>
                <tr>
                    <td><code>status</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>Filter by status: sent, failed, pending</td>
                </tr>
                <tr>
                    <td><code>per_page</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Results per page (default: 15)</td>
                </tr>
                <tr>
                    <td><code>page</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Page number (default: 1)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "data": [
    {
      "id": 5001,
      "webhook_id": 10,
      "event_type": "payment.success",
      "status": "sent",
      "attempt_count": 1,
      "http_status_code": 200,
      "response_body": "{\"received\": true}",
      "duration_ms": 234,
      "sent_at": "2026-03-24T15:00:00+00:00",
      "next_retry_at": null,
      "created_at": "2026-03-24T15:00:00+00:00"
    },
    {
      "id": 5002,
      "webhook_id": 10,
      "event_type": "invoice.paid",
      "status": "failed",
      "attempt_count": 3,
      "http_status_code": 500,
      "error_message": "Connection timeout",
      "duration_ms": 30000,
      "sent_at": "2026-03-24T14:00:00+00:00",
      "next_retry_at": "2026-03-24T14:45:00+00:00",
      "created_at": "2026-03-24T14:00:00+00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Regenerate Secret --}}
    <x-docs.endpoint
        id="regenerate-secret"
        method="POST"
        url="/api/v1/products/{product}/webhooks/{webhook}/regenerate-secret"
        title="Regenerate Webhook Secret"
        description="Generate a new secret for webhook signature verification. The old secret will be immediately invalidated.">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook secret regenerated successfully",
  "data": {
    "id": 1,
    "secret": "whsec_z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4"
  }
}
            </x-docs.code-block>
            <div style="background: var(--warning-bg, #fff3cd); border-left: 4px solid var(--warning, #ffc107); padding: 12px; margin-top: 16px; border-radius: 4px;">
                <p style="margin: 0; color: var(--warning-dark, #856404);">⚠️ <strong>Warning:</strong> Update your webhook verification code with the new secret before regenerating, otherwise webhook deliveries will fail verification.</p>
            </div>
        </x-slot>
    </x-docs.endpoint>

    {{-- Standardized Payload Section --}}
    <div style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-top: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">📦 Standardized Webhook Payload</h3>
        <p>All webhook events use the same payload structure regardless of the payment gateway (Stripe, Flutterwave, or UCN):</p>
        <x-docs.code-block language="json">
{
  "event": "payment.success",
  "timestamp": "2026-03-24T15:30:00+00:00",
  "product": {
    "id": 1,
    "name": "Hospital Management System",
    "type": "saas",
    "sku": "HMS-001"
  },
  "organization": {
    "id": 1,
    "name": "General Hospital",
    "email": "billing@hospital.com",
    "phone": "+255123456789"
  },
  "customer": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255987654321",
    "status": "active"
  },
  "payment": {
    "id": 456,
    "amount": "50000.00",
    "currency": "TZS",
    "status": "success",
    "payment_method": "card",
    "gateway_reference": "pi_1234567890",
    "paid_at": "2026-03-24T15:30:00+00:00"
  },
  "invoice": {
    "id": 789,
    "invoice_number": "INV-2026-001",
    "total_amount": "50000.00",
    "paid_amount": "50000.00",
    "status": "paid",
    "due_date": "2026-03-31"
  },
  "gateway_details": {
    "gateway": "stripe",
    "transaction_id": "ch_1234567890",
    "card_last4": "4242",
    "card_brand": "visa"
  },
  "metadata": {
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "processed_at": "2026-03-24T15:30:00+00:00"
  }
}
        </x-docs.code-block>
        <p style="margin-top: 12px;"><strong>Webhook Headers Sent:</strong></p>
        <ul>
            <li><code>X-Webhook-Signature</code> - HMAC SHA256 signature for verification</li>
            <li><code>X-Event-Type</code> - Event type (e.g., payment.success)</li>
            <li><code>X-Webhook-ID</code> - Webhook configuration ID</li>
            <li><code>X-Delivery-ID</code> - Unique delivery ID (use for idempotency)</li>
            <li><code>Content-Type</code> - application/json</li>
        </ul>
    </div>
</section>
