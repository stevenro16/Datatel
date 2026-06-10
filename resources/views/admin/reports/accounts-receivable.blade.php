@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Outstanding</div><div class="sc-value">${{ number_format($d['totalOwed'], 2) }}</div><div class="sc-sub">{{ $d['count'] }} invoice(s)</div></div>
    <div class="summary-card" style="border-left-color:#dc2626;"><div class="sc-label">Past Due</div><div class="sc-value">${{ number_format($d['pastDueOwed'], 2) }}</div><div class="sc-sub">beyond due date</div></div>
    <div class="summary-card"><div class="sc-label">Current</div><div class="sc-value">${{ number_format($d['totalOwed'] - $d['pastDueOwed'], 2) }}</div><div class="sc-sub">not yet due</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Aging Summary</div>
    <table>
        <thead><tr><th>Aging Bucket</th><th class="num">Invoices</th><th class="num">Amount</th><th class="num">% of Total</th></tr></thead>
        <tbody>
            @foreach($d['buckets'] as $label => $b)
            <tr>
                <td>{{ $label }}</td>
                <td class="num">{{ number_format($b['count']) }}</td>
                <td class="num">${{ number_format($b['total'], 2) }}</td>
                <td class="num">{{ $d['totalOwed'] ? number_format($b['total'] / $d['totalOwed'] * 100, 1) : '0.0' }}%</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td>Total Outstanding</td><td class="num">{{ number_format($d['count']) }}</td><td class="num">${{ number_format($d['totalOwed'], 2) }}</td><td class="num">100.0%</td></tr>
        </tfoot>
    </table>
</div>

<div class="report-section">
    <div class="section-heading">Outstanding Invoices <span class="sh-meta">oldest first</span></div>
    @if($d['rows']->isNotEmpty())
    <table>
        <thead>
            <tr><th>Invoice #</th><th>Customer</th><th>Due</th><th class="num">Days Past Due</th><th>Bucket</th><th>Status</th><th class="num">Amount</th></tr>
        </thead>
        <tbody>
            @foreach($d['rows'] as $row)
            @php $inv = $row['invoice']; @endphp
            <tr>
                <td>INV-{{ str_pad($inv->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $inv->workOrder?->customer?->name ?? '—' }}</td>
                <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('M j, Y') : '—' }}</td>
                <td class="num">
                    {{ $row['pastDue'] > 0 ? $row['pastDue'] : '—' }}
                    @if($row['pastDue'] > 90)<span class="pill pill-red">90+</span>@elseif($row['pastDue'] > 0)<span class="pill pill-amber">past due</span>@endif
                </td>
                <td>{{ $row['bucket'] }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $inv->status)) }}</td>
                <td class="num">${{ number_format($row['amount'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="6">Total Outstanding</td><td class="num">${{ number_format($d['totalOwed'], 2) }}</td></tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">No outstanding invoices. Accounts receivable is clear.</div>
    @endif
</div>
@endsection
