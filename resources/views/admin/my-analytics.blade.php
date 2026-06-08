@extends('layouts.admin')
@section('title', 'My Analytics')

@push('topbar-title')
    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--primary);flex-shrink:0;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        <div style="min-width:0;">
            <p style="font-size:.7rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.07em;margin:0;line-height:1;">Analytics</p>
            <h1 style="font-size:1.15rem;font-weight:800;color:var(--primary);margin:.15rem 0 0;line-height:1;">My Analytics</h1>
        </div>
    </div>
@endpush

@section('content')
@php
    $r          = $range['range'];
    $rangeLabel = $range['label'];
    $fromIso    = $range['from']->toDateString();
    $toIso      = $range['to']->toDateString();

    $chips = [
        '7d'     => 'Last 7 Days',
        '30d'    => 'Last 30 Days',
        'month'  => 'This Month',
        'ytd'    => 'Year to Date',
        'custom' => 'Custom',
    ];

    $kpiColors = [
        'active'      => 'var(--primary)',
        'scheduled'   => 'var(--accent)',
        'completed'   => 'var(--success)',
        'revenue'     => 'var(--success)',
        'outstanding' => 'var(--warning)',
        'pastdue'     => 'var(--danger)',
        'customers'   => 'var(--accent)',
        'companies'   => 'var(--primary)',
    ];

    $bucketLabel = ['day' => 'day', 'week' => 'week', 'month' => 'month'][$bucket] ?? 'day';
@endphp

<style>
.ma-chip-row { display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; }
.ma-chip {
    display:inline-flex; align-items:center; padding:.4rem .85rem;
    border:1.5px solid #d1d5db; border-radius:999px; background:#fff;
    color:#374151; font-size:.83rem; font-weight:600; text-decoration:none;
    transition:background .15s, border-color .15s, color .15s;
}
.ma-chip:hover { border-color:var(--accent); color:var(--accent); }
.ma-chip.active { background:var(--accent); border-color:var(--accent); color:#fff; }
.ma-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(185px,1fr)); gap:1rem; margin-bottom:1.25rem; }
.ma-chart-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1rem; margin-bottom:1.25rem; }
@media (max-width: 1100px) { .ma-chart-grid { grid-template-columns:1fr; } }
@media (max-width: 700px)  { .ma-kpi-grid { grid-template-columns:repeat(2, minmax(0,1fr)); } }

