# Webhook Manual Dispatch Command

This document explains how to manually push webhook data to third-party applications when automatic delivery fails or for testing purposes.

## Command Overview

```bash
php artisan webhooks:dispatch
```

## Use Cases

1. **Resend a failed webhook delivery** - Retry a specific webhook that failed
2. **Bulk retry failed webhooks** - Retry all failed webhook deliveries
3. **Manual webhook dispatch** - Trigger webhooks manually for testing or data recovery

---

## Option 1: Resend a Specific Webhook Delivery

Use this when you know the specific delivery ID that failed.

```bash
php artisan webhooks:dispatch --delivery=123
```

**Steps:**
1. Find the delivery ID from the `webhook_deliveries` table or logs
2. Run the command with the delivery ID
3. Confirm when prompted
4. The webhook will be resent with the original payload

**Example:**
```bash
# Resend delivery #456
php artisan webhooks:dispatch --delivery=456

# Output:
# 🔄 Resending webhook delivery #456...
# Webhook: Payment Success Notification
# URL: https://client-app.com/webhooks/billing
# Event: payment.success
# Status: failed
# Attempts: 2
# Do you want to resend this webhook? (yes/no) [yes]:
# ✅ Webhook resent successfully
# HTTP Status: 200
# Response Time: 234ms
```

---

## Option 2: Retry All Failed Webhooks

Use this to retry all failed webhook deliveries that are due for retry.

```bash
# Retry all failed webhooks
php artisan webhooks:dispatch --failed

# Retry failed webhooks for a specific product
php artisan webhooks:dispatch --failed --product=1
```

**Example:**
```bash
php artisan webhooks:dispatch --failed

# Output:
# 🔄 Retrying all failed webhook deliveries...
# Found 15 failed delivery(s) to retry
# [████████████████████████████] 15/15
# ✅ Completed: 12 succeeded, 3 failed
```

---

## Option 3: Manually Dispatch New Webhook

Use this to trigger webhooks manually for specific events.

### Payment Success Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=payment.success \
  --payment=456
```

### Payment Failed Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=payment.failed \
  --payment=789
```

### Invoice Created Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=invoice.created \
  --invoice=123
```

### Invoice Paid Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=invoice.paid \
  --invoice=123
```

### Subscription Created Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=subscription.created \
  --subscription=45
```

### Subscription Cancelled Event

```bash
php artisan webhooks:dispatch \
  --product=1 \
  --event=subscription.cancelled \
  --subscription=45
```

**Example Output:**
```bash
php artisan webhooks:dispatch --product=1 --event=payment.success --payment=456

# Output:
# 📦 Product: Hospital Management System (ID: 1)
# Event: payment.success
# Payload keys: event, payment_id, amount, currency, customer, invoice
# Payment: #456 - 50000.00 TZS
# Do you want to dispatch this webhook? (yes/no) [yes]:
# ✅ Dispatched to 2 webhook(s)
# ✅ 2 succeeded
```

---

## Available Options

| Option | Description | Required | Example |
|--------|-------------|----------|---------|
| `--delivery` | Resend a specific delivery by ID | No | `--delivery=123` |
| `--failed` | Retry all failed deliveries | No | `--failed` |
| `--product` | Product ID to dispatch webhooks for | No* | `--product=1` |
| `--event` | Event type to trigger | No** | `--event=payment.success` |
| `--payment` | Payment ID for payload | No*** | `--payment=456` |
| `--invoice` | Invoice ID for payload | No*** | `--invoice=123` |
| `--subscription` | Subscription ID for payload | No*** | `--subscription=45` |

\* Required when using manual dispatch  
\** Required when using `--product`  
\*** At least one resource ID required when using `--product` and `--event`

---

## Supported Event Types

### Payment Events
- `payment.success` - Payment completed successfully
- `payment.failed` - Payment attempt failed

### Invoice Events
- `invoice.created` - New invoice generated
- `invoice.paid` - Invoice payment completed

### Subscription Events
- `subscription.created` - New subscription started
- `subscription.cancelled` - Subscription terminated

---

## Scheduling Automatic Retries

Add this to your `app/Console/Kernel.php` to automatically retry failed webhooks:

```php
protected function schedule(Schedule $schedule)
{
    // Retry failed webhooks every 5 minutes
    $schedule->command('webhooks:retry')
        ->everyFiveMinutes()
        ->withoutOverlapping();
}
```

Or use the existing command:
```bash
php artisan webhooks:retry
```

---

## Finding Delivery IDs

### Via Database

```sql
-- Find failed deliveries
SELECT id, custom_webhook_id, event_type, status, attempts, error_message, created_at
FROM webhook_deliveries
WHERE status = 'failed'
ORDER BY created_at DESC
LIMIT 20;

