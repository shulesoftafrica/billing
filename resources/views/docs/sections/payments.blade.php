<section class="api-section" id="payments-section">
    <h2>💳 Payments</h2>
    <p>Process and manage payments from customers.</p>

    <x-docs.endpoint
        id="list-payments"
        method="GET"
        url="/api/v1/payments"
        title="List Payments"
        description="Get all payment transactions">
        
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
      "invoice_id": 5,
      "amount": 15000,
      "status": "completed",
      "payment_method": "mobile_money"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="process-payment"
        method="POST"
        url="/api/v1/payments"
        title="Process Payment"
        description="Process a new payment for an invoice">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "invoice_id": 5,
  "amount": 15000,
  "payment_method": "mobile_money",
  "phone": "+255712345678"
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
  "message": "Payment processed successfully",
  "data": {
    "id": 1,
    "status": "completed"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="get-payment"
        method="GET"
        url="/api/v1/payments/{id}"
        title="Get Payment"
        description="Retrieve payment transaction details">
        
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
    "invoice_id": 5,
    "amount": 15000,
    "status": "completed",
    "created_at": "2024-03-14T10:30:00Z"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
