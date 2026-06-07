@extends('layouts.portal')
@section('title', 'My Invoices')

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="margin:0 0 .2rem;font-size:1.75rem;font-weight:700;color:var(--primary);">My Invoices</h1>
    <p style="margin:0;font-size:.85rem;color:#6b7280;font-weight:500;">View and pay your invoices</p>
</div>

{{-- Filter pills --}}
<div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
    @foreach(['active' => 'Active', 'completed' => 'Completed', 'all' => 'All'] as $v => $lbl)
    <a href="{{ route('portal.invoices.index', ['view' => $v]) }}"
       style="padding:.35rem 1.1rem;border-radius:999px;font-size:.85rem;font-weight:600;text-decoration:none;
              border:2px solid var(--accent);
              background:{{ $view === $v ? 'var(--accent)' : 'transparent' }};
              color:{{ $view === $v ? '#fff' : 'var(--accent)' }};">
        {{ $lbl }}
    </a>
    @endforeach
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Work Order</th>
            <th>Status</th>
            <th>Total</th>
            <th>Due Date</th>
            <th>Issued</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $inv)
        @php
            $invNum = 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT);
            $bg    = match($inv->status) {
                'issued'           => '#dbeafe', 'payment_received' => '#fef3c7',
                'completed'        => '#d1fae5', 'canceled'         => '#fee2e2',
                default            => '#f3f4f6',
            };
            $color = match($inv->status) {
                'issued'           => '#1e40af', 'payment_received' => '#92400e',
                'completed'        => '#065f46', 'canceled'         => '#991b1b',
                default            => '#6b7280',
            };
            $label = match($inv->status) {
                'issued'           => 'Invoice Ready',
                'payment_received' => 'Payment Submitted',
                'completed'        => 'Completed',
                'canceled'         => 'Canceled',
                default            => 'Preparing',
            };
        @endphp
        <tr data-href="{{ route('portal.invoices.show', $inv) }}">
            <td style="font-weight:600;">{{ $invNum }}</td>
            <td style="font-size:.88rem;color:#555;">{{ $inv->workOrder?->woLabel() ?? '—' }}</td>
            <td><span class="badge" style="background:{{ $bg }};color:{{ $color }};">{{ $label }}</span></td>
            <td style="font-weight:600;font-size:.93rem;">${{ $inv->total > 0 ? number_format($inv->total, 2) : '—' }}</td>
            <td style="font-size:.85rem;color:#666;">
                @if($inv->due_date)
                    @php $due = \Carbon\Carbon::parse($inv->due_date); @endphp
                    <span style="{{ $due->isPast() && !in_array($inv->status, ['completed','canceled']) ? 'color:#dc2626;font-weight:600;' : '' }}">
                        {{ $due->format('M j, Y') }}
                    </span>
                @else
                    —
                @endif
            </td>
            <td style="font-size:.82rem;color:#888;">{{ $inv->created_at->format('M j, Y') }}</td>
            <td>
                <a href="{{ route('portal.invoices.show', $inv) }}" class="btn btn-secondary btn-sm">View</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;color:#999;padding:2.5rem;">
                No invoices yet.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<div style="margin-top:1rem;">{{ $invoices->links() }}</div>
@endsection
