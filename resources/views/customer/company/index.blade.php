@extends('layouts.portal')
@section('title', 'My Company')

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="margin:0 0 .2rem;font-size:1.75rem;font-weight:700;color:var(--primary);">My Company</h1>
    <p style="margin:0;font-size:.85rem;color:#6b7280;font-weight:500;">Manage your company membership</p>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STATE A — Active member
══════════════════════════════════════════════════════════════ --}}
@if($membership && $membership->status === 'active' && $membership->company)
@php $co = $membership->company; @endphp

{{-- Pending join requests --}}
@if($pendingRequests->isNotEmpty())
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;overflow:hidden;margin-bottom:1.25rem;">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid #fde68a;display:flex;align-items:center;gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
        <span style="font-size:.8rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.07em;">
            {{ $pendingRequests->count() }} Pending Request{{ $pendingRequests->count() > 1 ? 's' : '' }} to Join
        </span>
    </div>
    <div style="padding:.75rem 1.25rem;display:flex;flex-direction:column;gap:.5rem;">
        @foreach($pendingRequests as $req)
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;background:#fff;border-radius:7px;padding:.65rem 1rem;box-shadow:0 1px 3px rgba(0,0,0,.06);">
            <div>
                <div style="font-weight:600;color:#111;font-size:.9rem;">{{ $req->user->name }}</div>
                <div style="font-size:.75rem;color:#6b7280;">{{ $req->user->email }} &middot; {{ $req->created_at->diffForHumans() }}</div>
            </div>
            <div style="display:flex;gap:.4rem;">
                <form method="POST" action="{{ route('portal.company.members.approve', [$co, $req->user]) }}">
                    @csrf
                    <button type="submit" style="padding:.3rem .8rem;background:#059669;color:#fff;border:none;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;">Approve</button>
                </form>
                <form method="POST" action="{{ route('portal.company.members.reject', [$co, $req->user]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" style="padding:.3rem .8rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;">Decline</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Company details --}}
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
    <div style="padding:.85rem 1.4rem;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
        <div>
            <div style="font-size:1.05rem;font-weight:700;color:var(--primary);">{{ $co->name }}</div>
            <div style="margin-top:.2rem;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                <span style="display:inline-flex;align-items:center;gap:.25rem;background:#d1fae5;color:#065f46;border-radius:999px;padding:.15rem .6rem;font-size:.7rem;font-weight:700;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                    Active Member
                </span>
                @if($membership->is_primary)
                <span style="background:#dbeafe;color:#1e40af;border-radius:999px;padding:.15rem .6rem;font-size:.7rem;font-weight:700;">Primary Contact</span>
                @endif
            </div>
        </div>
    </div>
    <div style="padding:1rem 1.4rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.8rem 1.5rem;">
        @if($co->owner_name)
        <div>
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.2rem;">Owner</div>
            <div style="font-size:.88rem;color:#111;">{{ $co->owner_name }}</div>
        </div>
        @endif
        @if($co->phone)
        <div>
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.2rem;">Phone</div>
            <div style="font-size:.88rem;"><a href="tel:{{ $co->phone }}" style="color:var(--accent);text-decoration:none;">{{ $co->phone }}</a></div>
        </div>
        @endif
        @if($co->email)
        <div>
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.2rem;">Email</div>
            <div style="font-size:.88rem;"><a href="mailto:{{ $co->email }}" style="color:var(--accent);text-decoration:none;">{{ $co->email }}</a></div>
        </div>
        @endif
        @if($co->website)
        <div>
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.2rem;">Website</div>
            <div style="font-size:.88rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><a href="{{ $co->website }}" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:none;">{{ $co->website }}</a></div>
        </div>
        @endif
        @if($co->address_street)
        <div style="grid-column:1/-1;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#9ca3af;margin-bottom:.2rem;">Address</div>
            <div style="font-size:.88rem;color:#111;">{{ $co->address_street }}, {{ $co->address_city }}@if($co->address_state), {{ $co->address_state }}@endif {{ $co->address_zip }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Sites --}}
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
    <div style="padding:.85rem 1.4rem;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;">
            Sites
            <span style="background:#e5e7eb;color:#6b7280;border-radius:999px;padding:.1em .5em;font-size:.9em;margin-left:.3rem;">{{ $co->sites->count() }}</span>
        </span>
        <button type="button" onclick="toggleAddSite()" id="add-site-btn"
                style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Site
        </button>
    </div>

    @php $siteCount = $co->sites->count(); @endphp

    @if($co->sites->isNotEmpty())
    <div style="padding:.35rem 0;">
        @foreach($co->sites as $site)
        <div style="display:flex;align-items:center;gap:.6rem;padding:.6rem 1.1rem 0.6rem .9rem;{{ !$loop->last ? 'border-bottom:1px solid #f9fafb;' : '' }}">
            {{-- Star / default toggle --}}
            <div style="flex-shrink:0;width:22px;text-align:center;">
                @if($site->is_default)
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" title="Default site"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                @elseif($siteCount > 1)
                <form method="POST" action="{{ route('portal.sites.default', $site) }}" style="margin:0;">
                    @csrf
                    <button type="submit" title="Set as default" style="background:none;border:none;cursor:pointer;padding:0;line-height:0;color:#d1d5db;"
                            onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='#d1d5db'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </button>
                </form>
                @endif
            </div>

            {{-- Details --}}
            <div style="flex:1;min-width:0;">
                <div style="font-size:.87rem;font-weight:600;color:var(--primary);">
                    {{ $site->label ?? $site->street }}
                    @if($site->is_default)
                    <span style="font-size:.67rem;font-weight:700;background:#fef3c7;color:#92400e;border-radius:4px;padding:.1em .4em;margin-left:.3rem;vertical-align:middle;">Default</span>
                    @endif
                </div>
                <div style="font-size:.76rem;color:#6b7280;margin-top:.1rem;">{{ $site->street }}, {{ $site->city }}, {{ $site->state }} {{ $site->zip }}@if($site->county) &middot; {{ $site->county }}@endif</div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:.3rem;align-items:center;flex-shrink:0;">
                <button type="button" title="Edit"
                        data-label="{{ $site->label }}"
                        data-street="{{ $site->street }}"
                        data-city="{{ $site->city }}"
                        data-state="{{ $site->state }}"
                        data-zip="{{ $site->zip }}"
                        data-county="{{ $site->county ?? '' }}"
                        data-url="{{ route('portal.sites.update', $site) }}"
                        onclick="openSiteEdit(this)"
                        style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#9ca3af;cursor:pointer;"
                        onmouseover="this.style.background='#f0f4ff';this.style.color='var(--primary)';this.style.borderColor='#c7d2fe'"
                        onmouseout="this.style.background='#fff';this.style.color='#9ca3af';this.style.borderColor='#e5e7eb'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                @if(!$site->is_default || $siteCount === 1)
                <form method="POST" action="{{ route('portal.sites.deactivate', $site) }}"
                      onsubmit="return confirm('Inactivate this site?')" style="margin:0;">
                    @csrf
                    <button type="submit" title="Inactivate"
                            style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#9ca3af;cursor:pointer;"
                            onmouseover="this.style.borderColor='#fca5a5';this.style.color='#dc2626'"
                            onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#9ca3af'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="padding:1.25rem 1.4rem;text-align:center;color:#9ca3af;font-size:.85rem;">No sites added yet.</div>
    @endif

    {{-- Add Site form --}}
    <div id="add-site-form" style="display:none;border-top:1px solid #f0f0f0;padding:1.1rem 1.4rem;background:#f9fafb;">
        <div style="font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.75rem;">New Site</div>
        <form method="POST" action="{{ route('portal.sites.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem .85rem;margin-bottom:.75rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Site Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="label" placeholder="e.g. Main Office, Warehouse A" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Street <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="street" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">City <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="city" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:72px 1fr;gap:.4rem;">
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">State <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="state" maxlength="2" placeholder="TX" required
                               style="width:100%;padding:.42rem .5rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;text-transform:uppercase;text-align:center;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">ZIP <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="zip" maxlength="10" required
                               style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">County</label>
                    <input type="text" name="county"
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:.45rem;">
                <button type="submit" style="padding:.4rem .9rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Save Site</button>
                <button type="button" onclick="toggleAddSite()" style="padding:.4rem .9rem;background:#fff;color:#6b7280;border:1px solid #d1d5db;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Site edit modal --}}
