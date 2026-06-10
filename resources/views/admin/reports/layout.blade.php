<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }} — {{ $company['name'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 12.5px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 1.5rem;
        }

        .page {
            background: #fff;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 2.4rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
            border-radius: 6px;
        }

        /* ── Print/Close bar ── */
        .print-bar {
            display: flex;
            justify-content: flex-end;
            gap: .6rem;
            max-width: 1000px;
            margin: 0 auto .75rem;
        }
        .print-bar button {
            padding: .4rem 1rem;
            border-radius: 6px;
            font-size: .82rem;
            cursor: pointer;
            font-family: inherit;
        }
        .btn-print { background: #1A3C5E; color: #fff; border: none; font-weight: 600; }
        .btn-close { background: #fff; color: #374151; border: 1px solid #d1d5db; }

        /* ── Report header ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid #1A3C5E;
        }
        .report-header .hl img { height: 120px; display: block; margin-bottom: .4rem; }
        .company-meta { font-size: .74rem; color: #64748b; line-height: 1.6; }
        .report-header .hr { text-align: right; align-self: center; }
        .report-word { font-size: .68rem; font-weight: 700; letter-spacing: .12em; color: #94a3b8; text-transform: uppercase; }
        .report-title { font-size: 1.55rem; font-weight: 800; color: #1A3C5E; line-height: 1.1; }
        .report-range { font-size: .9rem; font-weight: 600; color: #2E86C1; margin-top: .15rem; }
        .report-generated { font-size: .72rem; color: #94a3b8; margin-top: .25rem; }

        /* ── Summary cards ── */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: .75rem;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2E86C1;
            border-radius: 6px;
            padding: .65rem .85rem;
        }
        .summary-card .sc-label { font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
        .summary-card .sc-value { font-size: 1.35rem; font-weight: 800; color: #1A3C5E; margin-top: .15rem; line-height: 1.1; }
        .summary-card .sc-sub   { font-size: .7rem; color: #64748b; margin-top: .1rem; }

        /* ── Section ── */
        .report-section { margin-bottom: 1.5rem; }
        .section-heading {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #1A3C5E;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: .35rem;
            margin-bottom: .65rem;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .section-heading .sh-meta { font-weight: 600; color: #64748b; font-size: .72rem; letter-spacing: 0; text-transform: none; }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; font-size: .8rem; margin-bottom: .5rem; }
        thead { display: table-header-group; }
        thead tr { background: #1A3C5E; color: #fff; }
        thead th { padding: .4rem .65rem; font-weight: 600; text-align: left; white-space: nowrap; }
        thead th.num { text-align: right; }
        tbody tr { border-bottom: 1px solid #eef2f7; page-break-inside: avoid; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: .35rem .65rem; vertical-align: top; }
        tbody td.num { text-align: right; white-space: nowrap; }
        tfoot tr { border-top: 2px solid #1A3C5E; font-weight: 700; color: #1A3C5E; background: #f1f5f9; }
        tfoot td { padding: .45rem .65rem; }
        tfoot td.num { text-align: right; white-space: nowrap; }

        .muted { color: #94a3b8; }
        .pill {
            display: inline-block; border-radius: 999px; padding: .08rem .5rem;
            font-size: .68rem; font-weight: 700; border: 1px solid;
        }
        .pill-amber  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .pill-red    { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .pill-green  { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .pill-blue   { background: #e0f2fe; color: #075985; border-color: #bae6fd; }
        .pill-gray   { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }

        .empty-state { padding: 1.5rem; text-align: center; color: #94a3b8; font-size: .85rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; }

        .group-title { font-size: .85rem; font-weight: 700; color: #1A3C5E; margin: 1rem 0 .4rem; page-break-after: avoid; }

        /* ── Print ── */
        @media print {
            @page { size: letter; margin: 1.3cm 1.2cm 1.6cm; }
            body { background: #fff; padding: 0; font-size: 11px; }
            .print-bar { display: none; }
            .page { box-shadow: none; border-radius: 0; padding: 0; max-width: none; margin: 0; }
            .report-header .hl img { height: 90px; }
            .report-section { page-break-inside: auto; }
            thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tbody tr:nth-child(even) { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .summary-card, .pill, tfoot tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-footer { position: fixed; bottom: 0; left: 0; right: 0; }
            .page-break { page-break-before: always; }
        }
        .print-footer {
            display: none;
            font-size: .62rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: .25rem;
            justify-content: space-between;
        }
        @media print { .print-footer { display: flex; } }
    </style>
</head>
<body>

<div class="print-bar">
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<div class="page">

    <div class="report-header">
        <div class="hl">
            <img src="{{ route('site.logo') }}" alt="{{ $company['name'] }}">
            <div class="company-meta">
                <div style="font-weight:700;color:#1e293b;">{{ $company['name'] }}</div>
                @if($company['address'])<div>{{ $company['address'] }}</div>@endif
                @if($company['phone'])<div>{{ $company['phone'] }}</div>@endif
                @if($company['email'])<div>{{ $company['email'] }}</div>@endif
            </div>
        </div>
        <div class="hr">
            <div class="report-word">Report</div>
            <div class="report-title">{{ $reportTitle }}</div>
            <div class="report-range">{{ $rangeLabel }}</div>
            <div class="report-generated">Generated {{ $generatedAt->format('M j, Y \a\t g:i A') }}</div>
        </div>
    </div>

    @yield('body')

    <div class="print-footer">
        <span>{{ $company['name'] }} — {{ $reportTitle }}</span>
        <span>{{ $rangeLabel }} · Generated {{ $generatedAt->format('n/j/Y g:i A') }}</span>
    </div>

</div>
</body>
</html>
