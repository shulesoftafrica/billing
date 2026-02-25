# Flutterwave Implementation Gap Analysis

## Assessment Date: February 22, 2026

## Executive Summary
**Status:** ❌ **INCOMPLETE** - Only 30% implemented (webhook handling only)

The Flutterwave integration in this billing system is **partially implemented**. While the system can receive and process payment notifications from Flutterwave via webhooks, it **CANNOT generate payment links** when creating invoices, subscriptions, or wallet topups.

### 🎯 Implementation Approach: Payment Button/Link Integration

This implementation uses **Flutterwave's Payment Link** approach where:
1. Third-party systems call existing endpoints (`POST /api/invoices`, etc.)
2. System automatically generates Flutterwave payment link behind the scenes
3. Payment link is returned in the invoice response
4. Customer uses the link to complete payment
5. Webhook notifies system of payment completion

**Key Principle:** Payment link generation happens **automatically during invoice creation**, not as a separate endpoint.

---

## ✅ What's Currently Working (30%)

### 1. Webhook Handler (`app/Http/Controllers/WebhookController.php`)
```php
// Lines 104-150
public function handleFlutterWaveWebhook(Request $request)
```
- ✅ Receives webhooks from Flutterwave
- ✅ Processes `charge.completed` events
- ✅ Processes `charge.failed` events
- ✅ Updates invoice status to 'paid'
- ✅ Creates payment records
- ✅ Signature verification (basic implementation)

**Route:** `POST /api/webhooks/flutterwave`

### 2. Database Configuration
```php
// database/seeders/PaymentGatewaySeeder.php:48-60
'name' => 'Flutterwave',
'type' => 'card',
'config' => [
    'public_key' => 'FLWPUBK_TEST-...',
    'secret_key' => 'FLWSECK_TEST-...',
    'encryption_key' => 'FLWSECK_TEST...',
    'webhook_url' => '/api/webhooks/flutterwave'
]
```
- ✅ Gateway configuration seeded
- ✅ Test API keys stored
- ✅ Webhook URL configured

### 3. Connectivity Testing
```php
// app/Http/Controllers/PaymentGatewayTestController.php:162-189
private function testFlutterWaveConnectivity(PaymentGateway $gateway)
```
- ✅ Tests Flutterwave API connectivity
- ✅ Validates API keys
- ✅ Returns account information

**Route:** `GET /api/payment-gateways/test-connection?gateway_id=X`

---

## ❌ Critical Missing Features (70%)

### 1. ❌ NO Payment Link Generation

**Current Invoice Creation Flow:**
```php
// app/Http/Controllers/Api/InvoiceController.php:42-312
public function store(Request $request)
{
    // Creates invoice
    // Generates control number for EcoBank
    // ❌ DOES NOT generate Flutterwave payment link
    
    return response()->json([
        'payment_details' => [
            'control_number' => $controlNumber,  // EcoBank only
            // ❌ Missing: 'flutterwave_link' => '...'
        ]
    ]);
}
```

**Missing in:**
- Invoice creation (`POST /api/invoices`)
- Subscription creation (`POST /api/invoices` with `invoice_type=subscription`)
- Wallet topup (`POST /api/invoices/wallet-topup`)
- Plan upgrade/downgrade

### 2. ❌ NO FlutterwaveService

**Missing Service Class:**
```
❌ app/Services/FlutterwaveService.php (DOES NOT EXIST)

Expected Methods:
- initializePayment()
- verifyPayment()
- generatePaymentLink()
- getBanks()
- verifyTransaction()
```

**Current Services:**
```
✅ app/Services/UNCPaymentService.php (exists)
✅ app/Services/WalletService.php (exists)
✅ app/Services/SubscriptionService.php (exists)
❌ app/Services/FlutterwaveService.php (MISSING!)
```

### 3. ❌ NO Automatic Payment Link Generation in Invoice Creation

**Integration Point:** Payment link generation should happen automatically within existing endpoints.

