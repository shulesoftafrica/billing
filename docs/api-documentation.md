## Auth

### Auth Middleware
**Method:** `N/A`
**URL:** `N/A`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "message": "Authenticated request accepted when APP_ACCESS_TOKEN is valid."
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`403 Forbidden`
```json
{
  "message": "Forbidden"
}
```

`404 Not Found`
```json
{
  "message": "Resource not found."
}
```

`422 Unprocessable Entity`
```json
{
  "message": "The given data was invalid.",
  "errors": {}
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "An unexpected error occurred. Please try again later."
}
```

### Handle User
**Method:** `GET`
**URL:** `/api/user`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "id": 1,
  "email": "user@example.com"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Bank Accounts

### List Bank Accounts
**Method:** `GET`
**URL:** `/api/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Bank Accounts
**Method:** `POST`
**URL:** `/api/bank-accounts`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "account_number": "sample",
  "branch": "sample",
  "refer_bank_id": "sample",
  "organization_id": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "account_number": [
      "The account number field is invalid."
    ],
    "branch": [
      "The branch field is invalid."
    ],
    "refer_bank_id": [
      "The refer bank id field is invalid."
    ],
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Bank Accounts
**Method:** `DELETE`
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Bank Accounts
**Method:** `GET`
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Bank Accounts
**Method:** `PUT`
**URL:** `/api/bank-accounts/{bank_account}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "account_number": "sample",
  "branch": "sample",
  "refer_bank_id": "sample",
  "organization_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "account_number": [
      "The account number field is invalid."
    ],
    "branch": [
      "The branch field is invalid."
    ],
    "refer_bank_id": [
      "The refer bank id field is invalid."
    ],
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Countries

