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

### Invoice Types Overview

The billing system supports three types of invoices, automatically determined by the product type:

| Invoice Type | Product Type | Billing Pattern | Use Case |
|-------------|--------------|-----------------|----------|
| **One-Time** | One-time Product (product_type_id: 1) | Single charge | One-off services, consulting, project work |
| **Subscription** | Subscription Product (product_type_id: 2) | Recurring charges | SaaS, memberships, monthly/yearly plans |
| **Usage-Based** | Usage Product (product_type: usage) | Pay-per-use | API calls, storage, bandwidth, credits |

**Important:** The invoice type is automatically determined by the product type associated with the price plan. You don't need to explicitly specify the invoice type.

### Create One-Time Invoice
**Method:** `POST`
**URL:** `/api/invoices`

**Description:** One-time invoices are for products that are charged once without creating a subscription. Perfect for consulting services, one-off projects, or standalone purchases.

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Required Parameters:**
- `organization_id` (integer) - Your organization ID
- `customer` (object) - Customer information
- `customer.name` (string) - Customer's full name
- `customer.email` (string) - Customer's email address
- `customer.phone` (string) - Customer's phone number
- `products` (array) - Array of products (minimum 1)
- `products.*.price_plan_id` (integer) - Price plan ID for a one-time product
- `products.*.amount` (number) - Invoice amount for this product
- `currency` (string) - 3-letter currency code (e.g., "TZS", "USD")

**Optional Parameters:**
- `tax_rate_ids` (array) - Array of tax rate IDs to apply
- `description` (string) - Invoice description
- `status` (string) - Invoice status: draft, issued, paid, cancelled (default: "issued")
- `date` (string) - Invoice date in Y-m-d format (default: current date)
- `due_date` (string) - Payment due date in Y-m-d format
- `payment_gateway` (string) - flutterwave, control_number, or both
- `success_url` (string) - Redirect URL after successful payment (required for Flutterwave)
- `cancel_url` (string) - Redirect URL after cancelled payment (required for Flutterwave)

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+255712345678"
  },
  "products": [
    {
      "price_plan_id": 5,
      "amount": 50000
    }
  ],
  "description": "Website development project",
  "currency": "TZS",
  "status": "issued",
  "date": "2026-02-26",
  "due_date": "2026-03-26"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 123,
      "invoice_number": "INV-2026-00123",
      "customer_id": 45,
      "customer_name": "John Doe",
      "customer_email": "john@example.com",
      "currency": "TZS",
      "status": "issued",
      "description": "Website development project",
      "subtotal": 50000,
      "tax_total": 0,
      "total": 50000,
      "date": "2026-02-26",
      "due_date": "2026-03-26",
      "issued_at": "2026-02-26T10:30:00.000000Z",
      "items": [
        {
          "id": 456,
          "price_plan_id": 5,
          "product_name": "Website Development",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        }
      ],
      "taxes": [],
      "payments": []
    }
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

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "organization_id": ["The organization id field is required."],
    "customer.email": ["The customer email must be a valid email address."],
    "products": ["The products field must have at least 1 items."],
    "currency": ["The currency must be 3 characters."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Subscription Invoice
**Method:** `POST`
**URL:** `/api/invoices`

**Description:** Subscription invoices automatically create a subscription record for recurring billing. The subscription remains in "pending" status until the invoice is paid, then becomes "active".

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Required Parameters:**
- `organization_id` (integer) - Your organization ID
- `customer` (object) - Customer information
- `customer.name` (string) - Customer's full name
- `customer.email` (string) - Customer's email address
- `customer.phone` (string) - Customer's phone number
- `products` (array) - Array of products with subscription price plans
- `products.*.price_plan_id` (integer) - Price plan ID for a subscription product
- `products.*.amount` (number) - Invoice amount for this product
- `currency` (string) - 3-letter currency code

**Optional Parameters:**
- `tax_rate_ids` (array) - Array of tax rate IDs to apply
- `description` (string) - Invoice description
- `status` (string) - Invoice status (default: "issued")
- `payment_gateway` (string) - flutterwave, control_number, or both
- `success_url` (string) - Redirect URL after successful payment
- `cancel_url` (string) - Redirect URL after cancelled payment

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 8,
      "amount": 75000
    }
  ],
  "description": "Premium hosting - Monthly subscription",
  "currency": "TZS",
  "status": "issued",
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 124,
      "invoice_number": "INV-2026-00124",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Premium hosting - Monthly subscription",
      "subtotal": 75000,
      "tax_total": 0,
      "total": 75000,
      "due_date": null,
      "issued_at": "2026-02-26T11:15:00.000000Z",
      "items": [
        {
          "id": 457,
          "price_plan_id": 8,
          "subscription_id": 89,
          "product_name": "Premium Hosting Plan",
          "billing_interval": "monthly",
          "quantity": 1,
          "unit_price": 75000,
          "total": 75000
        }
      ],
      "subscription": {
        "id": 89,
        "status": "pending",
        "price_plan_id": 8,
        "start_date": null,
        "next_billing_date": null,
        "note": "Subscription will activate upon payment"
      },
      "payment_details": {
        "flutterwave": {
          "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz",
          "tx_ref": "INV-2026-00124-1708956234",
          "expires_at": "2026-03-05T11:15:00.000000Z"
        }
      }
    }
  }
}
```

**Notes:**
- The subscription is created in "pending" status
- It will automatically activate when the invoice is paid
- Next billing date is calculated based on the price plan's billing interval
- If a pending subscription already exists for the same customer and price plan, the existing invoice is returned

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
    "products.0.price_plan_id": ["The selected price plan id is invalid."],
    "payment_gateway": ["The payment gateway must be one of: flutterwave, control_number, both."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Multi-Product Invoice
**Method:** `POST`
**URL:** `/api/invoices`

**Description:** Create a single invoice with multiple products of different types (one-time and subscription products can be combined).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Jane Smith",
    "email": "jane@company.com",
    "phone": "+255723456789"
  },
  "products": [
    {
      "price_plan_id": 3,
      "amount": 100000
    },
    {
      "price_plan_id": 5,
      "amount": 50000
    },
    {
      "price_plan_id": 8,
      "amount": 25000
    }
  ],
  "tax_rate_ids": [1, 2],
  "description": "Bundle: Hosting + Domain + SSL",
  "currency": "TZS",
  "status": "issued"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 126,
      "invoice_number": "INV-2026-00126",
      "customer_id": 46,
      "currency": "TZS",
      "status": "issued",
      "description": "Bundle: Hosting + Domain + SSL",
      "subtotal": 175000,
      "tax_total": 31500,
      "total": 206500,
      "issued_at": "2026-02-26T13:00:00.000000Z",
      "items": [
        {
          "id": 459,
          "price_plan_id": 3,
          "subscription_id": 90,
          "product_name": "Premium Hosting",
          "product_type": "Subscription Product",
          "quantity": 1,
          "unit_price": 100000,
          "total": 100000
        },
        {
          "id": 460,
          "price_plan_id": 5,
          "subscription_id": null,
          "product_name": "Domain Registration",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 50000,
          "total": 50000
        },
        {
          "id": 461,
          "price_plan_id": 8,
          "subscription_id": null,
          "product_name": "SSL Certificate",
          "product_type": "One-time Product",
          "quantity": 1,
          "unit_price": 25000,
          "total": 25000
        }
      ],
      "taxes": [
        {
          "tax_rate_id": 1,
          "name": "VAT",
          "percentage": 15,
          "amount": 26250
        },
        {
          "tax_rate_id": 2,
          "name": "Service Tax",
          "percentage": 3,
          "amount": 5250
        }
      ],
      "subscriptions": [
        {
          "id": 90,
          "price_plan_id": 3,
          "status": "pending",
          "product_name": "Premium Hosting"
        }
      ]
    }
  }
}
```

