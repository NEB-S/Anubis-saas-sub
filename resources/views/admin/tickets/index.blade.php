@extends('layouts.anubis')
@section('title', 'Support Tickets — Admin')
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

<div class="anx-container">
    <h2>Support Tickets</h2>

    <div class="anx-card" style="padding:0; overflow-x:auto;">
        <table class="anx-table">
            <thead>
                <tr><th>Subject</th><th>Customer</th><th>Status</th><th>Opened</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ $ticket->customer->name }}</td>
                    <td><span class="anx-badge anx-badge-{{ $ticket->status === 'open' ? 'active' : 'canceled' }}">{{ $ticket->status }}</span></td>
                    <td>{{ $ticket->created_at->diffForHumans() }}</td>
                    <td><a class="anx-btn anx-btn-ghost anx-btn-sm" href="{{ route('admin.tickets.show', $ticket) }}">Open</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="anx-empty">No tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $tickets->links() }}</div>
</div>
@endsection
