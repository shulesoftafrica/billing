<section class="api-section" id="taxes-section">
    <h2>📊 Tax Rates</h2>
    <p>Manage tax rates for different products and regions.</p>

    <x-docs.endpoint
        id="list-tax-rates"
        method="GET"
        url="/api/v1/tax-rates"
        title="List Tax Rates"
        description="Get all configured tax rates">
        
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
      "name": "VAT",
      "rate": 18.00,
      "country_id": 1,
      "is_active": true
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="create-tax-rate"
        method="POST"
        url="/api/v1/tax-rates"
        title="Create Tax Rate"
        description="Add a new tax rate configuration">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Sales Tax",
  "rate": 10.00,
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
  "message": "Tax rate created successfully",
  "data": {
    "id": 2,
    "name": "Sales Tax",
    "rate": 10.00
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="get-tax-rate"
        method="GET"
        url="/api/v1/tax-rates/{id}"
        title="Get Tax Rate"
        description="Retrieve a specific tax rate">
        
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
    "name": "VAT",
    "rate": 18.00,
    "country": {
      "name": "Tanzania"
    }
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
