<section class="api-section" id="customers-section">
    <h2>👥 Customers</h2>
    <p>Manage your customer database and their information.</p>

    <x-docs.endpoint
        id="list-customers"
        method="GET"
        url="/api/v1/customers"
        title="List Customers"
        description="Get all customers in your organization">
        
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
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+255712345678",
      "status": "active"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="create-customer"
        method="POST"
        url="/api/v1/customers"
        title="Create Customer"
        description="Add a new customer to your database">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+255712345679",
  "country_id": 1
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
  "message": "Customer created successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="get-customer"
        method="GET"
        url="/api/v1/customers/{id}"
        title="Get Customer"
        description="Retrieve a specific customer's details">
        
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
    "name": "John Doe",
    "email": "john@example.com",
    "subscriptions": [],
    "invoices": []
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="update-customer"
        method="PUT"
        url="/api/v1/customers/{id}"
        title="Update Customer"
        description="Update customer information">
        
        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Customer updated successfully"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
