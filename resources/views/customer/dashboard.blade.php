@extends('layouts.portal')

@section('title', 'My Dashboard')

@section('content')
<h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>

<div class="kpi-grid">
    <div class="kpi-card">
        <span class="kpi-number">{{ $stats['open'] }}</span>
        <span class="kpi-label">Open Work Orders</span>
    </div>
    <div class="kpi-card kpi-warn">
        <span class="kpi-number">{{ $stats['feedback'] }}</span>
        <span class="kpi-label">Need Your Feedback</span>
    </div>
    <div class="kpi-card kpi-info">
        <span class="kpi-number">{{ $stats['to_sign'] }}</span>
        <span class="kpi-label">Invoices to Sign</span>
    </div>
    <div class="kpi-card kpi-success">
        <span class="kpi-number">{{ $stats['completed'] }}</span>
        <span class="kpi-label">Completed This Month</span>
    </div>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;flex-wrap:wrap;gap:.5rem;">
    <h2 class="section-title" style="margin:0;">Recent Activity</h2>
    <div id="wo-filter" style="display:flex;gap:.35rem;">
        <button onclick="filterOrders('all')"       class="filter-pill active" data-filter="all">All</button>
        <button onclick="filterOrders('active')"    class="filter-pill"        data-filter="active">Active</button>
        <button onclick="filterOrders('completed')" class="filter-pill"        data-filter="completed">Completed</button>
    </div>
</div>

<table class="data-table" id="recent-orders-table">
    <thead>
        <tr>
            <th>#</th><th>Services</th><th>Status</th><th>Urgency</th><th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($recentOrders as $order)
        <tr data-href="{{ route('portal.work-orders.show', $order) }}"
            data-status="{{ $order->status }}">
            <td>{{ $order->woLabel() }}</td>
            <td>{{ $order->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
            <td><span class="badge badge-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
            <td>{{ ucfirst($order->urgency) }}</td>
            <td>{{ $order->created_at->format('M j, Y') }}</td>
        </tr>
        @empty
        <tr id="empty-row"><td colspan="5" style="text-align:center;color:#999;">No work orders yet. <a href="{{ route('portal.work-orders.create') }}" style="color:var(--accent);">Submit one now.</a></td></tr>
        @endforelse
    </tbody>
</table>

<style>
.filter-pill {
    padding:.3rem .85rem;border-radius:999px;border:1px solid #d1d5db;
    background:#fff;color:#555;font-size:.8rem;cursor:pointer;transition:all .15s;
}
.filter-pill:hover { border-color:var(--accent);color:var(--accent); }
.filter-pill.active { background:var(--accent);border-color:var(--accent);color:#fff;font-weight:600; }
</style>

<script>
function filterOrders(filter) {
    const completed = ['completed', 'canceled'];
    document.querySelectorAll('#wo-filter .filter-pill').forEach(b => {
        b.classList.toggle('active', b.dataset.filter === filter);
    });
    let visibleCount = 0;
    document.querySelectorAll('#recent-orders-table tbody tr[data-status]').forEach(row => {
        const status = row.dataset.status;
        const show = filter === 'all'
            || (filter === 'active'    && !completed.includes(status))
            || (filter === 'completed' && completed.includes(status));
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    const emptyRow = document.getElementById('empty-row');
    if (emptyRow) return;
    let noMatch = document.getElementById('no-match-row');
    if (visibleCount === 0) {
        if (!noMatch) {
            noMatch = document.createElement('tr');
            noMatch.id = 'no-match-row';
            noMatch.innerHTML = '<td colspan="5" style="text-align:center;color:#999;padding:1.5rem;">No ' + filter + ' work orders.</td>';
            document.querySelector('#recent-orders-table tbody').appendChild(noMatch);
        }
        noMatch.style.display = '';
    } else if (noMatch) {
        noMatch.style.display = 'none';
    }
}
</script>
@endsection
