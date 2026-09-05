@extends('layouts.anubis')
@section('title', 'Anubis License Server')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('pricing') }}">Pricing</a>
        <a href="{{ route('portal.login') }}">Log In</a>
        <a href="{{ route('portal.register') }}" class="active">Get Started</a>
    </div>
</div>

<div class="anx-container" style="max-width:700px; text-align:center; padding-top:80px;">
    <h1 style="font-size:2.3rem; color:var(--anx-gold-light); margin-bottom:14px;">Anubis License Server</h1>
    <p style="color:#9b8a6a; font-size:1.05rem; max-width:520px; margin:0 auto 36px;">
        The licensing and account platform for Anubis-powered game servers.
        Manage your subscription, licenses, and support requests all in one place.
    </p>

    <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-bottom:16px;">
        <a class="anx-btn anx-btn-primary" href="{{ route('portal.register') }}" style="padding:12px 28px;">Create Account</a>
        <a class="anx-btn anx-btn-ghost" href="{{ route('portal.login') }}" style="padding:12px 28px;">Log In</a>
    </div>
    <a href="{{ route('pricing') }}" style="font-size:13px;">View Pricing &rarr;</a>

    <div style="margin-top:70px; padding-top:20px; border-top:1px solid rgba(242,194,135,0.1);">
        <a href="{{ route('admin.login') }}" style="font-size:12px; color:#6b5d45;">Admin Login</a>
    </div>
</div>
@endsection
