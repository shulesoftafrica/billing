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
        id="create-subscription-invoice"
        method="POST"
        url="/api/v1/invoices"
        title="Create Subscription Invoice"
        description="Create subscription invoices with automatic subscription creation. Supports single or multiple subscription products, automatic customer creation, payment gateway integration, and control number generation.">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    },
    {
      "price_plan_id": 12,
      "amount": 75000
    }
  ],
  "description": "Monthly subscription - SafariChat Platform",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Required Parameters:</strong></p>
                <ul>
                    <li><code>organization_id</code> (integer) - Your organization ID</li>
                    <li><code>customer.name</code> (string) - Customer's full name</li>
                    <li><code>customer.email</code> (string) - Customer's email address</li>
                    <li><code>customer.phone</code> (string) - Customer's phone number</li>
                    <li><code>products</code> (array) - Array of products with subscription price plans</li>
                    <li><code>products.*.price_plan_id</code> (integer) - Price plan ID for subscription product</li>
                    <li><code>products.*.amount</code> (number) - Invoice amount for this product</li>
                    <li><code>currency</code> (string) - 3-letter currency code (e.g., "TZS", "USD")</li>
                </ul>
                <p><strong>Optional Parameters:</strong></p>
                <ul>
                    <li><code>description</code> (string) - Invoice description</li>
                    <li><code>status</code> (string) - Invoice status (default: "issued")</li>
                    <li><code>tax_rate_ids</code> (array) - Array of tax rate IDs to apply</li>
                    <li><code>payment_gateway</code> (string) - "flutterwave", "control_number", or "both"</li>
                    <li><code>success_url</code> (string) - URL to redirect after successful payment</li>
                    <li><code>cancel_url</code> (string) - URL to redirect after cancelled payment</li>
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
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Monthly subscription - SafariChat Platform",
      "subtotal": 150000,
      "tax_total": 0,
      "total": 150000,
      "due_date": null,
      "issued_at": "2026-03-19T11:15:00.000000Z",
      "items": [
        {
          "id": 457,
          "price_plan_id": 8,
          "subscription_id": 89,
          "product_name": "SafariChat Platform",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        },
        {
          "id": 458,
          "price_plan_id": 12,
          "subscription_id": 90,
          "product_name": "Email Marketing Suite",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        }
      ],
      "subscriptions": [
        {
          "id": 89,
          "status": "pending",
          "price_plan_id": 8,
          "start_date": null,
          "next_billing_date": null,
          "note": "Subscription will activate upon payment"
        },
        {
          "id": 90,
          "status": "pending",
          "price_plan_id": 12,
          "start_date": null,
          "next_billing_date": null,
          "note": "Subscription will activate upon payment"
        }
      ],
      "payment_details": {
        "control_number": {
          "reference": "9912345678",
          "amount": 150000,
          "currency": "TZS",
          "expires_at": "2026-03-26T11:15:00.000000Z"
        },
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1710844500"
        }
      }
    },
    "customer": {
      "id": 46,
      "name": "Jane Smith",
      "email": "jane@company.com",
      "phone": "+255723456789",
      "status": "active"
    }
  }
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <ul>
                    <li>System automatically detects subscription products and creates subscription records</li>
                    <li>If customer doesn't exist, a new customer is created automatically</li>
                    <li>Subscriptions start with <code>pending</code> status until the invoice is paid</li>
                    <li>Once paid, subscriptions become <code>active</code> with calculated start/end/billing dates</li>
                    <li>Control numbers and payment links are generated based on <code>payment_gateway</code> parameter</li>
                    <li><strong>Idempotent:</strong> If a pending subscription already exists for the same customer and price plan, existing invoice is returned</li>
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
