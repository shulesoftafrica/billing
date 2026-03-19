<section class="api-section" id="organizations-section">
    <h2>🏢 Organizations</h2>
    <p>Manage organizations and their billing configurations.</p>

    <x-docs.endpoint
        id="list-organizations"
        method="GET"
        url="/api/v1/organizations"
        title="List Organizations"
        description="Get all organizations">
        
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
      "name": "Acme Corporation",
      "email": "billing@acme.com",
      "currency": "USD",
      "timezone": "America/New_York"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="create-organization"
        method="POST"
        url="/api/v1/organizations"
        title="Create Organization"
        description="Create a new organization">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Acme Corporation",
  "email": "billing@acme.com",
  "currency": "USD",
  "timezone": "America/New_York",
  "address": {
    "street": "123 Main St",
    "city": "New York",
    "state": "NY",
    "postal_code": "10001",
    "country": "US"
  }
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
  "message": "Organization created successfully",
  "data": {
    "id": 1,
    "name": "Acme Corporation"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="get-organization"
        method="GET"
        url="/api/v1/organizations/{id}"
        title="Get Organization"
        description="Get organization details">
        
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
    "name": "Acme Corporation",
    "email": "billing@acme.com",
    "currency": "USD",
    "timezone": "America/New_York",
    "payment_gateways": ["stripe", "flutterwave"]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="update-organization"
        method="PUT"
        url="/api/v1/organizations/{id}"
        title="Update Organization"
        description="Update organization details">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Acme Corp LLC",
  "email": "new-billing@acme.com",
  "timezone": "Europe/London"
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
  "message": "Organization updated successfully"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
