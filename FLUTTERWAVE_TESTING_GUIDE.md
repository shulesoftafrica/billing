# Flutterwave Integration Testing Guide

## Overview
This document provides testing instructions for the newly implemented Flutterwave payment link integration.

## What Was Implemented

### 1. FlutterwaveService Class
- **Location**: `app/Services/FlutterwaveService.php`
- **Methods**:
  - `initializePayment()` - Generate Flutterwave payment link
  - `verifyPayment()` - Verify payment status (optional)
  - `isActive()` - Check if gateway is configured
  - `getConfig()` - Get gateway configuration

### 2. InvoiceController Enhancement
- **Location**: `app/Http/Controllers/Api/InvoiceController.php`
- **Changes**:
  - Added `payment_gateway` parameter (control_number, flutterwave, both)
  - Automatic payment link generation on invoice creation
  - Resilient error handling (invoice succeeds even if Flutterwave fails)
  - Comprehensive logging

### 3. PaymentController Enhancement
- **Location**: `app/Http/Controllers/Api/PaymentController.php`
- **New Method**: `verifyFlutterwavePayment()` - Optional manual verification

### 4. API Routes
- **Location**: `routes/api.php`
- **New Route**: `GET /api/payments/verify/{transaction_id}`

## Prerequisites

Before testing, ensure:
1. Flutterwave gateway is configured in `payment_gateways` table with valid credentials:
   - `public_key`
   - `secret_key` 
   - `encryption_key`
2. Gateway is marked as `active = true`
3. Database is seeded with test data (organizations, customers, products)

## Test Scenarios

### Test 1: Invoice with Control Number Only (Default)
**Request:**
```bash
POST /api/invoices
Content-Type: application/json
Authorization: Bearer {token}

{
  "organization_id": 1,
  "product_code": "safarichat",
  "customer": {
    "name": "John Doe",
    "phone": "0712345678",
    "email": "john@example.com"
  },
  "invoice_type": "subscription",
  "amount": 69000
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Control number generated successfully",
  "data": {
    "invoice": {...},
    "customer": {...},
    "payment_details": {
      "control_number": "9912345678",
      "amount": "69000.00",
      "currency": "TZS",
      "expires_at": "2026-03-01T...",
      "payment_instructions": {...}
    }
  }
}
```

---

### Test 2: Invoice with Flutterwave Payment Link
**Request:**
```bash
POST /api/invoices
Content-Type: application/json
Authorization: Bearer {token}

{
  "organization_id": 1,
  "product_code": "safarichat",
  "customer": {
    "name": "Jane Smith",
    "phone": "0723456789",
    "email": "jane@example.com"
  },
  "invoice_type": "subscription",
  "amount": 69000,
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Control number generated successfully",
  "data": {
    "invoice": {...},
    "customer": {...},
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
        "tx_ref": "INV20260222001-1708595400",
        "expires_at": "2026-02-23T10:00:00Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      }
    }
  }
}
```

---

### Test 3: Invoice with Both Payment Methods
**Request:**
```bash
POST /api/invoices
Content-Type: application/json
Authorization: Bearer {token}

{
  "organization_id": 1,
  "product_code": "safarichat",
  "customer": {
    "name": "Bob Johnson",
    "phone": "0734567890",
    "email": "bob@example.com"
  },
  "invoice_type": "subscription",
  "amount": 69000,
  "payment_gateway": "both",
  "currency": "TZS"
}
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Control number generated successfully",
  "data": {
    "invoice": {...},
    "customer": {...},
    "payment_details": {
      "control_number": "9912345678",
      "amount": "69000.00",
      "currency": "TZS",
      "expires_at": "2026-03-01T...",
      "payment_instructions": {...},
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
        "tx_ref": "INV20260222002-1708595420",
        "expires_at": "2026-02-23T10:00:00Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      }
    }
  }
}
```

---

### Test 4: Graceful Failure (Flutterwave API Error)
**Scenario**: Flutterwave API is down or credentials are invalid

**Expected Behavior**:
- Invoice creation succeeds
- Control number is generated
- Response includes `flutterwave_error` field
- Error is logged (check `storage/logs/laravel.log`)

**Expected Response:**
```json
{
  "success": true,
  "message": "Control number generated successfully",
  "data": {
    "invoice": {...},
    "customer": {...},
    "payment_details": {
      "flutterwave_error": "Flutterwave gateway not configured or inactive"
    }
  }
}
```

---

### Test 5: Payment Verification (Optional)
**Request:**
```bash
GET /api/payments/verify/{transaction_id}
Authorization: Bearer {token}
```

**Example:**
```bash
GET /api/payments/verify/12345678
```

