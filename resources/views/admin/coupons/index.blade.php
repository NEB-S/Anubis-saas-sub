@extends('layouts.anubis')
@section('title', 'Coupons — Admin')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
        <a href="{{ route('admin.coupons.index') }}" class="active">Coupons</a>
        <a href="{{ route('admin.tickets.index') }}" style="position:relative;">Tickets
            @if(($openTicketsCount ?? 0) > 0)
                <span style="position:absolute; top:-6px; right:-10px; background:#e85d5d; color:#fff; font-size:10px; font-weight:800; padding:1px 5px; border-radius:10px; line-height:1.4;">{{ $openTicketsCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.admins.index') }}">Admins</a>
        <a href="{{ route('admin.activity-log') }}">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    <div class="anx-flex-between">
        <h2 style="margin:0;">Coupons</h2>
        <a class="anx-btn anx-btn-primary" href="{{ route('admin.coupons.create') }}">+ New Coupon</a>
    </div>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    <div class="anx-card" style="padding:0; overflow-x:auto;">
        <table class="anx-table">
            <thead>
                <tr>
                    <th>Code</th><th>Extra Trial Days</th><th>Discount %</th>
                    <th>Uses</th><th>Expires</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr>
                    <td><code>{{ $coupon->code }}</code></td>
                    <td>{{ $coupon->extra_trial_days }} days</td>
                    <td>{{ $coupon->discount_percent ? $coupon->discount_percent . '%' : '—' }}</td>
                    <td>{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</td>
                    <td>{{ optional($coupon->expires_at)->format('Y-m-d') ?? 'Never' }}</td>
                    <td><span class="anx-badge anx-badge-{{ $coupon->is_active ? 'active' : 'canceled' }}">{{ $coupon->is_active ? 'active' : 'inactive' }}</span></td>
                    <td style="white-space:nowrap;">
                        <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}" style="display:inline;">
                            @csrf<button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit">{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete this coupon?')" style="display:inline;">
                            @csrf<button class="anx-btn anx-btn-danger anx-btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="anx-empty">No coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $coupons->links() }}</div>
</div>
@endsection
