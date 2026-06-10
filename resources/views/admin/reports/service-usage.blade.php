@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Active Services</div><div class="sc-value">{{ number_format($d['activeCount']) }}</div><div class="sc-sub">in catalog</div></div>
    <div class="summary-card"><div class="sc-label">Total Requests</div><div class="sc-value">{{ number_format($d['totalUsage']) }}</div><div class="sc-sub">across work orders</div></div>
    <div class="summary-card"><div class="sc-label">List Value</div><div class="sc-value">${{ number_format($d['totalListValue'], 2) }}</div><div class="sc-sub">price × requests</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Service Catalog Usage <span class="sh-meta">most requested first</span></div>
    @if($d['rows']->isNotEmpty())
    <table>
        <thead>
            <tr><th>Service</th><th>Status</th><th class="num">Times Requested</th><th class="num">On Completed Orders</th><th class="num">List Price</th><th class="num">List Value</th></tr>
        </thead>
        <tbody>
            @foreach($d['rows'] as $row)
            <tr>
                <td>{{ $row['service']->name }}</td>
                <td>@if($row['service']->is_active)<span class="pill pill-green">Active</span>@else<span class="pill pill-gray">Inactive</span>@endif</td>
                <td class="num">{{ number_format($row['usage']) }}</td>
                <td class="num">{{ number_format($row['completed']) }}</td>
                <td class="num">{{ $row['price'] > 0 ? '$' . number_format($row['price'], 2) : '—' }}</td>
                <td class="num">${{ number_format($row['listValue'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Totals</td>
                <td class="num">{{ number_format($d['totalUsage']) }}</td>
                <td class="num">{{ number_format($d['rows']->sum('completed')) }}</td>
                <td class="num">—</td>
                <td class="num">${{ number_format($d['totalListValue'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size:.7rem;color:#94a3b8;margin-top:.4rem;">List Value is the catalog default price multiplied by request count — an estimate, since invoice line items are entered free-text and not linked to specific services.</p>
    @else
    <div class="empty-state">No services have been requested in this period.</div>
    @endif
</div>
@endsection
