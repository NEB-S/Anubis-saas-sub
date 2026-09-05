@extends('layouts.anubis')
@section('title', 'Admin Login')
@section('content')
<div class="anx-container narrow">
    <div class="anx-card" style="text-align:center;">
        <h2>🔐 Admin Access</h2>
        <p class="anx-hint" style="text-align:center;">License Server Control Panel</p>
        <form method="POST" action="{{ route('admin.login.submit') }}" style="text-align:left; margin-top:20px;">
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
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Log In</button>
        </form>
    </div>
</div>
@endsection
