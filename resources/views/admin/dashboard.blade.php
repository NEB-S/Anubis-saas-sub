@extends('layouts.anubis')
@section('title', 'Dashboard — Admin')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}" class="active">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}">Customers</a>
        <a href="{{ route('admin.coupons.index') }}">Coupons</a>
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
    <h2>Overview</h2>

    <div class="anx-stats-grid">
        <div class="anx-stat-tile">
            <div class="num">{{ $totalCustomers }}</div>
            <div class="label">Customers</div>
        </div>
        <div class="anx-stat-tile">
            <div class="num">{{ $activeLicenses }}</div>
            <div class="label">Active Licenses</div>
        </div>
        <div class="anx-stat-tile">
            <div class="num">{{ $trialLicenses }}</div>
            <div class="label">On Trial</div>
        </div>
        <div class="anx-stat-tile">
            <div class="num">{{ $canceledLicenses }}</div>
            <div class="label">Canceled</div>
        </div>
        <div class="anx-stat-tile">
            <div class="num">{{ $pastDueLicenses }}</div>
            <div class="label">Past Due</div>
        </div>
        <div class="anx-stat-tile">
            <div class="num">${{ number_format($monthlyRevenueCents / 100, 2) }}</div>
            <div class="label">Est. Monthly Revenue</div>
        </div>
    </div>

    <div class="anx-card">
        <h3>Recent Activity</h3>
        @forelse($recentActivity as $activity)
            <div style="padding:10px 0; border-bottom:1px solid rgba(242,194,135,0.08); font-size:13.5px;">
                <strong style="color:var(--anx-gold-light);">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</strong>
                — {{ $activity->license->customer->name ?? 'Unknown customer' }}
                <span style="color:#9b8a6a; float:right;">{{ $activity->created_at->diffForHumans() }}</span>
                <div style="color:#9b8a6a; margin-top:2px;">{{ $activity->description }}</div>
            </div>
        @empty
            <div class="anx-empty">No activity yet.</div>
        @endforelse
    </div>
</div>
@endsection
