# Organization Creation with Payment Gateway Integration

## Overview
The organization creation process has been upgraded to automatically integrate with all active payment gateways in a single transactional operation.

## Implementation Details

### Flow
1. **Create Organization** - Standard organization record creation
2. **Fetch Active Gateways** - Query all payment gateways where `active = true`
3. **Process Each Gateway**:
   - **For UCN (Universal Control Number) Gateway**:
     - Fetch available virtual account from `constant.virtual_accounts` (status=1)
     - Create bank account for the organization
     - Update virtual account status to 2 (assigned)
     - Create organization_payment_gateway_integration with bank_account_id
     - Generate API credentials (api_key, signature_key)
     - Call EcoBank API to create merchant
     - Store merchant data in merchants table
     - Update integration status to 'completed'
   - **For Other Gateways**:
     - Create organization_payment_gateway_integration (bank_account_id = null)
     - Generate API credentials

### Transaction Safety
- Entire process wrapped in `DB::beginTransaction()`
- Automatic rollback on any failure via `DB::rollBack()`
- No partial data saved on errors
- UCN merchant creation failures are logged but don't fail the transaction

### Code References
- **ClientRegistrationController.php** - Virtual account fetching, API key generation
- **Partner.php** - Merchant creation, token generation, EcoBank API integration

### Models Created/Used
- `Organization`
- `PaymentGateway`
- `BankAccount`
- `OrganizationPaymentGatewayIntegration`
- `Configuration`
- `Merchant`

### Database Tables Involved
1. `organizations` - Core organization data
2. `payment_gateways` - Gateway definitions (UCN, Stripe, etc.)
3. `bank_accounts` - Bank accounts (UCN only)
4. `organization_payment_gateway_integrations` - Links org to gateway
5. `configurations` - API credentials per gateway
6. `merchants` - Merchant data from EcoBank (UCN only)
7. `constant.virtual_accounts` - Virtual account pool
8. `constant.refer_banks` - Bank reference data

## API Response Format

```json
{
  "success": true,
  "message": "Organization created successfully",
  "data": {
    "organization_detail": {
      "id": 1,
      "name": "Tech Solutions Inc",
      "legal_name": "Tech Solutions Incorporated",
      "currency_id": 1,
      "country_id": 1,
      "timezone": "America/New_York",
      "status": "active",
      "currency": {...},
      "country": {...}
    },
    "payment_gateways": [
      {
        "gateway": {...},
        "bank_account": {...},
        "configurations": {...},
        "merchant": {...}
      },
      {
        "gateway": {...},
        "bank_account": null,
        "configurations": {...},
        "merchant": null
      }
    ]
  }
}
```

## Key Features

### Automated Integration
- All active payment gateways automatically integrated
- No manual configuration needed per gateway

### UCN-Specific Processing
- Virtual account assignment
- Bank account creation
- EcoBank merchant registration
- QR code generation
