@extends('layouts.anubis')
@section('title', 'Licenses — Admin')
@section('content')
<div class="anx-topbar">
    <div class="anx-brand">ANUBIS <span>License Server</span></div>
    <div class="anx-nav">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.licenses.index') }}" class="active">Licenses</a>
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

<div class="anx-container" style="max-width:1500px;">
    <div class="anx-flex-between">
        <h2 style="margin:0;">Licenses</h2>
        <div style="display:flex; gap:8px;">
            <a class="anx-btn anx-btn-ghost" href="{{ route('admin.licenses.export') }}">⬇ Export CSV</a>
            <a class="anx-btn anx-btn-primary" href="{{ route('admin.licenses.create') }}">+ Issue New License</a>
        </div>
    </div>

    @if(session('success'))
        <div class="anx-alert anx-alert-success">{{ session('success') }}</div>
    @endif

    <!-- Hidden form used only for bulk actions — kept OUTSIDE the table so it
         never nests inside the per-row action forms below (HTML forbids
         nested <form> tags). JS fills it in with the checked license IDs
         right before submitting. -->
    <form id="bulk-form" method="POST" action="{{ route('admin.licenses.bulk-action') }}" style="display:none;">
        @csrf
        <input type="hidden" name="bulk_type" id="bulk-type-input">
        <div id="bulk-ids-container"></div>
    </form>

    <div id="bulk-bar" class="anx-card" style="display:none; padding:12px 20px; display:flex; align-items:center; gap:10px;">
        <span id="bulk-count" style="font-size:13px; color:#9b8a6a;"></span>
        <button type="button" class="anx-btn anx-btn-ghost anx-btn-sm" onclick="submitBulk('extend')">+30 Days (Bulk)</button>
        <button type="button" class="anx-btn anx-btn-danger anx-btn-sm" onclick="submitBulk('cancel')">Cancel (Bulk)</button>
        <button type="button" class="anx-btn anx-btn-danger anx-btn-sm" onclick="submitBulk('delete')">Delete (Bulk)</button>
    </div>

    <div class="anx-card" style="padding:0; overflow-x:auto;">
        <table class="anx-table" style="table-layout:fixed; width:100%; min-width:1260px;">
            <colgroup>
                <col style="width:34px;">
                <col style="width:190px;">
                <col style="width:180px;">
                <col style="width:75px;">
                <col style="width:75px;">
                <col style="width:210px;">
                <col style="width:85px;">
                <col style="width:85px;">
                <col style="width:270px;">
            </colgroup>
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
                    <th>Key</th><th>Customer</th><th>Plan</th><th>Status</th>
                    <th>Domain</th><th>Expires</th><th>Last Check</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($licenses as $license)
                <tr>
                    <td><input type="checkbox" class="bulk-checkbox" value="{{ $license->id }}" onclick="updateBulkBar()"></td>
                    <td style="white-space:nowrap;"><code>{{ $license->license_key }}</code></td>
                    <td style="overflow:hidden; text-overflow:ellipsis;"><a href="{{ route('admin.customers.show', $license->customer) }}">{{ $license->customer->name }}</a><br><small style="color:#9b8a6a;">{{ $license->customer->email }}</small></td>
                    <td>{{ $license->plan->name }}</td>
                    <td><span class="anx-badge anx-badge-{{ $license->status }}">{{ $license->status }}</span></td>
                    <td style="white-space:nowrap;">
                        {{ $license->bound_domain ?? '—' }}
                        @if($license->pending_domain)
                            <span style="color:#e8b85d; font-size:12px;"> → {{ $license->pending_domain }}</span>
                            <form method="POST" action="{{ route('admin.licenses.domain.approve', $license) }}" style="display:inline;">
                                @csrf<button class="anx-btn anx-btn-ghost anx-btn-sm" type="submit" title="Approve">✓</button>
                            </form>
                            <form method="POST" action="{{ route('admin.licenses.domain.reject', $license) }}" style="display:inline;">
                                @csrf<button class="anx-btn anx-btn-danger anx-btn-sm" type="submit" title="Reject">✕</button>
                            </form>
                        @endif
                    </td>
                    <td>{{ optional($license->current_period_ends_at)->format('Y-m-d') ?? ($license->trial_ends_at?->format('Y-m-d') ?? '—') }}</td>
                    <td>{{ optional($license->last_checked_at)->diffForHumans() ?? 'never' }}</td>
                    <td style="white-space:nowrap;">
                        <a class="anx-btn anx-btn-ghost anx-btn-sm" style="padding:5px 8px; font-size:11px;" href="{{ route('admin.licenses.activity', $license) }}">History</a>
                        @if($license->status !== 'canceled')
                            <form method="POST" action="{{ route('admin.licenses.cancel', $license) }}" onsubmit="return confirm('Cancel this license?')" style="display:inline;">
                                @csrf<button class="anx-btn anx-btn-danger anx-btn-sm" style="padding:5px 8px; font-size:11px;" type="submit">Cancel</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.licenses.reactivate', $license) }}" style="display:inline;">
                                @csrf<button class="anx-btn anx-btn-ghost anx-btn-sm" style="padding:5px 8px; font-size:11px;" type="submit">Reactivate</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.licenses.extend', $license) }}" style="display:inline;">
                            @csrf<input type="hidden" name="days" value="30">
                            <button class="anx-btn anx-btn-ghost anx-btn-sm" style="padding:5px 8px; font-size:11px;" type="submit">+30d</button>
                        </form>
                        <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" onsubmit="return confirm('Permanently DELETE this license?') && confirm('Really sure? This cannot be undone.')" style="display:inline;">
                            @csrf<button class="anx-btn anx-btn-danger anx-btn-sm" style="padding:5px 8px; font-size:11px;" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="anx-empty">No licenses issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $licenses->links() }}</div>
</div>

<script>
    function toggleAll(source) {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = source.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.bulk-checkbox:checked');
        const bar = document.getElementById('bulk-bar');
        const count = document.getElementById('bulk-count');
        if (checked.length > 0) {
            bar.style.display = 'flex';
            count.textContent = checked.length + ' selected';
        } else {
            bar.style.display = 'none';
        }
    }

    function submitBulk(type) {
        const checked = document.querySelectorAll('.bulk-checkbox:checked');
        if (checked.length === 0) return;

        const confirmText = {
            cancel: 'Cancel ' + checked.length + ' license(s)?',
            extend: 'Extend ' + checked.length + ' license(s) by 30 days?',
            delete: 'Permanently DELETE ' + checked.length + ' license(s)? This cannot be undone.',
        }[type];
        if (!confirm(confirmText)) return;

        const container = document.getElementById('bulk-ids-container');
        container.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'license_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });

        document.getElementById('bulk-type-input').value = type;
        document.getElementById('bulk-form').submit();
    }
</script>
@endsection
