# Billing System

A comprehensive subscription and invoice management system built with Laravel.

## 🎯 Features

- **Multi-Plan Subscriptions** - Subscribe to multiple price plans in a single request
- **Unified Invoicing** - All plans billed together under one invoice
- **Transaction Safety** - Atomic operations with automatic rollback
- **Duplicate Prevention** - No duplicate active subscriptions
- **Concurrency Control** - Thread-safe with row-level locking
- **Unique Invoice Numbers** - Auto-generated format: `INV[YYYYMMDD][XXXX]`

## 📚 Quick Links

- **[Implementation Summary](IMPLEMENTATION_SUMMARY.md)** - Complete overview
- **[API Documentation](SUBSCRIPTION_API.md)** - Detailed API guide
- **[Quick Reference](SUBSCRIPTION_QUICK_REFERENCE.md)** - Quick start guide
- **[Architecture Diagrams](ARCHITECTURE_DIAGRAMS.md)** - Visual system design
- **[Implementation Checklist](IMPLEMENTATION_CHECKLIST.md)** - Requirements tracking

## 🚀 Getting Started

### Installation

```bash
# Clone repository
git clone <repository-url>
cd billing

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Quick Test

```bash
# Run feature tests
php artisan test --filter SubscriptionTest

# Test API with cURL
curl -X POST http://localhost/api/subscriptions \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "plan_ids": [1, 2, 3]
  }'
```

## 📡 API Endpoints

### Create Subscription

**POST** `/api/subscriptions`

**Request:**
```json
{
  "customer_id": 1,
  "plan_ids": [1, 2, 3]
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Subscriptions created successfully",
  "data": {
    "invoice": {
      "id": 1,
      "invoice_number": "INV202601090001",
      "total": "179.97"
    },
    "invoice_items": [...],
    "customer": {...}
  }
}
```

## 🏗️ System Architecture

```
Client Request
     ↓
SubscriptionController (Validation)
     ↓
SubscriptionService (Business Logic)
     ↓
```
