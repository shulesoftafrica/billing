<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f6f9; font-family: Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #1a56db; padding: 32px 40px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 24px; letter-spacing: 0.5px; }
        .header p { margin: 8px 0 0; color: #c3d9ff; font-size: 14px; }
        .body { padding: 36px 40px; }
        .body p { line-height: 1.7; font-size: 15px; margin: 0 0 16px; }
        .credentials-box { background: #f0f4ff; border-left: 4px solid #1a56db; border-radius: 4px; padding: 20px 24px; margin: 24px 0; }
        .credentials-box table { width: 100%; border-collapse: collapse; }
        .credentials-box td { padding: 6px 0; font-size: 15px; }
        .credentials-box td:first-child { color: #666; width: 140px; }
        .credentials-box td:last-child { font-weight: 600; color: #1a56db; word-break: break-all; }
        .btn { display: inline-block; margin: 8px 0 24px; padding: 13px 32px; background: #1a56db; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: 600; }
        .warning { background: #fffbeb; border: 1px solid #f59e0b; border-radius: 4px; padding: 14px 18px; font-size: 14px; color: #92400e; margin: 20px 0; }
        .footer { background: #f8fafc; padding: 20px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .footer a { color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>Your account is ready</p>
    </div>

    <div class="body">
        <p>Hello <strong>{{ $organization->name }}</strong>,</p>
        <p>
            Your organization has been successfully registered on <strong>{{ config('app.name') }}</strong>.
            Below are your login credentials. Please keep them safe.
        </p>

        <div class="credentials-box">
            <table>
                <tr>
                    <td>Login Email:</td>
                    <td>{{ $email }}</td>
                </tr>
                <tr>
                    <td>Temporary Password:</td>
                    <td>{{ $tempPassword }}</td>
                </tr>
            </table>
        </div>

        <p style="text-align:center">
            <a href="{{ $loginUrl }}" class="btn">Login to Your Account</a>
        </p>

        <div class="warning">
            ⚠️ <strong>Security notice:</strong> This is a temporary password. You will be prompted to change it on your first login.
            Do not share this email with anyone.
        </div>

        <p>If you did not register this account, please contact us immediately at
            <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>.
        </p>

        <p>Welcome aboard,<br><strong>The {{ config('app.name') }} Team</strong></p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
        <a href="{{ $loginUrl }}">{{ config('app.url') }}</a>
    </div>
</div>
</body>
</html>
