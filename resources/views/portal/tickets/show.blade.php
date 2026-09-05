@extends('layouts.anubis')
@section('title', $ticket->subject)
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

    @if(session('success'))
        <div class="anx-alert anx-alert-success" style="margin-top:14px;">{{ session('success') }}</div>
    @endif

    <div class="anx-card" style="margin-top:14px;">
        <div class="anx-flex-between">
            <h2 style="margin:0;">{{ $ticket->subject }}</h2>
            <span class="anx-badge anx-badge-{{ $ticket->status === 'open' ? 'active' : 'canceled' }}">{{ $ticket->status }}</span>
        </div>
    </div>

    @foreach($ticket->messages as $message)
        <div class="anx-card" style="{{ $message->sender === 'admin' ? 'border-color:var(--anx-glass-border-strong);' : '' }}">
            <div style="font-size:12px; color:#9b8a6a; margin-bottom:6px;">
                <strong style="color:{{ $message->sender === 'admin' ? 'var(--anx-gold-light)' : '#9b8a6a' }};">{{ $message->sender === 'admin' ? 'Support Team' : 'You' }}</strong>
                — {{ $message->created_at->diffForHumans() }}
            </div>
            <div style="font-size:14px; white-space:pre-wrap;">{{ $message->message }}</div>
        </div>
    @endforeach

    <div class="anx-card">
        <h3>Reply</h3>
        <form method="POST" action="{{ route('portal.tickets.reply', $ticket) }}">
            @csrf
            <div class="anx-field">
                <textarea name="message" rows="4" style="width:100%; padding:11px 13px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-family:inherit; font-size:14px;"></textarea>
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Send Reply</button>
        </form>
        @if($ticket->status === 'closed')
            <p class="anx-hint" style="margin-top:8px;">This ticket is closed — replying will reopen it.</p>
        @endif
    </div>
</div>
@endsection
