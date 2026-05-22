<section class="api-section" id="payment-gateways-section">
    <h2>Payment Gateways</h2>
    <p>View configured payment gateways available to your organization.</p>

    <x-docs.endpoint
        id="list-payment-gateways"
        method="GET"
        url="/api/v1/payment-gateways"
        title="List Payment Gateways"
        description="Get all configured payment gateways. This documentation is read-only."
    >
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
                "name": "universal control number",
                "type": "control_number",
                "webhook_secret": "***",
                "config": {
                "base_url": "https://payservice.ecobank.com"
                },
                "active": true,
                "created_at": "2026-05-22T10:00:00.000000Z",
                "updated_at": "2026-05-22T10:00:00.000000Z"
                },
                {
                "id": 2,
                "name": "stripe",
                "type": "card",
                "webhook_secret": "***",
                "config": null,
                "active": true,
                "created_at": "2026-05-22T10:00:00.000000Z",
                "updated_at": "2026-05-22T10:00:00.000000Z"
                }
                ]
                }
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>
</section>