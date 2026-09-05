@extends('layouts.anubis')
@section('title', 'Customers — Admin')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}">Licenses</a>
        <a href="{{ route('admin.customers.index') }}" class="active">Customers</a>
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
    <h2>Customers</h2>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    <div class="anx-card" style="padding:0; overflow-x:auto;">
        <table class="anx-table">
            <thead>
                <tr>
                    <th>Name</th><th>Email</th><th>Portal Access</th><th>Licenses</th><th>Joined</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->password ? '✓ Active' : '— Not set up' }}</td>
                    <td>{{ $customer->licenses_count }}</td>
                    <td>{{ $customer->created_at->format('M j, Y') }}</td>
                    <td style="white-space:nowrap;">
                        <a class="anx-btn anx-btn-ghost anx-btn-sm" href="{{ route('admin.customers.show', $customer) }}">View</a>
                        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" style="display:inline;" onsubmit="return confirm('Permanently delete {{ $customer->name }} and ALL their licenses? This cannot be undone.')">
                            @csrf
                            <button class="anx-btn anx-btn-danger anx-btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="anx-empty">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $customers->links() }}</div>
</div>
@endsection
