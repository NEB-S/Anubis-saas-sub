@extends('layouts.anubis')
@section('title', 'Admin Accounts')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.admins.index') }}" class="active">Admins</a>
        <a href="{{ route('admin.activity-log') }}">Activity Log</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">@csrf<button type="submit">Log out</button></form>
    </div>
</div>

<div class="anx-container">
    <div class="anx-flex-between">
        <h2 style="margin:0;">Admin Accounts</h2>
        <a class="anx-btn anx-btn-primary" href="{{ route('admin.admins.create') }}">+ New Admin</a>
    </div>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif
    @error('admin')<div class="anx-alert anx-alert-error">{{ $message }}</div>@enderror

    <div class="anx-card" style="padding:0; overflow:hidden;">
        <table class="anx-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td>{{ $admin->name }}</td>
                    <td>{{ $admin->email }}</td>
                    <td>{{ $admin->is_super_admin ? 'Super Admin' : 'Admin' }}</td>
                    <td>{{ $admin->created_at->format('M j, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete this admin account?')" style="display:inline;">
                            @csrf<button class="anx-btn anx-btn-danger anx-btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="anx-empty">No admins yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
