@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Technicians</div><div class="sc-value">{{ number_format($d['rows']->count()) }}</div></div>
    <div class="summary-card"><div class="sc-label">Completed Orders</div><div class="sc-value">{{ number_format($d['totalCompleted']) }}</div><div class="sc-sub">in period</div></div>
    <div class="summary-card"><div class="sc-label">Hours Logged</div><div class="sc-value">{{ number_format($d['totalHours'], 1) }}</div><div class="sc-sub">clocked time</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Technician Performance</div>
    @if($d['rows']->isNotEmpty())
    <table>
        <thead>
            <tr><th>Technician</th><th class="num">Open Orders</th><th class="num">Completed</th><th class="num">Hours Logged</th><th class="num">Avg Days to Complete</th></tr>
        </thead>
        <tbody>
            @foreach($d['rows'] as $row)
            <tr>
                <td>{{ $row['tech']->name }}</td>
                <td class="num">{{ number_format($row['openOrders']) }}</td>
                <td class="num">{{ number_format($row['completed']) }}</td>
                <td class="num">{{ number_format($row['hours'], 1) }}</td>
                <td class="num">{{ $row['avgDays'] !== null ? number_format($row['avgDays'], 1) : '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Totals</td>
                <td class="num">{{ number_format($d['rows']->sum('openOrders')) }}</td>
                <td class="num">{{ number_format($d['totalCompleted']) }}</td>
                <td class="num">{{ number_format($d['totalHours'], 1) }}</td>
                <td class="num">—</td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size:.7rem;color:#94a3b8;margin-top:.4rem;">Completed counts orders that reached "Completed" within the period and were assigned to the technician. Hours come from closed time entries clocked-in within the period.</p>
    @else
    <div class="empty-state">No technicians found.</div>
    @endif
</div>
@endsection
