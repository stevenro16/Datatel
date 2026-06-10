@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Open Orders</div><div class="sc-value">{{ number_format($d['totalOpen']) }}</div><div class="sc-sub">not completed or canceled</div></div>
    <div class="summary-card"><div class="sc-label">Oldest</div><div class="sc-value">{{ number_format($d['oldestDays']) }}</div><div class="sc-sub">days open</div></div>
    <div class="summary-card"><div class="sc-label">Average Age</div><div class="sc-value">{{ number_format($d['avgAgeDays'], 1) }}</div><div class="sc-sub">days open</div></div>
</div>

@forelse($d['groups'] as $label => $group)
<div class="report-section">
    <div class="section-heading">
        {{ $label }}
        <span class="sh-meta">{{ $group['count'] }} order(s) · oldest {{ $group['oldest'] }}d · avg {{ number_format($group['avgAge'], 1) }}d</span>
    </div>
    <table>
        <thead>
            <tr><th>WO #</th><th>Customer</th><th>Created</th><th class="num">Age (days)</th><th>Scheduled</th><th>Assigned</th></tr>
        </thead>
        <tbody>
            @foreach($group['rows'] as $row)
            @php $o = $row['order']; @endphp
            <tr>
                <td>{{ $o->woLabel() }}</td>
                <td>{{ $o->customer?->name ?? '—' }}</td>
                <td>{{ $o->created_at->format('M j, Y') }}</td>
                <td class="num">
                    {{ $row['ageDays'] }}
                    @if($row['ageDays'] >= 30)<span class="pill pill-red">aging</span>@elseif($row['ageDays'] >= 14)<span class="pill pill-amber">watch</span>@endif
                </td>
                <td>
                    @if($o->scheduled_at)
                        {{ $o->scheduled_at->format('M j, Y') }}
                        @if($row['scheduledDays'] !== null && $row['scheduledDays'] < 0)<span class="pill pill-red">overdue</span>@endif
                    @else <span class="muted">—</span> @endif
                </td>
                <td>{{ $o->assignedEmployees->pluck('name')->join(', ') ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@empty
<div class="empty-state">No open work orders. Everything is completed or canceled.</div>
@endforelse
@endsection
