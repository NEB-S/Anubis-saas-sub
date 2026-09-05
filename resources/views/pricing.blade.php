@extends('layouts.anubis')
@section('title', 'Pricing — Anubis License Server')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('portal.login') }}">Log In</a>
        <a href="{{ route('portal.register') }}" class="active">Get Started</a>
    </div>
</div>

<div class="anx-container" style="max-width:1300px; text-align:center; padding-top:50px;">
    <h1 style="font-size:2.1rem; color:var(--anx-gold-light); margin-bottom:8px;">Simple, Transparent Pricing</h1>
    <p style="color:#9b8a6a; max-width:520px; margin:0 auto 40px;">
        Start free, or pick the plan that fits — one license per account.
    </p>

    <div style="display:grid; grid-template-columns:repeat({{ min($plans->count(), 4) }}, 1fr); gap:18px; text-align:left;">
        @foreach($plans as $plan)
        @php
            $isTrial = $plan->slug === 'trial';
            $cycle = match($plan->default_period_days) {
                7 => '7-Day Trial',
                30 => 'Monthly',
                180 => 'Every 6 Months',
                365 => 'Yearly',
                default => $plan->default_period_days . ' days',
            };
        @endphp
        <div class="anx-card" style="display:flex; flex-direction:column; {{ $plan->slug === 'advanced' ? 'border-color:var(--anx-glass-border-strong); box-shadow:var(--anx-glow-gold);' : '' }}">
            @if($plan->slug === 'advanced')
                <div style="align-self:flex-start; background:linear-gradient(135deg,var(--anx-gold-light),var(--anx-gold-deep)); color:var(--anx-black-soft); font-size:11px; font-weight:800; padding:3px 10px; border-radius:20px; margin-bottom:10px;">MOST POPULAR</div>
            @endif
            <h3 style="margin-bottom:2px;">{{ $plan->name }}</h3>
            <div style="font-size:12px; color:#9b8a6a; margin-bottom:8px; text-transform:uppercase; letter-spacing:.04em;">{{ $cycle }}</div>
            <div style="margin-bottom:4px;">
                @if($plan->price_monthly_cents > 0)
                    <span style="font-size:1.8rem; font-weight:800; color:var(--anx-gold-light);">${{ number_format($plan->price_monthly_cents / 100, 2) }}</span>
                @else
                    <span style="font-size:1.8rem; font-weight:800; color:var(--anx-gold-light);">Free</span>
                @endif
            </div>
            <div style="flex-grow:1; margin:18px 0;"></div>
            <a href="{{ route('portal.register', ['plan' => $plan->slug]) }}" class="anx-btn anx-btn-primary anx-btn-block">
                {{ $isTrial ? 'Start Free Trial' : 'Subscribe Now' }}
            </a>
        </div>
        @endforeach
    </div>

    <p style="color:#6b5d45; font-size:12px; margin-top:40px;">
        Prices in USD. Online card payment is being finalized — paid plans activate immediately at signup on us for now.
    </p>
</div>
@endsection