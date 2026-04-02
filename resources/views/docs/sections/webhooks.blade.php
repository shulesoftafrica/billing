<section class="api-section" id="webhooks-section">
    <h2>🔔 Webhooks</h2>
    <p>Configure webhook endpoints to receive <strong>real-time event notifications FROM this billing platform TO your application</strong> when billing events occur.</p>

    {{-- Overview Section --}}
    <div id="webhook-overview" style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-bottom: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">📋 How Webhooks Work</h3>
        
        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">🎯 What Are Webhooks?</h4>
            <p>Webhooks are HTTP callbacks that the billing platform sends <strong>TO your application</strong> when specific events occur. They allow you to receive real-time notifications about payments, invoices, and subscriptions.</p>
            <ul>
                <li><strong>Product-Level Isolation:</strong> Each webhook is configured per product (e.g., separate webhooks for Hospital Management vs Hotel Management)</li>
                <li><strong>Event-Driven:</strong> Webhooks fire immediately when events occur (no polling required)</li>
                <li><strong>Automatic Retry:</strong> Failed deliveries are automatically retried with exponential backoff</li>
                <li><strong>Secure:</strong> All webhook deliveries include HMAC SHA256 signatures for verification</li>
            </ul>
        </div>

        <div style="background: rgba(23, 162, 184, 0.1); border-left: 4px solid #17a2b8; padding: 16px; margin-bottom: 24px; border-radius: 4px;">
            <h4 style="margin-top: 0;">💡 Understanding URL Parameters</h4>
            <p style="margin-bottom: 8px;"><strong>What does <code>{product}</code> mean in the URL?</strong></p>
            <p style="margin-bottom: 12px;">In all webhook endpoints like <code>/api/v1/products/{product}/webhooks</code>, the <code>{product}</code> placeholder refers to the <strong>Product ID</strong> (numeric value).</p>
            <p style="margin-bottom: 8px;"><strong>Example:</strong></p>
            <ul style="margin: 0; padding-left: 20px;">
                <li>If your product has ID = <code>1</code>, use: <code>/api/v1/products/1/webhooks</code></li>
                <li>If your product has ID = <code>42</code>, use: <code>/api/v1/products/42/webhooks</code></li>
            </ul>
            <p style="margin-top: 12px; margin-bottom: 0;"><strong>How to get your Product ID:</strong> Use the <a href="#list-all-products" style="text-decoration: underline;">List Products endpoint</a> to retrieve all your products with their IDs.</p>
        </div>

        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">📅 Available Event Types</h4>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-primary);">
                        <th style="text-align: left; padding: 8px;">Event Type</th>
                        <th style="text-align: left; padding: 8px;">Description</th>
                        <th style="text-align: left; padding: 8px;">When It Fires</th>
                        <th style="text-align: left; padding: 8px;">Sweep Recovery</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>payment.success</code></td>
                        <td style="padding: 8px;">Payment cleared</td>
                        <td style="padding: 8px;">When a payment is confirmed as cleared by the gateway</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 2</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>payment.failed</code></td>
                        <td style="padding: 8px;">Payment rejected</td>
                        <td style="padding: 8px;">When a payment attempt is rejected by the gateway</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 2</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.created</code></td>
                        <td style="padding: 8px;">New subscription</td>
                        <td style="padding: 8px;">When a customer is subscribed to a price plan (after first payment)</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 3</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.renewed</code></td>
                        <td style="padding: 8px;">Subscription renewed</td>
                        <td style="padding: 8px;">When a subscription is renewed for a new billing period</td>
                        <td style="padding: 8px; color: #dc3545;">✗ Live-only</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.cancelled</code></td>
                        <td style="padding: 8px;">Subscription cancelled</td>
                        <td style="padding: 8px;">When a subscription is terminated</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 3</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.expired</code></td>
                        <td style="padding: 8px;">Subscription expired</td>
                        <td style="padding: 8px;">When a subscription reaches its end date without renewal</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 3</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>subscription.upgraded</code></td>
                        <td style="padding: 8px;">Plan upgraded</td>
                        <td style="padding: 8px;">When a customer moves to a different price plan</td>
                        <td style="padding: 8px; color: #28a745;">✓ Phase 3</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>credits.purchased</code></td>
                        <td style="padding: 8px;">Credits purchased</td>
                        <td style="padding: 8px;">When a customer purchases usage credits / wallet top-up</td>
                        <td style="padding: 8px; color: #dc3545;">✗ Live-only</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);">
                        <td style="padding: 8px;"><code>invoice.created</code></td>
                        <td style="padding: 8px;">Invoice created</td>
                        <td style="padding: 8px;">When an invoice is generated</td>
                        <td style="padding: 8px; color: #dc3545;">✗ Live-only</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;"><code>invoice.paid</code></td>
                        <td style="padding: 8px;">Invoice paid</td>
                        <td style="padding: 8px;">When an invoice is fully paid</td>
                        <td style="padding: 8px; color: #dc3545;">✗ Live-only</td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 8px;"><strong style="color: #28a745;">✓ Sweep Recovery</strong> — if this event fails to deliver, the <code>webhooks:retry</code> command can recover and resend it from the database. <strong style="color: #dc3545;">✗ Live-only</strong> — only fired at the moment it occurs; not recoverable via sweep.</p>
            <p style="margin-top: 12px;"><strong>💡 Wildcard Support:</strong></p>
            <table style="width: 100%; border-collapse: collapse; margin-top: 4px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-primary);">
                        <th style="text-align: left; padding: 8px;">Pattern</th>
                        <th style="text-align: left; padding: 8px;">Matches</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>payment.*</code></td><td style="padding: 8px;"><code>payment.success</code>, <code>payment.failed</code></td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>subscription.*</code></td><td style="padding: 8px;">All six subscription events</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>invoice.*</code></td><td style="padding: 8px;"><code>invoice.created</code>, <code>invoice.paid</code></td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>*</code></td><td style="padding: 8px;">Every event</td></tr>
                    <tr><td style="padding: 8px;"><em>empty array</em></td><td style="padding: 8px;">Every event</td></tr>
                </tbody>
            </table>
        </div>

        <div style="margin-bottom: 24px;">
            <h4 style="color: var(--text-primary);">⏱️ Retry Logic &amp; Delivery Lifecycle</h4>
            <p>Each webhook delivery goes through the following states:</p>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-primary);">
                        <th style="text-align: left; padding: 8px;">Status</th>
                        <th style="text-align: left; padding: 8px;">Meaning</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>pending</code></td><td style="padding: 8px;">Delivery failed but is scheduled for retry</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>sent</code></td><td style="padding: 8px;">Successfully delivered (HTTP 2xx received)</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>failed</code></td><td style="padding: 8px;">Permanently failed — no more retries</td></tr>
                    <tr><td style="padding: 8px;"><code>superseded</code></td><td style="padding: 8px;">Replaced by a fresh-payload retry during the sweep command</td></tr>
                </tbody>
            </table>
            <p><strong>Exponential backoff schedule</strong> — retries are triggered by a scheduled job that checks every 5 minutes:</p>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-primary);">
                        <th style="text-align: left; padding: 8px;">Attempt</th>
                        <th style="text-align: left; padding: 8px;">Delay after previous failure</th>
                        <th style="text-align: left; padding: 8px;">Cumulative time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><strong>1st</strong> (original)</td><td style="padding: 8px;">Immediate — fired when event occurs</td><td style="padding: 8px;">0</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><strong>2nd</strong></td><td style="padding: 8px;">5 minutes</td><td style="padding: 8px;">~5 min</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><strong>3rd</strong></td><td style="padding: 8px;">15 minutes</td><td style="padding: 8px;">~20 min</td></tr>
                    <tr><td style="padding: 8px;"><strong>4th</strong></td><td style="padding: 8px;">45 minutes</td><td style="padding: 8px;">~65 min</td></tr>
                </tbody>
            </table>
            <p style="margin-bottom: 8px;">Formula: <code>delay = 5 × 3^(attempt - 1)</code> minutes. The maximum number of retries defaults to <strong>3</strong> (configurable via <code>retry_count</code> on the webhook).</p>
            <div style="background: rgba(220,53,69,0.1); border-left: 4px solid #dc3545; padding: 12px; border-radius: 4px; margin-top: 8px;">
                <p style="margin: 0;"><strong>⛔ 4xx responses (except 429) are permanent failures — never retried.</strong><br>
                A <code>401 Unauthorized</code>, <code>403 Forbidden</code>, <code>404 Not Found</code>, or <code>422 Unprocessable Entity</code> from your endpoint means the platform <em>immediately</em> stops retrying. These indicate a client-side configuration problem that won't resolve on its own.<br>
                <strong>Only 5xx server errors, network timeouts, and <code>429 Too Many Requests</code> trigger retries.</strong></p>
            </div>
            <div style="background: rgba(255,193,7,0.1); border-left: 4px solid #ffc107; padding: 12px; border-radius: 4px; margin-top: 8px;">
                <p style="margin: 0;"><strong>⚠️ 429 Too Many Requests:</strong> When a <code>429</code> is received, the platform backs off for <strong>10 seconds</strong> and then skips all remaining deliveries for that webhook endpoint for the current sweep run. They will be retried in the next scheduled run.</p>
            </div>
        </div>

        <div>
            <h4 style="color: var(--text-primary);">✅ What Acknowledgement is Expected?</h4>
            <p>Your webhook endpoint must:</p>
            <ul>
                <li><strong>Return HTTP 2xx status code</strong> (200, 201, 202, 204) within 30 seconds to acknowledge receipt</li>
                <li><strong>Verify the HMAC signature</strong> in the <code>X-Webhook-Signature</code> header before processing</li>
                <li><strong>Process events idempotently</strong> using the delivery ID to prevent duplicate processing</li>
                <li><strong>Respond quickly</strong> - Process events asynchronously; don't keep the connection open</li>
            </ul>
            <p><strong>⚠️ Non-2xx responses</strong> (4xx, 5xx) or timeouts trigger automatic retry.</p>
        </div>
    </div>

    {{-- Event Firing Mechanics --}}
    <div style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-bottom: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">⚡ How Each Event is Fired</h3>
        <p style="margin-bottom: 20px;">Every event is dispatched synchronously at the moment it occurs using <code>WebhookDispatchService</code>. The platform fans out to <strong>all active webhooks</strong> registered for that product and event type. A <code>webhook_deliveries</code> record is created for every attempt.</p>

        <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px; white-space: nowrap;">Event</th>
                    <th style="text-align: left; padding: 8px;">Trigger</th>
                    <th style="text-align: left; padding: 8px; white-space: nowrap;">Fires once per</th>
                    <th style="text-align: left; padding: 8px;">Payload present</th>
                    <th style="text-align: left; padding: 8px;">Payload absent / null</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>payment.success</code></td>
                    <td style="padding: 8px;">Payment marked <code>cleared</code> by the gateway callback</td>
                    <td style="padding: 8px;">Payment record</td>
                    <td style="padding: 8px;"><code>payment</code>, <code>invoice</code>¹, <code>subscription</code>², <code>gateway_details</code></td>
                    <td style="padding: 8px;">—</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>payment.failed</code></td>
                    <td style="padding: 8px;">Payment attempt rejected by gateway</td>
                    <td style="padding: 8px;">Payment record</td>
                    <td style="padding: 8px;"><code>payment</code> (+ <code>error_code</code>, <code>error_message</code>), <code>invoice</code>¹, <code>subscription</code>², <code>gateway_details</code></td>
                    <td style="padding: 8px;">—</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>subscription.created</code></td>
                    <td style="padding: 8px;">Customer subscribed to a price plan (after first payment)</td>
                    <td style="padding: 8px;">Subscription record</td>
                    <td style="padding: 8px;"><code>subscription</code></td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>payment</code>, <code>gateway_details</code> all <code>null</code></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>subscription.renewed</code></td>
                    <td style="padding: 8px;">Subscription renewed for next billing cycle</td>
                    <td style="padding: 8px;">Renewal event</td>
                    <td style="padding: 8px;"><code>subscription</code>, <code>payment</code>³</td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>gateway_details</code> not sent</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>subscription.cancelled</code></td>
                    <td style="padding: 8px;">Subscription cancelled by admin or customer request</td>
                    <td style="padding: 8px;">Cancellation event</td>
                    <td style="padding: 8px;"><code>subscription</code>, <code>cancellation</code> (reason + timestamp)</td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>payment</code>, <code>gateway_details</code> all <code>null</code></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>subscription.expired</code></td>
                    <td style="padding: 8px;">Subscription end date reached without renewal</td>
                    <td style="padding: 8px;">Expiry event</td>
                    <td style="padding: 8px;"><code>subscription</code>, <code>expired_at</code></td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>payment</code>, <code>gateway_details</code> all <code>null</code></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>subscription.upgraded</code></td>
                    <td style="padding: 8px;">Customer moved to a different price plan</td>
                    <td style="padding: 8px;">Upgrade event</td>
                    <td style="padding: 8px;"><code>subscription</code> (new plan), <code>upgrade.previous_plan</code>, <code>upgrade.new_plan</code></td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>payment</code>, <code>gateway_details</code> all <code>null</code></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>credits.purchased</code></td>
                    <td style="padding: 8px;">Customer purchases usage credits / wallet top-up</td>
                    <td style="padding: 8px;">Credit purchase event</td>
                    <td style="padding: 8px;"><code>wallet_transaction</code>, <code>payment</code>³</td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>subscription</code>, <code>gateway_details</code> not sent</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px;"><code>invoice.created</code></td>
                    <td style="padding: 8px;">Invoice generated for a customer</td>
                    <td style="padding: 8px;">Invoice record</td>
                    <td style="padding: 8px;"><code>invoice</code></td>
                    <td style="padding: 8px;"><code>payment</code>, <code>subscription</code> both <code>null</code>; <code>gateway_details</code> all <code>null</code></td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><code>invoice.paid</code></td>
                    <td style="padding: 8px;">Invoice marked fully paid</td>
                    <td style="padding: 8px;">Invoice payment event</td>
                    <td style="padding: 8px;"><code>invoice</code>, <code>payment</code>, <code>gateway_details</code></td>
                    <td style="padding: 8px;"><code>subscription</code> is <code>null</code></td>
                </tr>
            </tbody>
        </table>
        </div>

        <div style="margin-top: 16px; font-size: 0.875rem; color: var(--text-secondary);">
            <p style="margin: 4px 0;"><strong>¹ invoice</strong> — present for invoice-linked payments. For UCN (bank transfer) payments where <code>invoice_id = NULL</code> on the payment row, the invoice is resolved via the <code>invoice_payments</code> join table. This link is created synchronously by <code>enableSubscription</code> before the UCN payment is marked <code>cleared</code>, so it is always available.</p>
            <p style="margin: 4px 0;"><strong>² subscription</strong> — the customer's most recent subscription. Falls back to client's latest subscription if not directly linked to the payment.</p>
            <p style="margin: 4px 0;"><strong>³ payment</strong> — optional; present only when a payment was captured as part of the event (e.g. renewal with immediate charge, or credit purchase).</p>
        </div>

        <div style="background: rgba(23,162,184,0.1); border-left: 4px solid #17a2b8; padding: 12px; margin-top: 16px; border-radius: 4px;">
            <p style="margin: 0; font-weight: 600;">Product-level isolation</p>
            <p style="margin: 4px 0 0 0;">Each webhook only receives events from <strong>its own product</strong>. A payment is scoped to a product via the chain: <code>payments → invoice_payments → invoices → invoice_items → price_plans.product_id</code>. A subscription is scoped via <code>subscriptions → price_plans.product_id</code>. This means an organization with two products (A and B) will never receive product A's customer events on product B's webhook endpoint.</p>
        </div>
    </div>

    {{-- Sweep & Recovery --}}
    <div style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-bottom: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">🔄 Sweep &amp; Recovery — <code>webhooks:retry</code></h3>
        <p>Beyond automatic retries, a scheduled command (<code>php artisan webhooks:retry</code>) runs periodically to ensure <strong>no event is permanently lost</strong>. It runs three phases in sequence:</p>

        <div style="margin-bottom: 24px; padding: 16px; background: var(--surface-card, white); border-radius: 6px; border: 1px solid var(--border-secondary);">
            <h4 style="margin-top: 0; color: var(--text-primary);">Phase 1 — Retry Failed / Pending Deliveries</h4>
            <p>Picks up any <code>webhook_deliveries</code> with <code>status = pending</code> and <code>next_retry_at &lt;= now()</code>.</p>
            <ul style="margin: 0; padding-left: 20px;">
                <li><strong>Fresh payload:</strong> The platform first rebuilds the payload from the current database state (e.g. latest payment/subscription data). The stored payload is updated and the original delivery is marked <code>superseded</code>. A new delivery record is created.</li>
                <li><strong>Stored payload fallback:</strong> If the source record (payment or subscription) no longer exists, the original stored JSON payload is re-sent as-is.</li>
                <li><strong>Inter-request delay:</strong> 500 ms between consecutive deliveries to the same endpoint.</li>
            </ul>
        </div>

        <div style="margin-bottom: 24px; padding: 16px; background: var(--surface-card, white); border-radius: 6px; border: 1px solid var(--border-secondary);">
            <h4 style="margin-top: 0; color: var(--text-primary);">Phase 2 — Sweep Unsent Payment Events</h4>
            <p>Finds <strong>all cleared / failed payments</strong> that have NO terminal delivery record (<code>sent</code> or <code>failed</code>) on a given webhook. This covers two scenarios:</p>
            <ul style="margin: 0 0 12px; padding-left: 20px;">
                <li>Webhook endpoint registered <em>after</em> payments occurred — it will receive all historical payments since its registration date.</li>
                <li>Deliveries that permanently errored and were never retried.</li>
            </ul>
            <table style="width: 100%; border-collapse: collapse;">
                <thead><tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px;">Condition</th>
                    <th style="text-align: left; padding: 8px;">Detail</th>
                </tr></thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;">Payment scope</td><td style="padding: 8px;">Only payments linked to the webhook's product (via invoice chain)</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;">Date filter</td><td style="padding: 8px;">Only payments with <code>paid_at &gt;= webhook.created_at</code></td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;">Skip condition</td><td style="padding: 8px;">Payment already has a <code>sent</code> or <code>failed</code> delivery on this webhook</td></tr>
                    <tr><td style="padding: 8px;">Events swept</td><td style="padding: 8px;"><code>payment.success</code> (status = cleared), <code>payment.failed</code> (status = failed)</td></tr>
                </tbody>
            </table>
        </div>

        <div style="padding: 16px; background: var(--surface-card, white); border-radius: 6px; border: 1px solid var(--border-secondary);">
            <h4 style="margin-top: 0; color: var(--text-primary);">Phase 3 — Sweep Unsent Subscription Events</h4>
            <p>Finds subscriptions with NO terminal delivery record on a given webhook for each subscription event type:</p>
            <table style="width: 100%; border-collapse: collapse;">
                <thead><tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px;">Event</th>
                    <th style="text-align: left; padding: 8px;">Subscription filter applied</th>
                    <th style="text-align: left; padding: 8px;">Why</th>
                </tr></thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>subscription.created</code></td><td style="padding: 8px;">Customer must have at least one payment record</td><td style="padding: 8px;">Avoids sending to receivers who have never heard of this customer (would return 422)</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>subscription.cancelled</code></td><td style="padding: 8px;"><code>status = 'cancelled'</code></td><td style="padding: 8px;">Only sweep actually cancelled subscriptions</td></tr>
                    <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>subscription.expired</code></td><td style="padding: 8px;"><code>status = 'expired'</code></td><td style="padding: 8px;">Only sweep actually expired subscriptions</td></tr>
                    <tr><td style="padding: 8px;"><code>subscription.upgraded</code></td><td style="padding: 8px;"><code>previous_plan_id IS NOT NULL</code></td><td style="padding: 8px;">Only subscriptions that were genuinely upgraded from a prior plan</td></tr>
                </tbody>
            </table>
            <p style="margin-top: 12px; margin-bottom: 0; color: var(--text-secondary); font-size: 0.875rem;">⚠️ <code>subscription.renewed</code> is intentionally not included in the sweep — it is only fired live when a renewal occurs and is not reconstructable after the fact without ambiguity. All subscription events respect the <code>subscriptions.created_at &gt;= webhook.created_at</code> date guard.</p>
        </div>

        <div style="background: rgba(40,167,69,0.1); border-left: 4px solid #28a745; padding: 12px; margin-top: 16px; border-radius: 4px;">
            <p style="margin: 0;"><strong>💡 Rate-limit handling during sweep:</strong> If your endpoint returns <code>429</code>, the sweep immediately pauses for <strong>10 seconds</strong> and then skips all remaining items for that webhook in the current run. They will be picked up again in the next run. Use this as a signal to increase your endpoint's capacity or reduce webhook throughput requirements.</p>
        </div>
    </div>

    {{-- Security & Headers --}}
    <div style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107; padding: 24px; margin-bottom: 32px; border-radius: 4px;">
        <h4 style="margin-top: 0;">🔐 Request Headers &amp; Signature Verification</h4>
        <p style="margin-bottom: 16px;">Every webhook delivery includes these HTTP headers:</p>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px;">Header</th>
                    <th style="text-align: left; padding: 8px;">Example</th>
                    <th style="text-align: left; padding: 8px;">Description</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>X-Webhook-Signature</code></td><td style="padding: 8px;"><code>a3f9d2...</code></td><td style="padding: 8px;">HMAC-SHA256 of the raw request body — <strong>verify this first</strong></td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>X-Event-Type</code></td><td style="padding: 8px;"><code>payment.success</code></td><td style="padding: 8px;">The event name — use this to route your handler logic</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>X-Webhook-ID</code></td><td style="padding: 8px;"><code>7</code></td><td style="padding: 8px;">ID of the webhook configuration that triggered this</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>X-Delivery-ID</code></td><td style="padding: 8px;"><code>142</code></td><td style="padding: 8px;">Unique delivery attempt ID — use for deduplication</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>Content-Type</code></td><td style="padding: 8px;"><code>application/json</code></td><td style="padding: 8px;">Always JSON</td></tr>
                <tr><td style="padding: 8px;"><code>User-Agent</code></td><td style="padding: 8px;"><code>BillingPlatform-Webhook/1.0</code></td><td style="padding: 8px;">Fixed identifier</td></tr>
            </tbody>
        </table>
        <p style="margin-bottom: 12px;"><strong>Signature formula:</strong> <code>HMAC-SHA256(raw_request_body, webhook_secret)</code>. Compute this over the <strong>raw bytes</strong> before any JSON parsing.</p>
        <x-docs.code-block language="php">
