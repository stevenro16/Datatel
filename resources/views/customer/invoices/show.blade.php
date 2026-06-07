@extends('layouts.portal')
@section('title', 'INV-'.str_pad($invoice->id,4,'0',STR_PAD_LEFT))

@section('content')
@php
    use App\Models\Invoice;
    $num     = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
    $isPaid  = in_array($invoice->status, [Invoice::STATUS_PAYMENT_RECEIVED, Invoice::STATUS_COMPLETED]);
    $subTotal = (float)($invoice->subtotal  ?? $invoice->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
    $taxAmt   = (float)($invoice->tax_amount ?? round($subTotal * (float)($invoice->tax_rate ?? 0), 2));
    $total    = (float)($invoice->total     ?? round($subTotal + $taxAmt, 2));
    $bg    = match($invoice->status) {
        'issued'           => '#dbeafe', 'payment_received' => '#fef3c7',
        'completed'        => '#d1fae5', 'canceled'         => '#fee2e2',
        default            => '#f3f4f6',
    };
    $badgeColor = match($invoice->status) {
        'issued'           => '#1e40af', 'payment_received' => '#92400e',
        'completed'        => '#065f46', 'canceled'         => '#991b1b',
        default            => '#6b7280',
    };
    $statusLabel = match($invoice->status) {
        'issued'           => 'Invoice Ready',
        'payment_received' => 'Payment Submitted',
        'completed'        => 'Completed',
        'canceled'         => 'Canceled',
        default            => 'Preparing',
    };
@endphp

<div style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
        <a href="{{ route('portal.invoices.index') }}" style="color:var(--accent);text-decoration:none;font-size:.9rem;">← My Invoices</a>
        <a href="{{ route('portal.work-orders.show', $invoice->work_order_id) }}"
           style="font-size:.85rem;color:var(--accent);text-decoration:none;">
            ← {{ $invoice->workOrder?->woLabel() ?? 'Work Order' }}
        </a>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <h1 style="margin:0;font-size:2rem;font-weight:800;color:var(--primary);letter-spacing:-.01em;">{{ $num }}</h1>
        <span style="font-size:.95rem;padding:.35rem 1rem;border-radius:999px;font-weight:700;background:{{ $bg }};color:{{ $badgeColor }};border:1.5px solid {{ $badgeColor }};">{{ $statusLabel }}</span>
        <a href="{{ route('portal.invoices.print', $invoice) }}" target="_blank"
           style="margin-left:auto;padding:.4rem .9rem;border:1px solid #d1d5db;border-radius:6px;background:#f8f9fa;color:#374151;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
            🖨 Print Invoice
        </a>
    </div>
</div>

{{-- Payment received / completed banners --}}
@if($invoice->status === Invoice::STATUS_PAYMENT_RECEIVED)
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;">
    <span style="font-size:1.3rem;">⏳</span>
    <div>
        <div style="font-weight:700;color:#78350f;font-size:.95rem;">Payment Submitted — Awaiting Verification</div>
        <div style="font-size:.85rem;color:#92400e;margin-top:.1rem;">We have received your payment confirmation and will mark your order complete shortly.</div>
    </div>
</div>
@elseif($invoice->status === Invoice::STATUS_COMPLETED)
<div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.75rem;">
    <span style="font-size:1.3rem;">✅</span>
    <div>
        <div style="font-weight:700;color:#166534;font-size:.95rem;">Payment Complete — Thank You!</div>
        <div style="font-size:.85rem;color:#15803d;margin-top:.1rem;">This invoice has been fully paid and closed.</div>
        @if($invoice->transaction_reference)
        <div style="font-size:.85rem;color:#166534;margin-top:.35rem;">
            Confirmation #: <span style="font-family:monospace;font-weight:700;">{{ $invoice->transaction_reference }}</span>
        </div>
        @endif
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;max-width:900px;">

    {{-- Main invoice card --}}
    <div style="background:#fff;padding:2rem;border-radius:8px;border:1px solid #c9d0d8;box-shadow:0 1px 4px rgba(0,0,0,.07);">

        {{-- Header meta --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem;font-size:.9rem;margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
            <div><strong>Invoice #:</strong> {{ $num }}</div>
            <div><strong>Work Order:</strong> {{ $invoice->workOrder?->woLabel() ?? '—' }}</div>
            <div><strong>Invoice Date:</strong> {{ $invoice->created_at->format('M j, Y') }}</div>
            <div>
                <strong>Due Date:</strong>
                @if($invoice->due_date)
                    @php $due = \Carbon\Carbon::parse($invoice->due_date); @endphp
                    <span style="{{ $due->isPast() && !$isPaid ? 'color:#dc2626;font-weight:600;' : '' }}">
                        {{ $due->format('M j, Y') }}{{ $due->isPast() && !$isPaid ? ' (overdue)' : '' }}
                    </span>
                @else
                    —
                @endif
            </div>
            @if($invoice->payment_terms)
            <div style="grid-column:1/-1;"><strong>Payment Terms:</strong> {{ $invoice->payment_terms }}</div>
            @endif
        </div>

        {{-- Line items --}}
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;margin-bottom:1.5rem;">
            <thead>
                <tr style="background:var(--primary);color:#fff;">
                    <th style="padding:.6rem 1rem;text-align:left;">Description</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Qty</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Unit Price</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems->sortBy('sort_order') as $item)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:.65rem 1rem;">{{ $item->description }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:1.75rem;">
            <div style="width:240px;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                    <span>Subtotal</span><span>${{ number_format($subTotal, 2) }}</span>
                </div>
                @if($taxAmt > 0)
                <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                    <span>Tax ({{ number_format((float)($invoice->tax_rate ?? 0) * 100, 2) }}%)</span>
                    <span>${{ number_format($taxAmt, 2) }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-size:1.05rem;font-weight:700;padding:.6rem 0;border-top:2px solid #e5e7eb;margin-top:.25rem;color:var(--primary);">
                    <span>Total Due</span><span>${{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer note --}}
        @if($invoice->footer_note)
        <div style="padding:.85rem 1rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.87rem;color:#555;">
            {{ $invoice->footer_note }}
        </div>
        @endif

        {{-- Visits covered --}}
        @php
            $coveredIds    = $invoice->covered_visit_ids ?? [];
            $allVisits     = $invoice->workOrder?->visits ?? collect();
            $coveredVisits = $coveredIds
                ? $allVisits->whereIn('id', $coveredIds)->sortBy('scheduled_at')->values()
                : collect();
        @endphp
        @if($coveredVisits->isNotEmpty())
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #e5e7eb;">
            <div style="font-size:.82rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.65rem;">Visits Covered by This Invoice</div>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($coveredVisits as $cv)
                @php $cvSig = $cv->signature; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.55rem .85rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.87rem;">
                    <div>
                        <span style="font-weight:600;color:#1e293b;">{{ $cv->scheduled_at->format('l, F j, Y') }}</span>
                        <span style="color:#6b7280;margin-left:.5rem;">{{ $cv->scheduled_at->format('g:i A') }}</span>
                        @if($cv->duration_estimate_minutes)
                        <span style="color:#9ca3af;margin-left:.35rem;">&mdash; {{ $cv->duration_estimate_minutes >= 60 ? floor($cv->duration_estimate_minutes/60).'h'.($cv->duration_estimate_minutes%60 ? ' '.($cv->duration_estimate_minutes%60).'m' : '') : $cv->duration_estimate_minutes.'m' }}</span>
                        @endif
                    </div>
                    @if($cvSig)
                    <span style="flex-shrink:0;font-size:.78rem;padding:.2rem .6rem;border-radius:999px;background:#d1fae5;color:#065f46;font-weight:600;border:1px solid #6ee7b7;">✓ Signed</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Payment action --}}
        @if($invoice->status === Invoice::STATUS_ISSUED)
        <div style="background:#fff;border:2px solid var(--accent);padding:1.25rem;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.1);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 .65rem;">Payment Due</h3>
            <div style="font-size:1.75rem;font-weight:800;color:var(--primary);margin-bottom:.5rem;">${{ number_format($total, 2) }}</div>
            @if($invoice->due_date)
            @php $due = \Carbon\Carbon::parse($invoice->due_date); @endphp
            <div style="font-size:.83rem;color:{{ $due->isPast() ? '#dc2626' : '#6b7280' }};margin-bottom:1rem;">
                Due {{ $due->format('M j, Y') }}{{ $due->isPast() ? ' — Overdue' : '' }}
            </div>
            @endif
            <p style="font-size:.85rem;color:#555;margin-bottom:1rem;line-height:1.5;">
                Please submit payment using the terms on this invoice, then click below to notify us.
            </p>
            <form method="POST" action="{{ route('portal.invoices.submit-payment', $invoice) }}">
                @csrf
                <button type="submit"
                        style="width:100%;padding:.7rem;background:var(--accent);color:#fff;border:none;border-radius:7px;font-size:.93rem;font-weight:700;cursor:pointer;">
                    ✓ I've Submitted My Payment
                </button>
            </form>
        </div>
        @endif

        {{-- Invoice summary card --}}
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #c9d0d8;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <h3 style="font-size:.95rem;color:var(--primary);margin-bottom:.75rem;">Summary</h3>
            <div style="font-size:.85rem;color:#555;display:flex;flex-direction:column;gap:.4rem;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Invoice</span>
                    <span style="font-weight:600;">{{ $num }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Status</span>
                    <span style="font-weight:600;color:{{ $badgeColor }};">{{ $statusLabel }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Issued</span>
                    <span>{{ $invoice->created_at->format('M j, Y') }}</span>
                </div>
                @if($invoice->due_date)
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Due</span>
                    <span>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M j, Y') }}</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;padding-top:.4rem;border-top:1px solid #f0f0f0;margin-top:.2rem;">
                    <span style="color:#888;">Total</span>
                    <span style="font-weight:700;font-size:.95rem;color:var(--primary);">${{ number_format($total, 2) }}</span>
                </div>
                @if($invoice->transaction_reference)
                <div style="display:flex;justify-content:space-between;padding-top:.4rem;border-top:1px solid #f0f0f0;margin-top:.2rem;">
                    <span style="color:#888;">Confirmation #</span>
                    <span style="font-weight:600;font-family:monospace;font-size:.83rem;">{{ $invoice->transaction_reference }}</span>
                </div>
                @endif
            </div>
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f0f0f0;">
                <a href="{{ route('portal.work-orders.show', $invoice->work_order_id) }}"
                   style="display:block;text-align:center;padding:.45rem;border:1px solid #d1d5db;border-radius:6px;color:#555;font-size:.83rem;text-decoration:none;background:#f8f9fa;">
                    View Work Order
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
