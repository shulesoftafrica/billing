# Billing Platform – Requirements & API Design Document

> **Status:** Working Draft (Living Document)
>
> **Purpose:** This document defines the functional, technical, and integration requirements for the Billing Platform. It is intended to be iteratively refined until it becomes the final system design and implementation blueprint.

---

## 1. Overview

### 1.1 What This Platform Is
The Billing Platform is a **central financial orchestration layer** responsible for defining, generating, collecting, reconciling, settling, auditing, and exposing all billable obligations across multiple systems and payment channels.

It is **not a payment gateway**, but a **control, intelligence, and accounting layer** that sits above banks, payment networks, and SaaS platforms.

### 1.2 What This Platform Is Not

To avoid ambiguity, the table below clearly distinguishes a **Payment Gateway**, **Invoicing System**, and **Billing Platform**.

| Dimension | Payment Gateway | Invoicing System | Billing Platform (This System) |
|---------|-----------------|------------------|--------------------------------|
| Primary Role | Moves money between payer and bank | Generates invoices for payment | Controls the full financial obligation lifecycle |
| Owns Amount Logic | ❌ No | ⚠️ Limited | ✅ Yes (authoritative) |
| Bill Lifecycle | ❌ None | ⚠️ Issue & mark paid | ✅ Full lifecycle (draft → paid → settled) |
| Discount Handling | ❌ Not supported | ⚠️ Basic, non-audited | ✅ Audited, governed, ledger-backed |
| Partial Payments | ❌ Channel-dependent | ⚠️ Limited | ✅ Native support |
| Overpayments / Refunds | ❌ Channel-specific | ❌ Weak | ✅ Controlled & reconciled |
| Settlement Logic | ❌ No | ❌ No | ✅ Multi-party, rule-based |
| Ledger & Accounting | ❌ No | ❌ No | ✅ Double-entry ledger |
| Reconciliation Authority | ❌ No | ❌ No | ✅ Yes |
| Bank / Regulator Ready | ⚠️ Channel only | ❌ No | ✅ Yes |
| API-First | ⚠️ Sometimes | ❌ Rarely | ✅ Mandatory |
| Example Products | Card processors, mobile money | Simple invoicing tools | SafariBank / ShuleSoft Billing Core |

**Conclusion:**
- A **payment gateway** only *moves money*
- An **invoicing system** only *asks for money*
- A **billing platform** *defines, governs, and accounts for money*

---

## 2. Objectives

### 2.1 Financial Objectives
The system must:
- Create enforceable, traceable bills
- Act as a single source of truth for obligations
- Guarantee deterministic reconciliation
- Support multi-party settlements
- Operate with bank-grade accuracy and auditability

### 2.2 Operational Objectives
The system must:
- Operate 24/7
- Handle high transaction volumes
- Support partial payments, overpayments, refunds, and reversals
- Be fault-tolerant and event-driven
- Provide real-time and historical visibility

### 2.3 Ecosystem Objectives
The system must:
- Support multiple external systems
- Be API-first and integration-friendly
- Be regulator- and bank-ready
- Allow future expansion without redesign

---

## 3. Core Domain Model

### 3.1 Core Entities
The billing platform must clearly define and manage the following entities:

- **Bill** – A financial obligation
- **Bill Item** – Line item within a bill
- **Payer** – The entity responsible for payment
- **Payee / Merchant** – The beneficiary
- **Billing Account** – Logical account linked to payer
- **Control Number / Terminal ID** – External payment reference
- **Payment** – Actual funds received
- **Settlement Instruction** – Rules for distributing funds
- **Ledger Entry** – Accounting record (double-entry)
- **Reconciliation Record** – Link between bill and payment

---

## 4. Bill Lifecycle

Each bill must follow a strict, auditable lifecycle:

1. Draft
2. Issued
3. Active
4. Partially Paid
5. Fully Paid
6. Expired
7. Cancelled
8. Refunded (if applicable)

Lifecycle transitions must be:
- Explicit
- Permission-controlled
- Fully logged

---

## 5. Billing Types Supported

### 5.1 One-Time Bills
- School fees
- Registration fees
- Insurance premiums

### 5.2 Recurring Bills
- Monthly subscriptions
- Term-based fees
- Loan repayments

### 5.3 Usage-Based Bills
- Per transaction
- Per API call
- Per student
- Per volume tier

### 5.4 Composite Bills
- Multiple items
- Multiple beneficiaries
- Multiple settlement rules

---

## 6. Discounts & Adjustments Engine

### 6.1 Design Principle
Discounts and financial adjustments **must be controlled, applied, and recorded by the Billing Platform**. End platforms may *request* discounts based on business rules, but they must never independently alter payable amounts.

> **Rule:** Any logic that changes the amount of money collected is owned by the Billing Platform.

---

### 6.2 Discount as a First-Class Financial Entity
Discounts are not UI-level features; they are auditable financial instruments.

Each discount must be represented as a structured entity linked to a bill or bill item.

**Discount Attributes:**
- `discount_id`
- `type` (percentage | fixed)
- `scope` (bill | bill_item)
- `value`
- `currency`
- `source` (platform | campaign | manual | policy)
- `reason`
- `requested_by`
- `approved_by`
- `approval_status` (pending | approved | rejected | auto-approved)
- `valid_from`
- `valid_to`
- `created_at`

---

### 6.3 Discount Types Supported
The billing platform must support:

1. **Percentage Discounts**
   - e.g. 10% sibling discount

2. **Fixed Amount Discounts**
   - e.g. TZS 50,000 bursary

3. **Conditional Discounts**
   - Early payment
   - Volume-based