.ma-card { background:#fff; border-radius:8px; border:1px solid #d0d5dd; box-shadow:0 1px 4px rgba(0,0,0,.07); overflow:hidden; }
.ma-card-header {
    background:var(--primary); padding:.75rem 1.15rem;
    display:flex; align-items:center; gap:.55rem;
}
.ma-card-header svg { flex-shrink:0; stroke:rgba(255,255,255,.85); }
.ma-card-header .t { font-size:.92rem; font-weight:700; color:#fff; line-height:1.15; }
.ma-card-header .s { font-size:.68rem; color:rgba(255,255,255,.6); margin-top:.1rem; line-height:1.1; }
.ma-card-body { padding:.9rem 1.05rem 1.05rem; }
.ma-empty { padding:1.6rem; text-align:center; color:#9ca3af; font-size:.85rem; }

.ma-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.ma-table th { text-align:left; padding:.55rem .75rem; background:#f8fafc; color:#475569; font-weight:600; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid #e5e7eb; }
.ma-table td { padding:.6rem .75rem; border-bottom:1px solid #f1f5f9; color:#1e293b; }
.ma-table tr:last-child td { border-bottom:none; }
.ma-rank {
    display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px;
    border-radius:50%; background:#e0e7ff; color:#4338ca; font-size:.72rem; font-weight:700;
}
</style>

{{-- ── Range chips ─────────────────────────────────────────── --}}
<div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.05);padding:1rem 1.15rem;margin-bottom:1.25rem;">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.85rem;">
        <div class="ma-chip-row">
            @foreach($chips as $key => $label)
            <a href="{{ route('admin.my-analytics', ['range' => $key]) }}"
               class="ma-chip {{ $r === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div style="font-size:.85rem;color:#475569;">
            <span style="font-weight:700;color:var(--primary);">{{ $rangeLabel }}</span>
            <span style="color:#9ca3af;margin-left:.5rem;">{{ $range['from']->format('M j, Y') }} – {{ $range['to']->format('M j, Y') }}</span>
        </div>
    </div>
    @if($r === 'custom')
    <form method="GET" action="{{ route('admin.my-analytics') }}"
          style="margin-top:.85rem;display:flex;flex-wrap:wrap;gap:.6rem;align-items:flex-end;border-top:1px solid #f1f5f9;padding-top:.85rem;">
        <input type="hidden" name="range" value="custom">
        <div>
            <label style="display:block;font-size:.72rem;color:#6b7280;font-weight:600;margin-bottom:.25rem;">From</label>
            <input type="date" name="from" value="{{ $fromIso }}" required
                   style="padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;">
        </div>
        <div>
            <label style="display:block;font-size:.72rem;color:#6b7280;font-weight:600;margin-bottom:.25rem;">To</label>
            <input type="date" name="to" value="{{ $toIso }}" required
                   style="padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;">
        </div>
        <button type="submit"
                style="padding:.42rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.84rem;font-weight:600;cursor:pointer;">
            Apply
        </button>
    </form>
    @endif
</div>

{{-- ── KPI strip ───────────────────────────────────────────── --}}
<div class="ma-kpi-grid">
    @php
        $kpiCards = [
            ['label' => 'Active Work Orders',      'value' => number_format($kpis['activeWorkOrders']),    'color' => $kpiColors['active']],
            ['label' => 'Scheduled This Week',     'value' => number_format($kpis['scheduledThisWeek']),   'color' => $kpiColors['scheduled']],
            ['label' => 'Completed in Period',     'value' => number_format($kpis['completedInPeriod']),   'color' => $kpiColors['completed']],
            ['label' => 'Revenue (Period)',        'value' => '$' . number_format($kpis['revenueInPeriod'], 2),   'color' => $kpiColors['revenue']],
            ['label' => 'Outstanding',             'value' => '$' . number_format($kpis['outstandingRevenue'], 2),'color' => $kpiColors['outstanding']],
            ['label' => 'Past-Due',                'value' => '$' . number_format($kpis['pastDueRevenue'], 2),    'color' => $kpiColors['pastdue']],
            ['label' => 'New Customers',           'value' => number_format($kpis['newCustomers']),        'color' => $kpiColors['customers']],
            ['label' => 'New Companies',           'value' => number_format($kpis['newCompanies']),        'color' => $kpiColors['companies']],
        ];
    @endphp
    @foreach($kpiCards as $card)
    <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;border-top:4px solid {{ $card['color'] }};padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="font-size:1.7rem;font-weight:700;color:{{ $card['color'] }};line-height:1.15;">{{ $card['value'] }}</div>
        <div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;font-weight:500;">{{ $card['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Chart grid ───────────────────────────────────────────── --}}
<div class="ma-chart-grid">

    {{-- WOs created vs completed --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg>
            <div><div class="t">Work Orders — Created vs Completed</div><div class="s">per {{ $bucketLabel }}</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-workorders" style="min-height:280px;"></div></div>
    </div>

    {{-- WO status distribution --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="2" x2="12" y2="12"/><line x1="12" y1="12" x2="20" y2="16"/></svg>
            <div><div class="t">Work Order Status</div><div class="s">distribution in period</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-status" style="min-height:280px;"></div></div>
    </div>

    {{-- Pipeline funnel (snapshot, no date filter) --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h18l-7 9v6l-4 2v-8z"/></svg>
            <div><div class="t">Pipeline</div><div class="s">current snapshot (all active WOs)</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-pipeline" style="min-height:280px;"></div></div>
    </div>

    {{-- Urgency mix --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 L2 22 L22 22 Z"/><line x1="12" y1="9" x2="12" y2="14"/><circle cx="12" cy="18" r=".6" fill="currentColor"/></svg>
            <div><div class="t">Urgency Mix</div><div class="s">distribution in period</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-urgency" style="min-height:280px;"></div></div>
    </div>

    {{-- Revenue --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            <div><div class="t">Revenue</div><div class="s">per {{ $bucketLabel }}</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-revenue" style="min-height:280px;"></div></div>
    </div>

    {{-- Invoice status --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
            <div><div class="t">Invoice Status</div><div class="s">distribution in period</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-invoice-status" style="min-height:280px;"></div></div>
    </div>

    {{-- Top services --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <div><div class="t">Top Services</div><div class="s">by work order count in period</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-top-services" style="min-height:280px;"></div></div>
    </div>

    {{-- New customers trailing 12m --}}
    <div class="ma-card">
        <div class="ma-card-header">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            <div><div class="t">New Customers</div><div class="s">trailing 12 months</div></div>
        </div>
        <div class="ma-card-body"><div id="chart-new-customers" style="min-height:280px;"></div></div>
    </div>

</div>

{{-- ── Employee performance ─────────────────────────────────── --}}
<div class="ma-card" style="margin-bottom:1.25rem;">
    <div class="ma-card-header">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        <div><div class="t">Employee Performance</div><div class="s">work orders completed in period</div></div>
    </div>
    <div class="ma-card-body">
        <div id="chart-employee" style="min-height:240px;margin-bottom:.5rem;"></div>

        @if(count($leaderboard) > 0)
        <table class="ma-table" style="margin-top:1rem;">
            <thead>
                <tr>
                    <th style="width:42px;">#</th>
                    <th>Technician</th>
                    <th style="text-align:right;">Completed</th>
                    <th style="text-align:right;">Avg Days</th>
                    <th style="text-align:right;">Last Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaderboard as $i => $row)
                <tr>
                    <td><span class="ma-rank">{{ $i + 1 }}</span></td>
                    <td style="font-weight:600;color:var(--primary);">{{ $row['name'] }}</td>
                    <td style="text-align:right;font-weight:700;">{{ number_format($row['completed']) }}</td>
                    <td style="text-align:right;color:#475569;">{{ $row['avg_days'] !== null ? $row['avg_days'] . ' d' : '—' }}</td>
                    <td style="text-align:right;color:#9ca3af;">{{ $row['last_completed_at']?->format('M j, Y') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="ma-empty">No completed work orders by technicians in this period.</div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
<script>
(function () {
    const data = @json($chartData);
    const PALETTE = ['#2E86C1','#1E8449','#D68910','#C0392B','#1A3C5E','#7D3C98','#117A65'];
    const fmtUSD = v => '$' + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
    const charts = [];

    function isDark() { return document.documentElement.classList.contains('dark'); }
    function themed(opts) {
        opts.theme = Object.assign({ palette: undefined }, opts.theme || {}, { mode: isDark() ? 'dark' : 'light' });
        opts.colors = opts.colors || PALETTE;
        opts.chart  = Object.assign({ background: 'transparent', toolbar: { show: false }, fontFamily: 'inherit' }, opts.chart || {});
        opts.grid   = Object.assign({ borderColor: isDark() ? '#334155' : '#e5e7eb' }, opts.grid || {});
        return opts;
    }
    function isEmpty(seriesArr) {
        if (!seriesArr || seriesArr.length === 0) return true;
        // For donut/funnel-style: array of numbers
        if (typeof seriesArr[0] === 'number') return seriesArr.every(v => !v);
        // For line/bar: array of objects with .data
        return seriesArr.every(s => !s.data || s.data.length === 0 || s.data.every(v => !v));
    }
    function renderEmpty(elId, msg) {
        const el = document.getElementById(elId);
        if (el) el.innerHTML = '<div class="ma-empty">' + (msg || 'No data for this period.') + '</div>';
    }
    function mount(elId, opts) {
        const el = document.getElementById(elId);
        if (!el) return;
        const chart = new ApexCharts(el, themed(opts));
        chart.render();
        charts.push(chart);
    }

    // ── 1. WOs created vs completed (line) ──────────────────────────
    if (isEmpty(data.workOrders.series)) {
        renderEmpty('chart-workorders');
    } else {
        mount('chart-workorders', {
            chart: { type: 'line', height: 280 },
            series: data.workOrders.series,
            xaxis: { categories: data.workOrders.categories },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            markers: { size: 3 },
            legend: { position: 'top', horizontalAlign: 'left' },
            colors: ['#2E86C1', '#1E8449'],
        });
    }

    // ── 2. WO status (donut) ────────────────────────────────────────
    if (isEmpty(data.status.series)) {
        renderEmpty('chart-status');
    } else {
        mount('chart-status', {
            chart: { type: 'donut', height: 280 },
            series: data.status.series,
            labels: data.status.labels,
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: true, formatter: (v) => v.toFixed(0) + '%' },
            plotOptions: { pie: { donut: { size: '65%' } } },
        });
    }

    // ── 3. Pipeline funnel (bar with isFunnel) ──────────────────────
    if (isEmpty(data.pipeline.series)) {
        renderEmpty('chart-pipeline');
    } else {
        mount('chart-pipeline', {
            chart: { type: 'bar', height: 280 },
            series: [{ name: 'Work Orders', data: data.pipeline.series }],
            xaxis: { categories: data.pipeline.labels },
            plotOptions: { bar: { horizontal: true, isFunnel: true, borderRadius: 0, barHeight: '80%' } },
            dataLabels: { enabled: true, style: { colors: ['#fff'], fontWeight: 700 } },
            legend: { show: false },
            colors: ['#2E86C1'],
        });
    }

    // ── 4. Urgency mix (donut) ──────────────────────────────────────
    if (isEmpty(data.urgency.series)) {
        renderEmpty('chart-urgency');
    } else {
        mount('chart-urgency', {
            chart: { type: 'donut', height: 280 },
            series: data.urgency.series,
            labels: data.urgency.labels,
            colors: ['#2E86C1', '#D68910', '#C0392B'],
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '65%' } } },
        });
    }

    // ── 5. Revenue (bar) ────────────────────────────────────────────
    if (isEmpty(data.revenue.series)) {
        renderEmpty('chart-revenue');
    } else {
        mount('chart-revenue', {
            chart: { type: 'bar', height: 280 },
            series: data.revenue.series,
            xaxis: { categories: data.revenue.categories },
            yaxis: { labels: { formatter: fmtUSD } },
            tooltip: { y: { formatter: fmtUSD } },
            plotOptions: { bar: { borderRadius: 5, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            colors: ['#1E8449'],
            legend: { show: false },
        });
    }

    // ── 6. Invoice status (donut) ───────────────────────────────────
    if (isEmpty(data.invoiceStatus.series)) {
        renderEmpty('chart-invoice-status');
    } else {
        mount('chart-invoice-status', {
            chart: { type: 'donut', height: 280 },
            series: data.invoiceStatus.series,
            labels: data.invoiceStatus.labels,
            legend: { position: 'bottom', fontSize: '12px' },
            plotOptions: { pie: { donut: { size: '65%' } } },
        });
    }

    // ── 7. Top services (horizontal bar) ────────────────────────────
    if (isEmpty(data.topServices.series)) {
        renderEmpty('chart-top-services');
    } else {
        mount('chart-top-services', {
            chart: { type: 'bar', height: 280 },
            series: data.topServices.series,
            xaxis: { categories: data.topServices.categories },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '70%' } },
            dataLabels: { enabled: true, style: { fontSize: '11px' } },
            legend: { show: false },
            colors: ['#2E86C1'],
        });
    }

    // ── 8. New customers trailing 12 months (line) ──────────────────
    if (isEmpty(data.newCustomers.series)) {
        renderEmpty('chart-new-customers');
    } else {
        mount('chart-new-customers', {
            chart: { type: 'line', height: 280 },
            series: data.newCustomers.series,
            xaxis: { categories: data.newCustomers.categories },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            markers: { size: 4 },
            legend: { show: false },
            colors: ['#7D3C98'],
        });
    }

    // ── 9. Employee performance (horizontal bar) ────────────────────
    if (isEmpty(data.employee.series)) {
        renderEmpty('chart-employee', 'No completed work orders by technicians in this period.');
    } else {
        mount('chart-employee', {
            chart: { type: 'bar', height: 240 },
            series: data.employee.series,
            xaxis: { categories: data.employee.categories },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
            dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#fff'] } },
            legend: { show: false },
            colors: ['#1A3C5E'],
        });
    }

    // ── Live dark-mode re-theming ───────────────────────────────────
    const obs = new MutationObserver(() => {
        const mode = isDark() ? 'dark' : 'light';
        charts.forEach(c => c.updateOptions({
            theme: { mode },
            grid:  { borderColor: isDark() ? '#334155' : '#e5e7eb' },
            chart: { background: 'transparent' },
        }, false, true));
    });
    obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
})();
</script>
@endpush