**Existing Endpoints (Need Flutterwave Integration):**
```
✅ POST /api/invoices (exists, needs Flutterwave link generation)
✅ POST /api/invoices/wallet-topup (exists, needs Flutterwave link generation)
✅ POST /api/invoices/plan-upgrade (exists, needs Flutterwave link generation)
✅ POST /api/invoices/plan-downgrade (exists, needs Flutterwave link generation)
```

**Optional Standalone Endpoints (for re-generating payment links):**
```
❌ GET /api/payments/verify/{reference} (optional - for manual verification)
```

**Current Payment Query Endpoints:**
```php
// routes/api.php:299-301
✅ GET /api/payments/by-invoice/{invoice_id}
✅ GET /api/payments?date_from=...&date_to=...
```

### 4. ❌ NO Flutterwave Package/SDK

**Missing Composer Dependency:**
```json
// composer.json
{
    "require": {
        // ❌ Missing: "flutterwave/flutterwave-php": "^3.0"
    }
}
```

No Flutterwave SDK installed or configured.

---

## 🔍 Current Payment Flow vs Expected

### Current Flow (Incomplete)
```
Third-Party App
      ↓
POST /api/invoices
      ↓
System generates control number (EcoBank only)
      ↓
Response: {
  "payment_details": {
    "control_number": "CN123456"
    // ❌ Missing: "flutterwave_link"
  }
}
      ↓
User pays via USSD/banking
      ↓
✅ Webhook received → Invoice marked as paid
```

### Expected Flutterwave Flow (Target Implementation)
```
Third-Party App
      ↓
POST /api/invoices
  ├─ With parameter: "payment_gateway": "flutterwave"
  └─ Or automatic for all invoices
      ↓
System creates invoice
      ↓
System automatically calls Flutterwave API (behind the scenes)
      ↓
Flutterwave returns payment link
      ↓
Response: {
  "invoice": {...},
  "payment_details": {
    "control_number": "CN123456",     // EcoBank
    "flutterwave": {                   // ✅ NEW
      "payment_link": "https://checkout.flutterwave.com/...",
      "tx_ref": "INV-123-1234567890",
      "expires_at": "2026-02-23T10:00:00Z"
    }
  }
}
      ↓
Third-party app redirects user to payment_link
      ↓
User completes payment on Flutterwave hosted page
      ↓
✅ Webhook received → Invoice marked as paid
      ↓
Third-party app gets confirmation
```

### Key Design Principles

1. **Automatic Integration:** No separate payment initialization API call needed
2. **Existing Endpoints:** All happens within `POST /api/invoices` and variants
3. **Optional Parameter:** Can be controlled via request parameter or organization settings
4. **Dual Support:** Both control number (EcoBank) and Flutterwave link in same response
5. **Third-Party Friendly:** Single API call creates invoice + payment link

---

## 🛠️ What Needs to be Implemented

### Priority 1: FlutterwaveService Class (Payment Link Generation)

This service uses **Flutterwave Standard/Payment Button API** to generate hosted payment links.

