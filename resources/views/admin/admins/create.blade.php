@extends('layouts.anubis')
@section('title', 'New Admin')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.admins.index') }}">Admins</a>
        <a href="{{ route('admin.activity-log') }}">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container narrow">
    <a href="{{ route('admin.admins.index') }}" style="font-size:13px;">&larr; Back to Admins</a>
    <div class="anx-card" style="margin-top:14px;">
        <h2>New Admin Account</h2>
        <form method="POST" action="{{ route('admin.admins.store') }}">
            @csrf
            <div class="anx-field">
                <label>Name</label>
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
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; margin-bottom:16px;">
                <input type="checkbox" name="is_super_admin" value="1" style="width:auto;"> Grant Super Admin (can manage other admins)
            </label>
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Create Admin</button>
        </form>
    </div>
</div>
@endsection
