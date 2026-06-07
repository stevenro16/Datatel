@extends('layouts.admin')
@section('title', $company->name)

@section('content')

{{-- Approve / Reject (pending companies only) --}}
@if($company->status === 'pending')
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;margin-top:.85rem;">
    <form method="POST" action="{{ route('admin.companies.approve', $company) }}">
        @csrf
        <button type="submit" style="padding:.4rem 1rem;background:#059669;color:#fff;border:none;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Approve Company</button>
    </form>
    <form method="POST" action="{{ route('admin.companies.reject', $company) }}"
          onsubmit="return confirm('Reject and permanently remove this company?')">
        @csrf @method('DELETE')
        <button type="submit" style="padding:.4rem 1rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:6px;font-size:.82rem;font-weight:700;cursor:pointer;">Reject</button>
    </form>
</div>
@endif

{{-- ── Company Details ───────────────────────────────────────────────────── --}}
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:1.25rem;{{ $company->status !== 'pending' ? 'margin-top:.85rem;' : '' }}overflow:hidden;">
    <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company Details</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Contact · Address · Tax</div>
            </div>
        </div>
        <a href="{{ route('admin.companies.edit', $company) }}" title="Edit company"
           style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;text-decoration:none;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </a>
    </div>
    <div style="padding:1.25rem 1.5rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem 2rem;">
        @if($company->owner_name)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Owner / Contact</div>
            <div style="font-size:.9rem;color:#111;">{{ $company->owner_name }}</div>
        </div>
        @endif
        @if($company->tax_id)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Tax ID</div>
            <div style="font-size:.9rem;color:#111;">{{ $company->tax_id }}</div>
        </div>
        @endif
        @if($company->phone)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Phone</div>
            <div style="font-size:.9rem;"><a href="tel:{{ $company->phone }}" style="color:var(--accent);text-decoration:none;">{{ $company->phone }}</a></div>
        </div>
        @endif
        @if($company->email)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Email</div>
            <div style="font-size:.9rem;"><a href="mailto:{{ $company->email }}" style="color:var(--accent);text-decoration:none;">{{ $company->email }}</a></div>
        </div>
        @endif
        @if($company->website)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Website</div>
            <div style="font-size:.9rem;"><a href="{{ $company->website }}" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:none;">{{ $company->website }}</a></div>
        </div>
        @endif
        @if($company->address_street)
        <div style="grid-column:span 2;">
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Address</div>
            <div style="font-size:.9rem;color:#111;">{{ $company->address_street }}<br>{{ $company->address_city }}@if($company->address_state), {{ $company->address_state }}@endif @if($company->address_zip) {{ $company->address_zip }}@endif</div>
        </div>
        @endif
        @if($company->tax_rate !== null)
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Tax Rate</div>
            <div style="font-size:.9rem;color:#111;">{{ number_format($company->tax_rate * 100, 2) }}%</div>
        </div>
        @endif
        <div>
            <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.2rem;">Created</div>
            <div style="font-size:.9rem;color:#555;">{{ $company->created_at->format('M j, Y') }}</div>
        </div>
    </div>
</div>