```php
<?php
// app/Services/FlutterwaveService.php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService
{
    protected $secretKey;
    protected $publicKey;
    protected $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $gateway = PaymentGateway::where('type', 'flutterwave')->first();
        
        if (!$gateway) {
            throw new \Exception('Flutterwave payment gateway not configured');
        }
        
        $config = is_array($gateway->config) ? $gateway->config : json_decode($gateway->config, true);
        $this->secretKey = $config['secret_key'] ?? null;
        $this->publicKey = $config['public_key'] ?? null;
        
        if (!$this->secretKey) {
            throw new \Exception('Flutterwave secret key not configured');
        }
    }

    /**
     * Initialize payment and get payment link using Flutterwave Payment Button API
     * 
     * @param Invoice $invoice
     * @param array $customerData ['email', 'phone', 'name']
     * @return array ['success' => bool, 'link' => string, 'tx_ref' => string, 'error' => string]
     */
    public function initializePayment(Invoice $invoice, array $customerData): array
    {
        try {
            $txRef = 'INV-' . $invoice->id . '-' . time();
            
            $payload = [
                'tx_ref' => $txRef,
                'amount' => (float) $invoice->total,
                'currency' => 'TZS',
                'redirect_url' => config('app.url') . '/api/payments/callback',
                'customer' => [
                    'email' => $customerData['email'] ?? 'customer@example.com',
                    'phonenumber' => $customerData['phone'] ?? '',
                    'name' => $customerData['name'] ?? 'Customer'
                ],
                'customizations' => [
                    'title' => 'Invoice Payment',
                    'description' => 'Payment for ' . $invoice->invoice_number,
                    'logo' => config('app.url') . '/logo.png'
                ],
                'meta' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_id' => $invoice->customer_id
                ]
            ];

            Log::info('Initializing Flutterwave payment', [
                'invoice_id' => $invoice->id,
                'amount' => $payload['amount'],
                'tx_ref' => $txRef
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payments', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    Log::info('Flutterwave payment link generated', [
                        'invoice_id' => $invoice->id,
                        'link' => $data['data']['link'] ?? null
                    ]);
                    
                    return [
                        'success' => true,
                        'link' => $data['data']['link'] ?? null,
                        'tx_ref' => $txRef,
                        'data' => $data['data']
                    ];
                }
            }

            $error = 'Failed to generate payment link: ' . $response->body();
            Log::error('Flutterwave payment initialization failed', [
                'invoice_id' => $invoice->id,
                'error' => $error,
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'error' => $error
            ];

        } catch (\Exception $e) {
            Log::error('Flutterwave payment initialization exception', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify payment status
     * 
     * @param string $transactionId
     * @return array
     */
    public function verifyPayment(string $transactionId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey
            ])->get($this->baseUrl . '/transactions/' . $transactionId . '/verify');

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'message' => 'Failed to verify payment',
                'data' => null
            ];

        } catch (\Exception $e) {
            Log::error('Flutterwave payment verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
}
```

### Priority 2: Update InvoiceController (Automatic Payment Link Generation)

Integrate Flutterwave payment link generation directly into the existing `store()` method.

