@extends('layouts.admin')
@section('title', 'Reports')

@section('content')
@php
    $defaultFrom = now()->startOfMonth()->format('Y-m-d');
    $defaultTo   = now()->format('Y-m-d');
@endphp

<div style="margin-bottom:1.5rem;">
    <h1 class="page-title" style="margin:0 0 .25rem;">Reports</h1>
    <p style="color:#64748b;font-size:.9rem;margin:0;">
        Print-ready reports across work orders, invoicing, technicians, and customers. Each report opens in a new tab formatted for printing or PDF export.
    </p>
</div>

@foreach($catalog as $category => $reports)
<div style="margin-bottom:1.75rem;">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#2E86C1;border-bottom:1px solid #e2e8f0;padding-bottom:.35rem;margin-bottom:.9rem;">
        {{ $category }}
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:1rem;">
        @foreach($reports as $r)
        <form action="{{ route('admin.reports.' . $r['slug']) }}" method="GET" target="_blank"
              style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.1rem 1.15rem;display:flex;flex-direction:column;gap:.65rem;">
            <div>
                <div style="font-size:.97rem;font-weight:700;color:#1A3C5E;">{{ $r['title'] }}</div>
                <div style="font-size:.8rem;color:#64748b;line-height:1.4;margin-top:.2rem;">{{ $r['desc'] }}</div>
            </div>

            @if(in_array('range', $r['filters']))
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                <label style="font-size:.7rem;font-weight:600;color:#475569;">Date range
                    <select name="range" onchange="this.closest('form').querySelectorAll('.custom-dates').forEach(el=>el.style.display=this.value==='custom'?'flex':'none')"
                            style="width:100%;margin-top:.2rem;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;background:#fff;">
                        <option value="month">This Month</option>
                        <option value="30d">Last 30 Days</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="ytd">Year to Date</option>
                        <option value="custom">Custom…</option>
                    </select>
                </label>
                <div class="custom-dates" style="display:none;gap:.4rem;">
                    <input type="date" name="from" value="{{ $defaultFrom }}" style="flex:1;padding:.35rem .45rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.8rem;">
                    <input type="date" name="to" value="{{ $defaultTo }}" style="flex:1;padding:.35rem .45rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.8rem;">
                </div>
            </div>
            @endif

            @if(in_array('customer', $r['filters']))
            <label style="font-size:.7rem;font-weight:600;color:#475569;">Customer
                <select name="customer_id" style="width:100%;margin-top:.2rem;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;background:#fff;">
                    <option value="">All customers</option>
                    @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </label>
            @endif

            @if(in_array('company', $r['filters']))
            <label style="font-size:.7rem;font-weight:600;color:#475569;">Company
                <select name="company_id" style="width:100%;margin-top:.2rem;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;background:#fff;">
                    <option value="">All companies</option>
                    @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </label>
            @endif

            @if(in_array('tech', $r['filters']))
            <label style="font-size:.7rem;font-weight:600;color:#475569;">Technician
                <select name="tech_id" style="width:100%;margin-top:.2rem;padding:.4rem .5rem;border:1px solid #cbd5e1;border-radius:6px;font-size:.82rem;background:#fff;">
                    <option value="">All technicians</option>
                    @foreach($techs as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
            </label>
            @endif

            <button type="submit"
                    style="margin-top:.2rem;align-self:flex-start;display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .95rem;background:#1A3C5E;color:#fff;border:none;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Generate Report
            </button>
        </form>
        @endforeach
    </div>
</div>
@endforeach

@endsection
