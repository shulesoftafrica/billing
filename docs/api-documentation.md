## Authentication

### Overview
**Method:** `Dual Authentication Support`
**URL:** `All API endpoints require authentication`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{
  "authentication_options": [
    "Option 1: User Personal Access Token (Recommended) - Login via /api/auth/login",
    "Option 2: APP_ACCESS_TOKEN (Legacy) - Shared token from system administrator"
  ],
  "recommended_flow": "Use Option 1 for better security and user-level access control",
  "how_it_works": {
    "user_tokens": [
      "1. Register a user account via POST /api/auth/register",
      "2. Login with email/password via POST /api/auth/login",
      "3. Receive personal access token in response",
      "4. Use token in Authorization header for all requests",
      "5. Tokens are user-specific and can be revoked independently"
    ],
    "app_access_token": [
      "1. System administrator configures APP_ACCESS_TOKEN in .env",
      "2. Shared with authorized systems",
      "3. Same token for all API consumers (less secure)",
      "4. For backward compatibility only"
    ]
  }
}
```

**Success Response:** `200 OK`
```json
{
  "message": "Request authenticated successfully"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Unauthenticated",
  "error": "invalid_access_token",
  "hint": "Provide either a valid user token (from /auth/login) or APP_ACCESS_TOKEN"
}
```

### Register New User
**Method:** `POST`
**URL:** `/api/auth/register`

**Required Headers:**
| Key | Value |
|-----|-------|
| Accept | application/json |
| Content-Type | application/json |

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "role": "user",
  "sex": "male"
}
```

**Success Response:** `201 Created`
```json
{
  "message": "User registered successfully",
  "access_token": "1|xK9mP2vL8nQ4wR7tY3uI6oP1aS5dF0gH2jK4mN7bV9cX1zL3qW5eR8tY0uI2oP4",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "sex": "male",
    "created_at": "2026-03-11T10:30:00.000000Z",
    "updated_at": "2026-03-11T10:30:00.000000Z"
  }
}
```

**Error Responses:**

`422 Unprocessable Entity`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

### Login (Get Access Token)
**Method:** `POST`
**URL:** `/api/auth/login`

**Required Headers:**
| Key | Value |
|-----|-------|
| Accept | application/json |
| Content-Type | application/json |

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```

**Success Response:** `200 OK`
```json
{
  "message": "Login successful",
  "access_token": "2|AbCdEfGhIjKlMnOpQrStUvWxYz0123456789AbCdEfGhIjKlMnOpQrStUvWx",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "sex": "male",
    "email_verified_at": null,
    "created_at": "2026-03-11T10:30:00.000000Z",
    "updated_at": "2026-03-11T10:30:00.000000Z"
  }
}
```

**Error Responses:**

`422 Unprocessable Entity`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### Get Current User
**Method:** `GET`
**URL:** `/api/auth/me`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "sex": "male",
    "email_verified_at": null,
    "created_at": "2026-03-11T10:30:00.000000Z",
    "updated_at": "2026-03-11T10:30:00.000000Z"
  }
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

### Logout (Revoke Current Token)
**Method:** `POST`
**URL:** `/api/auth/logout`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "Not authenticated."
}
```

### Logout All Devices (Revoke All Tokens)
**Method:** `POST`
**URL:** `/api/auth/logout-all`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {ACCESS_TOKEN} |
| Accept | application/json |

**Request Body:**
```json
{}
```

**Success Response:** `200 OK`
```json
{
  "message": "Logged out from all devices successfully"
}
```

**Error Responses:**

`401 Unauthorized`
```json
{
  "message": "No authenticated user found for logout-all."
}
```

### Usage Examples with User Token
**Method:** `EXAMPLES`
**URL:** `Complete Authentication Flow`

**Required Headers:**
| Key | Value |
|-----|-------|
| Language | Select your preferred language below |

**Request Body:**
```bash
# === cURL ===
# Step 1: Login to get token
curl -X POST https://your-domain.com/api/auth/login \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123!"
  }'

# Response: { "access_token": "2|YOUR_TOKEN_HERE", ... }

# Step 2: Use token in API requests
curl -X GET https://your-domain.com/api/customers \
  -H 'Authorization: Bearer 2|YOUR_TOKEN_HERE' \
  -H 'Accept: application/json'
```