// PHP — verify before processing
$rawBody  = file_get_contents('php://input');
$received = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'];
$computed = hash_hmac('sha256', $rawBody, $webhookSecret);

if (!hash_equals($computed, $received)) {
    http_response_code(401);
    exit('Invalid signature');
}

$event = json_decode($rawBody, true);
http_response_code(200); // always respond 2xx
        </x-docs.code-block>
        <x-docs.code-block language="javascript">
// Node.js (Express) — use express.raw() to get raw bytes
const crypto = require('crypto');

app.post('/webhooks/billing', express.raw({ type: 'application/json' }), (req, res) => {
    const computed = crypto
        .createHmac('sha256', webhookSecret)
        .update(req.body)               // Buffer — before JSON.parse
        .digest('hex');

    if (!crypto.timingSafeEqual(Buffer.from(computed), Buffer.from(req.headers['x-webhook-signature']))) {
        return res.status(401).send('Invalid signature');
    }

    const event = JSON.parse(req.body);
    // handle event...
    res.status(200).json({ received: true });
});
        </x-docs.code-block>
        <x-docs.code-block language="python">
# Python / Django
import hmac, hashlib

raw_body = request.body                 # bytes, before any parsing
received = request.headers.get('X-Webhook-Signature', '')
computed  = hmac.new(webhook_secret.encode(), raw_body, hashlib.sha256).hexdigest()

