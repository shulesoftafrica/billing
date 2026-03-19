<section class="api-section" id="webhooks-section">
    <h2>🔔 Webhooks</h2>
    <p>Configure webhooks to receive real-time notifications about events in your billing system.</p>

    <x-docs.endpoint
        id="list-webhooks"
        method="GET"
        url="/api/v1/webhooks"
        title="List Webhooks"
        description="Get all configured webhooks">
        
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
      "url": "https://example.com/webhook",
      "events": ["invoice.paid", "subscription.created"],
      "is_active": true
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="create-webhook"
        method="POST"
        url="/api/v1/webhooks"
        title="Create Webhook"
        description="Register a new webhook endpoint">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "url": "https://example.com/webhook",
  "events": [
    "invoice.paid",
    "invoice.created",
    "subscription.created",
    "payment.completed"
  ],
  "secret": "your_webhook_secret"
}
            </x-docs.code-block>
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
    "url": "https://example.com/webhook"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="webhook-events"
        method="GET"
        url="/api/v1/webhooks/events"
        title="Webhook Events"
        description="Available webhook event types">
        
        <x-slot name="responses">
            <div style="background: var(--surface-soft); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                <h4 style="margin-top: 0;">Available Events:</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><code>invoice.created</code> - Invoice created</li>
                    <li><code>invoice.paid</code> - Invoice payment received</li>
                    <li><code>subscription.created</code> - New subscription</li>
                    <li><code>subscription.cancelled</code> - Subscription cancelled</li>
                    <li><code>payment.completed</code> - Payment successful</li>
                    <li><code>payment.failed</code> - Payment failed</li>
                </ul>
            </div>
        </x-slot>
    </x-docs.endpoint>
</section>