<div id="site-edit-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;"
     onclick="if(event.target===this)closeSiteEdit()">
    <div style="background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.4rem 1.6rem;width:520px;max-width:94vw;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <span style="font-size:.92rem;font-weight:700;color:var(--primary);">Edit Site</span>
            <button type="button" onclick="closeSiteEdit()"
                    style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.4rem;line-height:1;padding:0;"
                    onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">&times;</button>
        </div>
        <form id="site-edit-form" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem .9rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Site Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="label" id="se-label" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Street <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="street" id="se-street" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">City <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="city" id="se-city" required
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:72px 1fr;gap:.4rem;">
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">State <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="state" id="se-state" maxlength="2" required
                               style="width:100%;padding:.42rem .4rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;text-transform:uppercase;text-align:center;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">ZIP <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="zip" id="se-zip" maxlength="10" required
                               style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">County</label>
                    <input type="text" name="county" id="se-county"
                           style="width:100%;padding:.42rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:.45rem;justify-content:flex-end;margin-top:1.1rem;">
                <button type="button" onclick="closeSiteEdit()" style="padding:.4rem .9rem;background:#fff;color:#6b7280;border:1px solid #d1d5db;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:.4rem .9rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- Work Orders --}}
@php
    $totalWos    = $activeWorkOrders->count() + $doneWorkOrders->count();
    $visibleDone = $doneWorkOrders->take(10);
    $hiddenDone  = $doneWorkOrders->skip(10);
    $allRows     = $activeWorkOrders->concat($visibleDone);