**Notes:**
- When an invoice contains both one-time and subscription products, subscriptions are created only for subscription-type products
- One-time products are charged without creating a subscription
- Taxes are calculated on the subtotal of all products

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
    "tax_rate_ids.0": ["The selected tax rate id is invalid."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Create Invoice with Payment Gateway
**Method:** `POST`
**URL:** `/api/invoices`

**Description:** Generate payment links automatically when creating invoices. Supports Flutterwave (card/mobile money) and EcoBank control numbers (bank payments).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Payment Gateway Options:**
| Option | Description | Required Parameters |
|--------|-------------|---------------------|
| `flutterwave` | Card, mobile money, and bank transfer | success_url, cancel_url |
| `control_number` | EcoBank control number for bank payments | None |
| `both` | Both Flutterwave link AND control number | success_url, cancel_url |

**Request Body (Flutterwave):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Sarah Lee",
    "email": "sarah@business.com",
    "phone": "+255756789012"
  },
  "products": [
    {
      "price_plan_id": 7,
      "amount": 120000
    }
  ],
  "payment_gateway": "flutterwave",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "description": "Premium hosting package",
  "currency": "TZS"
}
```

**Success Response (Flutterwave):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with Flutterwave payment link",
  "data": {
    "invoice": {
      "id": 127,
      "invoice_number": "INV-2026-00127",
      "total": 120000,
      "status": "issued",
      "customer_email": "sarah@business.com"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789",
        "tx_ref": "INV-2026-00127-1708960000",
        "expires_at": "2026-03-05T14:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer",
        "supported_methods": ["card", "mobile_money", "bank_transfer"]
      }
    },
    "redirect_url": "https://checkout.flutterwave.com/v3/hosted/pay/abc123xyz789"
  }
}
```

