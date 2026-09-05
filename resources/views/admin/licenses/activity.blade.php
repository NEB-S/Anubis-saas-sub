@extends('layouts.anubis')
@section('title', 'License History')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
        <a href="{{ route('admin.tickets.index') }}" style="position:relative;">Tickets
            @if(($openTicketsCount ?? 0) > 0)
                <span style="position:absolute; top:-6px; right:-10px; background:#e85d5d; color:#fff; font-size:10px; font-weight:800; padding:1px 5px; border-radius:10px; line-height:1.4;">{{ $openTicketsCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.admins.index') }}">Admins</a>
        <a href="{{ route('admin.activity-log') }}">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    <a href="{{ route('admin.licenses.index') }}" style="font-size:13px;">&larr; Back to Licenses</a>

    <div class="anx-card" style="margin-top:14px;">
        <div class="anx-flex-between">
            <div>
                <h2 style="margin:0;">{{ $license->customer->name }} — {{ $license->plan->name }}</h2>
                <code>{{ $license->license_key }}</code>
            </div>
            <span class="anx-badge anx-badge-{{ $license->status }}">{{ $license->status }}</span>
        </div>
    </div>

    <div class="anx-card">
        <h3>Activity History</h3>
        @forelse($license->activities as $activity)
            <div style="padding:12px 0; border-bottom:1px solid rgba(242,194,135,0.08);">
                <div style="display:flex; justify-content:space-between; font-size:13px;">
                    <span><strong style="color:var(--anx-gold-light);">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</strong>
                        @if($activity->actor)
                            <span style="color:#9b8a6a;"> — by {{ $activity->actor }}</span>
                        @endif
                    </span>
                    <span style="color:#9b8a6a;">{{ $activity->created_at->format('M j, Y g:ia') }}</span>
                </div>
                <div style="font-size:13.5px; margin-top:4px;">{{ $activity->description }}</div>
            </div>
        @empty
            <div class="anx-empty">No activity recorded yet.</div>
        @endforelse
    </div>
</div>
@endsection
