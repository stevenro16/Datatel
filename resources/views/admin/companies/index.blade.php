@extends('layouts.admin')
@section('title', 'Companies')

@section('content')

@php
$pills = [
    ['key' => '',         'label' => 'All'],
    ['key' => 'active',   'label' => 'Active'],
    ['key' => 'inactive', 'label' => 'Inactive'],
    ['key' => 'pending',  'label' => 'Pending'],
];
$pillBase = 'display:inline-flex;align-items:center;gap:.4rem;padding:.3rem .85rem;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid;';
$pillOn  = $pillBase . 'background:var(--primary);color:#fff;border-color:var(--primary);';
$pillOff = $pillBase . 'background:#fff;color:#555;border-color:#d1d5db;';
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1rem;margin-top:.85rem;flex-wrap:wrap;">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @foreach($pills as $pill)
        @php
            $isActive = $status === $pill['key'];
            $cnt      = $tabCounts[$pill['key']] ?? 0;
            $isPending = $pill['key'] === 'pending';
        @endphp
        <a href="{{ route('admin.companies.index', array_filter(['status' => $pill['key'], 'search' => $search])) }}"
           style="{{ $isActive ? $pillOn : $pillOff }}">
            {{ $pill['label'] }}
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:1.25rem;height:1.25rem;padding:0 .3rem;border-radius:999px;font-size:.68rem;font-weight:700;background:{{ $isActive ? 'rgba(255,255,255,.22)' : ($isPending && $cnt > 0 ? 'rgba(220,38,38,.12)' : 'rgba(0,0,0,.06)') }};color:{{ $isActive ? '#fff' : ($isPending && $cnt > 0 ? '#dc2626' : 'inherit') }};">{{ $cnt }}</span>
        </a>
        @endforeach
    </div>
    <a href="{{ route('admin.companies.create') }}"
       style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--accent);color:#fff;border-radius:6px;font-size:.875rem;font-weight:700;box-shadow:0 2px 6px rgba(46,134,193,.3);letter-spacing:.01em;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New
    </a>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PENDING TAB — two approval sections
══════════════════════════════════════════════════════════════ --}}
@if($status === 'pending')

{{-- Pending Company Creation --}}
<div style="margin-bottom:1.75rem;">
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;">
        <span style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#374151;">Pending Company Creation</span>
        <span style="background:#fef3c7;color:#92400e;border-radius:999px;padding:.15rem .6rem;font-size:.72rem;font-weight:700;">{{ $pendingCompanies->count() }}</span>
    </div>

    @if($pendingCompanies->isEmpty())
    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:2rem;text-align:center;color:#9ca3af;font-size:.88rem;">
        No pending company creation requests.
    </div>
    @else
    <div style="display:grid;gap:.85rem;">
        @foreach($pendingCompanies as $co)
        @php $requester = $co->members->first(); @endphp
        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;border-left:4px solid #f59e0b;">
            <div style="padding:1rem 1.35rem;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:1rem;font-weight:700;color:var(--primary);margin-bottom:.25rem;">{{ $co->name }}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.25rem;font-size:.82rem;color:#6b7280;">
                        @if($co->owner_name)<span>Owner: <strong style="color:#374151;">{{ $co->owner_name }}</strong></span>@endif
                        @if($co->phone)<span>{{ $co->phone }}</span>@endif
                        @if($co->email)<span>{{ $co->email }}</span>@endif
                        @if($co->address_city)<span>{{ $co->address_city }}@if($co->address_state), {{ $co->address_state }}@endif</span>@endif
                    </div>
                    @if($requester)
                    <div style="margin-top:.45rem;font-size:.78rem;color:#9ca3af;">
                        Requested by <strong style="color:#374151;">{{ $requester->name }}</strong> ({{ $requester->email }})
                        &middot; {{ $co->created_at->diffForHumans() }}
                    </div>
                    @endif
                </div>
                <div style="display:flex;gap:.45rem;flex-shrink:0;">
                    <form method="POST" action="{{ route('admin.companies.approve', $co) }}">
                        @csrf
                        <button type="submit" style="padding:.4rem 1rem;background:#059669;color:#fff;border:none;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.companies.reject', $co) }}"
                          onsubmit="return confirm('Reject and permanently remove this company request?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="padding:.4rem 1rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Reject</button>
                    </form>
                    <a href="{{ route('admin.companies.show', $co) }}" class="btn btn-secondary btn-sm">Details</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Pending Member Join Requests --}}
