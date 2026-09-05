<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; color: #222; background: #f5f0e6; padding: 30px;">
    <table style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden;">
        <tr>
            <td style="background: #151519; padding: 20px 30px;">
                <span style="color: #f2c287; font-size: 18px; font-weight: bold;">ANUBIS License Server</span>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <h2 style="color: #333; margin-top: 0;">Hi {{ $license->customer->name }},</h2>
                <p>Your <strong>{{ $license->plan->name }}</strong> license is expiring soon:</p>
                <table style="width: 100%; margin: 16px 0; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 6px 0; color: #777; font-size: 13px;">License Key</td>
                        <td style="padding: 6px 0; text-align: right;"><code>{{ $license->license_key }}</code></td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #777; font-size: 13px;">Expires On</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: bold; color: #8a6820;">{{ $expiresAt?->format('F j, Y') ?? 'Unknown' }}</td>
                    </tr>
                </table>
                <p>To keep your site running without interruption, please renew before this date.</p>
                <p style="margin-top: 24px;">
                    <a href="{{ route('portal.dashboard') }}" style="background: #c9a15a; color: #151519; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Go to My Account</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 30px; background: #f5f0e6; font-size: 12px; color: #999;">
                This is an automated reminder from Anubis License Server.
            </td>
        </tr>
    </table>
</body>
</html>
