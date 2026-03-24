# Stripe Payment Error Fix Guide

## Error Summary
You're experiencing a 400 error from Stripe Elements API and the Payment Element fails to mount.

## Root Causes & Solutions

### **Issue 1: Live/Test Key Mismatch** ⚠️ MOST COMMON

Your payment intent was created with one key type but you're using a different type:
- Payment Intent ID: `pi_3TBqv8...` (looks like LIVE mode)
- Publishable Key: `pk_live_51ScAiTFb1...` (LIVE mode)

**Check your `.env` file:**

```bash
# Make sure these match (both test OR both live)
STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLISHABLE_KEY=pk_live_...

# OR for testing
# STRIPE_SECRET_KEY=sk_test_...
# STRIPE_PUBLISHABLE_KEY=pk_test_...
```

**Solution:**
1. Open `.env` file
2. Verify BOTH keys are from the same mode (test or live)
3. Clear config cache: `php artisan config:clear`
4. Restart your server

### **Issue 2: Expired Payment Intent**

Payment Intents expire after 24 hours. If the invoice is old, the client_secret is invalid.

**Solution: Add expiry check and regeneration**

Edit: `app/Http/Controllers/PaymentPageController.php`

```php
private function resolveStripeIntentFromControlNumbers(Invoice $invoice): array
{
    // ... existing code ...

    foreach ($controlNumbers as $controlNumber) {
        if (!$this->isStripeControlNumberForInvoice($controlNumber, $invoice->id)) {
            continue;
        }

        $metadata = $this->normalizeControlNumberMetadata($controlNumber->metadata);
        $clientSecret = (string) ($this->extractClientSecretFromControlNumberMetadata($metadata) ?? '');

        if ($clientSecret === '') {
            continue;
        }

        // ADD THIS: Check if payment intent is expired (older than 23 hours)
        $createdAt = $controlNumber->created_at;
        $isExpired = $createdAt && now()->diffInHours($createdAt) > 23;
        
        if ($isExpired) {
            Log::info('Payment intent expired, needs regeneration', [
                'control_number_id' => $controlNumber->id,
                'created_at' => $createdAt,
            ]);
            continue; // Skip expired intents
        }

        $paymentIntentId = (string) data_get($metadata, 'payment_intent_id', '');

        return [
            'payment_intent_id' => $paymentIntentId,
            'client_secret' => $clientSecret,
        ];
    }

    return [
        'payment_intent_id' => '',
        'client_secret' => '',
    ];
}
```

### **Issue 3: Payment Already Completed**

If the payment intent status is already `succeeded` or `canceled`, you can't reuse it.

**Solution: Add status validation in the blade file**

Edit: `resources/views/billing/payment.blade.php`

Add before the script tag:

```php
@if(empty($clientSecret) || trim($clientSecret) === '')
    <div class="alert alert-warning">
        <h3>Payment Not Ready</h3>
        <p>Unable to initialize payment. Please contact support.</p>
        <p>Invoice: {{ $invoice->invoice_number }}</p>
    </div>
    @php($hidePaymentForm = true)
@endif
```

Then wrap the existing script in:

```php
@if(!($hidePaymentForm ?? false))
<script>
    // existing stripe code...
</script>
@endif
```

### **Issue 4: Empty Client Secret**

The payment page might be loading without creating a payment intent first.

**Solution: Auto-generate payment intent if missing**

Create a helper method in `PaymentPageController.php`:

