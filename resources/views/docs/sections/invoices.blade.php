<section class="api-section" id="invoices-section">
    <h2>📄 Invoices</h2>
    <p>Create, manage, and track invoices for your customers.</p>

    {{-- List All Invoices --}}
    <x-docs.endpoint
        id="list-invoices"
        method="GET"
        url="/api/v1/invoices"
        title="List All Invoices"
        description="Retrieve all invoices with pagination and filtering options">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
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
      "invoice_number": "INV-2024-001",
      "customer_id": 5,
      "status": "paid",
      "total_amount": 15000,
      "due_date": "2024-03-30"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Create Invoice --}}
    <x-docs.endpoint
        id="create-invoice"
        method="POST"
        url="/api/v1/invoices"
        title="Create Invoice"
        description="Create a new invoice for a customer. Pass customer details directly without requiring pre-existing customer_id.">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Content-Type', 'value' => 'application/json', 'description' => 'Request format', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="requestBody">
            <div style="background: var(--surface-soft); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                <h4 style="margin-top: 0;">📋 Required Parameters:</h4>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                  
                    <li><strong>customer</strong> (object) - Customer details:
                        <ul style="padding-left: 20px;">
                            <li><code>name</code> (string) - Customer's full name</li>
                            <li><code>email</code> (string) - Customer's email address</li>
                            <li><code>phone</code> (string) - Customer's phone number</li>
                        </ul>
                    </li>
                    <li><strong>products</strong> (array) - Array of products (minimum 1):
                        <ul style="padding-left: 20px;">
                            <li><code>price_plan_id</code> (integer) - Price plan ID</li>
                            <li><code>amount</code> (number) - Invoice amount</li>
                        </ul>
                    </li>
                    <li><strong>currency</strong> (string) - 3-letter currency code (e.g., "TZS", "USD")</li>
                </ul>
                <br/>
                <h4>💡 Optional Parameters:</h4>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                      <li><strong>organization_id</strong> (integer) - Your organization ID</li>
                    <li><strong>description</strong> (string) - Invoice description</li>
                    <li><strong>status</strong> (string) - draft, issued, paid, cancelled (default: "issued")</li>
                    <li><strong>date</strong> (string) - Invoice date (Y-m-d format)</li>
                    <li><strong>due_date</strong> (string) - Payment due date (Y-m-d format)</li>
                    <li><strong>tax_rate_ids</strong> (array) - Tax rate IDs to apply</li>
                    <li><strong>payment_gateway</strong> (string) - flutterwave, control_number, or both</li>
                </ul>
            </div>
            
            <x-docs.code-block language="json" label="request">
{
  "organization_id": 1,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255712345678"
  },
  "products": [
    {
      "price_plan_id": 5,
      "amount": 50000
    }
  ],
  "description": "Website development project",
  "currency": "TZS",
  "status": "issued",
  "date": "2026-02-26",
  "due_date": "2026-03-26",
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
            </x-docs.code-block>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json" label="success">
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV-2026-0123",
      "organization_id": 1,
      "customer_id": 45,
      "subtotal": 50000,
      "tax_amount": 0,
      "total_amount": 50000,
      "currency": "TZS",
      "status": "issued",
      "date": "2026-02-26",
      "due_date": "2026-03-26",
      "description": "Website development project",
      "customer": {
        "id": 45,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+255712345678"
      },
      "items": [
        {
          "id": 567,
          "description": "Premium Hosting - Monthly",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        }
      ],
           "payment_details": {
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1708956234",
          "expires_at": "2026-03-05T11:15:00.000000Z"
        }
      }
    }
  }
}
            </x-docs.code-block>
            
            <div class="response-head" style="margin-top: 24px;">
                <span class="response-title">Error Response</span>
                <span class="status-badge" style="background: #dc2626;">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "success": false,
  "errors": {
    "customer.email": ["The customer email must be a valid email address."],
    "products": ["The products field must have at least 1 items."],
    "currency": ["The currency must be 3 characters."]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Get Single Invoice --}}
    <x-docs.endpoint
        id="get-invoice"
        method="GET"
        url="/api/v1/invoices/{id}"
        title="Get Single Invoice"
        description="Retrieve details of a specific invoice">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true]
            ]"/>
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
    "invoice_number": "INV-2024-001",
    "customer": {
      "id": 5,
      "name": "John Doe"
    },
    "items": [],
    "total_amount": 15000
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Cancel Invoice --}}
    <x-docs.endpoint
        id="cancel-invoice"
        method="POST"
        url="/api/v1/invoices/{id}/cancel"
        title="Cancel Invoice"
        description="Cancel an invoice and reverse related pending operations. Cannot cancel invoices with active subscriptions.">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="requestBody">
            <div style="background: var(--surface-soft); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                <h4 style="margin-top: 0;">⚠️ Important Notes:</h4>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                    <li>No request body is required for this endpoint</li>
                    <li>Cannot cancel invoices with <strong>active subscriptions</strong></li>
                    <li>Will automatically cancel any <strong>pending</strong> or <strong>partial</strong> subscriptions</li>
                    <li>Cannot cancel an invoice that is already cancelled</li>
                    <li>Reverses any pending invoice payments</li>
                </ul>
            </div>
            
            <x-docs.code-block language="json" label="request">
// No request body required
{}
            </x-docs.code-block>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json" label="success">
{
  "success": true,
  "message": "Invoice cancelled successfully",
  "data": {
    "id": 123,
    "invoice_number": "INV-2026-0123",
    "status": "cancelled",
    "total_amount": 50000,
    "cancelled_at": "2026-03-14T10:30:00Z"
  }
}
            </x-docs.code-block>
            
            <div class="response-head" style="margin-top: 24px;">
                <span class="response-title">Error Response - Already Cancelled</span>
                <span class="status-badge" style="background: #dc2626;">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "success": false,
  "message": "Invoice is already cancelled"
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 24px;">
                <span class="response-title">Error Response - Active Subscriptions</span>
                <span class="status-badge" style="background: #dc2626;">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "success": false,
  "message": "Cannot cancel invoice with active subscriptions"
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 24px;">
                <span class="response-title">Error Response - Not Found</span>
                <span class="status-badge" style="background: #dc2626;">404 Not Found</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "success": false,
  "message": "Invoice not found"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
