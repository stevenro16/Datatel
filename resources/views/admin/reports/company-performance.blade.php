@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

@if($d['single'])
<div style="font-size:1rem;font-weight:700;color:#1A3C5E;margin-bottom:.75rem;">{{ $d['single']->name }}</div>
@endif

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Work Orders</div><div class="sc-value">{{ number_format($d['totalWorkOrders']) }}</div><div class="sc-sub">in period</div></div>
    <div class="summary-card"><div class="sc-label">Completed</div><div class="sc-value">{{ number_format($d['totalCompleted']) }}</div></div>
    <div class="summary-card"><div class="sc-label">Revenue</div><div class="sc-value">${{ number_format($d['totalRevenue'], 2) }}</div><div class="sc-sub">invoiced in period</div></div>
    <div class="summary-card" style="border-left-color:#dc2626;"><div class="sc-label">Outstanding</div><div class="sc-value">${{ number_format($d['totalOutstanding'], 2) }}</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Company Performance <span class="sh-meta">{{ $d['rows']->count() }} compan(ies)</span></div>
    @if($d['rows']->isNotEmpty())
    <table>
        <thead>
            <tr><th>Company</th><th class="num">Work Orders</th><th class="num">Open</th><th class="num">Completed</th><th class="num">Revenue</th><th class="num">Outstanding</th></tr>
        </thead>
        <tbody>
            @foreach($d['rows'] as $row)
            <tr>
                <td>{{ $row['company']->name }}</td>
                <td class="num">{{ number_format($row['woCount']) }}</td>
                <td class="num">{{ number_format($row['open']) }}</td>
                <td class="num">{{ number_format($row['completed']) }}</td>
                <td class="num">${{ number_format($row['revenue'], 2) }}</td>
                <td class="num">${{ number_format($row['outstanding'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Totals</td>
                <td class="num">{{ number_format($d['totalWorkOrders']) }}</td>
                <td class="num">{{ number_format($d['rows']->sum('open')) }}</td>
                <td class="num">{{ number_format($d['totalCompleted']) }}</td>
                <td class="num">${{ number_format($d['totalRevenue'], 2) }}</td>
                <td class="num">${{ number_format($d['totalOutstanding'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">No company activity in this period.</div>
    @endif
</div>
@endsection
