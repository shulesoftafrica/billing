# Webhook Integration Guide

This document describes every outgoing webhook event this billing system sends, the exact JSON payload for each event, the HTTP headers included on every request, how to verify the request is genuine, and what your endpoint must return.

---

## Table of Contents

1. [How Webhooks Work](#1-how-webhooks-work)
2. [Request Format](#2-request-format)
3. [Signature Verification](#3-signature-verification)
4. [What Your Endpoint Must Return](#4-what-your-endpoint-must-return)
5. [Retry Behaviour](#5-retry-behaviour)
6. [Event Filtering (Wildcard Support)](#6-event-filtering-wildcard-support)
7. [Event Reference](#7-event-reference)
   - [payment.success](#paymentsuccess)
   - [payment.failed](#paymentfailed)
   - [subscription.created](#subscriptioncreated)
   - [subscription.renewed](#subscriptionrenewed)
   - [subscription.cancelled](#subscriptioncancelled)
   - [subscription.expired](#subscriptionexpired)
   - [subscription.upgraded](#subscriptionupgraded)
   - [credits.purchased](#creditspurchased)
8. [Shared Object Schemas](#8-shared-object-schemas)
9. [Testing & Replay](#9-testing--replay)

---

## 1. How Webhooks Work

When a billable event occurs the billing platform:

1. Builds a signed JSON payload for the event.
2. Makes an **HTTP POST** request to every active webhook URL registered for that product that matches the event type.
3. Records the delivery attempt (status, HTTP response code, response body, duration).
4. Retries on failure according to the webhook's configured retry count.

Webhooks are per-product. A product can have multiple webhook endpoints, each listening to a different set of events.

---

## 2. Request Format

Every webhook delivery is an HTTP POST with the following headers:

| Header | Example value | Description |
|---|---|---|
| `Content-Type` | `application/json` | Always JSON |
| `X-Webhook-Signature` | `a3f9d2...` | HMAC-SHA256 signature (see §3) |
| `X-Event-Type` | `payment.success` | The event name |
| `X-Webhook-ID` | `7` | The ID of the webhook configuration that triggered this delivery |
| `X-Delivery-ID` | `142` | Unique ID of this delivery attempt — use for deduplication |
| `User-Agent` | `BillingPlatform-Webhook/1.0` | Fixed user-agent string |

Custom headers configured on the webhook (e.g. `Authorization: Bearer …`) are merged on top of these defaults.

---

## 3. Signature Verification

The `X-Webhook-Signature` header contains an **HMAC-SHA256** hex digest of the raw request body, keyed with the webhook's secret.

```
signature = HMAC-SHA256(raw_request_body, webhook_secret)
```

**Important:** compute the HMAC over the **raw bytes** of the request body before any JSON parsing. Do not reserialise the parsed object — whitespace differences will produce a different digest.

### Verification examples

**PHP**
```php
$computedSig = hash_hmac('sha256', $rawBody, $webhookSecret);
if (!hash_equals($computedSig, $request->header('X-Webhook-Signature'))) {
    http_response_code(401);
    exit;
}
```

**Node.js**
```js
const crypto = require('crypto');
const computed = crypto
    .createHmac('sha256', webhookSecret)
    .update(rawBody)          // Buffer or string, before JSON.parse
    .digest('hex');

if (computed !== req.headers['x-webhook-signature']) {
    return res.status(401).send('Invalid signature');
}
```

**Python**
```python
import hmac, hashlib

computed = hmac.new(
    webhook_secret.encode(),
    raw_body,                 # bytes, before json.loads
    hashlib.sha256
).hexdigest()

if not hmac.compare_digest(computed, request.headers['X-Webhook-Signature']):
    return Response(status=401)
```

---

## 4. What Your Endpoint Must Return

Return any **HTTP 2xx** status code (200, 201, 202, etc.) to acknowledge successful receipt. The response body is recorded but not inspected.

Any **non-2xx** response (4xx, 5xx, connection error, or timeout) is treated as a failure and triggers a retry.

**Respond quickly.** The billing platform waits up to the webhook's configured timeout (default: 30 seconds). If your processing takes longer, respond with `202 Accepted` immediately and handle the payload asynchronously.

```
// Minimal successful acknowledgement
HTTP/1.1 200 OK
Content-Type: application/json

{ "received": true }
```

### Deduplication

Each delivery has a unique `X-Delivery-ID` header and a unique `event_id` field in the payload. If your endpoint receives the same `event_id` more than once (due to a retry after a timeout), process it only once.

---

## 5. Retry Behaviour

If a delivery fails the platform retries up to the webhook's configured `retry_count`. On retry, a **fresh payload is rebuilt** from the original source record — the stored payload is not simply replayed. This means amounts, statuses, and timestamps in a retry reflect the current state of the record at retry time.

The `webhooks:retry` artisan command performs a full sweep:

- **Phase 1** — retries all existing `failed` / `pending` delivery records
- **Phase 2** — sweeps all `cleared` payments and `failed` payments that have no successful delivery on record for each active webhook
- **Phase 3** — sweeps all subscriptions matching each event type that have no successful delivery on record

---

## 6. Event Filtering (Wildcard Support)

When registering a webhook you specify which events it listens to via the `events` array. Wildcards are supported:

| Pattern | Matches |
|---|---|
| `payment.success` | Exact event only |
| `payment.*` | `payment.success`, `payment.failed` |
| `subscription.*` | All six subscription events |
| `*` | Every event |
| *(empty / null)* | Every event |

---

## 7. Event Reference

All payloads share a common envelope:

```json
{
  "event":       "payment.success",
  "event_id":    "evt_68026f3a4b1e2",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,
  ...event-specific fields...
}
```

| Field | Type | Description |
|---|---|---|
| `event` | string | Event name — use this to route logic in your handler |
| `event_id` | string | Globally unique ID for this event — use for deduplication |
| `timestamp` | ISO 8601 | When the event was triggered |
| `api_version` | string | Payload schema version |
| `customer_id` | integer | Shortcut to the customer — also present inside `customer.id` |

---

### payment.success

Triggered when a payment is confirmed as cleared by the payment gateway.

```json
{
  "event":       "payment.success",
  "event_id":    "evt_68026f3a4b1e2",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product": {
    "id": 3,
    "name": "School Management System",
    "product_code": "SMS-001",
    "organization_id": 1,
    "status": "active"
  },

  "organization": {
    "id": 1,
    "name": "Shule Soft Africa"
  },

  "payment": {
    "id": 187,
    "transaction_id":    "pi_3OqXyz",
    "amount":            150000.00,
    "currency":          "TZS",
    "status":            "success",
    "payment_method":    "card",
    "gateway":           "stripe",
    "gateway_reference": "pi_3OqXyz",
    "gateway_fee":       4500.00,
    "net_amount":        145500.00,
    "description":       "Invoice INV-2026-0042 payment",
    "paid_at":           "2026-03-29T10:14:58+00:00",
    "created_at":        "2026-03-29T10:14:50+00:00"
  },

  "invoice": {
    "id":             99,
    "invoice_number": "INV-2026-0042",
    "subtotal":       130435.00,
    "tax_total":      19565.00,
    "total":          150000.00,
    "amount_paid":    150000.00,
    "amount_due":     0.00,
    "currency":       "TZS",
    "status":         "paid",
    "due_date":       "2026-04-05",
    "issued_at":      "2026-03-29T08:00:00+00:00",
    "paid_at":        "2026-03-29T10:14:58+00:00",
    "items": [
      {
        "id":             201,
        "description":    "Term 1 Fees",
        "quantity":       1,
        "unit_price":     130435.00,
        "total":          130435.00,
        "price_plan_id":  5,
        "price_plan_name":"Standard Term Plan"
      }
    ],
    "ucn":             "9920240001234",
    "control_number":  "9920240001234",
    "control_numbers": ["9920240001234"]
  },

  "customer": {
    "id":         42,
    "product_id": 3,
    "name":       "Mwanafunzi Primary School",
    "email":      "accounts@mwanafunzi.ac.tz",
    "phone":      "+255712345678",
    "status":     "active"
  },

  "subscription": {
    "id":                   18,
    "status":               "active",
    "price_plan_id":        5,
    "price_plan_name":      "Standard Term Plan",
    "billing_interval":     "quarterly",
    "amount":               150000.00,
    "currency":             "TZS",
    "current_period_start": "2026-01-01",
    "current_period_end":   "2026-03-31",
    "next_billing_date":    "2026-04-01",
    "trial_ends_at":        null,
    "canceled_at":          null
  },

  "gateway_details": {
    "stripe": {
      "payment_intent_id":  "pi_3OqXyz",
      "charge_id":          "ch_3OqXyz",
      "payment_method_id":  "pm_3OqXyz",
      "customer_id":        "cus_Stripe123",
      "last4":              "4242",
      "brand":              "visa",
      "country":            "TZ",
      "receipt_url":        "https://pay.stripe.com/receipts/..."
    },
    "flutterwave": null,
    "ucn": null
  },

  "metadata": {
    "ip_address":           "41.75.200.10",
    "user_agent":           "Mozilla/5.0...",
    "webhook_triggered_at": "2026-03-29T10:15:00+00:00"
  }
}
```

**`payment.status` values your handler may receive:**

| Value | Meaning |
|---|---|
| `success` | Payment cleared (internally stored as `cleared`) |
| `pending` | Awaiting confirmation |
| `failed` | Gateway rejected |
| `cancelled` | Cancelled before processing |
| `refunded` | Refunded after clearing |

---

### payment.failed

Triggered when a payment attempt is rejected by the gateway.

Identical schema to `payment.success` with these differences:

- `event` = `"payment.failed"`
- `payment.status` = `"failed"`
- Two extra fields on the `payment` object:

```json
"payment": {
  ...
  "error_code":    "card_declined",
  "error_message": "Your card was declined."
}
```

`error_code` and `error_message` come from the raw gateway response. They may be `null` if the gateway did not provide details.

---

### subscription.created

Triggered when a new subscription is activated for a customer.

```json
{
  "event":       "subscription.created",
  "event_id":    "evt_68026f3a4b1e3",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "subscription": {
    "id":                   18,
    "status":               "active",
    "price_plan_id":        5,
    "price_plan_name":      "Standard Term Plan",
    "billing_interval":     "quarterly",
    "amount":               150000.00,
    "currency":             "TZS",
    "current_period_start": "2026-03-29",
    "current_period_end":   "2026-06-28",
    "next_billing_date":    "2026-06-29",
    "trial_ends_at":        null,
    "canceled_at":          null
  },

  "customer": { ...customer object... },
  "invoice":  null,
  "payment":  null,
  "gateway_details": { "stripe": null, "flutterwave": null, "ucn": null },
  "metadata": { ...metadata object... }
}
```

---

### subscription.renewed

Triggered when an existing subscription is renewed for a new billing period. The `payment` object is included if a renewal payment was captured.

```json
{
  "event":       "subscription.renewed",
  "event_id":    "evt_68026f3a4b1e4",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "subscription": {
    "id":                   18,
    "status":               "active",
    "price_plan_id":        5,
    "price_plan_name":      "Standard Term Plan",
    "billing_interval":     "quarterly",
    "amount":               150000.00,
    "currency":             "TZS",
    "current_period_start": "2026-04-01",
    "current_period_end":   "2026-06-30",
    "next_billing_date":    "2026-07-01",
    "trial_ends_at":        null,
    "canceled_at":          null
  },

  "customer": { ...customer object... },

  "payment": {
    "id":             201,
    "transaction_id": "pi_4RqAbc",
    "amount":         150000.00,
    "currency":       "TZS",
    "status":         "success",
    ...
  },

  "metadata": { ...metadata object... }
}
```

---

### subscription.cancelled

Triggered when a subscription is cancelled (immediately or at period end). The `cancellation` block gives the reason.

```json
{
  "event":       "subscription.cancelled",
  "event_id":    "evt_68026f3a4b1e5",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "subscription": {
    "id":         18,
    "status":     "cancelled",
    "canceled_at":"2026-03-29T10:15:00+00:00",
    ...
  },

  "customer": { ...customer object... },

  "cancellation": {
    "reason":       "Customer requested cancellation",
    "cancelled_at": "2026-03-29T10:15:00+00:00"
  },

  "metadata": { ...metadata object... }
}
```

---

### subscription.expired

Triggered when a subscription reaches its end date without renewal.

```json
{
  "event":       "subscription.expired",
  "event_id":    "evt_68026f3a4b1e6",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "subscription": {
    "id":     18,
    "status": "expired",
    ...
  },

  "customer":   { ...customer object... },
  "expired_at": "2026-03-31",
  "metadata":   { ...metadata object... }
}
```

`expired_at` is the subscription's `end_date`.

---

### subscription.upgraded

Triggered when a customer moves to a different price plan. Both the old and new plan details are included.

```json
{
  "event":       "subscription.upgraded",
  "event_id":    "evt_68026f3a4b1e7",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "subscription": {
    "id":               18,
    "status":           "active",
    "price_plan_id":    7,
    "price_plan_name":  "Premium Annual Plan",
    "billing_interval": "yearly",
    "amount":           500000.00,
    "currency":         "TZS",
    ...
  },

  "customer": { ...customer object... },

  "upgrade": {
    "previous_plan": {
      "id":       5,
      "name":     "Standard Term Plan",
      "amount":   150000.00,
      "interval": "quarterly"
    },
    "new_plan": {
      "id":       7,
      "name":     "Premium Annual Plan",
      "amount":   500000.00,
      "interval": "yearly"
    },
    "upgraded_at": "2026-03-29T10:15:00+00:00"
  },

  "metadata": { ...metadata object... }
}
```

---

### credits.purchased

Triggered when a customer purchases credits (e.g. SMS credits, usage units).

```json
{
  "event":       "credits.purchased",
  "event_id":    "evt_68026f3a4b1e8",
  "timestamp":   "2026-03-29T10:15:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,

  "product":      { ...product object... },
  "organization": { ...organization object... },

  "customer": { ...customer object... },

  "credits": {
    "id":           55,
    "amount":       1000,
    "balance":      4200,
    "description":  "SMS credit top-up",
    "purchased_at": "2026-03-29T10:15:00+00:00"
  },

  "payment": {
    "id":             210,
    "transaction_id": "pi_5StDef",
    "amount":         10000.00,
    "currency":       "TZS",
    "status":         "success",
    ...
  },

  "metadata": { ...metadata object... }
}
```

---

## 8. Shared Object Schemas

These objects appear in multiple payloads. The full schema is defined here once.

### `product`

```json
{
  "id":              3,
  "name":            "School Management System",
  "product_code":    "SMS-001",
  "organization_id": 1,
  "status":          "active"
}
```

### `organization`

```json
{
  "id":   1,
  "name": "Shule Soft Africa"
}
```

### `customer`

```json
{
  "id":         42,
  "product_id": 3,
  "name":       "Mwanafunzi Primary School",
  "email":      "accounts@mwanafunzi.ac.tz",
  "phone":      "+255712345678",
  "status":     "active"
}
```

### `subscription`

```json
{
  "id":                   18,
  "status":               "active",
  "price_plan_id":        5,
  "price_plan_name":      "Standard Term Plan",
  "billing_interval":     "quarterly",
  "amount":               150000.00,
  "currency":             "TZS",
  "current_period_start": "2026-01-01",
  "current_period_end":   "2026-03-31",
  "next_billing_date":    "2026-04-01",
  "trial_ends_at":        null,
  "canceled_at":          null
}
```

### `payment`

```json
{
  "id":                187,
  "transaction_id":    "pi_3OqXyz",
  "amount":            150000.00,
  "currency":          "TZS",
  "status":            "success",
  "payment_method":    "card",
  "gateway":           "stripe",
  "gateway_reference": "pi_3OqXyz",
  "gateway_fee":       4500.00,
  "net_amount":        145500.00,
  "description":       "Invoice INV-2026-0042 payment",
  "paid_at":           "2026-03-29T10:14:58+00:00",
  "created_at":        "2026-03-29T10:14:50+00:00"
}
```

`gateway` is one of: `stripe`, `flutterwave`, `ucn`, `unknown`.

### `gateway_details` (payment events only)

Only the key matching the active gateway is populated; the others are `null`.

```json
{
  "stripe": {
    "payment_intent_id": "pi_3OqXyz",
    "charge_id":         "ch_3OqXyz",
    "payment_method_id": "pm_3OqXyz",
    "customer_id":       "cus_Stripe123",
    "last4":             "4242",
    "brand":             "visa",
    "country":           "TZ",
    "receipt_url":       "https://pay.stripe.com/receipts/..."
  },
  "flutterwave": null,
  "ucn": null
}
```

**Flutterwave variant:**
```json
{
  "stripe": null,
  "flutterwave": {
    "transaction_id": 123456789,
    "flw_ref":        "FLW-MOCK-abc",
    "tx_ref":         "billing-187",
    "payment_type":   "mobilemoneyuganda",
    "card_brand":     null,
    "last4":          null
  },
  "ucn": null
}
```

**UCN (bank transfer) variant:**
```json
{
  "stripe": null,
  "flutterwave": null,
  "ucn": {
    "control_number":   "9920240001234",
    "bill_id":          "BILL-99",
    "payer_name":       "JOHN DOE",
    "payer_phone":      "+255712345678",
    "payment_channel":  "bank_transfer",
    "sp_code":          "SP001"
  }
}
```

### `invoice`

```json
{
  "id":             99,
  "invoice_number": "INV-2026-0042",
  "subtotal":       130435.00,
  "tax_total":      19565.00,
  "total":          150000.00,
  "amount_paid":    150000.00,
  "amount_due":     0.00,
  "currency":       "TZS",
  "status":         "paid",
  "due_date":       "2026-04-05",
  "issued_at":      "2026-03-29T08:00:00+00:00",
  "paid_at":        "2026-03-29T10:14:58+00:00",
  "items": [
    {
      "id":              201,
      "description":     "Term 1 Fees",
      "quantity":        1,
      "unit_price":      130435.00,
      "total":           130435.00,
      "price_plan_id":   5,
      "price_plan_name": "Standard Term Plan"
    }
  ],
  "ucn":             "9920240001234",
  "control_number":  "9920240001234",
  "control_numbers": ["9920240001234"]
}
```

`ucn` and `control_number` are aliases for the same value. `control_numbers` contains all control numbers if more than one is assigned.

### `metadata`

```json
{
  "ip_address":           "41.75.200.10",
  "user_agent":           "Mozilla/5.0...",
  "webhook_triggered_at": "2026-03-29T10:15:00+00:00"
}
```

`ip_address` and `user_agent` reflect the originating HTTP request that caused the event (e.g. the customer's browser making the payment). They may be `null` for system-triggered events (e.g. scheduled subscription expiry).

---

## 9. Testing & Replay

### Test a webhook immediately

```
POST /api/v1/products/{product}/webhooks/{webhook}/test
```

Sends a sample `payment.success` payload to the configured URL right now. Useful for testing connectivity and signature verification before going live.

### Replay missed payments to a specific webhook

```
POST /api/v1/products/{product}/webhooks/{webhook}/replay
```

Finds all cleared payments for this product that have **not** yet been successfully delivered to this webhook and dispatches them. Useful when a webhook URL is added after payments have already been processed.

Optional filters:

```json
{
  "from":        "2026-01-01",
  "to":          "2026-03-31",
  "payment_ids": [101, 102, 103]
}
```

### Sweep all unsent events via CLI

```bash
# Dry run — shows what would be sent without sending
php artisan webhooks:retry --dry-run

# Limit to one product
php artisan webhooks:retry --product=4

# Live run for all products
php artisan webhooks:retry
```

The command runs three phases:

| Phase | What it sweeps |
|---|---|
| 1 | All `failed` / `pending` delivery records — retries with a fresh payload |
| 2 | All `cleared` and `failed` payments that have no `sent` delivery for each active webhook |
| 3 | All subscriptions matching `subscription.created`, `subscription.cancelled`, `subscription.expired`, `subscription.upgraded` that have no `sent` delivery for each active webhook |

### View delivery history

```
GET /api/v1/products/{product}/webhooks/{webhook}/deliveries
```

Returns the delivery log for a webhook, including HTTP status, response body, duration, error messages, and retry timestamps.
