<section class="auth-guide" id="authentication-guide">
    <h2>🔐 Authentication Guide</h2>
    
    <p>This API uses <strong>OAuth 2.0 Client Credentials</strong> flow with Laravel Sanctum tokens for authentication. All API requests require a valid access token.</p>

    <h3>📋 Quick Start (4 Steps)</h3>
    
    <div class="auth-steps">
        <div class="auth-step">
            <div class="auth-step-title">
                <span class="auth-step-number">1</span>
                <span>Register a User</span>
            </div>
            <p>First, register a user under your <strong>active</strong> organization:</p>
            <x-docs.code-block language="bash">
POST {{ url('/api/v1/auth/register') }}
Content-Type: application/json

{
  "organization_email": "billing@acme.com",
  "name": "John Doe",
  "email": "john@acme.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "role": "admin",
  "sex": "M"
}
            </x-docs.code-block>
            
            <p><strong>Response:</strong> You'll receive a user token upon successful registration.</p>
            <x-docs.code-block language="json">
{
  "message": "User registered successfully",
  "access_token": "shulesoft_1|abc123xyz...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@acme.com",
    "role": "admin"
  }
}
            </x-docs.code-block>
            <p class="text-muted small"><strong>📌 Note:</strong> Your organization must be <strong>active</strong>. If it's still pending, contact support for activation.</p>
        </div>

        <div class="auth-step">
            <div class="auth-step-title">
                <span class="auth-step-number">2</span>
                <span>Login to Get User Token</span>
            </div>
            <p>First, login with your credentials to receive a user access token:</p>
            <x-docs.code-block language="bash">
POST {{ url('/api/v1/auth/login') }}
Content-Type: application/json

{
  "email": "your-email@example.com",
  "password": "YourPassword123!"
}
            </x-docs.code-block>
            
            <p><strong>Response:</strong> You'll receive a user token.</p>
            <x-docs.code-block language="json">
{
  "access_token": "shulesoft_1|abc123xyz...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "Your Name",
    "email": "your-email@example.com",
    "role": "admin"
  }
}
            </x-docs.code-block>
        </div>

        <div class="auth-step">
            <div class="auth-step-title">
                <span class="auth-step-number">3</span>
                <span>Create OAuth Client</span>
            </div>
            <p>Use your user token to create API client credentials:</p>
            <x-docs.code-block language="bash">
POST {{ url('/api/v1/oauth/clients') }}
Authorization: Bearer {YOUR_USER_TOKEN_FROM_STEP_1_OR_2}
Content-Type: application/json

{
  "name": "Production API Client",
  "environment": "live",
  "allowed_scopes": ["*"]
}
            </x-docs.code-block>
            
            <p class="text-muted small"><strong>🔒 Security:</strong> The client is automatically created for your organization. You cannot create clients for other organizations.</p>
            
            <div class="alert">
                <strong>⚠️ CRITICAL:</strong> Save your <code>client_id</code> and <code>client_secret</code> immediately! The <code>client_secret</code> is shown only once and cannot be retrieved again.
            </div>
            
            <p><strong>Response:</strong></p>
            <x-docs.code-block language="json">
{
    "message": "OAuth client created successfully",
    "client": {
        "id": 1,
        "name": "Production API Client",
        "client_id": "org_live_client_ZE9HizNKnNzcQ9ZGOlfSieWlvDimu3jH",
        "client_secret": "org_live_secret_GCNpI1J6wbPh3LS0IoUVS8WhqXjH0Ob2m4elud5x",
        "environment": "live",
        "allowed_scopes": [
            "*"
        ],
        "expires_at": null,
        "created_at": "2026-03-18T20:38:10.000000Z"
    },
    "warning": "Store the client_secret securely. It will not be shown again."
}
            </x-docs.code-block>
        </div>

        <div class="auth-step">
            <div class="auth-step-title">
                <span class="auth-step-number">4</span>
                <span>Get Access Token (For API Requests)</span>
            </div>
            <p>Exchange your client credentials for an access token:</p>
            <x-docs.code-block language="bash">
POST {{ url('/api/v1/oauth/token') }}
Content-Type: application/json

{
  "grant_type": "client_credentials",
  "client_id": "org_live_client_abc123xyz...",
  "client_secret": "org_live_secret_xyz789def...",
  "scope": "*"
}
            </x-docs.code-block>
            
            <p><strong>Response:</strong></p>
            <x-docs.code-block language="json">
{
  "access_token": "shulesoft_2|def456ghi789...",
  "token_type": "Bearer",
  "expires_in": 7776000,
  "scope": "*",
  "organization_id": 1
}
            </x-docs.code-block>
            
            <p><strong>Use this access token</strong> in all your API requests:</p>
            <x-docs.code-block language="bash">