```php
// app/Http/Controllers/Api/InvoiceController.php

use App\Services\FlutterwaveService;

class InvoiceController extends Controller
{
    protected $flutterwaveService;

    public function __construct(FlutterwaveService $flutterwaveService)
    {
        $this->flutterwaveService = $flutterwaveService;
    }

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            // ... existing validation rules ...
            'payment_gateway' => 'nullable|in:control_number,flutterwave,both', // NEW
        ];

        // ... existing invoice creation code ...
        
        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'subscription_id' => $subscription ? $subscription->id : null,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_type' => $request->invoice_type ?: 'subscription',
            'status' => 'issued',
            'description' => $this->generateInvoiceDescription($product, $request),
            'subtotal' => $amount,
            'tax_total' => 0,
            'total' => $amount,
            'due_date' => Carbon::now()->addDays(30),
            'issued_at' => Carbon::now(),
            'metadata' => $request->metadata ?: [],
        ]);

        // Create invoice items
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'price_plan_id' => $pricePlan ? $pricePlan->id : null,
            'description' => $product->name,
            'quantity' => 1,
            'unit_price' => $amount,
            'total' => $amount,
        ]);

        // Generate control number (existing EcoBank integration)
        $controlNumber = $this->generateControlNumber();

        // Prepare response data
        $responseData = [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $customer->id,
                'subscription_id' => $subscription ? $subscription->id : null,
                'organization_id' => $organization->id,
                'invoice_type' => $invoice->invoice_type,
                'status' => $invoice->status,
                'description' => $invoice->description,
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax_total' => number_format($invoice->tax_total, 2),
                'total' => number_format($invoice->total, 2),
                'currency' => $request->currency ?: 'TZS',
                'due_date' => $invoice->due_date->format('Y-m-d'),
                'issued_at' => $invoice->issued_at->toISOString(),
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'external_ref' => $customer->external_ref,
                'customer_type' => $customer->customer_type,
                'created' => $customerCreated
            ],
            'payment_details' => [
                'control_number' => $controlNumber,
                'amount' => number_format($amount, 2),
                'currency' => $request->currency ?: 'TZS',
                'expires_at' => Carbon::now()->addDays(7)->toISOString(),
                'payment_instructions' => [
                    'mobile_banking' => "Dial *150*01*{$controlNumber}# from your registered mobile number",
                    'internet_banking' => 'Login to your internet banking and pay bill using control number',
                    'agent_banking' => 'Visit any bank agent and provide the control number'
                ]
            ]
        ];

        // ✅ NEW: Generate Flutterwave payment link automatically
        // Controlled by request parameter or default to 'both'
        $paymentGateway = $request->input('payment_gateway', 'both');
        
        if (in_array($paymentGateway, ['flutterwave', 'both'])) {
            try {
                $flutterwaveResult = $this->flutterwaveService->initializePayment(
                    $invoice,
                    [
                        'email' => $customer->email ?? 'customer@example.com',
                        'phone' => $customer->phone,
                        'name' => $customer->name
                    ]
                );

                if ($flutterwaveResult['success']) {
                    $responseData['payment_details']['flutterwave'] = [
                        'payment_link' => $flutterwaveResult['link'],
                        'tx_ref' => $flutterwaveResult['tx_ref'],
                        'expires_at' => Carbon::now()->addHours(24)->toISOString(),
                        'instructions' => 'Click the payment link to complete payment via card, mobile money, or bank transfer'
                    ];
                    
                    Log::info('Flutterwave payment link generated for invoice', [
                        'invoice_id' => $invoice->id,
                        'link' => $flutterwaveResult['link']
                    ]);
                } else {
                    // Log error but don't fail the invoice creation
                    Log::error('Failed to generate Flutterwave link', [
                        'invoice_id' => $invoice->id,
                        'error' => $flutterwaveResult['error'] ?? 'Unknown error'
                    ]);
                    
                    $responseData['payment_details']['flutterwave'] = [
                        'error' => 'Payment link generation failed',
                        'message' => 'Please use control number for payment or contact support'
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Flutterwave integration exception', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
                
                // Don't fail invoice creation if Flutterwave fails
                $responseData['payment_details']['flutterwave'] = [
                    'error' => 'Payment link service unavailable',
                    'message' => 'Please use control number for payment'
                ];
            }
        }

        // Add subscription info if created
        if ($subscription) {
            $responseData['subscription'] = [
                'id' => $subscription->id,
                'subscription_number' => $subscription->subscription_number,
                'status' => $subscription->status,
                'start_date' => $subscription->start_date->format('Y-m-d'),
                'next_billing_date' => $subscription->next_billing_date->format('Y-m-d'),
                'trial_ends_at' => $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d H:i:s') : null,
            ];
        }

        if ($request->success_url || $request->cancel_url) {
            $responseData['urls'] = [
                'success_url' => $request->success_url,
                'cancel_url' => $request->cancel_url,
                'payment_url' => "http://localhost:8000/pay/{$controlNumber}"
            ];
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => $responseData
        ], 201);
    }
}
```

**Key Features:**
- ✅ Automatic: Flutterwave link generated in same request
- ✅ Optional: Controlled by `payment_gateway` parameter
- ✅ Resilient: Invoice creation succeeds even if Flutterwave fails
- ✅ Dual Support: Returns both control number AND Flutterwave link
- ✅ Third-Party Friendly: Single API call, complete response

### Priority 3: Optional Payment Verification Endpoint

Add optional endpoint for verifying payment status (useful for manual checks or debugging).

```php
// app/Http/Controllers/Api/PaymentController.php

use App\Services\FlutterwaveService;
use App\Models\Invoice;

class PaymentController extends Controller
{
    /**
     * Verify Flutterwave payment status
     * GET /api/payments/verify/{transaction_id}
     */
    public function verifyPayment(Request $request, $transactionId)
    {
        try {
            $flutterwaveService = new FlutterwaveService();
            $verificationResult = $flutterwaveService->verifyPayment($transactionId);

            return response()->json([
                'success' => true,
                'data' => $verificationResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payments for a given invoice ID
     * GET /api/payments/by-invoice/{invoice_id}
     */
    public function getByInvoice($invoice_id)
    {
        $payments = Payment::where('invoice_id', $invoice_id)->get();
        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get all payments within a date range
     * GET /api/payments?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
     */
    public function getByDateRange(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        $payments = Payment::whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
```

