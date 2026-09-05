@extends('layouts.anubis')
@section('title', 'Support Tickets')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>My Account</span></div>
    <div class="anx-nav">
        <a href="{{ route('portal.dashboard') }}">Dashboard</a>
        <a href="{{ route('portal.tickets.index') }}" class="active">Support</a>
        <a href="{{ route('portal.settings') }}">Settings</a>
        <form method="POST" action="{{ route('portal.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    <div class="anx-flex-between">
        <h2 style="margin:0;">Support Tickets</h2>
        <a class="anx-btn anx-btn-primary" href="{{ route('portal.tickets.create') }}">+ New Ticket</a>
    </div>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    @forelse($tickets as $ticket)
        <a href="{{ route('portal.tickets.show', $ticket) }}" style="text-decoration:none;">
            <div class="anx-card" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <strong style="color:var(--anx-gold-light);">{{ $ticket->subject }}</strong>
                    <div style="color:#9b8a6a; font-size:12px; margin-top:2px;">Opened {{ $ticket->created_at->diffForHumans() }}</div>
                </div>
                <span class="anx-badge anx-badge-{{ $ticket->status === 'open' ? 'active' : 'canceled' }}">{{ $ticket->status }}</span>
            </div>
        </a>
    @empty
        <div class="anx-card anx-empty">No support tickets yet.</div>
    @endforelse
</div>
@endsection
