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
        id="create-subscription-invoice"
        method="POST"
        url="/api/v1/invoices"
        title="Create Subscription Invoice"
        description="Create subscription invoices with automatic subscription creation. Supports single or multiple subscription products, automatic customer creation, payment gateway integration, and control number generation.">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    },
    {
      "price_plan_id": 12,
      "amount": 75000
    }
  ],
  "description": "Monthly subscription - SafariChat Platform",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Required Parameters:</strong></p>
                <ul>
                    <li><code>organization_id</code> (integer) - Your organization ID</li>
                    <li><code>customer.name</code> (string) - Customer's full name</li>
                    <li><code>customer.email</code> (string) - Customer's email address</li>
                    <li><code>customer.phone</code> (string) - Customer's phone number</li>
                    <li><code>products</code> (array) - Array of products with subscription price plans</li>
                    <li><code>products.*.price_plan_id</code> (integer) - Price plan ID for subscription product</li>
                    <li><code>products.*.amount</code> (number) - Invoice amount for this product</li>
                    <li><code>currency</code> (string) - 3-letter currency code (e.g., "TZS", "USD")</li>
                </ul>
                <p><strong>Optional Parameters:</strong></p>
                <ul>
                    <li><code>description</code> (string) - Invoice description</li>
                    <li><code>status</code> (string) - Invoice status (default: "issued")</li>
                    <li><code>tax_rate_ids</code> (array) - Array of tax rate IDs to apply</li>
                    <li><code>payment_gateway</code> (string) - "flutterwave", "control_number", or "both"</li>
                    <li><code>success_url</code> (string) - URL to redirect after successful payment</li>
                    <li><code>cancel_url</code> (string) - URL to redirect after cancelled payment</li>
                </ul>
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
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Monthly subscription - SafariChat Platform",
      "subtotal": 150000,
      "tax_total": 0,
      "total": 150000,
      "due_date": null,
      "issued_at": "2026-03-19T11:15:00.000000Z",
      "items": [
        {
          "id": 457,
          "price_plan_id": 8,
          "subscription_id": 89,
          "product_name": "SafariChat Platform",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        },
        {
          "id": 458,
          "price_plan_id": 12,
          "subscription_id": 90,
          "product_name": "Email Marketing Suite",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        }
      ],
      "subscriptions": [
        {
          "id": 89,
          "status": "pending",
          "price_plan_id": 8,
          "start_date": null,
          "next_billing_date": null,
          "note": "Subscription will activate upon payment"
        },
        {
          "id": 90,
          "status": "pending",
          "price_plan_id": 12,
          "start_date": null,
          "next_billing_date": null,
          "note": "Subscription will activate upon payment"
        }
      ],
      "payment_details": {
        "control_number": {
          "reference": "9912345678",
          "amount": 150000,
          "currency": "TZS",
          "expires_at": "2026-03-26T11:15:00.000000Z"
        },
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1710844500"
        }
      }
    },
    "customer": {
      "id": 46,
      "name": "Jane Smith",
      "email": "jane@company.com",
      "phone": "+255723456789",
      "status": "active"
    }
  }
}
            </x-docs.code-block>
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <ul>
                    <li>System automatically detects subscription products and creates subscription records</li>
                    <li>If customer doesn't exist, a new customer is created automatically</li>
                    <li>Subscriptions start with <code>pending</code> status until the invoice is paid</li>
                    <li>Once paid, subscriptions become <code>active</code> with calculated start/end/billing dates</li>
                    <li>Control numbers and payment links are generated based on <code>payment_gateway</code> parameter</li>
                    <li><strong>Idempotent:</strong> If a pending subscription already exists for the same customer and price plan, existing invoice is returned</li>
                </ul>
            </div>
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

    <h3 style="margin-top: 40px; color: #333; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;">⬆️ Subscription Upgrade & Downgrade</h3>
    <p>Change subscription plans mid-cycle with automatic proration. The system calculates fair charges based on actual billing cycle days.</p>

    <x-docs.endpoint
        id="upgrade-subscription"
        method="POST"
        url="/api/v1/invoices/plan-upgrade"
        title="Upgrade Subscription"
        description="Upgrade to a higher-tier plan with automatic proration. Customer pays the difference for remaining days in the billing cycle.">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "subscription_id": 89,
  "new_price_plan_id": 15,
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/upgrade/success",
  "cancel_url": "https://yourapp.com/upgrade/cancel"
}
            </x-docs.code-block>

            <x-docs.parameter-table :headers="['Parameter', 'Type', 'Required', 'Description']">
                <tr>
                    <td><code>subscription_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>ID of the active subscription to upgrade</td>
                </tr>
                <tr>
                    <td><code>new_price_plan_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>ID of the higher-tier price plan (must be same product, higher price)</td>
                </tr>
                <tr>
                    <td><code>payment_gateway</code></td>
                    <td>string</td>
                    <td><span class="badge-optional">Optional</span></td>
                    <td>Payment method: <code>flutterwave</code>, <code>control_number</code>, or <code>both</code> (default: both)</td>
                </tr>
                <tr>
                    <td><code>success_url</code></td>
                    <td>string</td>
                    <td><span class="badge-optional">Optional</span></td>
                    <td>Redirect URL after successful payment</td>
                </tr>
                <tr>
                    <td><code>cancel_url</code></td>
                    <td>string</td>
                    <td><span class="badge-optional">Optional</span></td>
                    <td>Redirect URL if payment is cancelled</td>
                </tr>
            </x-docs.parameter-table>

            <div class="alert alert-info" style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0;">
                <strong>📐 Proration Formula:</strong>
                <pre style="background: #fff; padding: 10px; margin: 10px 0; border-radius: 4px;">
