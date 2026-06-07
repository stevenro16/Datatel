@extends('layouts.admin')
@section('title', 'Reports')

@section('content')

<form method="GET" style="display:flex;gap:.75rem;align-items:flex-end;margin-bottom:2rem;margin-top:.85rem;background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
    <div>
        <label style="display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:.25rem;">From</label>
        <input type="date" name="from" value="{{ $from }}" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
    </div>
    <div>
        <label style="display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:.25rem;">To</label>
        <input type="date" name="to" value="{{ $to }}" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
    </div>
    <button type="submit" class="btn btn-primary">Run Report</button>
</form>

<div class="kpi-grid" style="margin-bottom:2rem;">
    <div class="kpi-card">
        <span class="kpi-number">{{ $stats['work_orders_total'] }}</span>
        <span class="kpi-label">Work Orders Created</span>
    </div>
    <div class="kpi-card kpi-success">
        <span class="kpi-number">{{ $stats['work_orders_completed'] }}</span>
        <span class="kpi-label">Completed</span>
    </div>
    <div class="kpi-card kpi-warn">
        <span class="kpi-number">{{ $stats['work_orders_canceled'] }}</span>
        <span class="kpi-label">Canceled</span>
    </div>
    <div class="kpi-card kpi-info">
        <span class="kpi-number">{{ $stats['new_customers'] }}</span>
        <span class="kpi-label">New Customers</span>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);color:#fff;padding:.85rem 1.25rem;font-weight:600;font-size:.9rem;">Work Orders by Status</div>
        <table class="data-table">
            <tbody>
                @forelse($byStatus as $status => $count)
                <tr>
                    <td><span class="badge badge-{{ $status }}">{{ str_replace('_',' ',ucfirst($status)) }}</span></td>
                    <td style="text-align:right;font-weight:600;">{{ $count }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align:center;color:#999;padding:1.5rem;">No data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);color:#fff;padding:.85rem 1.25rem;font-weight:600;font-size:.9rem;">Top Services Requested</div>
        <table class="data-table">
            <tbody>
                @forelse($topServices as $svc)
                <tr>
                    <td>{{ $svc->name }}</td>
                    <td style="text-align:right;font-weight:600;">{{ $svc->total }}</td>
                </tr>
                @empty
                <tr><td colspan="2" style="text-align:center;color:#999;padding:1.5rem;">No data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">REPORTING</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        Reports
    </h1>
</div>
@endpush