<div>
    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;">
        <span style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#374151;">Pending Member Requests</span>
        <span style="background:#dbeafe;color:#1e40af;border-radius:999px;padding:.15rem .6rem;font-size:.72rem;font-weight:700;">{{ $pendingMembers->count() }}</span>
    </div>

    @if($pendingMembers->isEmpty())
    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:2rem;text-align:center;color:#9ca3af;font-size:.88rem;">
        No pending member join requests.
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Requesting to Join</th>
                <th>Requested</th>
                <th style="width:180px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingMembers as $pm)
            <tr>
                <td>
                    <div style="font-weight:600;color:#111;">{{ $pm->user->name }}</div>
                    <div style="font-size:.78rem;color:#9ca3af;">{{ $pm->user->email }}</div>
                </td>
                <td>
                    <a href="{{ route('admin.companies.show', $pm->company) }}"
                       style="font-weight:600;color:var(--accent);text-decoration:none;">{{ $pm->company->name }}</a>
                </td>
                <td style="font-size:.82rem;color:#6b7280;">{{ $pm->created_at->diffForHumans() }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <form method="POST" action="{{ route('admin.companies.members.approve', [$pm->company, $pm->user]) }}">
                            @csrf
                            <button type="submit" style="padding:.3rem .75rem;background:#059669;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:700;cursor:pointer;">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.companies.members.reject', [$pm->company, $pm->user]) }}"
                              onsubmit="return confirm('Reject this join request?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:.3rem .75rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:5px;font-size:.78rem;font-weight:700;cursor:pointer;">Reject</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     NORMAL TABS — search + table
══════════════════════════════════════════════════════════════ --}}
@else

<form method="GET" action="{{ route('admin.companies.index') }}" style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem;align-items:center;">
    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
    <div style="position:relative;flex:1;min-width:220px;">
        <svg style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);pointer-events:none;" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Search by name, owner, email, or phone…"
               style="width:100%;padding:.5rem .85rem .5rem 2.2rem;border:1px solid #d0d5dd;border-radius:6px;font-size:.875rem;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05);outline:none;box-sizing:border-box;">
    </div>
    <button type="submit" class="btn btn-secondary" style="white-space:nowrap;">Search</button>
    @if($search)
    <a href="{{ route('admin.companies.index', array_filter(['status' => $status])) }}" class="btn btn-secondary" style="white-space:nowrap;">Clear</a>
    @endif
</form>

<table class="data-table">
    <thead>
        <tr>
            <th>Company</th>
            <th>Owner / Contact</th>
            <th>Members</th>
            <th>Sites</th>
            <th>Status</th>
            <th>Since</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($companies as $co)
        @php
            $sBg  = match($co->status) { 'active' => '#d1fae5', 'pending' => '#fef3c7', default => '#f3f4f6' };
            $sClr = match($co->status) { 'active' => '#065f46', 'pending' => '#92400e', default => '#374151' };
            $sLbl = match($co->status) { 'active' => 'Active',  'pending' => 'Pending', default => 'Inactive' };
        @endphp
        <tr data-href="{{ route('admin.companies.show', $co) }}">
            <td>
                <div style="font-weight:700;color:var(--primary);">{{ $co->name }}</div>
                @if($co->address_city)
                <div style="font-size:.78rem;color:#9ca3af;margin-top:.1rem;">{{ $co->address_city }}@if($co->address_state), {{ $co->address_state }}@endif</div>
                @endif
            </td>
            <td style="vertical-align:top;">
                @if($co->owner_name)<div style="font-size:.9rem;color:#374151;">{{ $co->owner_name }}</div>@endif
                @if($co->phone)<div style="font-size:.78rem;color:#6b7280;margin-top:.1rem;">{{ $co->phone }}</div>@endif
                @if($co->email)<div style="font-size:.78rem;color:#6b7280;">{{ $co->email }}</div>@endif
                @if(!$co->owner_name && !$co->phone && !$co->email)<span style="color:#ccc;">—</span>@endif
            </td>
            <td style="text-align:center;">
                <span style="font-size:.95rem;font-weight:700;color:{{ $co->active_members_count > 0 ? 'var(--primary)' : '#ccc' }};">{{ $co->active_members_count }}</span>
            </td>
            <td style="text-align:center;">
                <span style="font-size:.95rem;font-weight:700;color:{{ $co->sites_count > 0 ? 'var(--primary)' : '#ccc' }};">{{ $co->sites_count }}</span>
            </td>
            <td><span class="badge" style="background:{{ $sBg }};color:{{ $sClr }};">{{ $sLbl }}</span></td>
            <td style="font-size:.82rem;color:#666;">{{ $co->created_at->format('M j, Y') }}</td>
            <td>
                <a href="{{ route('admin.companies.edit', $co) }}" onclick="event.stopPropagation()" class="btn btn-secondary btn-sm">Edit</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:#999;padding:2.5rem;">No companies found.</td></tr>
        @endforelse
    </tbody>
</table>
<div style="margin-top:1rem;">{{ $companies->links() }}</div>

@endif
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">ACCOUNT MANAGEMENT</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">
        Companies
    </h1>
</div>
@endpush
