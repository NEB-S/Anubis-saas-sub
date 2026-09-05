@extends('layouts.anubis')
@section('title', 'Get Started')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>My Account</span></div>
    <div class="anx-nav">
        <a href="{{ route('portal.dashboard') }}">Dashboard</a>
        <a href="{{ route('portal.tickets.index') }}">Support</a>
        <a href="{{ route('portal.settings') }}">Settings</a>
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container narrow">
    <a href="{{ route('portal.dashboard') }}" style="font-size:13px;">&larr; Back to Dashboard</a>

    <div class="anx-card" style="margin-top:14px;">
        <h2>🚀 Get Started</h2>
        <p class="anx-hint">Three steps and your site is live — no manual editing needed.</p>
    </div>

    <div class="anx-card">
        <h3>Step 1 — Download</h3>
        @if($downloadUrl)
            <a class="anx-btn anx-btn-primary anx-btn-block" href="{{ $downloadUrl }}">⬇ Download Anubis SaaS</a>
        @else
            <p class="anx-hint">Download link not configured yet — contact us to get the installation package.</p>
        @endif
    </div>

    <div class="anx-card">
        <h3>Step 2 — Install</h3>
        <p style="font-size:13.5px;">Extract the download to your server, then run:</p>
        <div style="background:#0b0b0d; border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); padding:12px 14px; font-family:monospace; font-size:13px; color:#7ec8f2; margin-bottom:8px;">
            composer install
        </div>
    </div>

    <div class="anx-card" style="border-color:var(--anx-glass-border-strong);">
        <h3>Step 3 — Activate (your license key is already filled in)</h3>
        <p style="font-size:13.5px;">Run this single command — it saves your key and verifies it automatically:</p>
        <div style="display:flex; gap:8px; align-items:stretch;">
            <div id="activate-cmd" style="flex:1; background:#0b0b0d; border:1px solid var(--anx-gold); border-radius:var(--anx-radius-sm); padding:12px 14px; font-family:monospace; font-size:13px; color:var(--anx-gold-light); overflow-x:auto; white-space:nowrap;">{{ $activateCommand }}</div>
            <button class="anx-btn anx-btn-primary" onclick="copyActivateCommand()" style="white-space:nowrap;">📋 Copy</button>
        </div>
        <p class="anx-hint" style="margin-top:10px;">This command is unique to your account — don't share it publicly, it's the same as your license key.</p>
    </div>

    <div class="anx-card anx-empty">
        That's it. Once the command finishes, your site is licensed and live.
    </div>
</div>

<script>
    function copyActivateCommand() {
        const text = document.getElementById('activate-cmd').innerText;
        navigator.clipboard.writeText(text).then(() => {
            event.target.textContent = '✓ Copied';
            setTimeout(() => { event.target.textContent = '📋 Copy'; }, 2000);
        });
    }
</script>
@endsection
