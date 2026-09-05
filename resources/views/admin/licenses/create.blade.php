@extends('layouts.anubis')
@section('title', 'Issue New License')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
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

<div class="anx-container narrow">
    <a href="{{ route('admin.licenses.index') }}" style="font-size:13px;">&larr; Back to Licenses</a>
    <div class="anx-card" style="margin-top:14px;">
        <h2>Issue New License</h2>
        <form method="POST" action="{{ route('admin.licenses.store') }}">
            @csrf
            <div class="anx-field">
                <label>Existing Customer (optional)</label>
                <select name="customer_id">
                    <option value="">— New customer —</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="anx-field">
                <label>New Customer Name</label>
                <input type="text" name="customer_name">
            </div>
            <div class="anx-field">
                <label>New Customer Email</label>
                <input type="email" name="customer_email">
                @error('customer_email')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Plan</label>
                <select name="plan_id" id="plan-select" required>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" data-period-days="{{ $p->default_period_days }}">{{ $p->name }} ({{ $p->default_period_days }}-day cycle)</option>
                    @endforeach
                </select>
            </div>
            <div class="anx-field">
                <label>Status</label>
                <select name="status">
                    <option value="active">Active (paid)</option>
                    <option value="trial">Trial</option>
                </select>
            </div>
            <div class="anx-field">
                <label>Period Length (days)</label>
                <input type="number" name="period_days" id="period-days-input" value="{{ $plans->first()->default_period_days ?? 30 }}">
                <div class="anx-hint" style="margin-top:4px;">Auto-fills based on the selected plan's billing cycle — override if this is a one-off custom term.</div>
            </div>
            <div class="anx-field">
                <label>Trial Length (days, if trial)</label>
                <input type="number" name="trial_days" value="7">
            </div>
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Issue License</button>
        </form>
    </div>
</div>

<script>
    // Auto-fill the period length whenever the plan changes, using each
    // plan's own default_period_days (Basic=30, Advanced=180, Premium=365)
    // instead of leaving a stale flat 30 for every plan.
    document.getElementById('plan-select').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const days = selected.getAttribute('data-period-days');
        if (days) {
            document.getElementById('period-days-input').value = days;
        }
    });
</script>
@endsection