4. **Sponsored Discounts**
   - Third-party covers discounted portion

5. **Policy-Based Discounts**
   - Automatically applied based on rules

---

### 6.4 Discount Lifecycle

Each discount must follow a controlled lifecycle:

1. Requested
2. Validated
3. Approved / Auto-approved / Rejected
4. Applied
5. Expired / Revoked

All lifecycle changes must be logged and auditable.

---

### 6.5 Discount Application Rules

The Billing Platform must enforce:

- Maximum discount thresholds per bill
- Role-based approval requirements
- Time-bound validity
- Non-stackable vs stackable discount policies
- Idempotent application (no duplicate discounts)

Once a discount is applied:
- The bill’s **net payable amount is recalculated**
- The bill is **locked for payment reference generation**

---

### 6.6 Discount APIs

**Request a Discount (End Platform → Billing Platform)**
```
POST /v1/bills/{bill_id}/discounts
```
Payload example:
```json
{
  "type": "percentage",
  "value": 10,
  "scope": "bill",
  "reason": "Sibling discount",
  "source": "end_platform",
  "requested_by": "school_admin_123"
}
```

**Approve / Reject Discount**
```
POST /v1/discounts/{discount_id}/approve
POST /v1/discounts/{discount_id}/reject
```

**List Discounts on a Bill**
```
GET /v1/bills/{bill_id}/discounts
```

---

### 6.7 Accounting & Ledger Treatment

Discounts must:
- Be recorded as separate ledger entries
- Reduce gross bill amount to net payable
- Be attributable to the correct revenue or subsidy account

Example ledger treatment:
- Debit: Discount Expense / Subsidy Account
- Credit: Accounts Receivable

---

### 6.8 Reconciliation & Audit

The billing platform must ensure:
- Payments reconcile against **net payable**, not gross amount
- Discount history is visible in all reports
- Banks and auditors can trace:
  - Original amount
  - Discount applied
  - Final collected amount

---

### 6.9 Governance & Risk Controls

The system must support:
- Discount caps per merchant or platform
- Fraud detection triggers on abnormal discount usage
- Manual override with justification
- Emergency discount suspension (kill-switch)

---

## 7. Payment References & Control Numbers

The system must:
- Generate unique, collision-proof references
- Support TIPS control numbers, Terminal IDs, QR payloads
- Allow one reference to accept multiple payments
- Ensure deterministic mapping of payments to bills

---

## 8. Payment Handling

The system must support:
- Real-time payment notifications
- Delayed confirmations
- Idempotent ingestion
- Duplicate payment protection

Payment sources include:
- Bank APIs
- TIPS
- Mobile Money
- Card processors
- Internal transfers

---

## 9. Settlement Engine

The system must:
- Define settlement rules per bill or merchant
- Support percentage, fixed, and tiered splits
- Execute instant, scheduled, or conditional settlements

All settlements must be:
- Deterministic
- Auditable
- Reversible with controls

---

## 10. Reconciliation Engine

The platform must:
- Automatically reconcile payments to bills and ledger entries
- Support real-time and end-of-day reconciliation
- Allow manual overrides with audit trails

---

## 11. Ledger & Accounting Layer

The system must include:
- Double-entry ledger
- Immutable ledger entries
- Clear chart of accounts
- Separation of:
  - Customer balances
  - Platform revenue
  - Trust / collection accounts

---

## 12. API Design Principles

The Billing API must be:
- API-first
- Stateless
- Versioned
- Predictable
- Idempotent
- Secure

---

## 13. Required API Modules

### 12.1 Billing API
```
POST   /v1/bills
GET    /v1/bills/{bill_id}
GET    /v1/bills?payer_id=
PATCH  /v1/bills/{bill_id}
```

### 12.2 Reference / Control Number API
```
POST /v1/references
GET  /v1/references/{reference}
```

### 12.3 Payment Ingestion API
```
POST /v1/payments
```

### 12.4 Reconciliation API
```
POST /v1/reconcile
GET  /v1/reconciliation/{bill_id}
```

### 12.5 Settlement API
```
POST /v1/settlements/rules
POST /v1/settlements/execute
GET  /v1/settlements/{id}
```

### 12.6 Ledger API
```
GET /v1/ledger/entries
GET /v1/ledger/balances
```

---

## 14. Webhooks

The system must emit webhooks for:
- Bill issued
- Bill partially paid
- Bill fully paid
- Overpayment detected
- Settlement completed
- Refund issued

---

## 15. Security & Compliance

### 14.1 Security
- OAuth2 / JWT
- API key rotation
- HMAC verification for callbacks
- Role-based access control
- Encrypted data at rest and in transit

### 14.2 Compliance
- Full audit trails
- Time-stamped logs
- Exportable reports
- Regulator-readable data formats

---

## 16. Integration Support

The platform must support integration with:
- Banks
- Mobile Money Operators
- SaaS platforms
- Insurance systems
- Loan systems
- ERP and accounting tools

Integration methods include:
- REST APIs
- Webhooks
- File-based exchange
- Event streaming

---

## 17. Governance & Controls

The system must support:
- Per-merchant configuration
- Per-client API permissions
- Kill-switches for fraud
- Manual freeze and release of accounts

---

## 18. Open Design Sections (To Be Expanded)

- Database schema
- Event-driven architecture
- Idempotency strategy
- Error codes & taxonomy
- SLA definitions
- Reporting & analytics

---

## 19. Document Control

- This is a **living document**
- All changes must be reviewed and versioned
- Final version becomes implementation contract

---

**Next iteration focus:**
- Detailed data models
- Event flows
- Sequence diagrams
- Bank & TIPS mapping

