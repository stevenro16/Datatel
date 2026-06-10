@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Technicians</div><div class="sc-value">{{ number_format($d['groups']->count()) }}</div></div>
    <div class="summary-card"><div class="sc-label">Time Entries</div><div class="sc-value">{{ number_format($d['entryCount']) }}</div></div>
    <div class="summary-card"><div class="sc-label">Total Hours</div><div class="sc-value">{{ number_format($d['totalHours'], 1) }}</div></div>
</div>

@forelse($d['groups'] as $group)
<div class="report-section">
    <div class="section-heading">
        {{ $group['tech']->name }}
        <span class="sh-meta">{{ $group['entries']->count() }} entr(ies) · {{ number_format($group['totalHours'], 1) }} hrs</span>
    </div>
    <table>
        <thead>
            <tr><th>Date</th><th>WO #</th><th>Customer</th><th>Clock In</th><th>Clock Out</th><th class="num">Hours</th><th>Note</th></tr>
        </thead>
        <tbody>
            @foreach($group['entries'] as $e)
            <tr>
                <td>{{ $e->clocked_in_at->format('M j, Y') }}</td>
                <td>{{ $e->workOrder?->woLabel() ?? '—' }}</td>
                <td>{{ $e->workOrder?->customer?->name ?? '—' }}</td>
                <td>{{ $e->clocked_in_at->format('g:i A') }}</td>
                <td>{{ $e->clocked_out_at?->format('g:i A') ?? '—' }}</td>
                <td class="num">{{ number_format(($e->totalMinutes() ?? 0) / 60, 2) }}</td>
                <td>{{ $e->note ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="5">Subtotal — {{ $group['tech']->name }}</td><td class="num">{{ number_format($group['totalHours'], 2) }}</td><td></td></tr>
        </tfoot>
    </table>
</div>
@empty
<div class="empty-state">No clocked time entries in this period.</div>
@endforelse
@endsection