-- Find deliveries for a specific webhook
SELECT id, event_type, status, http_status, attempts, created_at
FROM webhook_deliveries
WHERE custom_webhook_id = 5
ORDER BY created_at DESC;
```

### Via Tinker

```bash
php artisan tinker
```

```php
// Find failed deliveries
$failed = \App\Models\WebhookDelivery::where('status', 'failed')
    ->with('customWebhook')
    ->latest()
    ->take(10)
    ->get();

foreach ($failed as $delivery) {
    echo "ID: {$delivery->id} | Webhook: {$delivery->customWebhook->name} | Event: {$delivery->event_type}\n";
}

// Resend programmatically
$delivery = \App\Models\WebhookDelivery::find(123);
$service = app(\App\Services\WebhookDispatchService::class);
$result = $service->retryDelivery($delivery);
print_r($result);
```

---

## Logs

All webhook dispatch attempts are logged in:
- **File**: `storage/logs/laravel.log`
- **Log Prefix**: `[WEBHOOK DISPATCH]`, `[WEBHOOK RETRY]`

Example log entries:
```
[2026-03-24 10:15:23] production.INFO: 📤 [WEBHOOK DISPATCH] Sending webhook {"webhook_id":5,"delivery_id":123,"event_type":"payment.success","url":"https://client.com/webhook"}
[2026-03-24 10:15:24] production.INFO: ✅ [WEBHOOK DISPATCH] Webhook delivered {"delivery_id":123,"status_code":200,"duration_ms":234}
```

---

## Troubleshooting

### Error: "Webhook delivery #123 not found"
**Solution**: Verify the delivery ID exists in the database:
```sql
SELECT * FROM webhook_deliveries WHERE id = 123;
```

### Error: "Product #1 not found"
**Solution**: Verify the product ID exists:
```sql
SELECT * FROM products WHERE id = 1;
```

### Error: "Payment #456 not found"
**Solution**: Verify the resource ID exists:
```sql
SELECT * FROM payments WHERE id = 456;
```

### Webhook still failing after retry
**Possible causes:**
1. Third-party endpoint is down
2. SSL certificate issues (try `verify_ssl = false` in webhook config)
3. Firewall blocking outgoing requests
4. Invalid payload format expected by third party

**Debug steps:**
1. Check webhook configuration in `custom_webhooks` table
2. Test the endpoint manually with cURL:
   ```bash
   curl -X POST https://client.com/webhook \
     -H "Content-Type: application/json" \
     -d '{"event":"payment.success","test":true}'
   ```
3. Review response in `webhook_deliveries.response_body`
4. Check third-party application logs

---

## Best Practices

1. **Monitor Failed Webhooks**: Regularly check for failed deliveries
   ```bash
   php artisan webhooks:dispatch --failed
   ```

2. **Test Before Production**: Use manual dispatch to test webhook integrations
   ```bash
   php artisan webhooks:dispatch --product=1 --event=payment.success --payment=TEST_ID
   ```

3. **Set Up Alerting**: Monitor webhook failure rates in your monitoring system

4. **Document Third-Party Requirements**: Keep documentation of expected payload formats

5. **Use Staging Webhooks**: Configure separate webhook URLs for staging/production

---

## Related Commands

```bash
# Automatic retry (runs via cron)
php artisan webhooks:retry

# View webhook configuration
php artisan tinker
>>> \App\Models\CustomWebhook::with('product')->get()

# View recent deliveries
php artisan tinker
>>> \App\Models\WebhookDelivery::latest()->take(10)->get()
```

---

## API Alternative

You can also trigger webhooks via the API:

```http
POST /api/v1/products/{product}/webhooks/{webhook}/test
Authorization: Bearer YOUR_API_KEY
Content-Type: application/json

{
  "event": "payment.success",
  "test_mode": true
}
```

See the [API Documentation](../docs/api-documentation.md) for details.
