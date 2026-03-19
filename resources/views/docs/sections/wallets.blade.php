<section class="api-section" id="wallets-section">
    <h2>💰 Wallets & Usage-Based Billing</h2>
    <p>Manage usage-based products with wallet/credit systems. This section covers pay-per-use billing for services like API calls, SMS credits, storage, bandwidth, and other consumable resources.</p>

    <div class="alert alert-info" style="background: #e8f4fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong>📋 Workflow Overview:</strong><br>
        <ol style="margin: 10px 0 0 20px; line-height: 1.8;">
            <li><strong>Setup Product:</strong> Create a usage product (product_type_id = 3) with a price plan (rate = price per unit)</li>
            <li><strong>Customer Purchase:</strong> Create invoice using the price_plan_id (identifies product) and amount (total payment)</li>
            <li><strong>System Calculation:</strong> Quantity = amount ÷ rate (e.g., 50,000 TZS ÷ 50 TZS/SMS = 1,000 SMS)</li>
            <li><strong>Record Usage:</strong> Track consumption as customer uses the service (deducts from balance)</li>
            <li><strong>Check Balance:</strong> balance = total_purchased - total_used</li>
        </ol>
    </div>

    <div class="alert alert-success" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 4px;">
        <strong>🎯 Key Concept - Product Identification:</strong><br>
        <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
            <li>Each <code>price_plan_id</code> is linked to a specific product (SMS, API Calls, Storage, etc.)</li>
            <li>Different products have different price plans with different rates</li>
            <li>Example: price_plan_id 15 = SMS product (50 TZS/SMS), price_plan_id 17 = API product (10 TZS/call)</li>
            <li>When you specify <code>price_plan_id: 15</code>, the system knows you're buying SMS credits</li>
        </ul>
    </div>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">📦 Step 1: Create Usage Product</h3>
    <p>First, create a product with <code>product_type_id = 3</code> (Usage Product) to enable wallet/usage-based billing.</p>

    <x-docs.endpoint
        id="create-usage-product"
        method="POST"
        url="/api/v1/products"
        title="Create Usage Product"
        description="Create a product for usage-based billing (API calls, SMS, storage, etc.)">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "organization_id": 1,
  "product_type_id": 3,
  "name": "SMS Credits",
  "product_code": "SMS-CREDITS",
  "description": "Prepaid SMS credits for bulk messaging",
  "unit": "SMS",
  "active": true,
  "price_plans": [
    {
      "name": "SMS Credit Package",
      "currency_id": 1,
      "rate": 50
    }
  ]
}
            </x-docs.code-block>
            
            <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                <strong>⚠️ Important:</strong> Set <code>product_type_id = 3</code> to enable usage-based billing. This allows the system to track purchases and consumption separately.
            </div>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 12,
    "organization_id": 1,
    "product_type_id": 3,
    "name": "SMS Credits",
    "product_code": "SMS-CREDITS",
    "description": "Prepaid SMS credits for bulk messaging",
    "unit": "SMS",
    "active": true,
    "product_type": {
      "id": 3,
      "name": "Usage Product"
    },
    "price_plans": [
      {
        "id": 15,
        "name": "SMS Credit Package",
        "rate": 50,
        "currency": "TZS"
      }
    ]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">💳 Step 2: Create Wallet Top-Up Invoice</h3>
    <p>Create an invoice for customers to purchase wallet credits. The <strong>price_plan_id identifies the specific product</strong> (e.g., SMS vs API Calls vs Storage), and the system automatically calculates the quantity based on the amount paid.</p>

    <x-docs.endpoint
        id="create-wallet-invoice"
        method="POST"
        url="/api/v1/invoices"
        title="Create Wallet Top-Up Invoice"
        description="Generate an invoice for customers to buy usage credits (SMS, API calls, storage, etc.)">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "organization_id": 1,
  "customer": {
    "name": "Tech Startup Inc",
    "email": "billing@techstartup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "price_plan_id": 15,
      "amount": 50000
    }
  ],
  "description": "SMS Credits Top-Up - 1000 SMS @ TZS 50 each",
  "currency": "TZS",
  "status": "issued"
}
            </x-docs.code-block>

            <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                <strong>🔑 How Product Selection Works:</strong>
                <ul style="margin: 8px 0 0 20px; line-height: 1.7;">
                    <li><code>price_plan_id: 15</code> identifies the <strong>specific usage product</strong> (e.g., SMS Credits product)</li>
                    <li>Each price plan is linked to ONE product and has a <code>rate</code> (price per unit)</li>
                    <li>The system calculates quantity as: <code>quantity = amount ÷ rate</code></li>
                </ul>
            </div>

            <div style="margin-top: 15px; padding: 12px; background: #e8f5e9; border-left: 3px solid #4caf50; border-radius: 4px;">
                <strong>📊 Calculation Example:</strong>
                <ul style="margin: 8px 0 0 20px; line-height: 1.7;">
                    <li><strong>Price Plan ID 15</strong> → SMS Credits product (rate: TZS 50 per SMS)</li>
                    <li><strong>Amount:</strong> TZS 50,000</li>
                    <li><strong>Quantity Calculated:</strong> 50,000 ÷ 50 = <strong>1,000 SMS credits</strong></li>
                    <li>After payment, a <code>ProductPurchase</code> record is created with quantity: 1000</li>
                </ul>
            </div>

            <x-docs.parameter-table :headers="['Parameter', 'Type', 'Required', 'Description']">
                <tr>
                    <td><code>organization_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Your organization ID</td>
                </tr>
                <tr>
                    <td><code>customer</code></td>
                    <td>object</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Customer details (name, email, phone)</td>
                </tr>
                <tr>
                    <td><code>products[].price_plan_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td><strong>Identifies the usage product</strong> (e.g., SMS product vs API product). Each price plan is linked to a specific product.</td>
                </tr>
                <tr>
                    <td><code>products[].amount</code></td>
                    <td>numeric</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Total payment amount. System calculates quantity as: <code>amount ÷ rate</code></td>
                </tr>
                <tr>
                    <td><code>description</code></td>
                    <td>string</td>
                    <td><span class="badge-optional">Optional</span></td>
                    <td>Invoice description for customer reference</td>
                </tr>
                <tr>
                    <td><code>currency</code></td>
                    <td>string</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>3-letter currency code (e.g., TZS, USD, EUR)</td>
                </tr>
            </x-docs.parameter-table>

            <div style="margin-top: 15px; padding: 12px; background: #e3f2fd; border-left: 3px solid #2196F3; border-radius: 4px;">
                <strong>💡 Multiple Products Example:</strong><br>
                To purchase credits for <strong>different products</strong>, include multiple items in the products array:
                <pre style="background: #fff; padding: 10px; border-radius: 4px; margin-top: 8px; overflow-x: auto;"><code>{
  "products": [
    {
      "price_plan_id": 15,  // SMS Credits (rate: 50 TZS/SMS)
      "amount": 50000       // = 1000 SMS
    },
    {
      "price_plan_id": 17,  // API Credits (rate: 10 TZS/call)
      "amount": 100000      // = 10000 API calls
    }
  ]
}</code></pre>
            </div>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "id": 125,
    "invoice_number": "INV-20260315-0125",
    "customer_id": 45,
    "total": 50000,
    "currency": "TZS",
    "status": "issued",
    "description": "SMS Credits Top-Up - 1000 SMS @ TZS 50 each",
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com"
    },
    "items": [
      {
        "id": 458,
        "price_plan_id": 15,
        "product_name": "SMS Credits",
        "quantity": 1,
        "unit_price": 50000,
        "total": 50000
      }
    ],
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123",
        "tx_ref": "INV-125-1710504000"
      }
    }
  }
}
            </x-docs.code-block>

            <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                <strong>⚠️ Next Step:</strong> After payment is received, the system automatically creates a <code>ProductPurchase</code> record that adds the credits to the customer's wallet balance.
            </div>
        </x-slot>
    </x-docs.endpoint>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">📊 Step 3: Record Usage</h3>
    <p>Track customer consumption by recording usage events. This deducts from their available balance.</p>

    <x-docs.endpoint
        id="record-usage"
        method="POST"
        url="/api/v1/product-usages"
        title="Record Product Usage"
        description="Track customer usage of wallet credits (SMS sent, API calls made, storage consumed, etc.)">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "customer_id": 45,
  "product_id": 12,
  "quantity": 150
}
            </x-docs.code-block>

            <x-docs.parameter-table :headers="['Parameter', 'Type', 'Required', 'Description']">
                <tr>
                    <td><code>customer_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Customer who consumed the service</td>
                </tr>
                <tr>
                    <td><code>product_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Usage product ID (must have product_type_id = 3)</td>
                </tr>
                <tr>
                    <td><code>quantity</code></td>
                    <td>numeric</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>Amount consumed (e.g., 150 SMS sent, 5GB stored)</td>
                </tr>
            </x-docs.parameter-table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Product usage recorded successfully",
  "data": {
    "id": 789,
    "customer_id": 45,
    "product_id": 12,
    "quantity": 150,
    "created_at": "2026-03-15T14:30:00.000000Z",
    "product": {
      "id": 12,
      "name": "SMS Credits",
      "product_type": "usage",
      "unit": "SMS"
    },
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com"
    }
  }
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Error Response</span>
                <span class="status-badge status-4xx">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_id": [
      "Product usage is only allowed for products with type usage."
    ]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">💰 Step 4: Check Balance</h3>
    <p>Get the current wallet balance for a specific product and customer.</p>

    <x-docs.endpoint
        id="get-usage-balance"
        method="GET"
        url="/api/v1/product-usages/balance"
        title="Get Usage Balance"
        description="Check remaining credits (Balance = Purchased - Used)">
        
        <x-slot name="requestBody">
            <x-docs.parameter-table :headers="['Parameter', 'Type', 'Location', 'Description']">
                <tr>
                    <td><code>customer_id</code></td>
                    <td>integer</td>
                    <td>Query</td>
                    <td>Customer ID to check balance for</td>
                </tr>
                <tr>
                    <td><code>product_id</code></td>
                    <td>integer</td>
                    <td>Query</td>
                    <td>Usage product ID</td>
                </tr>
            </x-docs.parameter-table>

            <x-docs.code-block language="bash" label="example">
