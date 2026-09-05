<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; color: #222; font-size: 13px; }
        .header { border-bottom: 3px solid #c9a15a; padding-bottom: 14px; margin-bottom: 24px; }
        .header h1 { color: #8a6820; margin: 0 0 4px; font-size: 20px; }
        .header p { margin: 0; color: #777; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td, th { padding: 8px 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f5f0e6; color: #6b5228; font-size: 11px; text-transform: uppercase; }
        .totals { margin-top: 20px; text-align: right; }
        .totals .amount { font-size: 18px; font-weight: bold; color: #8a6820; }
        .footer { margin-top: 40px; font-size: 11px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ANUBIS License Server</h1>
        <p>Invoice for {{ $license->license_key }}</p>
    </div>

    <table>
        <tr><th>Billed To</th><td>{{ $license->customer->name }}<br>{{ $license->customer->email }}</td></tr>
        <tr><th>Invoice Date</th><td>{{ now()->format('F j, Y') }}</td></tr>
        <tr><th>Plan</th><td>{{ $license->plan->name }}</td></tr>
        <tr><th>Billing Cycle</th><td>{{ $license->plan->default_period_days }} days</td></tr>
        <tr><th>Status</th><td>{{ ucfirst($license->status) }}</td></tr>
        <tr><th>Current Period Ends</th><td>{{ optional($license->current_period_ends_at)->format('F j, Y') ?? '—' }}</td></tr>
        <tr><th>License Key</th><td>{{ $license->license_key }}</td></tr>
    </table>

    <div class="totals">
        <div>Plan price ({{ $license->plan->default_period_days }}-day cycle)</div>
        <div class="amount">${{ number_format($license->plan->price_monthly_cents / 100, 2) }}</div>
    </div>

    <div class="footer">
        This is a system-generated record for your reference. Anubis License Server.
    </div>
</body>
</html>
