@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Work Orders</div><div class="sc-value">{{ number_format($d['total']) }}</div><div class="sc-sub">created in period</div></div>
    <div class="summary-card"><div class="sc-label">Completed</div><div class="sc-value">{{ number_format($d['completedNum']) }}</div><div class="sc-sub">reached completion</div></div>
    <div class="summary-card"><div class="sc-label">Statuses</div><div class="sc-value">{{ count($d['byStatus']) }}</div><div class="sc-sub">distinct in set</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Breakdown by Status</div>
    @if(count($d['byStatus']))
    <table>
        <thead><tr><th>Status</th><th class="num">Count</th><th class="num">% of Total</th></tr></thead>
        <tbody>
            @foreach($d['byStatus'] as $label => $count)
            <tr>
                <td>{{ $label }}</td>
                <td class="num">{{ number_format($count) }}</td>
                <td class="num">{{ $d['total'] ? number_format($count / $d['total'] * 100, 1) : '0.0' }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No work orders in this period.</div>
    @endif
</div>

<div class="report-section">
    <div class="section-heading">Breakdown by Urgency</div>
    @if(count($d['byUrgency']))
    <table>
        <thead><tr><th>Urgency</th><th class="num">Count</th><th class="num">% of Total</th></tr></thead>
        <tbody>
            @foreach($d['byUrgency'] as $label => $count)
            <tr>
                <td>{{ $label }}</td>
                <td class="num">{{ number_format($count) }}</td>
                <td class="num">{{ $d['total'] ? number_format($count / $d['total'] * 100, 1) : '0.0' }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No work orders in this period.</div>
    @endif
</div>

<div class="report-section">
    <div class="section-heading">Work Order Detail <span class="sh-meta">{{ number_format($d['total']) }} order(s)</span></div>
    @if($d['orders']->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>WO #</th><th>Customer</th><th>Company</th><th>Status</th><th>Urgency</th>
                <th>Created</th><th>Scheduled</th><th>Services</th><th>Assigned</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d['orders'] as $o)
            <tr>
                <td>{{ $o->woLabel() }}</td>
                <td>{{ $o->customer?->name ?? '—' }}</td>
                <td>{{ $o->company?->name ?? '—' }}</td>
                <td>{{ $o->statusLabel() }}</td>
                <td>{{ $o->urgencyLabel() }}</td>
                <td>{{ $o->created_at->format('M j, Y') }}</td>
                <td>{{ $o->scheduled_at ? $o->scheduled_at->format('M j, Y') : '—' }}</td>
                <td>{{ $o->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                <td>{{ $o->assignedEmployees->pluck('name')->join(', ') ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No work orders were created in this period.</div>
    @endif
</div>
@endsection