GET /api/v1/product-usages/balance?customer_id=45&product_id=12
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
  "message": "Usage balance retrieved successfully",
  "data": {
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com",
      "phone": "+255734567890"
    },
    "product": {
      "id": 12,
      "name": "SMS Credits",
      "description": "Prepaid SMS credits for bulk messaging",
      "unit": "SMS"
    },
    "usage": {
      "total_purchased": 1000,
      "total_used": 150,
      "balance": 850
    }
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">📈 Step 5: Get Usage Report</h3>
    <p>Retrieve comprehensive usage report for a customer across all products.</p>

    <x-docs.endpoint
        id="get-usage-report"
        method="GET"
        url="/api/v1/product-usages/{customer_id}/report"
        title="Get Usage Report"
        description="Get detailed usage summary for billing period">
        
        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Usage report retrieved successfully",
  "data": {
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com",
      "phone": "+255734567890"
    },
    "usage_by_product": [
      {
        "product": {
          "id": 12,
          "name": "SMS Credits",
          "description": "Prepaid SMS credits",
          "unit": "SMS",
          "usage": {
            "total_purchased": 5000,
            "total_used": 3450,
            "balance": 1550
          }
        }
      },
      {
        "product": {
          "id": 13,
          "name": "API Calls",
          "description": "Prepaid API call credits",
          "unit": "calls",
          "usage": {
            "total_purchased": 50000,
            "total_used": 45230,
            "balance": 4770
          }
        }
      }
    ]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">📜 Step 6: Get Usage History</h3>
    <p>View detailed transaction history showing all purchases and consumption.</p>

    <x-docs.endpoint
        id="get-usage-history"
        method="GET"
        url="/api/v1/product-usages/{customer_id}/{product_id}/history"
        title="Get Usage History"
        description="Retrieve complete audit trail of purchases and usage">
        
        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Usage history retrieved successfully",
  "data": {
    "customer_id": 45,
    "product_id": 12,
    "customer_name": "Tech Startup Inc",
    "product_name": "SMS Credits",
    "total_purchased": 1000,
    "total_used": 150,
    "balance": 850,
    "purchases": [
      {
        "id": 101,
        "quantity": 1000,
        "created_at": "2026-03-01T10:00:00.000000Z"
      }
    ],
    "usages": [
      {
        "id": 789,
        "quantity": 150,
        "created_at": "2026-03-15T14:30:00.000000Z"
      }
    ]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <div class="alert alert-success" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 20px; margin: 40px 0; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #2e7d32;">✅ Complete Workflow Example</h4>
        <pre style="background: #fff; padding: 15px; border-radius: 4px; overflow-x: auto; line-height: 1.6;"><code># 1. Create usage product (product_type_id = 3)
POST /api/v1/products

# 2. Customer buys 1000 SMS credits (creates ProductPurchase)
POST /api/v1/invoices

# 3. Customer sends 150 SMS (creates ProductUsage)
POST /api/v1/product-usages
{"customer_id": 45, "product_id": 12, "quantity": 150}

# 4. Check remaining balance (1000 - 150 = 850)
GET /api/v1/product-usages/balance?customer_id=45&product_id=12

# 5. Generate invoice for accumulated usage (if post-paid model)
POST /api/v1/invoices
</code></pre>
    </div>

    <div class="alert alert-info" style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 20px; margin: 20px 0 40px 0; border-radius: 4px;">
        <h4 style="margin-top: 0; color: #1565c0;">📌 Key Concepts</h4>
        <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
            <li><strong>Price Plan → Product Mapping:</strong> Each price_plan_id is linked to ONE specific product
                <ul style="margin-top: 5px;">
                    <li>price_plan_id 15 → SMS Credits (rate: 50 TZS/SMS)</li>
                    <li>price_plan_id 17 → API Calls (rate: 10 TZS/call)</li>
                    <li>price_plan_id 19 → Cloud Storage (rate: 100 TZS/GB)</li>
                </ul>
            </li>
            <li><strong>Automatic Quantity Calculation:</strong> quantity = amount ÷ rate
                <ul style="margin-top: 5px;">
                    <li>Customer pays 50,000 TZS for SMS (rate: 50 TZS/SMS)</li>
                    <li>System calculates: 50,000 ÷ 50 = 1,000 SMS credits</li>
                    <li>ProductPurchase record created with quantity: 1000</li>
                </ul>
            </li>
            <li><strong>ProductPurchase:</strong> Credits added to wallet (created after invoice payment)</li>
            <li><strong>ProductUsage:</strong> Credits consumed from wallet (created when service is used)</li>
            <li><strong>Balance Formula:</strong> Sum(ProductPurchase.quantity) - Sum(ProductUsage.quantity)</li>
            <li><strong>Billing Models:</strong> 
                <ul style="margin-top: 5px;">
                    <li><strong>Pre-paid:</strong> Customer buys credits first (invoice → payment → ProductPurchase), then uses them (ProductUsage)</li>
                    <li><strong>Post-paid:</strong> Record usage first (ProductUsage), then invoice at end of billing period</li>
                </ul>
            </li>
        </ul>
    </div>
</section>