Billing Cycle Days = Days from current_period_start to next_billing_date
Days Remaining = Billing Cycle Days - Days Used

Old Plan Daily Rate = Old Plan Price ÷ Billing Cycle Days
New Plan Daily Rate = New Plan Price ÷ Billing Cycle Days

Unused Credit = Old Plan Daily Rate × Days Remaining
New Plan Charge = New Plan Daily Rate × Days Remaining

Amount to Pay = New Plan Charge - Unused Credit</pre>
            </div>

            <div class="alert alert-success" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0;">
                <strong>💡 Example Calculation:</strong><br>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>Current Plan: Basic (TZS 30,000/month)</li>
                    <li>New Plan: Standard (TZS 75,000/month)</li>
                    <li>Billing Cycle: Jan 15 - Feb 15 (31 days)</li>
                    <li>Upgrade Date: Jan 25 (10 days used, 21 days remaining)</li>
                    <li>Old Daily Rate: 30,000 ÷ 31 = 967.74 TZS/day</li>
                    <li>New Daily Rate: 75,000 ÷ 31 = 2,419.35 TZS/day</li>
                    <li>Unused Credit: 967.74 × 21 = 20,322.54 TZS</li>
                    <li>New Plan Charge: 2,419.35 × 21 = 50,806.35 TZS</li>
                    <li><strong>Amount to Pay: 30,483.81 TZS</strong></li>
                </ul>
            </div>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Subscription upgraded successfully",
  "data": {
    "invoice": {
      "id": 250,
      "invoice_number": "INV-2026-00250",
      "status": "issued",
      "currency": "TZS",
      "subtotal": 30484,
      "grand_total": 30484,
      "outstanding_amount": 30484,
      "issued_at": "2026-01-25T10:30:00.000000Z",
      "customer": {
        "id": 45,
        "name": "Jane Smith",
        "email": "jane@company.com",
        "phone": "+255723456789"
      },
      "price_plans": [
        {
          "id": 15,
          "name": "Standard Plan",
          "unit_price": 30484,
          "amount": 30484,
          "product_name": "Cloud Hosting Premium",
          "payment_gateways": [
            {
              "gateway_name": "Flutterwave",
              "status": "active",
              "payment_link": "https://checkout.flutterwave.com/...",
              "reference": "FLW-REF-1234567890"
            },
            {
              "gateway_name": "Universal Control Number",
              "status": "active",
              "control_number": "UCN9876543210",
              "reference": "UCN9876543210"
            }
          ]
        }
      ]
    },
    "subscription": {
      "id": 89,
      "status": "active",
      "previous_plan_id": 8,
      "current_plan": {
        "id": 15,
        "name": "Standard Plan",
        "amount": 75000
      },
      "next_billing_date": "2026-02-15"
    },
    "proration": {
      "days_used": 10,
      "days_remaining": 21,
      "billing_cycle_days": 31,
      "old_plan_daily_rate": 967.74,
      "new_plan_daily_rate": 2419.35,
      "unused_credit": 20322.54,
      "new_plan_charge": 50806.35,
      "amount_charged": 30483.81
    }
  }
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Error Response - Invalid Upgrade</span>
                <span class="status-badge status-4xx">400 Bad Request</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": false,
  "message": "Failed to upgrade subscription: New plan must have a higher price than current plan. Use downgrade endpoint for lower-tier plans."
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <x-docs.endpoint
        id="downgrade-subscription"
        method="POST"
        url="/api/v1/invoices/plan-downgrade"
        title="Downgrade Subscription"
        description="Downgrade to a lower-tier plan. The system calculates unused credit which can be applied to the customer's account for future use.">
        
        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "subscription_id": 89,
  "new_price_plan_id": 8,
  "apply_credit": true
}
            </x-docs.code-block>

            <x-docs.parameter-table :headers="['Parameter', 'Type', 'Required', 'Description']">
                <tr>
                    <td><code>subscription_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>ID of the active subscription to downgrade</td>
                </tr>
                <tr>
                    <td><code>new_price_plan_id</code></td>
                    <td>integer</td>
                    <td><span class="badge-required">Required</span></td>
                    <td>ID of the lower-tier price plan (must be same product, lower price)</td>
                </tr>
                <tr>
                    <td><code>apply_credit</code></td>
                    <td>boolean</td>
                    <td><span class="badge-optional">Optional</span></td>
                    <td>Whether to apply unused credit to customer wallet (default: <code>true</code>)</td>
                </tr>
            </x-docs.parameter-table>

            <div class="alert alert-info" style="background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 20px 0;">
                <strong>💰 Credit Calculation:</strong>
                <pre style="background: #fff; padding: 10px; margin: 10px 0; border-radius: 4px;">