{{-- ── Sites ─────────────────────────────────────────────────────────────── --}}
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:1.25rem;overflow:hidden;">
    <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Sites</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $company->sites->count() }} {{ Str::plural('location', $company->sites->count()) }} on file</div>
            </div>
        </div>
        <button type="button" onclick="toggleAddSite()" id="add-site-btn"
                style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;cursor:pointer;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span id="add-site-btn-label">Add Site</span>
        </button>
    </div>

    {{-- Sites table --}}
    @if($company->sites->isNotEmpty())
    @php $siteCount = $company->sites->count(); @endphp
    <table class="data-table" style="margin:0;border-radius:0;box-shadow:none;">
        <thead>
            <tr>
                <th style="width:36px;text-align:center;"></th>
                <th>Label</th>
                <th>Address</th>
                <th>County</th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($company->sites as $site)
            <tr>
                {{-- Star / default --}}
                <td style="text-align:center;padding-right:.25rem;">
                    @if($site->is_default)
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" title="Default site"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @elseif($siteCount > 1)
                    <form method="POST" action="{{ route('admin.companies.sites.default', [$company, $site]) }}" style="margin:0;">
                        @csrf
                        <button type="submit" title="Set as default" style="background:none;border:none;cursor:pointer;padding:0;line-height:0;color:#d1d5db;"
                                onmouseover="this.style.color='#f59e0b'" onmouseout="this.style.color='#d1d5db'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </button>
                    </form>
                    @endif
                </td>
                <td style="font-weight:600;color:var(--primary);">{{ $site->label ?? '—' }}</td>
                <td style="font-size:.88rem;">{{ $site->street }}, {{ $site->city }}, {{ $site->state }} {{ $site->zip }}</td>
                <td style="font-size:.85rem;color:#6b7280;">{{ $site->county ?? '—' }}</td>
                <td>
                    <div style="display:flex;gap:.35rem;align-items:center;">
                        <button type="button" title="Edit site"
                                data-site-id="{{ $site->id }}"
                                data-label="{{ $site->label }}"
                                data-street="{{ $site->street }}"
                                data-city="{{ $site->city }}"
                                data-state="{{ $site->state }}"
                                data-zip="{{ $site->zip }}"
                                data-county="{{ $site->county ?? '' }}"
                                data-url="{{ route('admin.companies.sites.update', [$company, $site]) }}"
                                onclick="openSiteEdit(this)"
                                style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#9ca3af;cursor:pointer;"
                                onmouseover="this.style.background='#f0f4ff';this.style.color='var(--primary)';this.style.borderColor='#c7d2fe'"
                                onmouseout="this.style.background='#fff';this.style.color='#9ca3af';this.style.borderColor='#e5e7eb'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.companies.sites.destroy', [$company, $site]) }}"
                              onsubmit="return confirm('Remove this site?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.88rem;">No sites yet.</div>
    @endif

    {{-- Add Site inline form --}}
    <div id="add-site-form" style="display:none;border-top:1px solid #f0f0f0;padding:1.25rem 1.5rem;background:#f9fafb;">
        <div style="font-size:.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.85rem;">New Site</div>
        <form method="POST" action="{{ route('admin.companies.sites.store', $company) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem 1rem;margin-bottom:.85rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">Site Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="label" placeholder="e.g. Main Office, Warehouse A" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">Street <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="street" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">City <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="city" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:80px 1fr;gap:.5rem;">
                    <div>
                        <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">State <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="state" maxlength="2" placeholder="CA" required
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">ZIP <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="zip" maxlength="10" required
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.25rem;">County</label>
                    <input type="text" name="county"
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Save Site</button>
                <button type="button" onclick="toggleAddSite()" class="btn btn-secondary btn-sm">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Pending Member Requests ──────────────────────────────────────────── --}}
