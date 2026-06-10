@extends('admin.reports.layout')

@section('body')
@php $d = $data; @endphp

@if($d['single'])
<div style="font-size:1rem;font-weight:700;color:#1A3C5E;margin-bottom:.75rem;">
    {{ $d['single']->name }}
    @if($d['single']->email)<span style="font-weight:400;color:#64748b;font-size:.85rem;"> · {{ $d['single']->email }}</span>@endif
</div>
@endif

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Invoiced</div><div class="sc-value">${{ number_format($d['totalInvoiced'], 2) }}</div><div class="sc-sub">in period</div></div>
    <div class="summary-card"><div class="sc-label">Paid</div><div class="sc-value">${{ number_format($d['totalPaid'], 2) }}</div><div class="sc-sub">in period</div></div>
    <div class="summary-card" style="border-left-color:#dc2626;"><div class="sc-label">Outstanding Balance</div><div class="sc-value">${{ number_format($d['totalOutstanding'], 2) }}</div><div class="sc-sub">currently owed</div></div>
</div>

<div class="report-section">
    <div class="section-heading">
        @if($d['single']) Account Activity @else Customer Statements @endif
        <span class="sh-meta">{{ $d['rows']->count() }} customer(s)</span>
    </div>
    @if($d['rows']->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Customer</th><th>Company</th><th class="num">Work Orders</th><th class="num">Completed</th>
                <th class="num">Invoiced</th><th class="num">Paid</th><th class="num">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d['rows'] as $row)
            <tr>
                <td>{{ $row['customer']->name }}</td>
                <td>{{ $row['company']?->name ?? '—' }}</td>
                <td class="num">{{ number_format($row['woCount']) }}</td>
                <td class="num">{{ number_format($row['completed']) }}</td>
                <td class="num">${{ number_format($row['invoiced'], 2) }}</td>
                <td class="num">${{ number_format($row['paid'], 2) }}</td>
                <td class="num">${{ number_format($row['outstanding'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">Totals</td>
                <td class="num">${{ number_format($d['totalInvoiced'], 2) }}</td>
                <td class="num">${{ number_format($d['totalPaid'], 2) }}</td>
                <td class="num">${{ number_format($d['totalOutstanding'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size:.7rem;color:#94a3b8;margin-top:.4rem;">Invoiced and Paid reflect invoices dated within the selected period. Outstanding Balance is the current total of all unpaid (issued/payment-received) invoices, regardless of date.</p>
    @else
    <div class="empty-state">No activity or balances for the selected customer(s) in this period.</div>
    @endif
</div>
@endsection
