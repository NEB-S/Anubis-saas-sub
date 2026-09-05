@extends('layouts.anubis')
@section('title', 'Account Settings')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>My Account</span></div>
    <div class="anx-nav">
        <a href="{{ route('portal.dashboard') }}">Dashboard</a>
        <a href="{{ route('portal.settings') }}" class="active">Settings</a>
        <a href="{{ route('portal.tickets.index') }}">Support</a>
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container narrow">
    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    <div class="anx-card">
        <h3>Profile</h3>
        <form method="POST" action="{{ route('portal.settings.name') }}">
            @csrf
            <div class="anx-field">
                <label>Full Name</label>
                <input type="text" name="name" value="{{ $customer->name }}">
                @error('name')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Save Name</button>
        </form>
    </div>

    <div class="anx-card">
        <h3>Change Password</h3>
        <form method="POST" action="{{ route('portal.settings.password') }}">
            @csrf
            <div class="anx-field">
                <label>Current Password</label>
                <input type="password" name="current_password">
                @error('current_password')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>New Password</label>
                <input type="password" name="password">
                @error('password')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation">
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Update Password</button>
        </form>
    </div>
</div>
@endsection