### List Countries
**Method:** `GET`
**URL:** `/api/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Countries
**Method:** `POST`
**URL:** `/api/countries`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "code": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "code": [
      "The code field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Countries
**Method:** `DELETE`
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Countries
**Method:** `GET`
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Countries
**Method:** `PUT`
**URL:** `/api/countries/{country}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "code": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "code": [
      "The code field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```
## Customers

### List Customers
**Method:** `GET`
**URL:** `/api/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "username": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Customers
**Method:** `POST`
**URL:** `/api/customers`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "name": "sample",
  "username": "sample",
  "email": "sample",
  "phone": "sample",
  "status": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Lookupbyemailwithstatus Customers
**Method:** `GET`
**URL:** `/api/customers/by-email/{email}/status`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Lookupbyphonewithstatus Customers
**Method:** `GET`
**URL:** `/api/customers/by-phone/{phone}/status`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Customers
**Method:** `DELETE`
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Customers
**Method:** `GET`
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Customers
**Method:** `PUT`
**URL:** `/api/customers/{customer}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "name": "sample",
  "username": "sample",
  "email": "sample",
  "phone": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "username": [
      "The username field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### List Customers
**Method:** `GET`
**URL:** `/api/customers/{customer}/addresses`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Customers
**Method:** `POST`
**URL:** `/api/customers/{customer}/addresses`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "type": "sample",
  "country": "sample",
  "city": "sample",
  "address_line": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "type": [
      "The type field is invalid."
    ],
    "country": [
      "The country field is invalid."
    ],
    "city": [
      "The city field is invalid."
    ],
    "address_line": [
      "The address line field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Customers
**Method:** `DELETE`
**URL:** `/api/customers/{customer}/addresses/{address}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Customers
**Method:** `GET`
**URL:** `/api/customers/{customer}/addresses/{address}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Customers
**Method:** `PUT`
**URL:** `/api/customers/{customer}/addresses/{address}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "type": "sample",
  "country": "sample",
  "city": "sample",
  "address_line": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "type": [
      "The type field is invalid."
    ],
    "country": [
      "The country field is invalid."
    ],
    "city": [
      "The city field is invalid."
    ],
    "address_line": [
      "The address line field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getcustomersubscriptions Customers
**Method:** `GET`
**URL:** `/api/customers/{customer}/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Webhooks

### Generateflutterwavepayloadhash Flutterwave
**Method:** `POST`
**URL:** `/api/flutterwave/hash`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "payload": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "hash": "base64hash==",
  "algorithm": "HMAC-SHA256-BASE64"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "payload": [
      "The payload field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Handleucnpayment Webhooks
**Method:** `POST`
**URL:** `/api/webhooks/ecobank/notification`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "responseCode": "000"
}
```

**Error Responses:**

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Handleflutterwavewebhook Webhooks
**Method:** `POST`
**URL:** `/api/webhooks/flutterwave`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| flutterwave-signature | {base64_hmac_sha256} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Event received"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "success": false,
  "message": "Invalid webhook signature"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Handle Webhooks
**Method:** `POST`
**URL:** `/api/webhooks/stripe`

**Required Headers:**
| Key | Value |
|-----|-------|
| Content-Type | application/json |
| Stripe-Signature | t={timestamp},v1={signature} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true
}
```

**Error Responses:**

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "error": "Invalid webhook signature"
}
```

## Invoices

### List Invoices
**Method:** `GET`
**URL:** `/api/invoices`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id|product_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id|product_id": [
      "The organization id|product id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Invoices
**Method:** `POST`
**URL:** `/api/invoices`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "customer": "sample",
  "customer.name": "sample",
  "customer.email": "sample",
  "customer.phone": "sample",
  "products": "sample",
  "products.*.price_plan_id": "sample",
  "products.*.amount": "sample",
  "tax_rate_ids": "sample",
  "description": "sample",
  "currency": "sample",
  "status": "sample",
  "date": "sample",
  "due_date": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "customer": [
      "The customer field is invalid."
    ],
    "customer.name": [
      "The customer name field is invalid."
    ],
    "customer.email": [
      "The customer email field is invalid."
    ],
    "customer.phone": [
      "The customer phone field is invalid."
    ],
    "products": [
      "The products field is invalid."
    ],
    "products.*.price_plan_id": [
      "The products 0 price plan id field is invalid."
    ],
    "products.*.amount": [
      "The products 0 amount field is invalid."
    ],
    "tax_rate_ids": [
      "The tax rate ids field is invalid."
    ],
    "tax_rate_ids.*": [
      "The tax rate ids 0 field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ],
    "date": [
      "The date field is invalid."
    ],
    "due_date": [
      "The due date field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getbysubscriptions Invoices
**Method:** `POST`
**URL:** `/api/invoices/by-subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "subscription_ids": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "subscription_ids": [
      "The subscription ids field is invalid."
    ],
    "subscription_ids.*": [
      "The subscription ids 0 field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Createplandowngradeinvoice Invoices
**Method:** `POST`
**URL:** `/api/invoices/plan-downgrade`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `None OK`
```json
{}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "Method App\\Http\\Controllers\\Api\\InvoiceController::createPlanDowngradeInvoice does not exist."
}
```

### Createplanupgradeinvoice Invoices
**Method:** `POST`
**URL:** `/api/invoices/plan-upgrade`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `None OK`
```json
{}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "Method App\\Http\\Controllers\\Api\\InvoiceController::createPlanUpgradeInvoice does not exist."
}
```

### Createwallettopupinvoice Invoices
**Method:** `POST`
**URL:** `/api/invoices/wallet-topup`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `None OK`
```json
{}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "Method App\\Http\\Controllers\\Api\\InvoiceController::createWalletTopupInvoice does not exist."
}
```

### Delete Invoices
**Method:** `DELETE`
**URL:** `/api/invoices/{invoice}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "No response returned by controller action"
}
```

### Get Invoices
**Method:** `GET`
**URL:** `/api/invoices/{invoice}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Invoices
**Method:** `PUT`
**URL:** `/api/invoices/{invoice}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

`500 Internal Server Error`
```json
{
  "message": "No response returned by controller action"
}
```

### Getbyproduct Invoices
**Method:** `GET`
**URL:** `/api/invoices/{product_id}/product`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "product_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "product_id": [
      "The product id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Organizations

### List Organizations
**Method:** `GET`
**URL:** `/api/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Organizations
**Method:** `POST`
**URL:** `/api/organizations`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "phone": "sample",
  "email": "sample",
  "currency": "sample",
  "country_id": "sample",
  "status": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "country_id": [
      "The country id field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Integratepaymentgateway Organizations
**Method:** `POST`
**URL:** `/api/organizations/integrate-payment-gateway`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "payment_gateway_id": "sample",
  "endpoint": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "payment_gateway_id": [
      "The payment gateway id field is invalid."
    ],
    "endpoint": [
      "The endpoint field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Organizations
**Method:** `DELETE`
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Organizations
**Method:** `GET`
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Organizations
**Method:** `PUT`
**URL:** `/api/organizations/{organization}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "phone": "sample",
  "email": "sample",
  "currency": "sample",
  "country_id": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "phone": [
      "The phone field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "country_id": [
      "The country id field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Payment Gateways

### List Payment Gateways
**Method:** `GET`
**URL:** `/api/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Payment Gateways
**Method:** `POST`
**URL:** `/api/payment-gateways`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "type": "sample",
  "webhook_secret": "sample",
  "config": "sample",
  "active": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "type": [
      "The type field is invalid."
    ],
    "webhook_secret": [
      "The webhook secret field is invalid."
    ],
    "config": [
      "The config field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Testallconnections Payment Gateways
**Method:** `GET`
**URL:** `/api/payment-gateways/test-all-connections`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Testconnection Payment Gateways
**Method:** `GET`
**URL:** `/api/payment-gateways/test-connection`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "gateway_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "gateway_id": [
      "The gateway id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Payment Gateways
**Method:** `DELETE`
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Payment Gateways
**Method:** `GET`
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Payment Gateways
**Method:** `PUT`
**URL:** `/api/payment-gateways/{payment_gateway}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "type": "sample",
  "webhook_secret": "sample",
  "config": "sample",
  "active": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "type": [
      "The type field is invalid."
    ],
    "webhook_secret": [
      "The webhook secret field is invalid."
    ],
    "config": [
      "The config field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Payments

### Getbydaterange Payments
**Method:** `GET`
**URL:** `/api/payments`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "date_from": "sample",
  "date_to": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "date_from": [
      "The date from field is invalid."
    ],
    "date_to": [
      "The date to field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getbyinvoice Payments
**Method:** `GET`
**URL:** `/api/payments/by-invoice/{invoice_id}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Createintent Payments
**Method:** `POST`
**URL:** `/api/payments/intent`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "amount": "sample",
  "currency": "sample",
  "customer": "sample",
  "description": "sample",
  "metadata": "sample",
  "receipt_email": "sample",
  "capture_method": "sample",
  "statement_descriptor": "sample",
  "payment": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "amount": [
      "The amount field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "customer": [
      "The customer field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "metadata": [
      "The metadata field is invalid."
    ],
    "receipt_email": [
      "The receipt email field is invalid."
    ],
    "capture_method": [
      "The capture method field is invalid."
    ],
    "statement_descriptor": [
      "The statement descriptor field is invalid."
    ],
    "payment": [
      "The payment field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Product Types

### List Product Types
**Method:** `GET`
**URL:** `/api/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Product Types
**Method:** `POST`
**URL:** `/api/product-types`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Product Types
**Method:** `DELETE`
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Product Types
**Method:** `GET`
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Product Types
**Method:** `PUT`
**URL:** `/api/product-types/{product_type}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Product Usages

### Create Product Usages
**Method:** `POST`
**URL:** `/api/product-usages`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "product_id": "sample",
  "quantity": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "product_id": [
      "The product id field is invalid."
    ],
    "quantity": [
      "The quantity field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getbalance Product Usages
**Method:** `GET`
**URL:** `/api/product-usages/balance`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "product_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "product_id": [
      "The product id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Getusagereportbycustomer Product Usages
**Method:** `GET`
**URL:** `/api/product-usages/{customer_id}/report`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Gethistory Product Usages
**Method:** `GET`
**URL:** `/api/product-usages/{customer_id}/{product_id}/history`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Products

### List Products
**Method:** `GET`
**URL:** `/api/products`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "product_type": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "product_type": [
      "The product type field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Products
**Method:** `POST`
**URL:** `/api/products`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "product_type_id": "sample",
  "name": "sample",
  "description": "sample",
  "unit": "sample",
  "status": "sample",
  "price_plans": "sample",
  "price_plans.*.name": "sample",
  "price_plans.*.subscription_type": "sample",
  "price_plans.*.amount": "sample",
  "price_plans.*.currency": "sample",
  "price_plans.*.rate": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "product_type_id": [
      "The product type id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "unit": [
      "The unit field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ],
    "price_plans": [
      "The price plans field is invalid."
    ],
    "price_plans.*.name": [
      "The price plans 0 name field is invalid."
    ],
    "price_plans.*.subscription_type": [
      "The price plans 0 subscription type field is invalid."
    ],
    "price_plans.*.amount": [
      "The price plans 0 amount field is invalid."
    ],
    "price_plans.*.currency": [
      "The price plans 0 currency field is invalid."
    ],
    "price_plans.*.rate": [
      "The price plans 0 rate field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Products
**Method:** `DELETE`
**URL:** `/api/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Products
**Method:** `GET`
**URL:** `/api/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Products
**Method:** `PUT`
**URL:** `/api/products/{product}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "product_type_id": "sample",
  "name": "sample",
  "description": "sample",
  "status": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "product_type_id": [
      "The product type id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "status": [
      "The status field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### List Products
**Method:** `GET`
**URL:** `/api/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Products
**Method:** `POST`
**URL:** `/api/products/{product}/price-plans`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "subscription_type": "sample",
  "amount": "sample",
  "currency": "sample",
  "rate": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "subscription_type": [
      "The subscription type field is invalid."
    ],
    "amount": [
      "The amount field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Products
**Method:** `DELETE`
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Products
**Method:** `GET`
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Products
**Method:** `PUT`
**URL:** `/api/products/{product}/price-plans/{pricePlan}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "name": "sample",
  "subscription_type": "sample",
  "amount": "sample",
  "currency": "sample",
  "rate": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "name": [
      "The name field is invalid."
    ],
    "subscription_type": [
      "The subscription type field is invalid."
    ],
    "amount": [
      "The amount field is invalid."
    ],
    "currency": [
      "The currency field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Subscriptions

### List Subscriptions
**Method:** `GET`
**URL:** `/api/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Subscriptions
**Method:** `POST`
**URL:** `/api/subscriptions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "plan_ids": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "plan_ids": [
      "The plan ids field is invalid."
    ],
    "plan_ids.*": [
      "The plan ids 0 field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Cancel Subscriptions
**Method:** `POST`
**URL:** `/api/subscriptions/{id}/cancel`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Tax Rates

### List Tax Rates
**Method:** `GET`
**URL:** `/api/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Tax Rates
**Method:** `POST`
**URL:** `/api/tax-rates`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "country": "sample",
  "name": "sample",
  "rate": "sample",
  "active": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "country": [
      "The country field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Tax Rates
**Method:** `DELETE`
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Tax Rates
**Method:** `GET`
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Tax Rates
**Method:** `PUT`
**URL:** `/api/tax-rates/{tax_rate}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "country": "sample",
  "name": "sample",
  "rate": "sample",
  "active": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "country": [
      "The country field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "rate": [
      "The rate field is invalid."
    ],
    "active": [
      "The active field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Users

### List Users
**Method:** `GET`
**URL:** `/api/users`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Users
**Method:** `POST`
**URL:** `/api/users`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "name": "sample",
  "email": "sample",
  "password": "sample",
  "role": "sample",
  "sex": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "password": [
      "The password field is invalid."
    ],
    "role": [
      "The role field is invalid."
    ],
    "sex": [
      "The sex field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Delete Users
**Method:** `DELETE`
**URL:** `/api/users/{user}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Users
**Method:** `GET`
**URL:** `/api/users/{user}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`404 Not Found`
```json
{
  "success": false,
  "message": "Resource not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Update Users
**Method:** `PUT`
**URL:** `/api/users/{user}`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": "sample",
  "name": "sample",
  "email": "sample",
  "password": "sample",
  "role": "sample",
  "sex": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "organization_id": [
      "The organization id field is invalid."
    ],
    "name": [
      "The name field is invalid."
    ],
    "email": [
      "The email field is invalid."
    ],
    "password": [
      "The password field is invalid."
    ],
    "role": [
      "The role field is invalid."
    ],
    "sex": [
      "The sex field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

## Wallets

### Getbalance Wallets
**Method:** `GET`
**URL:** `/api/wallets/balance`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "wallet_type": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Checkbalance Wallets
**Method:** `GET`
**URL:** `/api/wallets/check-balance`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "wallet_type": "sample",
  "required_amount": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "required_amount": [
      "The required amount field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Addcredits Wallets
**Method:** `POST`
**URL:** `/api/wallets/credit`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "wallet_type": "sample",
  "units": "sample",
  "unit_price": "sample",
  "description": "sample",
  "invoice_id": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "units": [
      "The units field is invalid."
    ],
    "unit_price": [
      "The unit price field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ],
    "invoice_id": [
      "The invoice id field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Deductcredits Wallets
**Method:** `POST`
**URL:** `/api/wallets/deduct`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "wallet_type": "sample",
  "units": "sample",
  "description": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "units": [
      "The units field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Gettransactionsbywallet Wallets
**Method:** `GET`
**URL:** `/api/wallets/transactions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": "sample",
  "wallet_type": "sample",
  "limit": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "customer_id": [
      "The customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "limit": [
      "The limit field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Transfercredits Wallets
**Method:** `POST`
**URL:** `/api/wallets/transfer`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "from_customer_id": "sample",
  "to_customer_id": "sample",
  "wallet_type": "sample",
  "units": "sample",
  "description": "sample"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "from_customer_id": [
      "The from customer id field is invalid."
    ],
    "to_customer_id": [
      "The to customer id field is invalid."
    ],
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "units": [
      "The units field is invalid."
    ],
    "description": [
      "The description field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Gettransactionhistory Wallets
**Method:** `GET`
**URL:** `/api/wallets/{customer_id}/transactions`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "wallet_type": "sample",
  "transaction_type": "sample",
  "limit": "sample"
}
```

**Success Response:** `200 OK`
```json
{
  "success": true,
  "data": {}
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
```

`422 Unprocessable Entity`
```json
{
  "errors": {
    "wallet_type": [
      "The wallet type field is invalid."
    ],
    "transaction_type": [
      "The transaction type field is invalid."
    ],
    "limit": [
      "The limit field is invalid."
    ]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```