### Priority 4: Add Optional Routes

Add optional verification route (payment link generation happens automatically in invoice creation).

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // ... existing routes ...
    
    // Optional: Payment verification (for manual checks)
    Route::get('payments/verify/{transaction_id}', [PaymentController::class, 'verifyPayment']);
});

// Note: NO separate payment initialization endpoint needed
// Payment links are automatically generated when creating invoices
```

### Priority 5: Install Flutterwave SDK (Optional)
```bash
composer require flutterwave/flutterwave-php
```

---

## 📋 Implementation Workflow & Architecture

### Integration Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     Third-Party Application                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ POST /api/invoices
                             │ {
                             │   "product_code": "safarichat",
                             │   "customer": {...},
                             │   "payment_gateway": "both"  // or "flutterwave"
                             │ }
                             │
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│              InvoiceController::store()                          │
│                                                                   │
│  1. Validate request                                             │
│  2. Create/find customer                                         │
│  3. Create invoice                                               │
│  4. Generate control number (EcoBank)                            │
│  5. ✅ Generate Flutterwave payment link (NEW - automatic)       │
│                                                                   │
│     ┌──────────────────────────────────────────┐                │
│     │   FlutterwaveService::initializePayment() │                │
│     │   - POST to Flutterwave API               │                │
│     │   - Get payment link                      │                │
│     │   - Handle errors gracefully              │                │
│     └──────────────────────────────────────────┘                │
│                                                                   │
│  6. Return complete response                                     │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ Response:
                             │ {
                             │   "invoice": {...},
                             │   "payment_details": {
                             │     "control_number": "CN123456",
                             │     "flutterwave": {
                             │       "payment_link": "https://checkout.flutterwave.com/...",
                             │       "tx_ref": "INV-123-1234567890"
                             │     }
                             │   }
                             │ }
                             │
                             ↓
┌─────────────────────────────────────────────────────────────────┐
│                     Third-Party Application                      │
│  - Receives both payment options                                 │
│  - Redirects user to Flutterwave link                            │
│  - Or displays control number                                    │
└─────────────────────────────────────────────────────────────────┘
```

### Request Flow Example

**1. Third-Party Creates Invoice:**
```bash
curl -X POST http://localhost:8000/api/invoices \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "organization_id": 1,
    "product_code": "safarichat",
    "customer": {
      "name": "John Doe",
      "phone": "+255712345678",
      "email": "john@example.com"
    },
    "plan_code": "starter",
    "billing_cycle": "monthly",
    "payment_gateway": "flutterwave"
  }'
```

**2. System Automatically Generates Payment Link (Behind the Scenes)**

**3. Response Includes Payment Link:**
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV20260222001",
      "total": "69000.00",
      "status": "issued"
    },
    "customer": {
      "id": 45,
      "name": "John Doe",
      "phone": "+255712345678",
      "created": true
    },
    "payment_details": {
      "control_number": "CN123456789",
      "amount": "69000.00",
      "currency": "TZS",
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
        "tx_ref": "INV-123-1708595400",
        "expires_at": "2026-02-23T10:00:00Z",
        "instructions": "Click the payment link to complete payment via card, mobile money, or bank transfer"
      }
    }
  }
}
```

**4. Third-Party Redirects User to Payment Link**

**5. User Completes Payment on Flutterwave**

**6. Webhook Notifies System → Invoice Marked as Paid**

---

## 📋 Implementation Checklist

### Must Have (Critical)
- [ ] Create `app/Services/FlutterwaveService.php` class
- [ ] Add `initializePayment()` method using Flutterwave Payment Button API
- [ ] Update `InvoiceController::store()` to automatically generate Flutterwave links
- [ ] Add `payment_gateway` parameter to invoice creation validation
- [ ] Test automatic payment link generation in invoice creation
- [ ] Test webhook handling end-to-end
- [ ] Ensure invoice creation succeeds even if Flutterwave API fails (graceful degradation)
- [ ] Add proper logging for all Flutterwave API calls

### Should Have
- [ ] Add optional payment verification endpoint (`GET /api/payments/verify/{transaction_id}`)
- [ ] Implement proper error handling and retry logic
- [ ] Add response format validation
- [ ] Test with real Flutterwave test API keys
- [ ] Document the `payment_gateway` parameter in API docs
- [ ] Add configuration for default payment gateway per organization

### Nice to Have
- [ ] Install official Flutterwave PHP SDK (optional - HTTP client works fine)
- [ ] Add payment method selection in Flutterwave link (card, mobile money, bank)
- [ ] Add payment expiry tracking
- [ ] Add payment analytics dashboard
- [ ] Support for multiple currencies beyond TZS
- [ ] Recurring payment setup for subscriptions

---

## 🧪 Testing Requirements

### Test Scenario 1: Invoice Creation with Flutterwave Link
```bash
# Create invoice with Flutterwave payment gateway
POST /api/invoices
{
  "organization_id": 1,
  "product_code": "safarichat",
  "customer": {
    "name": "Test Customer",
    "phone": "+255712345678",
    "email": "test@example.com"
  },
  "payment_gateway": "flutterwave"
}

