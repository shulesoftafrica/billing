<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Approval Request</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f8fafc; color:#1e293b; margin:0; padding:24px;">
    <div style="max-width:620px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
        <div style="background:#0A1628; color:#fff; padding:18px 24px;">
            <h2 style="margin:0; font-size:18px;">Safari API – Developer Verification Request</h2>
        </div>

        <div style="padding:24px;">
            <p style="margin-top:0;">Hello {{ $organizationName }},</p>

            <p>A developer is requesting access under your organization:</p>

            <table style="width:100%; border-collapse:collapse; margin:14px 0 20px;">
                <tr>
                    <td style="padding:8px; border:1px solid #e2e8f0; font-weight:600; width:180px;">Developer Name</td>
                    <td style="padding:8px; border:1px solid #e2e8f0;">{{ $developerName }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; border:1px solid #e2e8f0; font-weight:600;">Developer Email</td>
                    <td style="padding:8px; border:1px solid #e2e8f0;">{{ $developerEmail }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; border:1px solid #e2e8f0; font-weight:600;">Requested At</td>
                    <td style="padding:8px; border:1px solid #e2e8f0;">{{ $requestedAt }}</td>
                </tr>
            </table>

            <p style="margin-bottom:16px;">If you recognize this developer and want to approve access, click below:</p>

            <p style="margin:22px 0;">
                <a href="{{ $verificationUrl }}" style="display:inline-block; background:#2563EB; color:#fff; text-decoration:none; padding:11px 18px; border-radius:8px; font-weight:600;">
                    Verify Developer
                </a>
            </p>

            <p style="font-size:13px; color:#64748b; margin-bottom:0;">
                This verification link expires in 48 hours. If this request is unknown, ignore this email and no access will be granted.
            </p>
        </div>
    </div>
</body>
</html>
