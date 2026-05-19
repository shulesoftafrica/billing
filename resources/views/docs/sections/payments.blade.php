<section class="api-section" id="payments-section">
    <h2>Payments</h2>
    <p>Retrieve and reconcile payment transactions.</p>

    <x-docs.endpoint
        id="list-payments-by-date"
        method="GET"
        url="/api/v1/payments"
        title="List Payments"
        description="Get payments within a date range, optionally filtered by customer">

      <x-slot name="headers">
        <x-docs.parameter-table :parameters="[
          ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true],
          ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
        ]"/>
      </x-slot>

        <x-slot name="urlParams">
          <x-docs.parameter-table
                :parameters="[
              ['key' => 'date_from', 'value' => 'YYYY-MM-DD', 'required' => true, 'description' => 'Start date in YYYY-MM-DD format'],
              ['key' => 'date_to', 'value' => 'YYYY-MM-DD', 'required' => true, 'description' => 'End date in YYYY-MM-DD format'],
              ['key' => 'customer_id', 'value' => 'integer', 'required' => false, 'description' => 'Filter by customer ID (must exist)']
                ]"
            />
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
      "gateway_id": 2,
      "gateway_name": "Flutterwave",
      "customer_id": 7,
      "amount": 15000,
      "transaction_reference": "flw_txn_123456",
      "payment_reference": "PAY-2026-0001",
      "status": "completed",
      "paid_at": "2026-05-10T09:15:00.000000Z",
      "created_at": "2026-05-10T09:14:58.000000Z",
      "updated_at": "2026-05-10T09:15:02.000000Z",
      "customer": {
        "id": 7,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="list-payments-by-invoice"
        method="GET"
        url="/api/v1/payments/by-invoice/{invoice_id}"
        title="List Payments by Invoice"
        description="Get all payments linked to a specific invoice">

      <x-slot name="headers">
        <x-docs.parameter-table :parameters="[
          ['key' => 'Authorization', 'value' => 'Bearer {token}', 'description' => 'Your OAuth access token', 'required' => true],
          ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
        ]"/>
      </x-slot>

        <x-slot name="urlParams">
          <x-docs.parameter-table
                :parameters="[
              ['key' => 'invoice_id', 'value' => 'integer', 'required' => true, 'description' => 'Invoice ID']
                ]"
            />
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
      "gateway_id": 2,
      "gateway_name": "Flutterwave",
      "customer_id": 7,
      "amount": 15000,
      "transaction_reference": "flw_txn_123456",
      "payment_reference": "PAY-2026-0001",
      "status": "completed",
      "paid_at": "2026-05-10T09:15:00.000000Z",
      "created_at": "2026-05-10T09:14:58.000000Z",
      "updated_at": "2026-05-10T09:15:02.000000Z",
      "customer": {
        "id": 7,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
