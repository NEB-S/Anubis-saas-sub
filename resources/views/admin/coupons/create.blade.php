@extends('layouts.anubis')
@section('title', 'New Coupon')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
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

<div class="anx-container narrow">
    <a href="{{ route('admin.coupons.index') }}" style="font-size:13px;">&larr; Back to Coupons</a>
    <div class="anx-card" style="margin-top:14px;">
        <h2>New Coupon</h2>
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf
            <div class="anx-field">
                <label>Code</label>
                <input type="text" name="code" placeholder="e.g. LAUNCH25" value="{{ old('code') }}" style="text-transform:uppercase;">
                @error('code')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Extra Trial Days (applied at signup, works today)</label>
                <input type="number" name="extra_trial_days" value="0" min="0">
                <div class="anx-hint" style="margin-top:4px;">Added on top of the normal 7-day trial when a customer uses this code at signup.</div>
            </div>
            <div class="anx-field">
                <label>Discount % (for paid checkout — reserved for when Paddle goes live)</label>
                <input type="number" name="discount_percent" min="1" max="100" placeholder="optional">
            </div>
            <div class="anx-field">
                <label>Max Uses (optional)</label>
                <input type="number" name="max_uses" min="1" placeholder="unlimited if blank">
            </div>
            <div class="anx-field">
                <label>Expires On (optional)</label>
                <input type="date" name="expires_at">
            </div>
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Create Coupon</button>
        </form>
    </div>
</div>
@endsection
