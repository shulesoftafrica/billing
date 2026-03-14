<section class="api-section" id="wallets-section">
    <h2>💰 Wallets</h2>
    <p>Manage customer wallet balances and transactions.</p>

    <x-docs.endpoint
        id="get-wallet-balance"
        method="GET"
        url="/api/v1/wallets/{customer_id}/balance"
        title="Get Wallet Balance"
        description="Get the current wallet balance for a customer">
        
        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "data": {
    "customer_id": 123,
    "balance": 150.00,
    "currency": "USD",
    "last_updated": "2024-01-15T10:30:00Z"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="add-wallet-funds"
        method="POST"
        url="/api/v1/wallets/{customer_id}/add-funds"
        title="Add Wallet Funds"
        description="Add funds to a customer's wallet">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "amount": 100.00,
  "description": "Top-up via credit card",
  "payment_method": "stripe",
  "reference": "TXN-123456"
}
            </x-docs.code-block>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Funds added successfully",
  "data": {
    "transaction_id": 789,
    "new_balance": 250.00
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="wallet-transactions"
        method="GET"
        url="/api/v1/wallets/{customer_id}/transactions"
        title="Wallet Transactions"
        description="Get transaction history for a wallet">
        
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
      "id": 789,
      "type": "credit",
      "amount": 100.00,
      "description": "Top-up via credit card",
      "balance_after": 250.00,
      "created_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 788,
      "type": "debit",
      "amount": 50.00,
      "description": "Invoice payment",
      "balance_after": 150.00,
      "created_at": "2024-01-14T15:20:00Z"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="deduct-wallet-funds"
        method="POST"
        url="/api/v1/wallets/{customer_id}/deduct-funds"
        title="Deduct Wallet Funds"
        description="Deduct funds from a customer's wallet">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "amount": 50.00,
  "description": "Invoice payment",
  "invoice_id": 456
}
            </x-docs.code-block>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Funds deducted successfully",
  "data": {
    "transaction_id": 790,
    "new_balance": 100.00
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
