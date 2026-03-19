<section class="api-section" id="subscriptions-section">
    <h2>🔄 Subscriptions</h2>
    <p>Manage recurring subscriptions and billing cycles for your customers.</p>

    <x-docs.endpoint
        id="list-subscriptions"
        method="GET"
        url="/api/v1/subscriptions"
        title="List Subscriptions"
        description="Get all active subscriptions">
        
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
      "customer_id": 5,
      "product_id": 2,
      "status": "active",
      "start_date": "2024-01-01",
      "next_billing_date": "2024-04-01"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="create-subscription"
        method="POST"
        url="/api/v1/subscriptions"
        title="Create Subscription"
        description="Create new subscriptions for a customer with one or more price plans. Generates an invoice with control numbers and payment links.">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "customer_id": 5,
  "plan_ids": [8, 12],
  "success_url": "https://yourapp.com/payment/success"
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Required Parameters:</strong></p>
                <ul>
                    <li><code>customer_id</code> (integer) - ID of the customer</li>
                    <li><code>plan_ids</code> (array) - Array of price plan IDs to subscribe to</li>
                </ul>
                <p><strong>Optional Parameters:</strong></p>
                <ul>
                    <li><code>success_url</code> (string) - URL to redirect after successful payment</li>
                </ul>
            </div>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "status": "issued",
      "currency": "TZS",
      "subtotal": 150000,
      "tax_total": 0,
      "total": 150000,
      "due_date": "2026-04-18",
      "issued_at": "2026-03-19T10:30:00.000000Z"
    },
    "customer": {
      "id": 5,
      "name": "Jane Smith",
      "email": "jane@company.com",
      "phone": "+255723456789"
    },
    "subscriptions": [
      {
        "id": 89,
        "price_plan_id": 8,
        "plan_name": "Premium Plan",
        "product_name": "SafariChat Platform",
        "status": "pending",
        "start_date": null,
        "end_date": null,
        "next_billing_date": null,
        "amount": 75000
      },
      {
        "id": 90,
        "price_plan_id": 12,
        "plan_name": "Pro Plan",
        "product_name": "Email Marketing Suite",
        "status": "pending",
        "start_date": null,
        "end_date": null,
        "next_billing_date": null,
        "amount": 75000
      }
    ],
    "control_numbers": [
      {
        "reference": "991234567890",
        "payment_link": "https://payment.gateway.com/pay/xyz123",
        "gateway": "Universal Control Number",
        "expires_at": "2026-03-26T10:30:00.000000Z"
      }
    ],
    "payment_message": "Control numbers and payment links are being generated. You will receive them shortly."
  }
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <ul>
                    <li>Subscriptions start with <code>pending</code> status until the invoice is paid</li>
                    <li>Once paid, subscriptions become <code>active</code> with calculated start/end dates</li>
                    <li>Control numbers and payment links are generated asynchronously</li>
                    <li>Customer will receive payment instructions via SMS/email if configured</li>
                </ul>
            </div>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="get-subscription"
        method="GET"
        url="/api/v1/subscriptions/{id}"
        title="Get Subscription"
        description="Retrieve details of a specific subscription">
        
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
    "customer_id": 5,
    "product": {
      "name": "Premium Plan",
      "price": 29900
    },
    "status": "active"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="cancel-subscription"
        method="POST"
        url="/api/v1/subscriptions/{id}/cancel"
        title="Cancel Subscription"
        description="Cancel an active subscription">
        
        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Subscription cancelled successfully"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