```php
public function show(Invoice $invoice): View
{
    $invoice->load(['invoiceItems.pricePlan', 'customer']);
    $customer = $invoice->customer;

    $stripeIntent = $this->resolveStripeIntentFromControlNumbers($invoice);
    
    // If no valid client secret found, create one
    if (empty($stripeIntent['client_secret'])) {
        $stripeIntent = $this->createNewPaymentIntent($invoice);
    }

    return view('billing.payment', [
        'invoice' => $invoice,
        'customer' => $customer,
        'clientSecret' => (string) ($stripeIntent['client_secret'] ?: null),
        'stripePublishableKey' => config('services.stripe.publishable_key'),
    ]);
}

private function createNewPaymentIntent(Invoice $invoice): array
{
    try {
        // Call the existing API endpoint to create payment intent
        $invoiceController = app(\App\Http\Controllers\Api\InvoiceController::class);
        
        // Get the first product from invoice items
        $product = $invoice->invoiceItems->first()?->pricePlan?->product;
        
        if (!$product) {
            return ['payment_intent_id' => '', 'client_secret' => ''];
        }

        $result = $invoiceController->createStripePaymentIntent($invoice->id, $product->id);
        
        if ($result['success'] ?? false) {
            $metadata = json_decode($result['control_number']['metadata'] ?? '{}', true);
            return [
                'payment_intent_id' => $metadata['payment_intent_id'] ?? '',
                'client_secret' => $metadata['client_secret'] ?? '',
            ];
        }
    } catch (\Exception $e) {
        Log::error('Failed to create payment intent', [
            'invoice_id' => $invoice->id,
            'error' => $e->getMessage(),
        ]);
    }

    return ['payment_intent_id' => '', 'client_secret' => ''];
}
```

## Quick Diagnostic Steps

### **Step 1: Check your environment**
```bash
cd c:\xampp\htdocs\billing
php artisan tinker
```

In Tinker:
```php
// Check config
echo config('services.stripe.publishable_key');
echo config('services.stripe.secret');

// Check if keys match mode
echo substr(config('services.stripe.publishable_key'), 0, 7); // Should be pk_test or pk_live
echo substr(config('services.stripe.secret'), 0, 7);          // Should be sk_test or sk_live
```

### **Step 2: Check the invoice's control number**
```php
$invoice = \App\Models\Invoice::find(YOUR_INVOICE_ID);
$controlNumbers = \App\Models\ControlNumber::where('customer_id', $invoice->customer_id)->get();

foreach($controlNumbers as $cn) {
    $meta = json_decode($cn->metadata, true);
    echo "Control Number ID: {$cn->id}\n";
    echo "Created: {$cn->created_at}\n";
    echo "Gateway ID: {$cn->organization_payment_gateway_integration_id}\n";
    echo "Client Secret: " . ($meta['client_secret'] ?? 'MISSING') . "\n";
    echo "Payment Intent: " . ($meta['payment_intent_id'] ?? 'MISSING') . "\n";
    echo "---\n";
}
```

### **Step 3: Test with a fresh payment intent**
```bash
# Clear everything and test
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

Then navigate to your invoice payment page with fresh session.

## Immediate Fix (Quick & Dirty)

If you need to fix this RIGHT NOW for testing:

1. **Delete old control numbers:**
```php
// In Tinker
\App\Models\ControlNumber::where('customer_id', YOUR_CUSTOMER_ID)->delete();
```

2. **Create fresh invoice** or **regenerate payment intent via API**

3. **Verify environment variables match:**
```bash
# .env file
STRIPE_SECRET_KEY=sk_test_YOUR_TEST_SECRET
STRIPE_PUBLISHABLE_KEY=pk_test_YOUR_TEST_PUBLIC
```

4. **Clear cache:**
```bash
php artisan config:clear
```

## Google Pay Warning (Optional Fix)

To suppress the Google Pay manifest warning, add this to your payment page `<head>`:

```html
<link rel="payment-method-manifest" href="{{ url('/payment-manifest.json') }}">
```

Then create `public/payment-manifest.json`:
```json
{
  "default_applications": [],
  "supported_origins": ["*"]
}
```

## Still Not Working?

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

Enable Stripe debug mode in your blade file:
```javascript
// Add this after stripe initialization
stripe.on('error', (event) => {
    console.error('Stripe Error:', event);
});
```

## Contact Support If:
- Keys are correct but still 400 error
- Payment intents are fresh but failing
- Multiple invoices affected

Then provide:
- Invoice ID
- Control Number ID
- Full error from browser console
- Laravel log snippet