**Success Response:** `200 OK`
```javascript
// === JavaScript (Fetch) ===
// Step 1: Login to get token
const loginResponse = await fetch('https://your-domain.com/api/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'SecurePassword123!'
  })
});

const { access_token } = await loginResponse.json();

// Step 2: Use token in API requests
const response = await fetch('https://your-domain.com/api/customers', {
  headers: {
    'Authorization': `Bearer ${access_token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();
```

**Error Responses:**

`200 OK`
```python
# === Python (Requests) ===
import requests

# Step 1: Login to get token
login_response = requests.post(
    'https://your-domain.com/api/auth/login',
    json={
        'email': 'john@example.com',
        'password': 'SecurePassword123!'
    },
    headers={'Accept': 'application/json'}
)

access_token = login_response.json()['access_token']

# Step 2: Use token in API requests
response = requests.get(
    'https://your-domain.com/api/customers',
    headers={
        'Authorization': f'Bearer {access_token}',
        'Accept': 'application/json'
    }
)

data = response.json()
```

`200 OK`
```php
// === PHP (Guzzle) ===
<?php
use GuzzleHttp\Client;

$client = new Client();

// Step 1: Login to get token
$loginResponse = $client->post('https://your-domain.com/api/auth/login', [
    'headers' => ['Accept' => 'application/json'],
    'json' => [
        'email' => 'john@example.com',
        'password' => 'SecurePassword123!'
    ]
]);

$loginData = json_decode($loginResponse->getBody(), true);
$accessToken = $loginData['access_token'];

// Step 2: Use token in API requests
$response = $client->get('https://your-domain.com/api/customers', [
    'headers' => [
        'Authorization' => 'Bearer ' . $accessToken,
        'Accept' => 'application/json'
    ]
]);

$data = json_decode($response->getBody(), true);
```

`200 OK`
```php
// === PHP (cURL) ===
<?php

// Step 1: Login to get token
$ch = curl_init('https://your-domain.com/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'john@example.com',
    'password' => 'SecurePassword123!'
]));

$loginResponse = curl_exec($ch);
curl_close($ch);

$loginData = json_decode($loginResponse, true);
$accessToken = $loginData['access_token'];

// Step 2: Use token in API requests
$ch = curl_init('https://your-domain.com/api/customers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Accept: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
```

`200 OK`
```go
// === Go ===
package main

import (
    "bytes"
    "encoding/json"
    "fmt"
    "net/http"
)

func main() {
    // Step 1: Login to get token
    loginData := map[string]string{
        "email":    "john@example.com",
        "password": "SecurePassword123!",
    }
    
    loginBody, _ := json.Marshal(loginData)
    loginReq, _ := http.NewRequest("POST", "https://your-domain.com/api/auth/login", bytes.NewBuffer(loginBody))
    loginReq.Header.Set("Content-Type", "application/json")
    loginReq.Header.Set("Accept", "application/json")
    
    client := &http.Client{}
    loginResp, _ := client.Do(loginReq)
    defer loginResp.Body.Close()
    
    var loginResult map[string]interface{}
    json.NewDecoder(loginResp.Body).Decode(&loginResult)
    accessToken := loginResult["access_token"].(string)
    
    // Step 2: Use token in API requests
    req, _ := http.NewRequest("GET", "https://your-domain.com/api/customers", nil)
    req.Header.Set("Authorization", "Bearer "+accessToken)
    req.Header.Set("Accept", "application/json")
    
    resp, _ := client.Do(req)
    defer resp.Body.Close()
    
    var result map[string]interface{}
    json.NewDecoder(resp.Body).Decode(&result)
    fmt.Println(result)
}
```

### Legacy: APP_ACCESS_TOKEN (Backward Compatibility)
**Method:** `MANUAL - Contact Administrator`
**URL:** `N/A`

**Required Headers:**
| Key | Value |
|-----|-------|
| N/A | Legacy authentication method |

**Request Body:**
```json
{
  "status": "Supported for backward compatibility only",
  "recommendation": "Use the new login-based authentication instead",
  "how_to_use": [
    "1. Contact system administrator for APP_ACCESS_TOKEN",
    "2. Use token in Authorization: Bearer {APP_ACCESS_TOKEN} header",
    "3. Same token shared across all API consumers (less secure)"
  ],
  "migration_path": [
    "1. Create user accounts via POST /api/auth/register",
    "2. Switch to using personal access tokens from /api/auth/login",
    "3. Enjoy better security with user-level access control"
  ]
}
```

**Success Response:** `200 OK`
```json
{
  "message": "APP_ACCESS_TOKEN still works but consider migrating to user tokens"
}
```

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

###  Find Customer by Email
**Method:** `GET`
**URL:** `/api/customers/by-email/{email}`

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

### Find Customer by Phone
**Method:** `GET`
**URL:** `/api/customers/by-phone/{phone}`

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

### Handle UCN payment Webhooks
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

### Handle Flutterwave Webhooks
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

### Handle Stripe PaymantIntent Webhooks
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

### List All Invoices
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
  "success": false,
  "message": "Either organization_id or product_id must be provided"
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

**Success Response:** `200 OK`
```json
{
  "success": true,
  "message": "Invoices retrieved successfully",
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

### Get Invoices by Subscriptions
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

### Cancel Invoices
**Method:** `POST`
**URL:** `/api/invoices/{id}/cancel`

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
  "message": "Invoice cancelled successfully",
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
  "message": "Invoice not found"
}
```

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Invoice is already cancelled"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Get Invoices by id
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
  "message": "Invoice not found"
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```


### Get Invoices by product_id
**Method:** `GET`
**URL:** `/api/invoices/{product_id}/product`

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

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "product_id": [
      "The product id field is required."
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

### Get Payments by Date Range 
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

### Get Payments by invoice 
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

## Product Usages/Wallets

### Record Product Usage
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

### Get Product Usage/Wallet balance
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

### Get Product Usage/Wallet Report
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

### Get Product Usage/Wallet History
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

### List All Products
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

### Get Single Product
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

### Update Product
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

### List Product Price-Plans
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

### Create Product Price-Plans
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

### Delete Product Price-Plans
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

### Get Product Price-Plans
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

### Update Product Price-Plans
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