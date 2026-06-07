<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $num }} — {{ $company['name'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 1.5rem;
        }

        .page {
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            padding: 1.75rem 2.25rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            border-radius: 6px;
        }

        /* ── Print/Close bar ── */
        .print-bar {
            display: flex;
            justify-content: flex-end;
            gap: .6rem;
            max-width: 800px;
            margin: 0 auto .75rem;
        }
        .print-bar button {
            padding: .35rem .9rem;
            border-radius: 6px;
            font-size: .82rem;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-print { background: #1A3C5E; color: #fff; border: none; font-weight: 600; }
        .btn-close  { background: #fff; color: #374151; border: 1px solid #d1d5db; }

        /* ── Page header ── */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #1A3C5E;
        }
        .header-left img { height: 156px; display: block; margin-bottom: .4rem; }
        .company-meta { font-size: .75rem; color: #64748b; line-height: 1.6; }
        .header-right { text-align: right; align-self: center; }
        .inv-word { font-size: 1.6rem; font-weight: 800; color: #1A3C5E; letter-spacing: .05em; }
        .inv-num  { font-size: .95rem; font-weight: 600; color: #2E86C1; margin-top: .1rem; }
        .paid-badge {
            display: inline-block;
            margin-top: .4rem;
            background: #d1fae5;
            color: #065f46;
            border: 1.5px solid #6ee7b7;
            border-radius: 999px;
            padding: .15rem .75rem;
            font-size: .75rem;
            font-weight: 700;
        }

        /* ── Section headings ── */
        .section-heading {
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #2E86C1;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: .3rem;
            margin-bottom: .65rem;
        }

        /* ── Compact meta row (inline key: value pairs) ── */
        .compact-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .2rem 1.5rem;
            font-size: .8rem;
            margin-bottom: .75rem;
            padding: .5rem .65rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }
        .compact-meta .cm-item { white-space: nowrap; }
        .compact-meta .cm-label { color: #94a3b8; font-weight: 600; margin-right: .2rem; }
        .compact-meta .cm-value { color: #1e293b; }

        /* ── Work Order Details ── */
        .wo-section { margin-bottom: 1.25rem; }

        .wo-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        .desc-block {
            font-size: .82rem;
            color: #374151;
            line-height: 1.55;
            white-space: pre-wrap;
        }

        .equipment-block {
            background: #f8fafc;
            border-left: 3px solid #1A3C5E;
            padding: .45rem .7rem;
            border-radius: 0 4px 4px 0;
            margin-top: .5rem;
        }
        .equipment-block .eq-label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #1A3C5E; margin-bottom: .2rem; }
        .equipment-block .eq-body  { font-size: .8rem; color: #374151; white-space: pre-wrap; }

        .right-col { display: flex; flex-direction: column; gap: .5rem; }

        .services-label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: .25rem; }
        .services-row { display: flex; flex-wrap: wrap; gap: .25rem; }
        .svc-pill {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: .15rem .55rem;
            font-size: .73rem;
            font-weight: 600;
        }

        .sub-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: .45rem .65rem;
            font-size: .8rem;
        }
        .sub-card .sc-label  { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-bottom: .2rem; }
        .sub-card .sc-name   { font-weight: 600; color: #1e293b; }
        .sub-card .sc-detail { color: #475569; margin-top: .1rem; }

        /* ── Invoice Details ── */
        .inv-section { }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
            margin-bottom: 1rem;
        }
        thead tr { background: #1A3C5E; color: #fff; }
        thead th { padding: .4rem .75rem; font-weight: 600; }
        thead th:first-child { text-align: left; }
        thead th:not(:first-child) { text-align: right; white-space: nowrap; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child { border-bottom: 2px solid #e2e8f0; }
        tbody td { padding: .35rem .75rem; }
        tbody td:not(:first-child) { text-align: right; }

        /* Totals + payment terms side by side */
        .inv-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }
        .payment-block { flex: 1; font-size: .8rem; color: #475569; padding-top: .2rem; }
        .payment-block strong { color: #1e293b; display: block; margin-bottom: .15rem; }
        .footer-note-text { font-size: .78rem; color: #64748b; margin-top: .5rem; font-style: italic; }

        .totals-inner { width: 220px; font-size: .82rem; flex-shrink: 0; }
        .totals-row   { display: flex; justify-content: space-between; padding: .2rem 0; color: #64748b; }
        .totals-total {
            display: flex; justify-content: space-between;
            padding: .4rem 0;
            border-top: 2px solid #1A3C5E;
            margin-top: .2rem;
            font-size: .95rem;
            font-weight: 700;
            color: #1A3C5E;
        }

        /* ── Print styles ── */
        @media print {
            body { background: #fff; padding: 0; }
            .print-bar { display: none; }
            .page { box-shadow: none; border-radius: 0; padding: .8cm 1.2cm; max-width: none; margin: 0; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
    <button class="btn-close"  onclick="window.close()">Close</button>
</div>

<div class="page">

    {{-- ── Page header ── --}}
    <div class="page-header">
        <div class="header-left">
            <img src="{{ route('site.logo') }}" alt="{{ $company['name'] }}">
            <div class="company-meta">
                @if($company['address'])<div>{{ $company['address'] }}</div>@endif
                @if($company['phone'])<div>{{ $company['phone'] }}</div>@endif
                @if($company['email'])<div>{{ $company['email'] }}</div>@endif
            </div>
        </div>
        <div class="header-right">
            <div class="inv-word">INVOICE</div>
            <div class="inv-num">{{ $num }}</div>
            <div><span class="paid-badge">✓ Paid</span></div>
        </div>
    </div>

    {{-- ── Work Order Details ── --}}
    @php $wo = $invoice->workOrder; @endphp
    <div class="wo-section">
        <div class="section-heading">Work Order Details — {{ $wo->woLabel() }}</div>

        {{-- Compact single-row meta ── --}}
        <div class="compact-meta">
            <span class="cm-item"><span class="cm-label">Customer:</span><span class="cm-value">{{ $wo->customer->name }}</span></span>
            <span class="cm-item"><span class="cm-label">Urgency:</span><span class="cm-value">{{ ucfirst($wo->urgency) }}</span></span>
            @if($wo->scheduled_at)
            <span class="cm-item"><span class="cm-label">Service Date:</span><span class="cm-value">{{ $wo->scheduled_at->format('M j, Y') }}</span></span>
            @endif
            @if($wo->preferred_date)
            <span class="cm-item"><span class="cm-label">Requested:</span><span class="cm-value">{{ $wo->preferred_date->format('M j, Y') }}</span></span>
            @endif
            @if($wo->site_street)
            <span class="cm-item"><span class="cm-label">Site:</span><span class="cm-value">{{ $wo->site_street }}</span></span>
            @endif
        </div>

        {{-- Two-column body ── --}}
        <div class="wo-body">
            {{-- Left: description + equipment ── --}}
            <div>
                @if($wo->description)
                <div class="desc-block">{{ $wo->description }}</div>
                @endif
                @if($wo->equipment_details)
                <div class="equipment-block">
                    <div class="eq-label">Equipment Details</div>
                    <div class="eq-body">{{ $wo->equipment_details }}</div>
                </div>
                @endif
            </div>
            {{-- Right: services + site contact ── --}}
            <div class="right-col">
                @if($wo->serviceTypes->count())
                <div>
                    <div class="services-label">Services Performed</div>
                    <div class="services-row">
                        @foreach($wo->serviceTypes as $svc)
                        <span class="svc-pill">{{ $svc->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($wo->site_contact_name || $wo->site_contact_phone)
                <div class="sub-card">
                    <div class="sc-label">Site Contact</div>
                    @if($wo->site_contact_name)<div class="sc-name">{{ $wo->site_contact_name }}</div>@endif
                    @if($wo->site_contact_phone)<div class="sc-detail">{{ $wo->site_contact_phone }}</div>@endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Invoice Details ── --}}
    <div class="inv-section">
        <div class="section-heading">Invoice Details</div>

        {{-- 2-column / 2-row meta grid ── --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.3rem .75rem;font-size:.8rem;margin-bottom:.75rem;padding:.5rem .65rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;">
            <div><span class="cm-label">Bill To:</span> <span class="cm-value">{{ $wo->customer->name }}@if($wo->customer->email) · {{ $wo->customer->email }}@endif</span></div>
            <div><span class="cm-label">Invoice Date:</span> <span class="cm-value">{{ $invoice->created_at->format('M j, Y') }}</span></div>
            @if($invoice->due_date)
            <div><span class="cm-label">Due:</span> <span class="cm-value">{{ $invoice->due_date->format('M j, Y') }}</span></div>
            @endif
            @if($completedAt)
            <div><span class="cm-label">Paid On:</span> <span class="cm-value">{{ \Carbon\Carbon::parse($completedAt)->format('M j, Y') }}</span></div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems->sortBy('sort_order') as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
                <tr><td colspan="4" style="padding:.6rem 0;border-bottom:none;"></td></tr>
            </tbody>
        </table>

        {{-- Totals + payment terms / footer side by side ── --}}
        <div class="inv-footer">
            <div class="payment-block">
                @if($invoice->payment_terms)
                <strong>Payment Terms</strong>{{ $invoice->payment_terms }}
                @endif
                @if($invoice->footer_note)
                <div class="footer-note-text">{{ $invoice->footer_note }}</div>
                @endif
                @if($invoice->transaction_reference)
                <div style="margin-top:.5rem;font-size:.82rem;color:#374151;">
                    <strong>Confirmation #:</strong> <span style="font-family:monospace;">{{ $invoice->transaction_reference }}</span>
                </div>
                @endif
            </div>
            <div class="totals-inner">
                <div class="totals-row"><span>Subtotal</span><span>${{ number_format($subTotal, 2) }}</span></div>
                @if($taxAmt > 0)
                <div class="totals-row">
                    <span>Tax ({{ number_format((float)($invoice->tax_rate ?? 0) * 100, 2) }}%)</span>
                    <span>${{ number_format($taxAmt, 2) }}</span>
                </div>
                @endif
                <div class="totals-total"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>
            </div>
        </div>
    </div>

</div>
</body>
</html>