if not hmac.compare_digest(computed, received):
    return HttpResponse(status=401)

event = json.loads(raw_body)
        </x-docs.code-block>
    </div>

    {{-- List Webhooks --}}
    <x-docs.endpoint
        id="list-webhooks"
        method="GET"
        url="/api/v1/products/{product}/webhooks"
        title="List Webhooks"
        description="Get all webhook configurations for a specific product">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
            </table>
        </x-slot>

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
      "product_id": 1,
      "name": "Production Payment Webhook",
      "url": "https://your-app.com/webhooks/billing",
      "status": "active",
      "events": ["payment.success", "invoice.paid"],
      "http_method": "POST",
      "timeout": 30,
      "retry_count": 3,
      "verify_ssl": true,
      "last_triggered_at": "2026-03-24T14:00:00+00:00",
      "delivery_stats": {
        "total": 150,
        "successful": 145,
        "failed": 5
      },
      "created_at": "2026-03-20T10:00:00+00:00",
      "updated_at": "2026-03-24T15:30:00+00:00"
    }
  ]
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Create Webhook --}}
    <x-docs.endpoint
        id="create-webhook"
        method="POST"
        url="/api/v1/products/{product}/webhooks"
        title="Create Webhook"
        description="Register a new webhook endpoint to receive event notifications for a product">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Production Payment Webhook",
  "url": "https://your-app.com/webhooks/billing",
  "events": ["payment.success", "invoice.paid"],
  "status": "active",
  "http_method": "POST",
  "headers": {
    "X-Custom-Header": "custom-value",
    "X-API-Key": "your-internal-api-key"
  },
  "timeout": 30,
  "retry_count": 3,
  "verify_ssl": true
}
            </x-docs.code-block>
            <h4>Request Parameters</h4>
            <table>
                <tr>
                    <td><code>name</code></td>
                    <td>string</td>
                    <td>required</td>
                    <td>Descriptive name for the webhook</td>
                </tr>
                <tr>
                    <td><code>url</code></td>
                    <td>string</td>
                    <td>required</td>
                    <td>Your endpoint URL (HTTPS recommended)</td>
                </tr>
                <tr>
                    <td><code>events</code></td>
                    <td>array</td>
                    <td>required</td>
                    <td>Event types to subscribe to</td>
                </tr>
                <tr>
                    <td><code>status</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>active or inactive (default: active)</td>
                </tr>
                <tr>
                    <td><code>http_method</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>POST or PUT (default: POST)</td>
                </tr>
                <tr>
                    <td><code>headers</code></td>
                    <td>object</td>
                    <td>optional</td>
                    <td>Custom headers to include</td>
                </tr>
                <tr>
                    <td><code>timeout</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Request timeout in seconds (default: 30)</td>
                </tr>
                <tr>
                    <td><code>retry_count</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Number of retry attempts (default: 3)</td>
                </tr>
                <tr>
                    <td><code>verify_ssl</code></td>
                    <td>boolean</td>
                    <td>optional</td>
                    <td>Verify SSL certificates (default: true)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook created successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "name": "Production Payment Webhook",
    "url": "https://your-app.com/webhooks/billing",
    "secret": "whsec_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "status": "active",
    "events": ["payment.success", "invoice.paid"],
    "http_method": "POST",
    "headers": {
      "X-Custom-Header": "custom-value"
    },
    "timeout": 30,
    "retry_count": 3,
    "verify_ssl": true,
    "created_at": "2026-03-24T15:30:00+00:00"
  }
}
            </x-docs.code-block>
            <div class="response-head">
                <span class="response-title">Error Response</span>
                <span class="status-badge status-4xx">422 Unprocessable Entity</span>
            </div>
            <x-docs.code-block language="json">
{
  "errors": {
    "url": ["The url field must be a valid URL."],
    "events": ["The events field must contain valid event types."]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Get Webhook --}}
    <x-docs.endpoint
        id="get-webhook"
        method="GET"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Get Webhook Details"
        description="Get details of a specific webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

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
    "product_id": 1,
    "name": "Production Payment Webhook",
    "url": "https://your-app.com/webhooks/billing",
    "secret": "whsec_a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "status": "active",
    "events": ["payment.success", "invoice.paid"],
    "http_method": "POST",
    "headers": {"X-Custom-Header": "custom-value"},
    "timeout": 30,
    "retry_count": 3,
    "verify_ssl": true,
    "last_triggered_at": "2026-03-24T14:00:00+00:00",
    "created_at": "2026-03-20T10:00:00+00:00",
    "updated_at": "2026-03-24T15:30:00+00:00"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Update Webhook --}}
    <x-docs.endpoint
        id="update-webhook"
        method="PUT"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Update Webhook"
        description="Update webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "name": "Updated Webhook Name",
  "url": "https://your-app.com/webhooks/billing-v2",
  "events": ["payment.*", "invoice.*"],
  "status": "active"
}
            </x-docs.code-block>
            <p style="margin-top: 8px; color: var(--text-secondary);">All fields are optional. Only send fields you want to update.</p>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Webhook Name",
    "url": "https://your-app.com/webhooks/billing-v2",
    "events": ["payment.*", "invoice.*"],
    "updated_at": "2026-03-24T16:00:00+00:00"
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Delete Webhook --}}
    <x-docs.endpoint
        id="delete-webhook"
        method="DELETE"
        url="/api/v1/products/{product}/webhooks/{webhook}"
        title="Delete Webhook"
        description="Delete a webhook configuration">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook deleted successfully"
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Test Webhook --}}
    <x-docs.endpoint
        id="test-webhook"
        method="POST"
        url="/api/v1/products/{product}/webhooks/{webhook}/test"
        title="Test Webhook"
        description="Send a test webhook delivery to verify your endpoint is working correctly">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Test webhook sent successfully",
  "delivery": {
    "id": 1234,
    "status": "sent",
    "http_status_code": 200,
    "response_body": "{\"received\": true}",
    "duration_ms": 145,
    "sent_at": "2026-03-24T16:30:00+00:00"
  }
}
            </x-docs.code-block>
            <div style="background: rgba(23,162,184,0.1); border-left: 4px solid #17a2b8; padding: 12px; margin-top: 16px; border-radius: 4px;">
                <p style="margin: 0;">💡 <strong>Test payload:</strong> A real <code>payment.success</code> payload is built from your product's most recent cleared payment and sent to your endpoint — the same schema you will receive in production. If no payment exists yet, a synthetic sample is used. This means your signature verification, field parsing, and routing logic are tested against the actual payload structure.</p>
            </div>
        </x-slot>
    </x-docs.endpoint>

    {{-- Webhook Deliveries --}}
    <x-docs.endpoint
        id="webhook-deliveries"
        method="GET"
        url="/api/v1/products/{product}/webhooks/{webhook}/deliveries"
        title="Webhook Delivery History"
        description="View delivery history and retry status for a webhook">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
            <h4>Query Parameters</h4>
            <table>
                <tr>
                    <td><code>status</code></td>
                    <td>string</td>
                    <td>optional</td>
                    <td>Filter by status: sent, failed, pending</td>
                </tr>
                <tr>
                    <td><code>per_page</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Results per page (default: 15)</td>
                </tr>
                <tr>
                    <td><code>page</code></td>
                    <td>integer</td>
                    <td>optional</td>
                    <td>Page number (default: 1)</td>
                </tr>
            </table>
        </x-slot>

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
      "id": 5001,
      "webhook_id": 10,
      "event_type": "payment.success",
      "status": "sent",
      "attempt_count": 1,
      "http_status_code": 200,
      "response_body": "{\"received\": true}",
      "duration_ms": 234,
      "sent_at": "2026-03-24T15:00:00+00:00",
      "next_retry_at": null,
      "created_at": "2026-03-24T15:00:00+00:00"
    },
    {
      "id": 5002,
      "webhook_id": 10,
      "event_type": "invoice.paid",
      "status": "failed",
      "attempt_count": 3,
      "http_status_code": 500,
      "error_message": "Connection timeout",
      "duration_ms": 30000,
      "sent_at": "2026-03-24T14:00:00+00:00",
      "next_retry_at": "2026-03-24T14:45:00+00:00",
      "created_at": "2026-03-24T14:00:00+00:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    {{-- Regenerate Secret --}}
    <x-docs.endpoint
        id="regenerate-secret"
        method="POST"
        url="/api/v1/products/{product}/webhooks/{webhook}/regenerate-secret"
        title="Regenerate Webhook Secret"
        description="Generate a new secret for webhook signature verification. The old secret will be immediately invalidated.">
        
        <x-slot name="parameters">
            <h4>Path Parameters</h4>
            <table>
                <tr>
                    <td><code>product</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Product ID (e.g., 1, 42)</td>
                </tr>
                <tr>
                    <td><code>webhook</code></td>
                    <td>integer</td>
                    <td>required</td>
                    <td>Webhook ID (e.g., 10, 25)</td>
                </tr>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">200 OK</span>
            </div>
            <x-docs.code-block language="json">
{
  "success": true,
  "message": "Webhook secret regenerated successfully",
  "data": {
    "id": 1,
    "secret": "whsec_z9y8x7w6v5u4t3s2r1q0p9o8n7m6l5k4"
  }
}
            </x-docs.code-block>
            <div style="background: var(--warning-bg, #fff3cd); border-left: 4px solid var(--warning, #ffc107); padding: 12px; margin-top: 16px; border-radius: 4px;">
                <p style="margin: 0; color: var(--warning-dark, #856404);">⚠️ <strong>Warning:</strong> Update your webhook verification code with the new secret before regenerating, otherwise webhook deliveries will fail verification.</p>
            </div>
        </x-slot>
    </x-docs.endpoint>

    {{-- Payload Reference --}}
    <div style="background: var(--surface-soft); padding: 24px; border-radius: 8px; margin-top: 32px;">
        <h3 style="margin-top: 0; color: var(--text-primary);">📦 Payload Reference</h3>

        <p>All events share a common envelope. Fields that don't apply to a given event are sent as <code>null</code>.</p>

        <h4 style="color: var(--text-primary);">Common Envelope Fields</h4>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px;">Field</th>
                    <th style="text-align: left; padding: 8px;">Type</th>
                    <th style="text-align: left; padding: 8px;">Description</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>event</code></td><td style="padding: 8px;">string</td><td style="padding: 8px;">Event name — route your handler on this field</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>event_id</code></td><td style="padding: 8px;">string</td><td style="padding: 8px;">Globally unique ID — use for deduplication</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>timestamp</code></td><td style="padding: 8px;">ISO 8601</td><td style="padding: 8px;">When the event was triggered</td></tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);"><td style="padding: 8px;"><code>api_version</code></td><td style="padding: 8px;">string</td><td style="padding: 8px;">Payload schema version (<code>2026-03-24</code>)</td></tr>
                <tr><td style="padding: 8px;"><code>customer_id</code></td><td style="padding: 8px;">integer</td><td style="padding: 8px;">Shortcut to the customer — also present inside <code>customer.id</code></td></tr>
            </tbody>
        </table>

        <h4 style="color: var(--text-primary);">Complete <code>payment.success</code> Example</h4>
        <x-docs.code-block language="json">
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
        </x-docs.code-block>

        <div style="background: rgba(23,162,184,0.1); border-left: 4px solid #17a2b8; padding: 12px; margin: 16px 0; border-radius: 4px;">
            <p style="margin: 0;">💡 <strong><code>payment.status</code> values:</strong> <code>success</code> (cleared), <code>pending</code>, <code>failed</code>, <code>cancelled</code>, <code>refunded</code>. The system stores payments internally as <code>cleared</code> but always sends <code>success</code> in the webhook payload.</p>
        </div>

        {{-- Per-Event Payload Reference --}}
        <h4 style="color: var(--text-primary); margin-top: 28px;">📋 Per-Event Payload Reference</h4>
        <p>All events share the same top-level envelope (<code>event</code>, <code>event_id</code>, <code>timestamp</code>, <code>api_version</code>, <code>customer_id</code>, <code>product</code>, <code>organization</code>, <code>customer</code>, <code>metadata</code>). The table below shows which additional blocks are <strong>present</strong>, <strong>optional</strong>, or <strong>absent</strong> per event.</p>

        <div style="overflow-x: auto; margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-primary);">
                    <th style="text-align: left; padding: 8px; white-space: nowrap;">Block</th>
                    <th style="padding: 8px; text-align: center;">payment.success</th>
                    <th style="padding: 8px; text-align: center;">payment.failed</th>
                    <th style="padding: 8px; text-align: center;">subscription.created</th>
                    <th style="padding: 8px; text-align: center;">subscription.renewed</th>
                    <th style="padding: 8px; text-align: center;">subscription.cancelled</th>
                    <th style="padding: 8px; text-align: center;">subscription.expired</th>
                    <th style="padding: 8px; text-align: center;">subscription.upgraded</th>
                    <th style="padding: 8px; text-align: center;">credits.purchased</th>
                    <th style="padding: 8px; text-align: center;">invoice.created</th>
                    <th style="padding: 8px; text-align: center;">invoice.paid</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>payment</code></td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ + errors</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #fd7e14;">optional</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #fd7e14;">optional</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>invoice</code></td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ ¹</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ ¹</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>subscription</code></td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ ²</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ ²</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓ (new plan)</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">null</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>gateway_details</code></td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #999;">all null</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #999;">all null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">all null</td>
                    <td style="padding: 8px; text-align: center; color: #999;">all null</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #999;">all null</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>cancellation</code></td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>expired_at</code></td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border-secondary);">
                    <td style="padding: 8px; font-weight: 600;"><code>upgrade</code></td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                </tr>
                <tr>
                    <td style="padding: 8px; font-weight: 600;"><code>wallet_transaction</code></td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #28a745;">✓</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                    <td style="padding: 8px; text-align: center; color: #dc3545;">—</td>
                </tr>
            </tbody>
        </table>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0 0 24px;">
            <strong>✓</strong> = always present &nbsp;|&nbsp;
            <strong style="color: #fd7e14;">optional</strong> = present only when applicable &nbsp;|&nbsp;
            <strong style="color: #999;">null</strong> = key sent with <code>null</code> value &nbsp;|&nbsp;
            <strong style="color: #dc3545;">—</strong> = key not included in payload<br>
            ¹ <code>invoice</code> in payment events: resolved via <code>invoice_payments</code> join for UCN payments; null only if no invoice chain exists.<br>
            ² <code>subscription</code> in payment events: customer's most recent active subscription at time of payment.
        </p>

        {{-- Individual Event Payloads --}}
        <h4 style="color: var(--text-primary);">Event-specific payload examples</h4>

        <p><strong><code>payment.failed</code></strong> — identical to <code>payment.success</code> with two extra fields on the <code>payment</code> object:</p>
        <x-docs.code-block language="json">