@endphp
@if($totalWos > 0)
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
    <div style="padding:.85rem 1.4rem;border-bottom:1px solid #f0f0f0;background:#fafafa;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;">
            Company Work Orders
            <span style="background:#e5e7eb;color:#6b7280;border-radius:999px;padding:.1em .5em;font-size:.9em;margin-left:.3rem;">{{ $totalWos }}</span>
        </span>
        @if($activeWorkOrders->count() > 0)
        <span style="font-size:.72rem;color:#059669;font-weight:600;">
            {{ $activeWorkOrders->count() }} active
        </span>
        @endif
    </div>

    <div style="overflow-x:auto;">
        <table class="data-table" style="margin:0;border-radius:0;box-shadow:none;min-width:600px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Services</th>
                    <th>Site</th>
                    <th>Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeWorkOrders as $wo)
                <tr data-href="{{ route('portal.work-orders.show', $wo) }}" style="cursor:pointer;">
                    <td style="white-space:nowrap;font-weight:600;color:var(--primary);">{{ $wo->woLabel() }}</td>
                    <td style="font-size:.85rem;color:#555;">{{ $wo->customer?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->site_street ?: ($wo->site_city ?: '—') }}</td>
                    <td><span class="badge badge-{{ $wo->status }}">{{ str_replace('_', ' ', $wo->status) }}</span></td>
                    <td style="font-size:.83rem;color:#666;white-space:nowrap;">{{ $wo->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach

                @foreach($visibleDone as $wo)
                <tr data-href="{{ route('portal.work-orders.show', $wo) }}" style="cursor:pointer;opacity:.75;">
                    <td style="white-space:nowrap;font-weight:600;color:var(--primary);">{{ $wo->woLabel() }}</td>
                    <td style="font-size:.85rem;color:#555;">{{ $wo->customer?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->site_street ?: ($wo->site_city ?: '—') }}</td>
                    <td><span class="badge badge-{{ $wo->status }}">{{ str_replace('_', ' ', $wo->status) }}</span></td>
                    <td style="font-size:.83rem;color:#666;white-space:nowrap;">{{ $wo->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach

                @foreach($hiddenDone as $wo)
                <tr data-href="{{ route('portal.work-orders.show', $wo) }}" class="wo-hidden-row" style="cursor:pointer;opacity:.75;display:none;">
                    <td style="white-space:nowrap;font-weight:600;color:var(--primary);">{{ $wo->woLabel() }}</td>
                    <td style="font-size:.85rem;color:#555;">{{ $wo->customer?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                    <td style="font-size:.83rem;color:#666;">{{ $wo->site_street ?: ($wo->site_city ?: '—') }}</td>
                    <td><span class="badge badge-{{ $wo->status }}">{{ str_replace('_', ' ', $wo->status) }}</span></td>
                    <td style="font-size:.83rem;color:#666;white-space:nowrap;">{{ $wo->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($hiddenDone->count() > 0)
    <div style="padding:.75rem 1.4rem;border-top:1px solid #f0f0f0;text-align:center;">
        <button type="button" id="wo-show-more"
                onclick="document.querySelectorAll('.wo-hidden-row').forEach(r=>r.style.display='');this.style.display='none';"
                style="background:none;border:none;color:var(--accent);font-size:.83rem;font-weight:600;cursor:pointer;">
            Show {{ $hiddenDone->count() }} more completed order{{ $hiddenDone->count() > 1 ? 's' : '' }} ↓
        </button>
    </div>
    @endif
</div>
@endif

{{-- Team members --}}
@if($activeMembers->isNotEmpty())
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;">
    <div style="padding:.85rem 1.4rem;border-bottom:1px solid #f0f0f0;background:#fafafa;">
        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;">
            Team Members
            <span style="background:#e5e7eb;color:#6b7280;border-radius:999px;padding:.1em .5em;font-size:.9em;margin-left:.3rem;">{{ $activeMembers->count() }}</span>
        </span>
    </div>
    <div style="padding:.5rem 0;">
        @foreach($activeMembers as $m)
        @php $isMe = $m->user_id === $user->id; @endphp
        <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem 1.4rem;{{ $isMe ? 'background:#f8faff;' : '' }}{{ !$loop->last ? 'border-bottom:1px solid #f9fafb;' : '' }}">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;">
                {{ strtoupper(mb_substr($m->user->name ?? '?', 0, 1)) }}{{ strtoupper(mb_substr(strstr($m->user->name ?? ' ', ' '), 1, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.88rem;font-weight:600;color:#111;">
                    {{ $m->user->name }}
                    @if($isMe)<span style="font-size:.72rem;color:#9ca3af;font-weight:400;margin-left:.3rem;">(you)</span>@endif
                </div>
                <div style="font-size:.75rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $m->user->email }}</div>
            </div>
            @if($m->is_primary)
            <span style="flex-shrink:0;background:#dbeafe;color:#1e40af;border-radius:999px;padding:.15rem .55rem;font-size:.68rem;font-weight:700;">Primary</span>
            @elseif($membership->is_primary && !$isMe)
            <form method="POST" action="{{ route('portal.company.members.unlink', [$co, $m->user]) }}"
                  onsubmit="return confirm('Remove {{ addslashes($m->user->name) }} from {{ addslashes($co->name) }}? They will lose access to the company\'s work orders and sites.')"
                  style="margin:0;flex-shrink:0;">
                @csrf @method('DELETE')
                <button type="submit"
                        style="display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;background:#fff;color:#9ca3af;border:1px solid #e5e7eb;border-radius:5px;font-size:.75rem;font-weight:600;cursor:pointer;"
                        onmouseover="this.style.borderColor='#fca5a5';this.style.color='#dc2626'"
                        onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#9ca3af'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Unlink
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Leave company — bottom right --}}
<div style="margin-top:2rem;padding-top:1.1rem;border-top:1px solid #e5e7eb;display:flex;align-items:center;justify-content:flex-end;gap:1.25rem;">
    <p style="margin:0;font-size:.78rem;color:#9ca3af;line-height:1.45;text-align:right;max-width:320px;">
        Leaving will remove you from <strong style="color:#6b7280;">{{ $co->name }}</strong> and you will no longer have visibility into the company's work orders and team activity.
    </p>
    <form method="POST" action="{{ route('portal.company.leave') }}"
          onsubmit="return confirm('Leave {{ addslashes($co->name) }}? You will no longer be part of the company and will lose access to all shared work orders and sites.')"
          style="margin:0;flex-shrink:0;">
        @csrf @method('DELETE')
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;background:#fff;color:#9ca3af;border:1px solid #d1d5db;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.borderColor='#fca5a5';this.style.color='#dc2626'"
                onmouseout="this.style.borderColor='#d1d5db';this.style.color='#9ca3af'">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Leave Company
        </button>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STATE B — Pending request
══════════════════════════════════════════════════════════════ --}}
@elseif($membership && $membership->status === 'pending' && $membership->company)
@php $co = $membership->company; $isNewCompany = $co->status === 'pending'; @endphp

<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;max-width:620px;">
    <div style="padding:1.75rem 1.75rem 1.25rem;text-align:center;">
        <div style="width:50px;height:50px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;margin:0 auto .85rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 6v6l4 2"/></svg>
        </div>
        @if($isNewCompany)
        <h2 style="font-size:1.1rem;font-weight:700;color:#111;margin:0 0 .4rem;">Company Creation Pending</h2>
        <p style="font-size:.88rem;color:#6b7280;margin:0 auto;max-width:420px;">Your request to create <strong>{{ $co->name }}</strong> is being reviewed by an administrator. You'll be able to use the company once it's approved.</p>
        @else
        <h2 style="font-size:1.1rem;font-weight:700;color:#111;margin:0 0 .4rem;">Join Request Pending</h2>
        <p style="font-size:.88rem;color:#6b7280;margin:0 auto;max-width:420px;">Your request to join <strong>{{ $co->name }}</strong> is pending. It can be approved by an administrator or by an existing member of that company.</p>
        @endif
    </div>

    @if($co->phone || $co->email || $co->address_city)
    <div style="border-top:1px solid #f0f0f0;padding:.85rem 1.75rem;background:#fafafa;display:flex;flex-wrap:wrap;gap:.6rem 1.5rem;">
        @if($co->phone)<div><div style="font-size:.68rem;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.15rem;">Phone</div><div style="font-size:.85rem;color:#374151;">{{ $co->phone }}</div></div>@endif
        @if($co->email)<div><div style="font-size:.68rem;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.15rem;">Email</div><div style="font-size:.85rem;color:#374151;">{{ $co->email }}</div></div>@endif
        @if($co->address_city)<div><div style="font-size:.68rem;color:#9ca3af;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.15rem;">Location</div><div style="font-size:.85rem;color:#374151;">{{ $co->address_city }}@if($co->address_state), {{ $co->address_state }}@endif</div></div>@endif
    </div>
    @endif

    <div style="padding:1rem 1.75rem;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;">
        <form method="POST" action="{{ route('portal.company.cancel') }}"
              onsubmit="return confirm('Cancel this request?')">
            @csrf @method('DELETE')
            <button type="submit" style="font-size:.83rem;color:#dc2626;background:#fff;border:1px solid #fca5a5;border-radius:6px;padding:.4rem .9rem;cursor:pointer;">Cancel Request</button>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STATE C — No company
══════════════════════════════════════════════════════════════ --}}
@else

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1.25rem;max-width:860px;">

    {{-- Join existing --}}
    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;">
                <div style="width:30px;height:30px;border-radius:7px;background:rgba(46,134,193,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--accent)" stroke-width="2"><path stroke-linecap="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:var(--primary);">Join an Existing Company</div>
                    <div style="font-size:.72rem;color:#9ca3af;">Approved by admin or existing members</div>
                </div>
            </div>
            <p style="font-size:.8rem;color:#6b7280;margin:.3rem 0 0;">Search for your company and request to be added.</p>
        </div>

        <div style="padding:1.1rem 1.4rem;flex:1;">
            <div style="display:flex;gap:.5rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;padding:.65rem .85rem;margin-bottom:.85rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2" style="flex-shrink:0;margin-top:.05rem;"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <p style="margin:0;font-size:.78rem;color:#1e40af;line-height:1.45;">Joining a company gives you visibility into <strong>all work orders submitted by any member</strong> of that company, not just your own.</p>
            </div>
            @if($companies->isEmpty())
            <div style="text-align:center;padding:1.5rem 0;color:#9ca3af;font-size:.85rem;">No active companies are registered yet.</div>
            @else
            <form method="POST" action="{{ route('portal.company.request-join') }}">
                @csrf
                @error('company_id')<div class="alert alert-error" style="margin-bottom:.75rem;">{{ $message }}</div>@enderror
                <div style="position:relative;margin-bottom:.5rem;">
                    <svg style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);pointer-events:none;" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" id="co-search" placeholder="Search by name or city…" autocomplete="off"
                           style="width:100%;padding:.45rem .7rem .45rem 2rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                </div>
                <div id="co-results" style="border:1px solid #d1d5db;border-radius:6px;overflow:hidden;max-height:220px;overflow-y:auto;background:#fff;">
                    @foreach($companies as $c)
                    <label id="co-opt-{{ $c->id }}"
                           style="display:flex;align-items:center;gap:.65rem;padding:.6rem .85rem;cursor:pointer;border-bottom:1px solid #f3f4f6;"
                           onmouseover="this.style.background='#f8faff'" onmouseout="this.style.background=''">
                        <input type="radio" name="company_id" value="{{ $c->id }}"
                               data-label="{{ strtolower($c->name . ' ' . $c->address_city . ' ' . $c->address_state) }}"
                               style="flex-shrink:0;accent-color:var(--accent);">
                        <div>
                            <div style="font-size:.87rem;font-weight:600;color:#111;">{{ $c->name }}</div>
                            @if($c->address_city)<div style="font-size:.75rem;color:#9ca3af;">{{ $c->address_city }}@if($c->address_state), {{ $c->address_state }}@endif</div>@endif
                        </div>
                    </label>
                    @endforeach
                </div>
                <button type="submit" style="display:block;width:100%;margin-top:.85rem;padding:.5rem;background:var(--accent);color:#fff;border:none;border-radius:7px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    Request to Join
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Create new --}}
    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #f0f0f0;">
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;">
                <div style="width:30px;height:30px;border-radius:7px;background:rgba(26,60,94,.08);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--primary)" stroke-width="2"><path stroke-linecap="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:var(--primary);">Create a New Company</div>
                    <div style="font-size:.72rem;color:#9ca3af;">Requires admin approval</div>
                </div>
            </div>
            <p style="font-size:.8rem;color:#6b7280;margin:.3rem 0 0;">Submit your company details for review. Once approved, you can invite team members.</p>
        </div>

        <div style="padding:1.1rem 1.4rem;flex:1;">
            <form method="POST" action="{{ route('portal.company.request-create') }}">
                @csrf
                <div style="display:grid;gap:.6rem;">
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Company Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                        @error('name')<div style="color:#dc2626;font-size:.73rem;margin-top:.15rem;">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Owner / Primary Contact</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#444;margin-bottom:.2rem;">Website</label>
                        <input type="url" name="website" value="{{ old('website') }}" placeholder="https://"
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <div style="padding-top:.25rem;border-top:1px solid #f0f0f0;">
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:.45rem;">Address</div>
                        <div style="display:grid;gap:.45rem;">
                            <input type="text" name="address_street" value="{{ old('address_street') }}" placeholder="Street"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                            <div style="display:grid;grid-template-columns:1fr 52px 76px;gap:.4rem;">
                                <input type="text" name="address_city" value="{{ old('address_city') }}" placeholder="City"
                                       style="padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                                <input type="text" name="address_state" value="{{ old('address_state') }}" maxlength="2" placeholder="ST"
                                       style="padding:.45rem .4rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;text-transform:uppercase;text-align:center;">
                                <input type="text" name="address_zip" value="{{ old('address_zip') }}" placeholder="ZIP"
                                       style="padding:.45rem .5rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;box-sizing:border-box;">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" style="display:block;width:100%;margin-top:.9rem;padding:.5rem;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    Submit for Approval
                </button>
            </form>
        </div>
    </div>

</div>

<script>
(function () {
    var inp = document.getElementById('co-search');
    if (!inp) return;
    var labels = document.querySelectorAll('#co-results label');
    inp.addEventListener('input', function () {
        var q = inp.value.toLowerCase().trim();
        labels.forEach(function (lbl) {
            var radio = lbl.querySelector('input[type=radio]');
            var text  = radio ? radio.dataset.label : '';
            lbl.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
})();
</script>
@endif

@if($membership && $membership->status === 'active' && $membership->company)
<script>
function toggleAddSite() {
    var f = document.getElementById('add-site-form');
    var b = document.getElementById('add-site-btn');
    var showing = f.style.display !== 'none';
    f.style.display = showing ? 'none' : 'block';
    b.innerHTML = showing
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Site'
        : '✕ Cancel';
}
function openSiteEdit(btn) {
    document.getElementById('site-edit-form').action = btn.dataset.url;
    document.getElementById('se-label').value  = btn.dataset.label;
    document.getElementById('se-street').value = btn.dataset.street;
    document.getElementById('se-city').value   = btn.dataset.city;
    document.getElementById('se-state').value  = btn.dataset.state;
    document.getElementById('se-zip').value    = btn.dataset.zip;
    document.getElementById('se-county').value = btn.dataset.county;
    document.getElementById('site-edit-modal').style.display = 'flex';
}
function closeSiteEdit() {
    document.getElementById('site-edit-modal').style.display = 'none';
}
</script>
@endif

@endsection