Billing Cycle Days = Days from current_period_start to current_period_end
Days Remaining = Billing Cycle Days - Days Used

Old Plan Daily Rate = Old Plan Price ÷ Billing Cycle Days
New Plan Daily Rate = New Plan Price ÷ Billing Cycle Days

Unused Value = Old Plan Daily Rate × Days Remaining
New Plan Cost = New Plan Daily Rate × Days Remaining

Credit Amount = Unused Value - New Plan Cost</pre>
            </div>

            <div class="alert alert-success" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0;">
                <strong>💡 Example Calculation:</strong><br>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>Current Plan: Standard (TZS 75,000/month)</li>
                    <li>New Plan: Basic (TZS 30,000/month)</li>
                    <li>Billing Cycle: Jan 15 - Feb 15 (31 days)</li>
                    <li>Downgrade Date: Jan 25 (10 days used, 21 days remaining)</li>
                    <li>Old Daily Rate: 75,000 ÷ 31 = 2,419.35 TZS/day</li>
                    <li>New Daily Rate: 30,000 ÷ 31 = 967.74 TZS/day</li>
                    <li>Unused Value: 2,419.35 × 21 = 50,806.35 TZS</li>
                    <li>New Plan Cost: 967.74 × 21 = 20,322.54 TZS</li>
                    <li><strong>Credit: 30,483.81 TZS</strong> (available for future use)</li>
                </ul>
            </div>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Subscription downgraded successfully",
  "data": {
    "subscription": {
      "id": 89,
      "status": "active",
      "previous_plan_id": 15,
      "current_plan": {
        "id": 8,
        "name": "Basic Plan",
        "amount": 30000,
        "billing_interval": "monthly"
      },
      "next_billing_date": "2026-02-15"
    },
    "credit": {
      "credit_amount": 30484,
      "credit_applied": true,
      "days_remaining": 21,
      "description": "Credit from unused portion of higher plan"
    },
    "proration_details": {
      "days_used": 10,
      "days_remaining": 21,
      "billing_cycle_days": 31,
      "old_plan_name": "Standard Plan",
      "old_plan_amount": 75000,
      "new_plan_name": "Basic Plan",
      "new_plan_amount": 30000,
      "old_plan_daily_rate": 2419.35,
      "new_plan_daily_rate": 967.74,
      "unused_value": 50806.35,
      "new_plan_cost": 20322.54,
      "credit_calculated": 30483.81
    }
  }
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 20px;">
                <span class="response-title">Error Response - Invalid Downgrade</span>
                <span class="status-badge status-4xx">400 Bad Request</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": false,
  "message": "Failed to downgrade subscription: New plan must have a lower price than current plan. Use upgrade endpoint for higher-tier plans."
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <div class="alert alert-warning">
        <h4>⚠️ Important Business Rules</h4>
        <ul>
            <li><strong>Subscription Status:</strong> Only <code>active</code> subscriptions can be upgraded or downgraded</li>
            <li><strong>Same Product:</strong> New plan must belong to the <strong>same product</strong> as current plan</li>
            <li><strong>Price Validation:</strong>
                <ul style="margin-top: 5px;">
                    <li>Upgrade: New plan must have <strong>higher price</strong> than current plan</li>
                    <li>Downgrade: New plan must have <strong>lower price</strong> than current plan</li>
                </ul>
            </li>
            <li><strong>Proration:</strong> All calculations use <strong>actual billing cycle days</strong> (28-31 days depending on month)</li>
            <li><strong>Payment:</strong>
                <ul style="margin-top: 5px;">
                    <li>Upgrade: Creates invoice with payment required</li>
                    <li>Downgrade: No payment required, credit applied to account</li>
                </ul>
            </li>
            <li><strong>Immediate Effect:</strong> Plan changes take effect immediately upon successful operation</li>
        </ul>
    </div>

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