# Expected Response:
{
  "success": true,
  "data": {
    "invoice": {...},
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/...",
        "tx_ref": "INV-123-...",
        "expires_at": "2026-02-23T10:00:00Z"
      }
    }
  }
}
```

### Test Scenario 2: Both Payment Methods
```bash
# Create invoice with both control number and Flutterwave
POST /api/invoices
{
  ...
  "payment_gateway": "both"
}

# Expected Response: Both control_number AND flutterwave in payment_details
```

### Test Scenario 3: Full Payment Flow
```
1. POST /api/invoices → Get payment_link
2. Visit payment_link in browser → Complete payment
3. Check invoice status → Should be 'paid'
4. GET /api/invoices/{id} → Confirm status updated
```

### Test Scenario 4: Graceful Degradation
```bash
# Simulate Flutterwave API failure (use invalid key)
POST /api/invoices

# Expected: Invoice still created successfully
# Expected: Response includes error message but not failure
{
  "success": true,
  "data": {
    "invoice": {...},  // ✅ Created
    "payment_details": {
      "control_number": "...",  // ✅ Still available
      "flutterwave": {
        "error": "Payment link service unavailable",
        "message": "Please use control number for payment"
      }
    }
  }
}
```

### Test Scenario 5: Webhook Handling
```bash
# Simulate Flutterwave webhook
POST /api/webhooks/flutterwave
Headers: verif-hash: {hash}
Body: {
  "event": "charge.completed",
  "data": {
    "id": "FLW123",
    "amount": 69000,
    "meta": {
      "invoice_id": 123
    }
  }
}

