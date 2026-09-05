@extends('layouts.anubis')
@section('title', 'My Account')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>My Account</span></div>
    <div class="anx-nav">
        <a href="{{ route('portal.dashboard') }}" class="active">Dashboard</a>
        <a href="{{ route('portal.settings') }}">Settings</a>
        <a href="{{ route('portal.tickets.index') }}">Support</a>
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="anx-alert" style="background:rgba(93,184,232,0.12); border:1px solid rgba(93,184,232,0.3); color:#7ec8f2;">{{ session('info') }}</div>
    @endif

    @forelse($licenses as $license)
        <div class="anx-card">
            <div class="anx-flex-between" style="margin-bottom:12px;">
                <h3 style="margin:0;">
                    {{ $license->plan->name }} Plan
                    @if($license->pending_plan_id)
                        <span style="color:#e8b85d; font-size:12px; font-weight:normal;">→ {{ $license->pendingPlan->name }} (pending approval)</span>
                    @endif
                </h3>
                <span class="anx-badge anx-badge-{{ $license->status }}">{{ $license->status }}</span>
            </div>
            <table class="anx-table" style="width:100%;">
                <tr><td style="color:#9b8a6a; width:40%;">License Key</td><td><code>{{ $license->license_key }}</code></td></tr>
                <tr>
                    <td style="color:#9b8a6a;">Bound Domain</td>
                    <td>
                        {{ $license->bound_domain ?? 'Not activated yet' }}
                        @if($license->pending_domain)
                            <br><small style="color:#e8b85d;">Pending change to "{{ $license->pending_domain }}" — awaiting approval</small>
                        @endif
                    </td>
                </tr>
                <tr><td style="color:#9b8a6a;">Renews / Expires</td><td>{{ optional($license->current_period_ends_at)->format('F j, Y') ?? ($license->trial_ends_at?->format('F j, Y') ?? '—') }}</td></tr>
                <tr><td style="color:#9b8a6a;">Last checked in</td><td>{{ optional($license->last_checked_at)->diffForHumans() ?? 'Never — install your license key to activate' }}</td></tr>
            </table>

            <div style="margin-top:16px; display:flex; gap:8px; flex-wrap:wrap;">
                <a class="anx-btn anx-btn-primary anx-btn-sm" href="{{ route('portal.licenses.get-started', $license) }}">🚀 Get Started (Download & Setup)</a>
                <form method="POST" action="{{ route('portal.licenses.renew', $license) }}">
                    @csrf
                    <button class="anx-btn anx-btn-primary anx-btn-sm" type="submit">Renew Now</button>
                </form>
                <a class="anx-btn anx-btn-ghost anx-btn-sm" href="{{ route('portal.licenses.invoice', $license) }}">⬇ Download Invoice</a>

                @unless($license->pending_domain)
                <form method="POST" action="{{ route('portal.licenses.request-domain-change', $license) }}" style="display:flex; gap:6px;" onsubmit="return this.domain.value.trim().length > 0">
                    @csrf
                    <input type="text" name="domain" placeholder="new-domain.com" style="width:180px; padding:7px 10px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-size:13px;">
                    <button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit">Request Domain Change</button>
                </form>
                @endunless

                @unless($license->pending_plan_id)
                <form method="POST" action="{{ route('portal.licenses.request-plan-change', $license) }}" style="display:flex; gap:6px;">
                    @csrf
                    <select name="plan_id" style="padding:7px 10px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-size:13px;">
                        @foreach($allPlans as $p)
                            <option value="{{ $p->id }}" {{ $p->id === $license->plan_id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit">Request Plan Change</button>
                </form>
                @endunless
            </div>
        </div>
    @empty
        <div class="anx-card anx-empty">You don't have any licenses yet. Contact us to get set up.</div>
    @endforelse
</div>
@endsection
