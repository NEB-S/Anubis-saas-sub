@extends('layouts.anubis')
@section('title', 'New Ticket')
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
    <a href="{{ route('portal.tickets.index') }}" style="font-size:13px;">&larr; Back to Tickets</a>
    <div class="anx-card" style="margin-top:14px;">
        <h2>New Support Ticket</h2>
        <form method="POST" action="{{ route('portal.tickets.store') }}">
            @csrf
            <div class="anx-field">
                <label>Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}">
                @error('subject')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <div class="anx-field">
                <label>Message</label>
                <textarea name="message" rows="6" style="width:100%; padding:11px 13px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-family:inherit; font-size:14px;">{{ old('message') }}</textarea>
                @error('message')<div class="anx-error">{{ $message }}</div>@enderror
            </div>
            <button class="anx-btn anx-btn-primary anx-btn-block" type="submit">Submit Ticket</button>
        </form>
    </div>
</div>
@endsection