"payment": {
  "status":        "failed",
  "error_code":    "card_declined",
  "error_message": "Your card was declined."
}
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>subscription.created</code></strong> — <code>invoice</code>, <code>payment</code>, and <code>gateway_details</code> are all <code>null</code>. The event fires once per subscription record, immediately when the subscription is created:</p>
        <x-docs.code-block language="json">
{
  "event":       "subscription.created",
  "event_id":    "evt_68026f3a4b1e7",
  "timestamp":   "2026-04-01T08:00:00+00:00",
  "api_version": "2026-03-24",
  "customer_id": 42,
  "product":        { "id": 3, "name": "School Management System", ... },
  "organization":   { "id": 1, "name": "Shule Soft Africa" },
  "customer":       { "id": 42, "name": "Mwanafunzi Primary School", ... },
  "subscription": {
    "id":                   18,
    "status":               "active",
    "price_plan_id":        5,
    "price_plan_name":      "Standard Term Plan",
    "billing_interval":     "monthly",
    "amount":               75000.00,
    "currency":             "TZS",
    "starts_at":            "2026-04-01",
    "ends_at":              "2026-05-01",
    "current_period_start": "2026-04-01",
    "current_period_end":   "2026-05-01",
    "next_billing_date":    "2026-05-01",
    "trial_ends_at":        null,
    "canceled_at":          null
  },
  "invoice":          null,
  "payment":          null,
  "gateway_details":  { "stripe": null, "flutterwave": null, "ucn": null },
  "metadata":         { ... }
}
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>subscription.renewed</code></strong> — <code>invoice</code> and <code>gateway_details</code> are <strong>not included</strong> in this event. <code>payment</code> is optional — present only when a payment was captured as part of the renewal:</p>
        <x-docs.code-block language="json">
{
  "event":        "subscription.renewed",
  "subscription": { "id": 18, "status": "active", "current_period_start": "2026-05-01", "current_period_end": "2026-06-01", ... },
  "payment":      { "id": 201, "amount": 75000.00, ... },
  "metadata":     { ... }
  // NOTE: "invoice" and "gateway_details" are NOT present in this event
}
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>subscription.cancelled</code></strong> — adds a <code>cancellation</code> block. <code>invoice</code>, <code>payment</code>, <code>gateway_details</code> are all <code>null</code>:</p>
        <x-docs.code-block language="json">
