@extends('layouts.anubis')
@section('title', $customer->name . ' — Customer')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}" class="active">Customers</a>
        <a href="{{ route('admin.coupons.index') }}">Coupons</a>
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
    <a href="{{ route('admin.licenses.index') }}" style="font-size:13px;">&larr; Back to Licenses</a>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    @if(session('new_password_plaintext'))
        <div class="anx-alert" style="background:rgba(93,184,232,0.12); border:1px solid rgba(93,184,232,0.3); color:#7ec8f2; display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <span>New password: <code id="new-pw-value" style="font-size:14px;">{{ session('new_password_plaintext') }}</code> — copy it now, this won't be shown again.</span>
            <button type="button" class="anx-btn anx-btn-ghost anx-btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('new-pw-value').innerText); this.innerText='Copied!'">Copy</button>
        </div>
    @endif

    <div class="anx-card" style="margin-top:14px;">
        <h2 style="margin-bottom:4px;">{{ $customer->name }}</h2>
        <p style="color:#9b8a6a; margin-top:0;">{{ $customer->email }}</p>
        <p style="font-size:13px; color:#9b8a6a;">
            Portal access: {{ $customer->password ? 'Active' : 'Not set up yet' }}
            &nbsp;•&nbsp; Joined {{ $customer->created_at->format('M j, Y') }}
        </p>
    </div>

    <div class="anx-card">
        <h3>Reset Portal Password</h3>
        <p class="anx-hint">Sets their password directly — copy the result and send it to them yourself. There's no automatic email for this yet.</p>
        <form method="POST" action="{{ route('admin.customers.reset-password', $customer) }}" onsubmit="return confirm('Set a new password for {{ $customer->name }}? Their old password will stop working immediately.')">
            @csrf
            <div class="anx-field" style="display:flex; gap:8px; align-items:flex-end;">
                <div style="flex:1;">
                    <label>New Password</label>
                    <input type="text" name="new_password" id="new-password-input" minlength="8" required>
                </div>
                <button type="button" class="anx-btn anx-btn-ghost" onclick="document.getElementById('new-password-input').value = Math.random().toString(36).slice(-5) + Math.random().toString(36).slice(-5).toUpperCase() + '!2'">Generate</button>
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Set New Password</button>
        </form>
    </div>

    <div class="anx-card">
        <h3>Private Note (admin only — never shown to the customer)</h3>
        <form method="POST" action="{{ route('admin.customers.notes', $customer) }}">
            @csrf
            <div class="anx-field">
                <textarea name="admin_notes" rows="4" style="width:100%; padding:11px 13px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-family:inherit; font-size:14px;">{{ $customer->admin_notes }}</textarea>
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Save Note</button>
        </form>
    </div>

    <div class="anx-card">
        <h3>Licenses ({{ $customer->licenses->count() }})</h3>
        @forelse($customer->licenses as $license)
            <div style="padding:12px 0; border-bottom:1px solid rgba(242,194,135,0.08); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <code>{{ $license->license_key }}</code>
                    <span style="color:#9b8a6a; font-size:13px; margin-left:8px;">{{ $license->plan->name }}</span>
                </div>
                <div>
                    <span class="anx-badge anx-badge-{{ $license->status }}">{{ $license->status }}</span>
                    <a class="anx-btn anx-btn-ghost anx-btn-sm" href="{{ route('admin.licenses.activity', $license) }}">History</a>
                    <a class="anx-btn anx-btn-ghost anx-btn-sm" href="{{ route('admin.licenses.invoice', $license) }}">⬇ Invoice</a>
                </div>
            </div>
        @empty
            <div class="anx-empty">No licenses yet.</div>
        @endforelse
    </div>
    <div class="anx-card">
        <h3>Account Activity</h3>
        @forelse($customer->activities as $activity)
            <div style="padding:8px 0; border-bottom:1px solid rgba(242,194,135,0.08); font-size:13px;">
                <strong style="color:var(--anx-gold-light);">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</strong>
                <span style="color:#9b8a6a; float:right;">{{ $activity->created_at->format('M j, Y g:ia') }}</span>
                <div style="color:#9b8a6a; margin-top:2px;">{{ $activity->description }}</div>
            </div>
        @empty
            <div class="anx-empty">No account activity recorded.</div>
        @endforelse
    </div>

    <div class="anx-card" style="border-color:rgba(232,93,93,0.3);">
        <h3 style="color:#ff8c8c;">Danger Zone</h3>
        <p class="anx-hint">Permanently deletes this customer and all {{ $customer->licenses->count() }} of their license(s). This cannot be undone.</p>
        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Permanently delete {{ $customer->name }} and ALL their licenses?') && confirm('This cannot be undone. Really sure?')">
            @csrf
            <button class="anx-btn anx-btn-danger" type="submit">Delete Customer Permanently</button>
        </form>
    </div>
</div>
@endsection