**Expected Success Response:**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "data": {
    "transaction_id": 12345678,
    "tx_ref": "INV20260222001-1708595400",
    "amount": 69000,
    "currency": "TZS",
    "status": "successful",
    "payment_type": "card",
    "charged_amount": 69000
  }
}
```

**Expected Failure Response:**
```json
{
  "success": false,
  "message": "Payment verification failed or payment not successful"
}
```

---

## Manual Testing Steps

### Step 1: Test API Connectivity
```bash
# Using tinker to verify FlutterwaveService loads
php artisan tinker

$service = new \App\Services\FlutterwaveService();
$service->isActive(); // Should return true if gateway is configured
$service->getConfig(); // Should return array with keys
exit
```

### Step 2: Test Invoice Creation with Postman/curl

**Using curl:**
```bash
# Get authentication token first
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@shulesoft.africa",
    "password": "password123",
    "device_name": "PostmanTest"
  }'

# Copy the bearer_token from response

# Create invoice with Flutterwave
curl -X POST http://localhost:8000/api/invoices \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {YOUR_TOKEN}" \
  -d '{
    "organization_id": 1,
    "product_code": "safarichat",
    "customer": {
      "name": "Test Customer",
      "phone": "0712345678",
      "email": "test@example.com"
    },
    "invoice_type": "subscription",
    "amount": 69000,
    "payment_gateway": "flutterwave"
  }'
```

### Step 3: Check Logs
```bash
# Check Laravel logs for Flutterwave integration messages
tail -f storage/logs/laravel.log

# Look for:
# - "Flutterwave payment initialized successfully"
# - "Flutterwave payment link generated successfully"
# - Any error messages
```

### Step 4: Test Payment Link
- Copy the `payment_link` from the response
- Open in browser
- Verify Flutterwave checkout page loads
- Test with Flutterwave test cards (see Flutterwave docs)

### Step 5: Test Verification Endpoint
```bash
# After successful payment, verify with transaction ID
curl -X GET http://localhost:8000/api/payments/verify/{transaction_id} \
  -H "Authorization: Bearer {YOUR_TOKEN}"
```

---

## Validation Checklist

- [ ] Invoice created successfully with control_number gateway
- [ ] Invoice created successfully with flutterwave gateway
- [ ] Invoice created successfully with both gateways
- [ ] Payment link is valid Flutterwave URL
- [ ] Invoice creation succeeds even when Flutterwave API fails
- [ ] Errors are properly logged
- [ ] Verification endpoint returns correct data
- [ ] Webhook handler processes Flutterwave callbacks (already implemented)

---

## Configuration Check

### Verify PaymentGateway Record
```sql
SELECT * FROM payment_gateways WHERE name = 'Flutterwave';
```

**Should return:**
- `active = 1`
- `config` JSON with `secret_key`, `public_key`, `encryption_key`

### Update Configuration (if needed)
```php
// Using tinker
php artisan tinker

$gateway = \App\Models\PaymentGateway::where('name', 'Flutterwave')->first();
$gateway->config = [
    'public_key' => 'FLWPUBK_TEST-xxxxxxxxxxxxxxxx',
    'secret_key' => 'FLWSECK_TEST-xxxxxxxxxxxxxxxx',
    'encryption_key' => 'FLWSECK_TESTxxxxxxxx',
    'webhook_url' => '/api/webhooks/flutterwave'
];
$gateway->active = true;
$gateway->save();
exit
```

---

## Troubleshooting

### Issue: "Flutterwave gateway not configured"
**Solution**: Check if gateway exists and is active in database

### Issue: "Payment link generation failed"
**Solutions**:
- Check Flutterwave API credentials
- Verify secret_key is correct
- Check network connectivity
- Review `storage/logs/laravel.log` for API error details

### Issue: Invalid email error
**Solution**: Ensure customer email is provided or update FlutterwaveService to use fallback email

### Issue: Amount validation error
**Solution**: Ensure amount is numeric and greater than 0

---

## Next Steps

1. **Test with Real Flutterwave Credentials**: Replace test keys with production keys
2. **Test Webhook Handler**: Use Flutterwave webhook simulator
3. **Monitor Production Logs**: Track payment initialization and verification
4. **Add to Documentation**: Update API documentation with new payment_gateway parameter

---

## Implementation Summary

✅ **Completed Features:**
- FlutterwaveService class with payment initialization
- Automatic payment link generation in invoice creation
- Flexible payment gateway parameter (control_number, flutterwave, both)
- Resilient error handling
- Comprehensive logging
- Optional payment verification endpoint
- Route configuration

✅ **Integration Approach:**
- Single API call creates invoice and payment link
- No separate initialization endpoint needed
- Third-party friendly
- Backward compatible (defaults to control_number)

✅ **Production Ready:**
- Error handling prevents invoice creation failures
- Logging for debugging
- Configuration-based gateway selection
- Webhook already implemented (30% existing work)

---

**Implementation Date**: February 22, 2026
**Status**: ✅ Complete and Ready for Testing
