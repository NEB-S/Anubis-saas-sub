@extends('layouts.anubis')
@section('title', 'Create Account')
@section('content')
<div class="anx-container narrow">
    @if(session('info'))
        <div class="anx-alert" style="background:rgba(93,184,232,0.12); border:1px solid rgba(93,184,232,0.3); color:#7ec8f2;">{{ session('info') }}</div>
    @endif
    <div class="anx-card">
        <h2 style="text-align:center;">Create Portal Access</h2>
        <p class="anx-hint">Already have a license from us? Use the same email you gave us — this links your account automatically.</p>
        <form method="POST" action="{{ route('portal.register.submit') }}">
            @csrf
            <div class="anx-field">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Password</label>
                <input type="password" name="password">
                @error('password')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation">
            </div>

            <div class="anx-field">
                <label>Choose a Plan</label>
                <select name="plan_id" required>
                    @foreach($plans as $p)
                        @php
                            $cycle = match($p->default_period_days) {
                                7 => '7-Day Free Trial',
                                30 => 'Monthly (30 days)',
                                180 => 'Every 6 Months (180 days)',
                                365 => 'Yearly (365 days)',
                                default => $p->default_period_days . ' days',
                            };
                        @endphp
                        <option value="{{ $p->id }}" {{ ($preselectedPlan && $preselectedPlan->id === $p->id) ? 'selected' : '' }}>
                            {{ $p->name }} — {{ $cycle }}{{ $p->price_monthly_cents > 0 ? ' — $' . number_format($p->price_monthly_cents / 100, 2) : ' — Free' }}
                        </option>
                    @endforeach
                </select>
                @error('plan_id')<div class="anx-error">{{ $message }}</div>@enderror
                <div class="anx-hint" style="margin-top:4px;">Picking a paid plan activates it immediately — online payment is being finalized, so this is on us for now.</div>
            </div>

            <div class="anx-field">
                <label>Coupon Code (optional, Trial plan only)</label>
                <input type="text" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="e.g. LAUNCH25" style="text-transform:uppercase;">
                @error('coupon_code')<div class="anx-error">{{ $message }}</div>@enderror
            </div>

            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Create Account</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:13px;">
            Already registered? <a href="{{ route('portal.login') }}">Log in</a>
        </p>
    </div>
</div>
@endsection