"cancellation": {
  "reason":       "Customer requested cancellation",
  "cancelled_at": "2026-03-29T10:15:00+00:00"
}
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>subscription.expired</code></strong> — adds a top-level <code>expired_at</code> date. <code>invoice</code>, <code>payment</code>, <code>gateway_details</code> are all <code>null</code>:</p>
        <x-docs.code-block language="json">
"expired_at": "2026-03-31"
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>subscription.upgraded</code></strong> — <code>subscription</code> reflects the <strong>new</strong> plan. Adds an <code>upgrade</code> block showing old vs new plan. <code>invoice</code>, <code>payment</code>, <code>gateway_details</code> are all <code>null</code>:</p>
        <x-docs.code-block language="json">
"upgrade": {
  "previous_plan": { "id": 5, "name": "Standard Term Plan",   "amount": 150000.00, "interval": "quarterly" },
  "new_plan":      { "id": 7, "name": "Premium Annual Plan",  "amount": 500000.00, "interval": "yearly" },
  "upgraded_at":   "2026-03-29T10:15:00+00:00"
}
        </x-docs.code-block>

        <p style="margin-top: 20px;"><strong><code>credits.purchased</code></strong> — replaces <code>invoice</code>, <code>subscription</code>, and <code>gateway_details</code> with a <code>wallet_transaction</code> block. <code>payment</code> is optional:</p>
        <x-docs.code-block language="json">
{
  "event":   "credits.purchased",
  "wallet_transaction": {
    "invoice_id":     55,
    "invoice_number": "INV-2026-0055",
    "wallet_type":    "sms",
    "unit":           "SMS",
    "units":          1000,
    "unit_price":     0.05,
    "amount":         50000.00,
    "currency":       "TZS",
    "plan_id":        12,
    "plan_name":      "SMS Credit Pack - 1000 units",
    "purchased_at":   "2026-03-29T10:15:00+00:00"
  },
  "payment": { "id": 188, "amount": 50000.00, ... },
  // NOTE: "invoice", "subscription", "gateway_details" are NOT present in this event
  "metadata": { ... }
}
        </x-docs.code-block>

        <h4 style="color: var(--text-primary); margin-top: 24px;"><code>gateway_details</code> by gateway</h4>
        <p>Only the key matching the active gateway is populated; others are <code>null</code>. This block is sent on <code>payment.success</code>, <code>payment.failed</code>, and <code>invoice.paid</code> only.</p>
        <x-docs.code-block language="json">
// Flutterwave
"gateway_details": {
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

// UCN (bank / control-number transfer)
"gateway_details": {
  "stripe": null,
  "flutterwave": null,
  "ucn": {
    "control_number":  "9920240001234",
    "bill_id":         "BILL-99",
    "payer_name":      "JOHN DOE",
    "payer_phone":     "+255712345678",
    "payment_channel": "bank_transfer",
    "sp_code":         "SP001"
  }
}
        </x-docs.code-block>
    </div>
</section>