# Expected: Invoice status → 'paid'
# Expected: Payment record created
```

---

## 📊 Completion Status

| Feature | Status | Progress | Notes |
|---------|--------|----------|-------|
| Webhook Handling | ✅ Done | 100% | Receives and processes payments |
| Database Config | ✅ Done | 100% | Gateway settings stored |
| Connectivity Test | ✅ Done | 100% | Can test API connection |
| **FlutterwaveService** | ❌ Missing | 0% | **CRITICAL** - Must implement |
| **Auto Payment Link** | ❌ Missing | 0% | **CRITICAL** - Integrate in InvoiceController |
| Payment Verification | ❌ Missing | 0% | Optional endpoint |

**Overall: 30% Complete** (3/6 core features)

---

## 🎯 Recommended Implementation Order

### Week 1: Core Service (Days 1-3)
- [ ] **Day 1:** Create `FlutterwaveService.php` with `initializePayment()` method
- [ ] **Day 2:** Test service in isolation with Flutterwave API
- [ ] **Day 3:** Add error handling and logging

### Week 2: Invoice Integration (Days 4-7)
- [ ] **Day 4:** Update `InvoiceController::store()` to use FlutterwaveService
- [ ] **Day 5:** Add `payment_gateway` parameter validation
- [ ] **Day 6:** Test invoice creation with Flutterwave link generation
- [ ] **Day 7:** Test graceful degradation (Flutterwave API fails)

### Week 3: Testing & Polish (Days 8-10)
- [ ] **Day 8:** End-to-end testing (invoice → payment → webhook → paid)
- [ ] **Day 9:** Add optional verification endpoint
- [ ] **Day 10:** Update API documentation

**Estimated Time: 2-3 weeks** for production-ready implementation

### Quick Start (Priority Tasks)
1. ✅ Create `app/Services/FlutterwaveService.php` (2 hours)
2. ✅ Add `initializePayment()` method (3 hours)
3. ✅ Update `InvoiceController::store()` (4 hours)
4. ✅ Test with real API (2 hours)

**Minimum Viable Implementation: 1-2 days**

---

## 🔗 Useful Resources

- [Flutterwave Standard Payment API](https://developer.flutterwave.com/docs/collecting-payments/standard)
- [Flutterwave Payment Button Documentation](https://developer.flutterwave.com/docs/collecting-payments/payment-button)
- [Flutterwave PHP SDK](https://github.com/Flutterwave/Flutterwave-PHP-v3)
- [Webhook Implementation Guide](https://developer.flutterwave.com/docs/integration-guides/webhooks)
- [Testing with Flutterwave](https://developer.flutterwave.com/docs/integration-guides/testing-helpers)

---

## 📝 Key Design Decisions Summary

### ✅ What This Design Achieves

1. **Automatic Integration**
   - No separate payment initialization endpoint needed
   - Payment link generated automatically when invoice created
   - Single API call for third-party apps

2. **Flexible Payment Options**
   - Support both control number (EcoBank) and Flutterwave
   - Controlled via `payment_gateway` parameter
   - Can default to specific gateway per organization

3. **Resilient Design**
   - Invoice creation succeeds even if Flutterwave API fails
   - Fallback to control number if payment link generation fails
   - Proper error logging without breaking workflow

4. **Third-Party Friendly**
   - Complete payment details in single response
   - No need for multiple API calls
   - Clear payment instructions for both methods

5. **Webhook Ready**
   - Webhook handler already implemented (30% done)
   - Payment confirmation updates invoice automatically
   - Status tracking for both payment methods

### 🎯 Implementation Method

**Using:** Flutterwave **Standard/Payment Button API**
- Returns hosted payment page link
- Supports card, mobile money, bank transfer
- Handles payment form UI
- Redirects after payment
- Sends webhook on completion

**NOT Using:** 
- ❌ Inline/Embedded payment (requires frontend SDK)
- ❌ Direct charge API (requires card details handling)
- ❌ Separate payment initialization endpoints

### 🔄 Example Response Format

```json
{
  "success": true,
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV20260222001",
      "total": "69000.00",
      "status": "issued"
    },
    "payment_details": {
      // Option 1: Control Number (EcoBank)
      "control_number": "CN123456789",
      "payment_instructions": {
        "mobile_banking": "Dial *150*01*CN123456789#",
        "internet_banking": "Login and pay with control number",
        "agent_banking": "Visit bank agent with control number"
      },
      
      // Option 2: Flutterwave Payment Link
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
        "tx_ref": "INV-123-1708595400",
        "expires_at": "2026-02-23T10:00:00Z",
        "instructions": "Click link to pay via card, mobile money, or bank"
      }
    }
  }
}
```

---

## ⚠️ Critical Note

**The system currently CANNOT:**
- ❌ Generate Flutterwave payment links for invoices
- ❌ Generate Flutterwave payment links for subscriptions
- ❌ Generate Flutterwave payment links for wallet topups
- ❌ Initialize payments via Flutterwave API

**The system CAN only:**
- ✅ Receive webhook notifications (after external payment)
- ✅ Test API connectivity
- ✅ Store gateway configuration

**Implementation Approach:**
- ✅ Automatic payment link generation IN invoice creation endpoint
- ✅ No separate payment initialization endpoint required
- ✅ Uses Flutterwave Payment Button/Standard API
- ✅ Returns hosted payment page link
- ✅ Third-party friendly single API call

**Conclusion:** Flutterwave integration is **NOT production-ready**. Payment link generation must be implemented before use. Follow the implementation checklist above for automatic payment link generation within invoice creation flow.
