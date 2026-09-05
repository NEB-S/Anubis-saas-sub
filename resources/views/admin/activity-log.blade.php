@extends('layouts.anubis')
@section('title', 'Activity Log')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.admins.index') }}">Admins</a>
        <a href="{{ route('admin.activity-log') }}" class="active">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    <h2>Admin Activity Log</h2>

    <div class="anx-card">
        @forelse($activities as $activity)
            <div style="padding:10px 0; border-bottom:1px solid rgba(242,194,135,0.08); font-size:13.5px;">
                <strong style="color:var(--anx-gold-light);">{{ ucfirst(str_replace('_', ' ', $activity->type)) }}</strong>
                — {{ $activity->admin_name_snapshot }}
                <span style="color:#9b8a6a; float:right;">{{ $activity->created_at->diffForHumans() }}</span>
                <div style="color:#9b8a6a; margin-top:2px;">{{ $activity->description }}</div>
            </div>
        @empty
            <div class="anx-empty">No activity recorded yet.</div>
        @endforelse
    </div>

    <div style="margin-top:16px;">{{ $activities->links() }}</div>
</div>
@endsection