GET {{ url('/api/v1/products') }}
Authorization: Bearer shulesoft_2|def456ghi789...
Accept: application/json
            </x-docs.code-block>
        </div>
    </div>

    <h3 id="user-registration">👤 User Registration (Step 1 — Detail)</h3>
    <p>Register a new user account under an existing <strong>active</strong> organization. The organization must have been registered and activated before users can be created.</p>

    <x-docs.endpoint
        id="register-user"
        method="POST"
        url="/api/v1/auth/register"
        title="Register User"
        description="Create a new user account under an active organization">

        <x-slot name="requestBody">
            <x-docs.code-block language="json" label="request">
{
  "organization_email": "billing@acme.com",
  "name": "John Doe",
  "email": "john@acme.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!",
  "role": "admin",
  "sex": "M"
}
            </x-docs.code-block>

            <table class="param-table">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>organization_email</code></td>
                        <td>string</td>
                        <td>Yes</td>
                        <td>Email of the organization (must exist and be <strong>active</strong>)</td>
                    </tr>
                    <tr>
                        <td><code>name</code></td>
                        <td>string</td>
                        <td>Yes</td>
                        <td>Full name of the user</td>
                    </tr>
                    <tr>
                        <td><code>email</code></td>
                        <td>string</td>
                        <td>Yes</td>
                        <td>User email (must be unique within the organization)</td>
                    </tr>
                    <tr>
                        <td><code>password</code></td>
                        <td>string</td>
                        <td>Yes</td>
                        <td>Minimum 8 characters</td>
                    </tr>
                    <tr>
                        <td><code>password_confirmation</code></td>
                        <td>string</td>
                        <td>Yes</td>
                        <td>Must match password</td>
                    </tr>
                    <tr>
                        <td><code>role</code></td>
                        <td>string</td>
                        <td>No</td>
                        <td>One of: <code>admin</code>, <code>user</code>, <code>manager</code>. Default: <code>user</code></td>
                    </tr>
                    <tr>
                        <td><code>sex</code></td>
                        <td>string</td>
                        <td>No</td>
                        <td>One of: <code>M</code>, <code>F</code>, <code>O</code>. Default: <code>O</code></td>
                    </tr>
                </tbody>
            </table>
        </x-slot>

        <x-slot name="responses">
            <div class="response-head">
                <span class="response-title">Success Response</span>
                <span class="status-badge status-2xx">201 Created</span>
            </div>
            <x-docs.code-block language="json">
{
  "message": "User registered successfully",
  "access_token": "shulesoft_1|abc123xyz...",
  "token_type": "Bearer",
  "expires_in": 2592000,
  "expires_at": "2026-04-30T08:00:00+00:00",
  "user": {
    "id": 1,
    "organization_id": 1,
    "name": "John Doe",
    "email": "john@acme.com",
    "role": "admin"
  }
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 16px;">
                <span class="response-title">Organization Not Active</span>
                <span class="status-badge status-4xx">403 Forbidden</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "message": "Organization is not active. Current status: pending. Please contact support to activate your organization."
}
            </x-docs.code-block>

            <div class="response-head" style="margin-top: 16px;">
                <span class="response-title">Duplicate Email</span>
                <span class="status-badge status-4xx">422 Unprocessable</span>
            </div>
            <x-docs.code-block language="json" label="error">
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["A user with this email already exists in this organization."]
  }
}
            </x-docs.code-block>
        </x-slot>
    </x-docs.endpoint>

    <h3>🔄 Token Management</h3>
    <ul>
        <li><strong>Token Lifetime:</strong> Access tokens expire after 90 days</li>
        <li><strong>Token Caching:</strong> Cache tokens and reuse them until expiration</li>
        <li><strong>Token Refresh:</strong> When you receive a 401 error, request a new token</li>
        <li><strong>Security:</strong> Store credentials in environment variables, never in code</li>
    </ul>

    <h3>🔐 Best Practices</h3>
    <ol>
        <li><strong>Never commit credentials to version control</strong> - Use environment variables</li>
        <li><strong>Use separate clients for different environments</strong> - Create <code>test</code> clients for development</li>
        <li><strong>Implement automatic token refresh</strong> - Handle 401 errors gracefully</li>
        <li><strong>Cache access tokens</strong> - Reduce unnecessary token requests</li>
        <li><strong>Monitor token usage</strong> - Check last_used_at in client list endpoint</li>
    </ol>

    <h3>❓ Common Errors</h3>
    <div class="auth-step">
        <h4>Invalid Client Credentials (401)</h4>
        <x-docs.code-block language="json" label="error">
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
        </x-docs.code-block>
        <p><strong>Solution:</strong> Verify your <code>client_id</code> and <code>client_secret</code> are correct.</p>
    </div>

    <div class="auth-step">
        <h4>Expired Token (401)</h4>
        <x-docs.code-block language="json" label="error">
{
  "message": "Unauthenticated",
  "error": "invalid_access_token"
}
        </x-docs.code-block>
        <p><strong>Solution:</strong> Request a new access token using your client credentials.</p>
    </div>

    <div class="auth-step">
        <h4>Rate Limit Exceeded (429)</h4>
        <x-docs.code-block language="json" label="error">
{
  "message": "Too Many Attempts."
}
        </x-docs.code-block>
        <p><strong>Solution:</strong> Wait 60 seconds before making more requests. Implement exponential backoff.</p>
    </div>

    <div class="info-box">
        <strong>📖 Ready to start?</strong> Use your access token with any of the endpoints documented below. All endpoints require the <code>Authorization: Bearer {token}</code> header.
    </div>
</section>
