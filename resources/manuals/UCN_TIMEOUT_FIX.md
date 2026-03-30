# UCN Creation Timeout Fix

## Problem
UCN (Unified Control Number) creation was failing for wallet-based invoices with the error:
```
Failed to get credit UCN
cURL error 28: Operation timed out after 30002 milliseconds with 0 bytes received
for https://api.safaribank.africa/api/invoices
```

## Root Cause
The `createControlNumber()` and `createEcobankToken()` methods in `InvoiceController.php` were **not setting explicit cURL timeouts**. This caused them to use PHP's default timeout of **30 seconds**, which was insufficient for:

1. EcoBank API token generation
2. EcoBank API control number creation (UCN/QR code generation)
3. Potential network latency issues

## Files Changed
### `app/Http/Controllers/Api/InvoiceController.php`

#### 1. createControlNumber() - Lines 597-608
**Before:**
```php
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: ' . $this->origin,
]);
```

**After:**
```php
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_TIMEOUT, 90);          // 90 second timeout for UCN creation
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // 30 second connection timeout
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: ' . $this->origin,
]);
```

#### 2. createEcobankToken() - Lines 696-705
**Before:**
```php
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: ' . $this->origin,
]);
```

**After:**
```php
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);          // 60 second timeout for token generation
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // 30 second connection timeout
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: ' . $this->origin,
]);
```

## Timeout Settings Explained

### CURLOPT_TIMEOUT
- **Purpose:** Maximum time the entire request can take
- **Token Generation:** 60 seconds (authentication should be fast)
- **UCN Creation:** 90 seconds (QR code generation + database writes)

### CURLOPT_CONNECTTIMEOUT
- **Purpose:** Maximum time to establish initial connection
- **Setting:** 30 seconds for both (network connection phase)
- **Why:** Prevents hanging on network issues without waiting for full timeout

## Related Components

### UCNPaymentService (Already Has Timeout)
The webhook notification service already has proper timeout handling:
```php
// Line 174 in app/Services/UCNPaymentService.php
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
```

This handles notifications to organization endpoints (like `api.safaribank.africa/api/invoices`) **after** UCN is created.

## Testing Recommendations

1. **Test Wallet Invoice Creation:**
   ```bash
   POST /api/v1/invoices
   {
     "organization_id": 1,
     "customer": {...},
     "products": [{"price_plan_id": 15, "amount": 50000}],  # Wallet product
     "currency": "TZS"
   }
   ```

2. **Monitor Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep "UCN\|Control number"
   ```

3. **Check for Timeout Errors:**
   - Should no longer see "Operation timed out after 30002 milliseconds"
   - UCN creation should complete within 90 seconds
   - Token generation should complete within 60 seconds

## Potential Issues to Monitor

### If Timeouts Still Occur:
1. **EcoBank API Performance:** Check if their API is responding slowly
2. **Network Issues:** Verify server can reach `payservice.ecobank.com`
3. **Organization Notification:** Check if `api.safaribank.africa/api/invoices` is responsive

### Error Handling:
The code already logs cURL errors:
```php
if ($curlError) {
    Log::error('EcoBank API cURL Error: ' . $curlError);
    return [
        'success' => false,
        'message' => 'API request failed: ' . $curlError
    ];
}
```

## Monitoring Commands

```bash
# Check recent UCN creation attempts
grep "Control number" storage/logs/laravel.log | tail -20

# Check for timeout errors
grep "timed out" storage/logs/laravel.log | tail -10

# Monitor real-time
tail -f storage/logs/laravel.log | grep -E "UCN|Control number|timed out"
```

## Summary
✅ **Fixed:** Added explicit 90-second timeout for UCN creation  
✅ **Fixed:** Added explicit 60-second timeout for token generation  
✅ **Fixed:** Added 30-second connection timeouts for both operations  
✅ **Already Handled:** Webhook notifications have 60-second timeout  

**Expected Result:** Wallet-based invoices should now successfully create UCNs without timing out at 30 seconds.
