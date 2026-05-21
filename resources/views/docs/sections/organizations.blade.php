<section class="api-section" id="organizations-section">
    <h2> Organizations</h2>
    <p>Manage organizations and their billing configurations.</p>

    <x-docs.endpoint id="list-organizations" method="GET" url="/api/v1/organizations" title="List Organizations"
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

    <x-docs.endpoint id="create-organization" method="POST" url="/api/v1/organizations" title="Create Organization"
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

    <x-docs.endpoint id="get-organization" method="GET" url="/api/v1/organizations/{id}" title="Get Organization"
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

    <x-docs.endpoint id="update-organization" method="PUT" url="/api/v1/organizations/{id}"
        title="Update Organization" description="Update organization details">

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

    <x-docs.endpoint id="integrate-organization-payment-gateway" method="POST"
        url="/api/v1/organizations/integrate-payment-gateway" title="Integrate Organization Payment Gateway"
        description="Integrate a payment gateway with an organization. Currently supported: UCN, Stripe, and Flutterwave. PayPal returns a not implemented response.">

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
                {
                "organization_id": 1,
                "payment_gateway_id": 2,
                "endpoint": "https://merchant.example.com/api/payments/callback"
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
                "message": "Stripe integrated successfully",
                "data": {
                "organization": {
                "id": 1,
                "name": "Acme Corporation",
                "email": "billing@acme.com"
                },
                "payment_gateway": {
                "id": 2,
                "name": "Stripe",
                "type": "card",
                "configuration": {
                "id": 22,
                "env": "testing",
                "config": "{\"api_key\":\"org_...\",\"signature_key\":\"...\",\"api_endpoint\":\"https://merchant.example.com/api/payments/callback\"}"
                }
                }
                }
                }
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 1rem;">
                <span class="response-title">Gateway-Specific Success: UCN</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
                {
                "success": true,
                "message": "Universal Control Number integrated successfully",
                "data": {
                "organization": {
                "id": 1,
                "name": "Acme Corporation"
                },
                "payment_gateway": {
                "id": 1,
                "name": "Universal Control Number",
                "type": "bank_transfer",
                "configuration": {
                "id": 45,
                "env": "testing",
                "config": "{\"api_key\":\"org_...\",\"signature_key\":\"...\",\"api_endpoint\":\"https://merchant.example.com/api/payments/callback\"}"
                },
                "merchants": {
                "merchant_code": "UCN-MER-240114-000873",
                "terminal_id": "26561424",
                "terminal_name": "ShuleSoft High School"
                }
                }
                }
                }
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 1rem;">
                <span class="response-title">Gateway-Specific Success: Stripe</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
                {
                "success": true,
                "message": "Stripe integrated successfully",
                "data": {
                "organization": {
                "id": 1,
                "name": "Acme Corporation"
                },
                "payment_gateway": {
                "id": 2,
                "name": "Stripe",
                "type": "card",
                "configuration": {
                "id": 46,
                "env": "testing",
                "config": "{\"api_key\":\"org_...\",\"signature_key\":\"...\",\"api_endpoint\":\"https://merchant.example.com/api/payments/callback\"}"
                }
                }
                }
                }
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 1rem;">
                <span class="response-title">Gateway-Specific Success: Flutterwave</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
                {
                "success": true,
                "message": "Flutterwave integrated successfully",
                "data": {
                "organization": {
                "id": 1,
                "name": "Acme Corporation"
                },
                "payment_gateway": {
                "id": 3,
                "name": "Flutterwave",
                "type": "card",
                "configuration": {
                "id": 47,
                "env": "testing",
                "config": "{\"api_key\":\"org_...\",\"signature_key\":\"...\",\"api_endpoint\":\"https://merchant.example.com/api/payments/callback\"}"
                }
                }
                }
                }
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 1rem;">
                <span class="response-title">Validation/Error Response</span>
                <span class="status-badge status-4xx">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json">
                {
                "success": false,
                "message": "Payment gateway already integrated with this organization"
                }
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 1rem;">
                <span class="response-title">Server Error</span>
                <span class="status-badge status-5xx">500 Internal Server Error</span>
            </div>
            <x-docs.code-block language="json">
                {
                "success": false,
                "message": "Payment gateway integration failed",
                "error": "An error occurred during integration"
                }
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>
