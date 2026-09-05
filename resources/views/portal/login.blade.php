@extends('layouts.anubis')
@section('title', 'My Account — Login')
@section('content')
<div class="anx-container narrow">
    <div class="anx-card">
        <h2 style="text-align:center;">My Account</h2>
        <form method="POST" action="{{ route('portal.login.submit') }}">
            @csrf
            <div class="anx-field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" autofocus>
                @error('email')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Password</label>
                <input type="password" name="password">
            </div>
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; margin-bottom:16px;">
                <input type="checkbox" name="remember" style="width:auto;"> Remember me
            </label>
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Log In</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:13px;">
            Don't have portal access yet? <a href="{{ route('portal.register') }}">Create one</a>
        </p>
    </div>
</div>
@endsection
