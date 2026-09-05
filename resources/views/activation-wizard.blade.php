@extends('layouts.anubis')
@section('title', 'Activate Your Installation')
@section('content')
<div class="anx-container narrow">
    <div style="text-align:right; margin-bottom:10px;">
        <span style="font-size:12px; color:#9b8a6a;">Signed in as {{ auth('customer')->user()->name }} ({{ auth('customer')->user()->email }})</span>
        &nbsp;•&nbsp;
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:none; border:none; color:#ff8c8c; font-size:12px; cursor:pointer; text-decoration:underline; padding:0;">Not you? Log out</button>
        </form>
    </div>

    <div class="anx-card">
        <h2 style="text-align:center;">🔗 Activate Your Installation</h2>

        @error('license')<div class="anx-alert anx-alert-error">{{ $message }}</div>@enderror

        @if($license)
            <div style="background:var(--anx-glass-bg); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); padding:16px; margin:16px 0;">
                <strong style="color:var(--anx-gold-light);">{{ $license->plan->name }}</strong>
                <div style="font-size:12px; color:#9b8a6a; margin-top:4px;"><code>{{ $license->license_key }}</code> — {{ $license->status }}</div>
            </div>

            <form method="POST" action="{{ route('activate.confirm') }}">
                @csrf
                <input type="hidden" name="return_url" value="{{ $returnUrl }}">
                <input type="hidden" name="state" value="{{ $state }}">
                <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Activate This Installation</button>
            </form>
        @else
            <p class="anx-hint" style="text-align:center;">No license found on this account. Please contact support.</p>
        @endif
    </div>
</div>
@endsection
