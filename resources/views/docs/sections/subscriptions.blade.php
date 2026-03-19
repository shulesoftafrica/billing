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
        description="Create a new subscription for a customer">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "customer_id": 5,
  "product_id": 2,
  "start_date": "2024-03-15"
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
  "message": "Subscription created successfully",
  "data": {
    "id": 1,
    "status": "active"
  }
}
            </x-docs.code-block>
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