@if($pendingMembers->isNotEmpty())
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:1.25rem;overflow:hidden;border-left:4px solid #f59e0b;">
    <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
        <div>
            <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Pending Join Requests</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $pendingMembers->count() }} awaiting approval</div>
        </div>
    </div>
    <table class="data-table" style="margin:0;border-radius:0;box-shadow:none;">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Requested</th><th style="width:180px;"></th></tr>
        </thead>
        <tbody>
            @foreach($pendingMembers as $pm)
            <tr>
                <td style="font-weight:600;color:#111;">{{ $pm->user->name }}</td>
                <td style="font-size:.88rem;color:#6b7280;">{{ $pm->user->email }}</td>
                <td style="font-size:.82rem;color:#9ca3af;">{{ $pm->created_at->diffForHumans() }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <form method="POST" action="{{ route('admin.companies.members.approve', [$company, $pm->user]) }}">
                            @csrf
                            <button type="submit" style="padding:.3rem .75rem;background:#059669;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:700;cursor:pointer;">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.companies.members.reject', [$company, $pm->user]) }}"
                              onsubmit="return confirm('Reject this request?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="padding:.3rem .75rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:5px;font-size:.78rem;font-weight:700;cursor:pointer;">Reject</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Members / Customers ───────────────────────────────────────────────── --}}
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:1.25rem;overflow:hidden;">
    <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        <div>
            <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Customers</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $company->members->count() }} {{ Str::plural('member', $company->members->count()) }} linked</div>
        </div>
    </div>

    @if($company->members->isNotEmpty())
    <table class="data-table" style="margin:0;border-radius:0;box-shadow:none;">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th style="width:80px;text-align:center;">Primary</th>
                <th style="width:120px;">Linked</th>
                <th style="width:160px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($company->members as $member)
            <tr>
                <td>
                    <div style="font-weight:600;color:#111;">{{ $member->name }}</div>
                    @if($member->pivot->is_primary)
                    <span style="font-size:.68rem;background:#dbeafe;color:#1e40af;padding:.1em .45em;border-radius:3px;">Primary</span>
                    @endif
                </td>
                <td style="font-size:.88rem;color:#6b7280;">{{ $member->email }}</td>
                <td style="text-align:center;">
                    @if(!$member->pivot->is_primary)
                    <form method="POST" action="{{ route('admin.companies.members.primary', [$company, $member]) }}">
                        @csrf
                        <button type="submit" title="Set as primary contact"
                                style="background:none;border:1px solid #d1d5db;border-radius:5px;padding:.2rem .5rem;cursor:pointer;font-size:.72rem;color:#6b7280;">
                            Set Primary
                        </button>
                    </form>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#1e40af" stroke-width="2.5"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </td>
                <td style="font-size:.78rem;color:#9ca3af;">
                    @if($member->pivot->approved_at)
                    {{ \Carbon\Carbon::parse($member->pivot->approved_at)->format('M j, Y') }}
                    @else
                    —
                    @endif
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="{{ route('admin.users.edit', $member) }}" onclick="event.stopPropagation()" class="btn btn-secondary btn-sm">View User</a>
                        <form method="POST" action="{{ route('admin.companies.members.detach', [$company, $member]) }}"
                              onsubmit="return confirm('Unlink {{ addslashes($member->name) }} from this company?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Unlink</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.88rem;">No customers linked yet.</div>
    @endif

    {{-- Link Customer form --}}
    <div style="border-top:1px solid #f0f0f0;padding:1rem 1.5rem;background:#f9fafb;">
        @if($availableUsers->isNotEmpty())
        <div style="font-size:.8rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.6rem;">Link a Customer</div>
        <form method="POST" action="{{ route('admin.companies.members.attach', $company) }}"
              style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:flex-end;">
            @csrf
            <div style="flex:1;min-width:220px;">
                <input type="text" id="member-search" placeholder="Type to search customers…"
                       style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;margin-bottom:.3rem;">
                <select name="user_id" id="member-select" required size="1"
                        style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                    <option value="">— Select customer —</option>
                    @foreach($availableUsers as $u)
                    <option value="{{ $u->id }}" data-label="{{ strtolower($u->name . ' ' . $u->email) }}">{{ $u->name }} — {{ $u->email }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-bottom:0;">Link Customer</button>
        </form>
        @else
        <div style="font-size:.85rem;color:#9ca3af;">All active customers are already linked to this company.</div>
        @endif
    </div>
</div>

{{-- ── Recent Work Orders ────────────────────────────────────────────────── --}}
@if($recentOrders->isNotEmpty())
<div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;">
    <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Recent Work Orders</div>
        </div>
        <a href="{{ route('admin.work-orders.index', ['company_id' => $company->id]) }}"
           style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.8);text-decoration:none;white-space:nowrap;flex-shrink:0;">View All →</a>
    </div>
    <table class="data-table" style="margin:0;border-radius:0;box-shadow:none;">
        <thead>
            <tr><th>WO #</th><th>Customer</th><th>Status</th><th>Scheduled</th></tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $wo)
            @php
                $woBg  = match($wo->status) {
                    'new','triaged'     => '#fef3c7',
                    'scheduled'        => '#dbeafe',
                    'services_performed','invoice_prepared' => '#d1fae5',
                    'billed','completed' => '#f0fdf4',
                    'canceled'         => '#fee2e2',
                    default            => '#f3f4f6',
                };
                $woClr = match($wo->status) {
                    'new','triaged'     => '#92400e',
                    'scheduled'        => '#1e40af',
                    'services_performed','invoice_prepared' => '#065f46',
                    'billed','completed' => '#14532d',
                    'canceled'         => '#991b1b',
                    default            => '#374151',
                };
                $woLbl = match($wo->status) {
                    'new'                => 'New',
                    'triaged'            => 'Triaged',
                    'scheduled'          => 'Scheduled',
                    'awaiting_feedback'  => 'Awaiting Feedback',
                    'services_performed' => 'Services Performed',
                    'invoice_prepared'   => 'Invoice Prepared',
                    'billed'             => 'Billed',
                    'completed'          => 'Completed',
                    'canceled'           => 'Canceled',
                    default              => ucfirst($wo->status),
                };
            @endphp
            <tr data-href="{{ route('admin.work-orders.show', $wo) }}">
                <td style="font-weight:700;color:var(--primary);">WO-{{ str_pad($wo->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td style="font-size:.88rem;">{{ $wo->customer?->name ?? '—' }}</td>
                <td><span class="badge" style="background:{{ $woBg }};color:{{ $woClr }};font-size:.7rem;">{{ $woLbl }}</span></td>
                <td style="font-size:.82rem;color:#6b7280;">{{ $wo->scheduled_at?->format('M j, Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ── Inactivate ────────────────────────────────────────────────────────── --}}
@if($company->status !== 'inactive')
<div style="display:flex;justify-content:flex-end;margin-top:2rem;padding-top:1.1rem;border-top:1px solid #e5e7eb;">
    <form method="POST" action="{{ route('admin.companies.destroy', $company) }}"
          onsubmit="return confirm('Inactivate {{ addslashes($company->name) }}? The company will be deactivated but remain in the system.')">
        @csrf @method('DELETE')
        <button type="submit"
                style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem 1rem;background:#fff;color:#9ca3af;border:1px solid #d1d5db;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.borderColor='#fca5a5';this.style.color='#dc2626'"
                onmouseout="this.style.borderColor='#d1d5db';this.style.color='#9ca3af'">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            Inactivate
        </button>
    </form>
</div>
@endif

{{-- ── Site Edit Modal ──────────────────────────────────────────────────── --}}
<div id="site-edit-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;"
     onclick="if(event.target===this)closeSiteEdit()">
    <div style="background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.5rem 1.75rem;width:540px;max-width:94vw;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;">
            <span style="font-size:.95rem;font-weight:700;color:var(--primary);">Edit Site</span>
            <button type="button" onclick="closeSiteEdit()"
                    style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.4rem;line-height:1;padding:0;"
                    onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">&times;</button>
        </div>
        <form id="site-edit-form" method="POST">
            @csrf
            <input type="hidden" name="_method" value="PATCH">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.7rem 1rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">Site Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="label" id="se-label" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">Street <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="street" id="se-street" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">City <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="city" id="se-city" required
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:80px 1fr;gap:.5rem;">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">State <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="state" id="se-state" maxlength="2" required
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">ZIP <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="zip" id="se-zip" maxlength="10" required
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:.8rem;font-weight:600;color:#444;margin-bottom:.25rem;">County</label>
                    <input type="text" name="county" id="se-county"
                           style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.875rem;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1.25rem;">
                <button type="button" onclick="closeSiteEdit()" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSiteEdit(btn) {
    var modal = document.getElementById('site-edit-modal');
    document.getElementById('site-edit-form').action = btn.dataset.url;
    document.getElementById('se-label').value  = btn.dataset.label;
    document.getElementById('se-street').value = btn.dataset.street;
    document.getElementById('se-city').value   = btn.dataset.city;
    document.getElementById('se-state').value  = btn.dataset.state;
    document.getElementById('se-zip').value    = btn.dataset.zip;
    document.getElementById('se-county').value = btn.dataset.county;
    modal.style.display = 'flex';
}
function closeSiteEdit() {
    document.getElementById('site-edit-modal').style.display = 'none';
}

function toggleAddSite() {
    var f = document.getElementById('add-site-form');
    var showing = f.style.display !== 'none';
    f.style.display = showing ? 'none' : 'block';
    document.getElementById('add-site-btn-label').textContent = showing ? 'Add Site' : '✕ Cancel';
}

(function () {
    var inp = document.getElementById('member-search');
    var sel = document.getElementById('member-select');
    if (!inp || !sel) return;
    var opts = Array.from(sel.options).filter(o => o.value);
    inp.addEventListener('input', function () {
        var q = inp.value.toLowerCase();
        Array.from(sel.options).forEach(o => {
            if (!o.value) return;
            o.hidden = q && !o.dataset.label.includes(q);
        });
        var vis = opts.filter(o => !o.hidden);
        if (vis.length === 1) sel.value = vis[0].value;
    });
})();
</script>
@endsection

@push('topbar-title')
@php
    $_showSBg  = match($company->status) { 'active' => '#d1fae5', 'pending' => '#fef3c7', default => '#f3f4f6' };
    $_showSClr = match($company->status) { 'active' => '#065f46', 'pending' => '#92400e', default => '#374151' };
    $_showSLbl = match($company->status) { 'active' => 'Active',  'pending' => 'Pending', default => 'Inactive' };
@endphp
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">COMPANY</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        {{ $company->name }}
        <span class="badge" style="background:{{ $_showSBg }};color:{{ $_showSClr }};font-size:.7rem;">{{ $_showSLbl }}</span>
    </h1>
</div>
@endpush
