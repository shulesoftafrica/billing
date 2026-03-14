<section class="api-section" id="products-section">
    <h2>📦 Products</h2>
    <p>Manage your product catalog including subscriptions, one-time products, and usage-based services.</p>

    {{-- List All Products --}}
    <x-docs.endpoint
        id="list-all-products"
        method="GET"
        url="/api/v1/products"
        title="List All Products"
        description="Retrieve all products for your organization with their price plans">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {YOUR_ACCESS_TOKEN}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"success\": true,
  \"data\": [
    {
      \"id\": 1,
      \"organization_id\": 1,
      \"product_type_id\": 2,
      \"name\": \"SafariChat Platform\",
      \"product_code\": \"safarichat\",
      \"description\": \"WhatsApp business messaging platform\",
      \"unit\": \"month\",
      \"status\": \"active\",
      \"price_plans\": [
        {
          \"id\": 1,
          \"name\": \"Starter Plan\",
          \"subscription_type\": \"monthly\",
          \"amount\": 69000,
          \"currency\": \"TZS\",
          \"rate\": 30
        }
      ]
    }
  ]
}"'/>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Error Response</span>
                <span class="status-badge status-4xx">401 Unauthorized</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"message\": \"Unauthenticated\",
  \"error\": \"invalid_access_token\"
}"'/>
        </x-slot>
    </x-docs.endpoint>

    {{-- Create Product --}}
    <x-docs.endpoint
        id="create-product"
        method="POST"
        url="/api/v1/products"
        title="Create Product"
        description="Create a new product with 4-tier pricing structure (Trial, Starter, Pro, Premium)">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {YOUR_ACCESS_TOKEN}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Content-Type', 'value' => 'application/json', 'description' => 'Request format', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="requestBody">
            <h4 class="block-title">SafariChat 4-Tier Product Example</h4>
            <x-docs.code-block language="json" :code='"{
  \"product_type_id\": 2,
  \"name\": \"SafariChat Platform\",
  \"product_code\": \"safarichat\",
  \"description\": \"WhatsApp business messaging platform with AI-powered features\",
  \"unit\": \"month\",
  \"status\": \"active\",
  \"price_plans\": [
    {
      \"name\": \"Trial Plan\",
      \"subscription_type\": \"monthly\",
      \"amount\": 0,
      \"currency\": \"TZS\",
      \"rate\": 3
    },
    {
      \"name\": \"Starter Plan\",
      \"subscription_type\": \"monthly\",
      \"amount\": 69000,
      \"currency\": \"TZS\",
      \"rate\": 30
    },
    {
      \"name\": \"Pro Plan\",
      \"subscription_type\": \"monthly\",
      \"amount\": 149000,
      \"currency\": \"TZS\",
      \"rate\": 30
    },
    {
      \"name\": \"Premium Plan\",
      \"subscription_type\": \"monthly\",
      \"amount\": 299000,
      \"currency\": \"TZS\",
      \"rate\": 30
    }
  ]
}"'/>

            <h4 class="block-title" style="margin-top: 24px;">How to Use This Endpoint</h4>
            <p style="margin-bottom: 16px; line-height: 1.6; color: var(--text-soft);">
                Create a new product by sending a POST request with the product details in the request body. 
                Use the examples below in your preferred programming language:
            </p>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"success\": true,
  \"message\": \"Product created successfully\",
  \"data\": {
    \"id\": 1,
    \"organization_id\": 1,
    \"product_type_id\": 2,
    \"name\": \"SafariChat Platform\",
    \"product_code\": \"safarichat\",
    \"description\": \"WhatsApp business messaging platform with AI-powered features\",
    \"unit\": \"month\",
    \"status\": \"active\",
    \"price_plans\": [
      {
        \"id\": 1,
        \"name\": \"Trial Plan\",
        \"amount\": \"0.00\",
        \"currency\": \"TZS\",
        \"rate\": 3,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 2,
        \"name\": \"Starter Plan\",
        \"amount\": \"69000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 3,
        \"name\": \"Pro Plan\",
        \"amount\": \"149000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 4,
        \"name\": \"Premium Plan\",
        \"amount\": \"299000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      }
    ]
  }
}"'/>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Validation Error</span>
                <span class="status-badge status-4xx">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"message\": \"The name field is required.\",
  \"errors\": {
    \"name\": [\"The name field is required.\"],
    \"product_code\": [\"The product code field is required.\"]
  }
}"'/>
        </x-slot>
    </x-docs.endpoint>

    {{-- Get Single Product --}}
    <x-docs.endpoint
        id="get-single-product"
        method="GET"
        url="/api/v1/products/{product}"
        title="Get Single Product"
        description="Retrieve a single product by ID or product code. Accepts either numeric ID (e.g., 1) or alphanumeric product_code (e.g., safarichat) in the URL path.">
        
        <x-slot name="headers">
            <x-docs.parameter-table :parameters="[
                ['key' => 'Authorization', 'value' => 'Bearer {YOUR_ACCESS_TOKEN}', 'description' => 'Your OAuth access token', 'required' => true],
                ['key' => 'Accept', 'value' => 'application/json', 'description' => 'Response format', 'required' => true]
            ]"/>
        </x-slot>

        <x-slot name="requestBody">
            <div style="background: var(--surface-soft); border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 12px; font-size: 0.9rem; color: var(--text);">URL Parameter</h4>
                <ul style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 0.88rem; color: var(--text-soft);">
                    <li><code style="background: var(--surface-code); padding: 2px 6px; border-radius: 4px; font-family: 'IBM Plex Mono', monospace;">{product}</code> <strong style="color: var(--danger);">Required</strong>. Can be either:
                        <ul style="margin-top: 8px;">
                            <li><strong>Product ID</strong> (integer): <code style="background: var(--surface-code); padding: 2px 6px; border-radius: 4px; font-family: 'IBM Plex Mono', monospace;">/api/v1/products/1</code></li>
                            <li><strong>Product Code</strong> (string): <code style="background: var(--surface-code); padding: 2px 6px; border-radius: 4px; font-family: 'IBM Plex Mono', monospace;">/api/v1/products/safarichat</code></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <h4 class="block-title">How to Get This Product</h4>
            <p style="margin-bottom: 16px; line-height: 1.6; color: var(--text-soft);">
                Use either the <strong>product ID</strong> (numeric) or <strong>product code</strong> (alphanumeric) in the URL path. 
                Both methods return the same product details including organization info, product type, and all price plans.
            </p>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"success\": true,
  \"message\": \"Product retrieved successfully\",
  \"data\": {
    \"id\": 1,
    \"organization_id\": 1,
    \"product_type_id\": 2,
    \"name\": \"SafariChat Platform\",
    \"product_code\": \"safarichat\",
    \"description\": \"WhatsApp business messaging platform\",
    \"unit\": \"month\",
    \"status\": \"active\",
    \"organization\": {
      \"id\": 1,
      \"name\": \"ACME Corp\"
    },
    \"product_type\": {
      \"id\": 2,
      \"name\": \"Subscription\"
    },
    \"price_plans\": [
      {
        \"id\": 1,
        \"name\": \"Trial Plan\",
        \"amount\": \"0.00\",
        \"currency\": \"TZS\",
        \"rate\": 3,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 2,
        \"name\": \"Starter Plan\",
        \"amount\": \"69000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 3,
        \"name\": \"Pro Plan\",
        \"amount\": \"149000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      },
      {
        \"id\": 4,
        \"name\": \"Premium Plan\",
        \"amount\": \"299000.00\",
        \"currency\": \"TZS\",
        \"rate\": 30,
        \"subscription_type\": \"monthly\"
      }
    ]
  }
}"'/>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Not Found Error</span>
                <span class="status-badge status-4xx">404 Not Found</span>
            </div>
            <x-docs.code-block language="json" :code='"{
  \"success\": false,
  \"message\": \"Product not found\"
}"'/>
        </x-slot>
    </x-docs.endpoint>

</section>
