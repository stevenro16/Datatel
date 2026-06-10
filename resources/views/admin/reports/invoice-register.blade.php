@extends('admin.reports.layout')

@section('body')
@php
    $d = $data;
    $statusPill = fn ($s) => match ($s) {
        'Issued'           => 'pill-amber',
        'Payment Received' => 'pill-blue',
        'Completed'        => 'pill-green',
        'Canceled'         => 'pill-red',
        default            => 'pill-gray',
    };
@endphp

<div class="summary-grid">
    <div class="summary-card"><div class="sc-label">Invoices</div><div class="sc-value">{{ number_format($d['count']) }}</div><div class="sc-sub">raised in period</div></div>
    <div class="summary-card"><div class="sc-label">Subtotal</div><div class="sc-value">${{ number_format($d['subtotal'], 2) }}</div></div>
    <div class="summary-card"><div class="sc-label">Tax</div><div class="sc-value">${{ number_format($d['taxTotal'], 2) }}</div></div>
    <div class="summary-card"><div class="sc-label">Grand Total</div><div class="sc-value">${{ number_format($d['grandTotal'], 2) }}</div></div>
</div>

<div class="report-section">
    <div class="section-heading">Totals by Status</div>
    @if(count($d['byStatus']))
    <table>
        <thead><tr><th>Status</th><th class="num">Count</th><th class="num">Total</th></tr></thead>
        <tbody>
            @foreach($d['byStatus'] as $label => $row)
            <tr>
                <td><span class="pill {{ $statusPill($label) }}">{{ $label }}</span></td>
                <td class="num">{{ number_format($row['count']) }}</td>
                <td class="num">${{ number_format($row['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No invoices in this period.</div>
    @endif
</div>

<div class="report-section">
    <div class="section-heading">Invoice Detail <span class="sh-meta">{{ number_format($d['count']) }} invoice(s)</span></div>
    @if($d['invoices']->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>Invoice #</th><th>Customer</th><th>Created</th><th>Due</th><th>Status</th>
                <th class="num">Subtotal</th><th class="num">Tax</th><th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($d['invoices'] as $inv)
            <tr>
                <td>INV-{{ str_pad($inv->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $inv->workOrder?->customer?->name ?? '—' }}</td>
                <td>{{ $inv->created_at->format('M j, Y') }}</td>
                <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('M j, Y') : '—' }}</td>
                <td><span class="pill {{ $statusPill(ucwords(str_replace('_', ' ', $inv->status))) }}">{{ ucwords(str_replace('_', ' ', $inv->status)) }}</span></td>
                <td class="num">${{ number_format((float) $inv->subtotal, 2) }}</td>
                <td class="num">${{ number_format((float) $inv->tax_amount, 2) }}</td>
                <td class="num">${{ number_format((float) $inv->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Totals</td>
                <td class="num">${{ number_format($d['subtotal'], 2) }}</td>
                <td class="num">${{ number_format($d['taxTotal'], 2) }}</td>
                <td class="num">${{ number_format($d['grandTotal'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="empty-state">No invoices were raised in this period.</div>
    @endif
</div>
@endsection
