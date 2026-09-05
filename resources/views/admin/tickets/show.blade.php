@extends('layouts.anubis')
@section('title', $ticket->subject)
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
        <a href="{{ route('admin.coupons.index') }}">Coupons</a>
        <a href="{{ route('admin.tickets.index') }}" class="active">Tickets</a>
        <a href="{{ route('admin.admins.index') }}">Admins</a>
        <a href="{{ route('admin.activity-log') }}">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container narrow">
    <a href="{{ route('admin.tickets.index') }}" style="font-size:13px;">&larr; Back to Tickets</a>

    @if(session('success'))
        <div class="anx-alert anx-alert-success" style="margin-top:14px;">{{ session('success') }}</div>
    @endif

    <div class="anx-card" style="margin-top:14px;">
        <div class="anx-flex-between">
            <div>
                <h2 style="margin:0;">{{ $ticket->subject }}</h2>
                <a href="{{ route('admin.customers.show', $ticket->customer) }}" style="font-size:13px;">{{ $ticket->customer->name }} ({{ $ticket->customer->email }})</a>
            </div>
            <span class="anx-badge anx-badge-{{ $ticket->status === 'open' ? 'active' : 'canceled' }}">{{ $ticket->status }}</span>
        </div>
    </div>

    @foreach($ticket->messages as $message)
        <div class="anx-card" style="{{ $message->sender === 'admin' ? 'border-color:var(--anx-glass-border-strong);' : '' }}">
            <div style="font-size:12px; color:#9b8a6a; margin-bottom:6px;">
                <strong style="color:{{ $message->sender === 'admin' ? 'var(--anx-gold-light)' : '#9b8a6a' }};">{{ $message->sender === 'admin' ? 'You (Support)' : $ticket->customer->name }}</strong>
                — {{ $message->created_at->diffForHumans() }}
            </div>
            <div style="font-size:14px; white-space:pre-wrap;">{{ $message->message }}</div>
        </div>
    @endforeach

    <div class="anx-card">
        <h3>Reply</h3>
        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
            @csrf
            <div class="anx-field">
                <textarea name="message" rows="4" style="width:100%; padding:11px 13px; background:var(--anx-surface-2); border:1px solid var(--anx-glass-border); border-radius:var(--anx-radius-sm); color:var(--anx-gold-light); font-family:inherit; font-size:14px;"></textarea>
            </div>
            <button class="anx-btn anx-btn-primary" type="submit">Send Reply</button>
        </form>

        <div style="margin-top:14px;">
            @if($ticket->status === 'open')
                <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}" style="display:inline;">
                    @csrf<button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit">Close Ticket</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}" style="display:inline;">
                    @csrf<button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit">Reopen Ticket</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