**Request Body (Control Number):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Michael Brown",
    "email": "michael@enterprise.com",
    "phone": "+255767890123"
  },
  "products": [
    {
      "product_code": "CLOUD-SERVER-M",
      "amount": 500000
    }
  ],
  "payment_gateway": "control_number",
  "description": "Cloud server subscription",
  "currency": "TZS"
}
```

**Success Response (Control Number):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with control number",
  "data": {
    "invoice": {
      "id": 128,
      "invoice_number": "INV-2026-00128",
      "total": 500000,
      "status": "issued",
      "customer_email": "michael@enterprise.com"
    },
    "payment_details": {
      "control_number": {
        "reference": "9912345678",
        "amount": 500000,
        "currency": "TZS",
        "expires_at": "2026-03-12T14:30:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345678# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number: 9912345678",
          "agent_banking": "Visit any bank agent and provide the control number: 9912345678",
          "atm": "Use ATM bill payment option with control number: 9912345678"
        }
      }
    }
  }
}
```

**Request Body (Both Gateways):**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "David Chen",
    "email": "david@corp.com",
    "phone": "+255778901234"
  },
  "products": [
    {
      "price_plan_id": 10,
      "amount": 250000
    }
  ],
  "payment_gateway": "both",
  "success_url": "https://yourapp.com/payment/success",
  "cancel_url": "https://yourapp.com/payment/cancel",
  "currency": "TZS"
}
```

**Success Response (Both):** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully with multiple payment options",
  "data": {
    "invoice": {
      "id": 129,
      "invoice_number": "INV-2026-00129",
      "total": 250000,
      "status": "issued"
    },
    "payment_details": {
      "flutterwave": {
        "payment_link": "https://checkout.flutterwave.com/v3/hosted/pay/xyz789abc",
        "tx_ref": "INV-2026-00129-1708961000",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "instructions": "Click the payment link to pay via card, mobile money, or bank transfer"
      },
      "control_number": {
        "reference": "9912345679",
        "amount": 250000,
        "currency": "TZS",
        "expires_at": "2026-03-12T15:00:00.000000Z",
        "payment_instructions": {
          "mobile_banking": "Dial *150*01*9912345679# from your registered mobile number",
          "internet_banking": "Login to your internet banking and pay bill using control number",
          "agent_banking": "Visit any bank agent and provide the control number"
        }
      }
    },
    "urls": {
      "success_url": "https://yourapp.com/payment/success",
      "cancel_url": "https://yourapp.com/payment/cancel"
    }
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

`422 Unprocessable Entity`
```json
{
  "success": false,
  "errors": {
    "success_url": ["The success url field is required when payment gateway is flutterwave."],
    "cancel_url": ["The cancel url field is required when payment gateway is flutterwave."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

### Product Lookup Methods

You can specify products using three different lookup methods:

| Method | Parameter | When to Use | Example |
|--------|-----------|-------------|---------|
| **Price Plan ID** | `price_plan_id` | Most specific - when you know the exact plan | `"price_plan_id": 5` |
| **Product Code** | `product_code` | User-friendly - use readable codes | `"product_code": "HOSTING-BASIC"` |
| **Product ID** | `product_id` | Simple product reference | `"product_id": 12` |

**Important:** Each product must have EXACTLY ONE identifier (price_plan_id, product_code, or product_id). Using multiple identifiers will result in a validation error.

**Example using Product Code:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Bob Wilson",
    "email": "bob@startup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "product_code": "HOSTING-BASIC",
      "amount": 50000
    },
    {
      "product_code": "DOMAIN-COM",
      "amount": 15000
    }
  ],
  "currency": "TZS"
}
```

### Complete Parameter Reference

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `organization_id` | integer | **Required** | Your organization ID |
| `customer` | object | **Required** | Customer information object |
| `customer.name` | string | **Required** | Customer's full name |
| `customer.email` | string (email) | **Required** | Customer's email address |
| `customer.phone` | string | **Required** | Customer's phone number |
| `products` | array | **Required** | Array of products (minimum 1) |
| `products.*.price_plan_id` | integer | Conditional | Price plan ID (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.product_code` | string | Conditional | Product code (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.product_id` | integer | Conditional | Product ID (use ONE of: price_plan_id, product_code, or product_id) |
| `products.*.amount` | number | **Required** | Invoice amount for this product (minimum: 0) |
| `currency` | string (3 chars) | **Required** | 3-letter currency code (e.g., "TZS", "USD", "EUR") |
| `tax_rate_ids` | array | Optional | Array of tax rate IDs to apply to invoice |
| `description` | string | Optional | Invoice description or notes |
| `status` | string | Optional | Invoice status: draft, issued, paid, cancelled (default: "issued") |
| `date` | string (date) | Optional | Invoice date in Y-m-d format (default: current date) |
| `due_date` | string (date) | Optional | Payment due date in Y-m-d format |
| `payment_gateway` | string | Optional | Payment gateway: "flutterwave", "control_number", or "both" |
| `success_url` | string (URL) | Conditional | Required if using Flutterwave - redirect URL after successful payment |
| `cancel_url` | string (URL) | Conditional | Required if using Flutterwave - redirect URL after cancelled payment |

### Create Usage-Based Invoice

**Description:** Usage-based billing is a two-step process: first record usage throughout the billing period, then create invoices based on accumulated usage.

#### Step 1: Record Product Usage
**Method:** `POST`
**URL:** `/api/product-usage`

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "customer_id": 45,
  "product_id": 12,
  "quantity": 5000
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Product usage recorded successfully",
  "data": {
    "id": 789,
    "customer_id": 45,
    "product_id": 12,
    "quantity": 5000,
    "created_at": "2026-02-26T12:00:00.000000Z",
    "product": {
      "id": 12,
      "name": "API Calls",
      "product_type": "usage",
      "unit": "calls"
    },
    "customer": {
      "id": 45,
      "name": "Tech Startup Inc",
      "email": "billing@techstartup.com"
    }
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

`422 Unprocessable Entity`
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_id": ["Product usage is only allowed for products with type usage."]
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

#### Step 2: Get Usage Report
**Method:** `GET`
**URL:** `/api/product-usage/report/{customer_id}`

**Description:** Retrieve accumulated usage data for a customer to calculate charges.

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
  "data": {
    "customer_id": 45,
    "customer_name": "Tech Startup Inc",
    "usage_summary": [
      {
        "product_id": 12,
        "product_name": "API Calls",
        "product_code": "API-USAGE",
        "total_purchased": 50000,
        "total_used": 45000,
        "balance": 5000,
        "unit": "calls"
      },
      {
        "product_id": 13,
        "product_name": "Cloud Storage",
        "product_code": "STORAGE-GB",
        "total_purchased": 1000,
        "total_used": 750,
        "balance": 250,
        "unit": "GB"
      }
    ]
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

`404 Not Found`
```json
{
  "success": true,
  "message": "No usage data found for this customer",
  "data": {
    "customer_id": 45,
    "customer_name": "Tech Startup Inc",
    "usage_summary": []
  }
}
```

`429 Too Many Requests`
```json
{
  "message": "Too Many Attempts."
}
```

#### Step 3: Create Invoice for Usage
**Method:** `POST`
**URL:** `/api/invoices`

**Description:** Create an invoice based on the usage data. Calculate the amount based on your pricing model (e.g., price per API call, per GB).

**Required Headers:**
| Key | Value |
|-----|-------|
| Authorization | Bearer {APP_ACCESS_TOKEN} |
| Content-Type | application/json |
| Accept | application/json |

**Request Body:**
```json
{
  "organization_id": 1,
  "customer": {
    "name": "Tech Startup Inc",
    "email": "billing@techstartup.com",
    "phone": "+255734567890"
  },
  "products": [
    {
      "price_plan_id": 15,
      "amount": 45000
    }
  ],
  "description": "API Usage - 45,000 calls @ TZS 1 per call",
  "currency": "TZS",
  "status": "issued"
}
```

**Success Response:** `201 Created`
```json
{
  "success": true,
  "message": "Invoice created successfully",
  "data": {
    "invoice": {
      "id": 125,
      "invoice_number": "INV-2026-00125",
      "customer_id": 45,
      "currency": "TZS",
      "status": "issued",
      "description": "API Usage - 45,000 calls @ TZS 1 per call",
      "subtotal": 45000,
      "tax_total": 0,
      "total": 45000,
      "issued_at": "2026-02-26T12:30:00.000000Z",
      "items": [
        {
          "id": 458,
          "price_plan_id": 15,
          "product_name": "API Usage Charges",
          "quantity": 1,
          "unit_price": 45000,
          "total": 45000,
          "metadata": {
            "usage_period": "2026-02-01 to 2026-02-28",
            "total_calls": 45000,
            "rate_per_call": 1
          }
        }
      ]
    }
  }
}
```

**Usage-Based Billing Pattern:**
Record usage throughout the billing period → Retrieve usage report → Calculate charges → Create invoice

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
    "products.0.amount": ["The products.0.amount must be at least 0."]
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