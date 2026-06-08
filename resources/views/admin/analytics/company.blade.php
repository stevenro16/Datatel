@extends('layouts.admin')
@section('title', 'Company Analytics')

@section('content')

{{-- Company search + recents --}}
<div style="display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;margin-bottom:1.25rem;margin-top:.85rem;">
<div id="co-search-wrap" style="position:relative;min-width:280px;max-width:400px;flex:0 1 400px;">
    <div style="position:relative;">
        <svg style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);pointer-events:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
        <input id="co-search-input" type="text" autocomplete="off" spellcheck="false"
               value="{{ $company ? $company->name : '' }}"
               placeholder="Search by company name, owner, phone, or email…"
               style="width:100%;padding:.5rem .9rem .5rem 2.2rem;border:1px solid #d0d5dd;border-radius:6px;font-size:.875rem;color:#374151;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05);outline:none;">
        <button id="co-search-clear" type="button" title="Clear"
                style="display:{{ $company ? 'flex' : 'none' }};position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:.15rem;line-height:0;align-items:center;justify-content:center;"
                onclick="clearCompanySearch()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div id="co-search-dropdown"
         style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #d0d5dd;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:200;overflow:hidden;max-height:340px;overflow-y:auto;">
        <div id="co-search-results"></div>
    </div>
</div>

{{-- Recents --}}
<div id="co-recents-wrap" style="display:none;align-items:center;gap:.45rem;flex-wrap:wrap;">
    <span style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;">Recents:</span>
    <div id="co-recents" style="display:flex;gap:.35rem;flex-wrap:wrap;"></div>
</div>
</div>{{-- end flex row --}}

@if(!$company)
    <div style="text-align:center;padding:5rem 2rem;background:#fff;border-radius:12px;border:1px solid #e5e7eb;">
        <div style="font-size:3rem;margin-bottom:1rem;">🏢</div>
        <div style="font-size:1.05rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Select a company to view analytics</div>
        <div style="font-size:.875rem;color:#9ca3af;">Search above to load work order history, invoices, outstanding items, and revenue metrics for all members of a company.</div>
    </div>
@else

    {{-- Company info strip --}}
    <div style="background:#fff;border:1px solid #d0d5dd;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="display:flex;align-items:center;gap:1rem;">
            @php
                $coInitials = collect(explode(' ', $company->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
            @endphp
            <div style="width:50px;height:50px;border-radius:10px;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid #e5e7eb;">
                <span style="color:#fff;font-size:.9rem;font-weight:700;">{{ $coInitials }}</span>
            </div>
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:var(--primary);">{{ $company->name }}</div>
                @if($company->owner_name)
                    <div style="font-size:.78rem;color:#6b7280;font-weight:500;">{{ $company->owner_name }}</div>
                @endif
                <div style="font-size:.82rem;color:#555;">
                    @if($company->email){{ $company->email }}@endif
                    @if($company->phone) @if($company->email)&nbsp;·&nbsp;@endif {{ $company->phone }} @endif
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            @if($company->address_city)
                <div style="padding:.5rem .85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.15rem;">Location</div>
                    <div style="font-size:.9rem;font-weight:600;color:var(--primary);">{{ $company->address_city }}, {{ $company->address_state }}</div>
                </div>
            @endif
            <div style="padding:.5rem .85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.15rem;">Members</div>
                <div style="font-size:.9rem;font-weight:600;color:var(--primary);">{{ $company->members->count() }} active</div>
            </div>
            <a href="{{ route('admin.companies.show', $company) }}"
               style="padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;color:#374151;">
               Manage Company →
            </a>

            {{-- YTD Toggle --}}
            <div style="display:flex;border:1px solid #d0d5dd;border-radius:7px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.05);">
                <a href="{{ route('admin.analytics.companies', ['company_id' => $company->id]) }}"
                   style="padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;
                          background:{{ !$ytd ? 'var(--primary)' : '#fff' }};
                          color:{{ !$ytd ? '#fff' : '#4b5563' }};transition:background .15s,color .15s;">All Time</a>
                <a href="{{ route('admin.analytics.companies', ['company_id' => $company->id, 'ytd' => 1]) }}"
                   style="padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;
                          background:{{ $ytd ? 'var(--primary)' : '#fff' }};
                          color:{{ $ytd ? '#fff' : '#4b5563' }};
                          border-left:1px solid #d0d5dd;transition:background .15s,color .15s;">{{ now()->year }} YTD</a>
            </div>
        </div>
    </div>

    {{-- Metric cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:1rem;margin-bottom:1.25rem;">
        @php
            $metricCards = [
                ['label' => 'Total Work Orders',   'value' => number_format($totalWorkOrders),  'prefix' => '',  'color' => '#1A3C5E'],
                ['label' => 'Completed Orders',    'value' => number_format($totalCompleted),   'prefix' => '',  'color' => '#059669'],
                ['label' => 'Active / In Progress','value' => number_format($activeWorkOrders), 'prefix' => '',  'color' => '#0284c7'],
                ['label' => 'Total Revenue',       'value' => number_format($totalRevenue, 2),  'prefix' => '$', 'color' => '#7c3aed'],
            ];
        @endphp
        @foreach($metricCards as $card)
        <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;border-top:4px solid {{ $card['color'] }};padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div style="font-size:1.7rem;font-weight:700;color:{{ $card['color'] }};line-height:1.15;">{{ $card['prefix'] }}{{ $card['value'] }}</div>
            <div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;font-weight:500;">{{ $card['label'] }}</div>
            @if($ytd)<div style="font-size:.7rem;color:#b0bac5;margin-top:.2rem;">{{ now()->year }} year-to-date</div>@endif
        </div>
        @endforeach

        {{-- Uncollected / Outstanding card --}}
        @php
            $ucColor     = $pastDueCount > 0 ? '#dc2626' : '#ea580c';
            $ucFooterBg  = $pastDueCount > 0 ? '#fef2f2' : '#fff7ed';
            $ucFooterBdr = $pastDueCount > 0 ? '#fecaca' : '#fed7aa';
            $ucLblColor  = $pastDueCount > 0 ? '#991b1b' : '#9a3412';
        @endphp
        <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;border-top:4px solid {{ $ucColor }};box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:1.1rem 1.25rem;flex:1;">
                <div style="font-size:1.7rem;font-weight:700;color:{{ $ucColor }};line-height:1.15;">{{ number_format($awaitingPayment) }}</div>
                <div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;font-weight:500;">
                    Awaiting Payment
                    @if($pastDueCount > 0)
                    <span style="margin-left:.35rem;font-size:.68rem;font-weight:700;color:#dc2626;background:#fee2e2;border:1px solid #fca5a5;border-radius:999px;padding:.05rem .4rem;">{{ $pastDueCount }} Past Due</span>
                    @endif
                </div>
                @if($ytd)<div style="font-size:.7rem;color:#b0bac5;margin-top:.2rem;">{{ now()->year }} year-to-date</div>@endif
            </div>
            <div style="background:{{ $ucFooterBg }};border-top:1px solid {{ $ucFooterBdr }};padding:.5rem 1.25rem;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.72rem;font-weight:600;color:{{ $ucLblColor }};">Uncollected</span>
                <span style="font-size:.82rem;font-weight:800;color:{{ $ucColor }};">${{ number_format($uncollectedRevenue, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Pending follow-up alert --}}
    @if($hasPendingFollowUp)
    <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:.85rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <span style="font-size:1.1rem;flex-shrink:0;">⚠️</span>
        <div style="font-size:.875rem;font-weight:600;color:#92400e;flex:1;">Items needing follow-up:</div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            @if($needsConfirmation > 0)
            <a href="{{ route('admin.work-orders.index', ['queue' => 'pending_confirmation']) }}"
               style="font-size:.78rem;font-weight:700;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:999px;padding:.25rem .7rem;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;transition:background .15s,box-shadow .15s;"
               onmouseover="this.style.background='#fde68a';this.style.boxShadow='0 2px 8px rgba(146,64,14,.2)'"
               onmouseout="this.style.background='#fef3c7';this.style.boxShadow=''">
                ⏳ {{ $needsConfirmation }} Pending Confirmation
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            @endif
            @if($needsInvoice > 0)
            <a href="{{ route('admin.work-orders.index', ['queue' => 'prepare_invoice']) }}"
               style="font-size:.78rem;font-weight:700;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:999px;padding:.25rem .7rem;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;transition:background .15s,box-shadow .15s;"
               onmouseover="this.style.background='#6ee7b7';this.style.boxShadow='0 2px 8px rgba(6,95,70,.2)'"
               onmouseout="this.style.background='#d1fae5';this.style.boxShadow=''">
                📄 {{ $needsInvoice }} Ready to Invoice
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            @endif
            @if($pastDueCount > 0)
            <a href="{{ route('admin.invoices.index', ['tab' => 'billed', 'past_due' => 1]) }}"
               style="font-size:.78rem;font-weight:700;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:999px;padding:.25rem .7rem;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;transition:background .15s,box-shadow .15s;"
               onmouseover="this.style.background='#fca5a5';this.style.boxShadow='0 2px 8px rgba(153,27,27,.2)'"
               onmouseout="this.style.background='#fee2e2';this.style.boxShadow=''">
                🔴 {{ $pastDueCount }} Invoice{{ $pastDueCount > 1 ? 's' : '' }} Past Due
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Two-column layout --}}
    <div style="display:grid;grid-template-columns:1fr 300px;gap:1.25rem;align-items:start;">

        {{-- LEFT — work orders + invoices --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Recent Work Orders --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Recent Work Orders</div>
                    </div>
                    <a href="{{ route('admin.work-orders.index') }}" style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.8);text-decoration:none;white-space:nowrap;flex-shrink:0;">View All →</a>
                </div>
                @if($recentWorkOrders->isEmpty())
                    <div style="padding:2.5rem;text-align:center;color:#9ca3af;font-size:.875rem;">No work orders found.</div>
                @else
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Order</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Customer</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Services</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Status</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentWorkOrders as $wo)
                        <tr data-href="{{ route('admin.work-orders.show', [$wo, 'from' => 'company', 'from_id' => $company->id]) }}"
                            style="border-bottom:1px solid #f3f4f6;cursor:pointer;">
                            <td style="padding:.65rem 1rem;font-size:.875rem;font-weight:600;color:var(--primary);white-space:nowrap;">{{ $wo->woLabel() }}</td>
                            <td style="padding:.65rem 1rem;font-size:.82rem;color:#374151;white-space:nowrap;">{{ $wo->customer->name ?? '—' }}</td>
                            <td style="padding:.65rem 1rem;font-size:.8rem;color:#555;">{{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                            <td style="padding:.65rem 1rem;">
                                <span class="badge badge-{{ $wo->status }}">{{ str_replace('_',' ',$wo->status) }}</span>
                                @if($wo->confirmation_status === 'pending')
                                    <br><span style="font-size:.68rem;font-weight:700;color:#d97706;background:#fef3c7;border:1px solid #fde68a;border-radius:999px;padding:.05rem .4rem;white-space:nowrap;">⏳ Awaiting Confirm.</span>
                                @endif
                            </td>
                            <td style="padding:.65rem 1rem;font-size:.8rem;color:#6b7280;white-space:nowrap;">{{ $wo->created_at->format('M j, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Open Invoices --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Open Invoices</div>
                    </div>
                    <a href="{{ route('admin.invoices.index') }}" style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.8);text-decoration:none;white-space:nowrap;flex-shrink:0;">View All →</a>
                </div>
                @if($openInvoices->isEmpty())
                    <div style="padding:2.5rem;text-align:center;color:#9ca3af;font-size:.875rem;">No open invoices.</div>
                @else
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Invoice</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Customer</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Status</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Due</th>
                            <th style="padding:.55rem 1rem;text-align:right;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openInvoices as $inv)
                        @php
                            $isPastDue = $inv->status === 'issued' && $inv->due_date && $inv->due_date < now()->toDateString();
                        @endphp
                        <tr data-href="{{ route('admin.invoices.show', $inv) }}"
                            style="border-bottom:1px solid #f3f4f6;cursor:pointer;{{ $isPastDue ? 'background:#fff5f5;' : '' }}">
                            <td style="padding:.65rem 1rem;font-size:.875rem;font-weight:600;color:var(--primary);">
                                INV-{{ str_pad($inv->id, 4, '0', STR_PAD_LEFT) }}
                                @if($inv->work_order_id)
                                    <div style="font-size:.72rem;color:#9ca3af;font-weight:400;">{{ $inv->workOrder->woLabel() }}</div>
                                @endif
                            </td>
                            <td style="padding:.65rem 1rem;font-size:.82rem;color:#374151;">{{ $inv->workOrder?->customer?->name ?? '—' }}</td>
                            <td style="padding:.65rem 1rem;"><span class="badge badge-{{ $inv->status }}">{{ str_replace('_',' ',$inv->status) }}</span></td>
                            <td style="padding:.65rem 1rem;font-size:.82rem;white-space:nowrap;">
                                @if($inv->due_date)
                                    <span style="color:{{ $isPastDue ? '#dc2626' : '#374151' }};font-weight:{{ $isPastDue ? '700' : '400' }};">
                                        {{ \Carbon\Carbon::parse($inv->due_date)->format('M j, Y') }}
                                    </span>
                                    @if($isPastDue)
                                        <span style="font-size:.68rem;font-weight:700;color:#dc2626;background:#fee2e2;border:1px solid #fca5a5;border-radius:999px;padding:.05rem .35rem;margin-left:.3rem;">Past Due</span>
                                    @endif
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td style="padding:.65rem 1rem;font-size:.9rem;font-weight:700;color:#111;text-align:right;">${{ number_format($inv->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>{{-- end left column --}}

        {{-- RIGHT — members + sites --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Company Members --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Linked Customers</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $company->members->count() }} active {{ Str::plural('member', $company->members->count()) }}</div>
                    </div>
                </div>
                @if($company->members->isEmpty())
                    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.82rem;">No active members.</div>
                @else
                    @foreach($company->members as $member)
                    @php
                        $mi           = collect(explode(' ', $member->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                        $completedCnt = $memberCompletedCounts[$member->id] ?? 0;
                    @endphp
                    <div class="member-card" id="member-card-{{ $member->id }}"
                         style="padding:.65rem 1.1rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:.65rem;transition:background .1s;">

                        {{-- Avatar --}}
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="color:#fff;font-size:.63rem;font-weight:700;">{{ $mi }}</span>
                        </div>

                        {{-- Info (clickable → customer analytics) --}}
                        <a href="{{ route('admin.analytics.customers', ['customer_id' => $member->id]) }}"
                           style="min-width:0;flex:1;text-decoration:none;color:inherit;">
                            <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                                <span style="font-size:.83rem;font-weight:600;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->name }}</span>
                                @if($completedCnt > 0)
                                <span style="font-size:.65rem;font-weight:700;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:999px;padding:.05rem .4rem;white-space:nowrap;flex-shrink:0;">{{ $completedCnt }} completed</span>
                                @endif
                            </div>
                            @if($member->title)<div style="font-size:.71rem;color:#6b7280;">{{ $member->title }}</div>@endif
                            <div style="font-size:.71rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->email }}</div>
                        </a>

                        {{-- Unlink button --}}
                        <button type="button"
                                onclick="confirmUnlink({{ $member->id }}, '{{ addslashes($member->name) }}')"
                                title="Unlink from company"
                                style="width:24px;height:24px;border-radius:50%;border:1.5px solid #e5e7eb;background:#fff;color:#9ca3af;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;line-height:1;transition:border-color .15s,color .15s,background .15s;"
                                onmouseover="this.style.borderColor='#dc2626';this.style.color='#dc2626';this.style.background='#fef2f2';"
                                onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#9ca3af';this.style.background='#fff';">
                            &minus;
                        </button>

                        {{-- Hidden unlink form --}}
                        <form id="unlink-form-{{ $member->id }}" method="POST"
                              action="{{ route('admin.companies.members.detach', [$company, $member]) }}"
                              style="display:none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Service Sites --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <div>
                            <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Service Sites</div>
                            <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $company->sites->count() }} active {{ Str::plural('site', $company->sites->count()) }}</div>
                        </div>
                    </div>
                    <button type="button" onclick="openAddSite()"
                            title="Add site"
                            style="display:flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;cursor:pointer;flex-shrink:0;">
                        + Add Site
                    </button>
                </div>
                @if($company->sites->isEmpty())
                    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.82rem;">No active sites on file.</div>
                @else
                    @foreach($company->sites as $site)
                    <div class="co-site-row"
                         style="position:relative;padding:.7rem 1.1rem;padding-right:2.75rem;border-bottom:1px solid #f3f4f6;"
                         data-site="{{ json_encode(['id' => $site->id, 'label' => $site->label ?? '', 'street' => $site->street ?? '', 'city' => $site->city ?? '', 'state' => $site->state ?? '', 'zip' => $site->zip ?? '', 'county' => $site->county ?? '']) }}"
                         data-update-url="{{ route('admin.companies.sites.update', [$company, $site]) }}">
                        <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;margin-bottom:.15rem;">
                            <span style="font-size:.83rem;font-weight:600;color:#111;">{{ $site->label ?: 'Unlabeled Site' }}</span>
                            @if($site->is_default)
                                <span style="font-size:.65rem;background:#d1fae5;color:#065f46;padding:.1rem .45rem;border-radius:999px;font-weight:700;flex-shrink:0;">Default</span>
                            @else
                                <form method="POST" action="{{ route('admin.companies.sites.default', [$company, $site]) }}" style="margin:0;display:inline-flex;">
                                    @csrf
                                    <button type="submit"
                                            style="font-size:.63rem;background:#f8fafc;color:#9ca3af;padding:.1rem .45rem;border-radius:999px;font-weight:600;border:1px solid #e5e7eb;cursor:pointer;line-height:1.5;transition:background .12s,color .12s,border-color .12s;"
                                            onmouseover="this.style.background='#d1fae5';this.style.color='#065f46';this.style.borderColor='#6ee7b7'"
                                            onmouseout="this.style.background='#f8fafc';this.style.color='#9ca3af';this.style.borderColor='#e5e7eb'">
                                        Set Default
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">{{ $site->street }}</div>
                        <div style="font-size:.75rem;color:#6b7280;">{{ $site->city }}, {{ $site->state }} {{ $site->zip }}</div>
                        <button type="button"
                                class="co-site-edit-btn"
                                onclick="openSiteEdit(this.closest('.co-site-row'))"
                                title="Edit site"
                                style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:6px;background:#f1f5f9;border:1px solid #e2e8f0;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s,background .12s,border-color .12s;flex-shrink:0;"
                                onmouseover="this.style.background='#dbeafe';this.style.borderColor='var(--accent)'"
                                onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#e2e8f0'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                    </div>
                    @endforeach
                @endif
            </div>

        </div>{{-- end right column --}}
    </div>{{-- end grid --}}

    {{-- Add Site Modal --}}
    <div id="site-add-modal"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.22);padding:1.75rem;width:100%;max-width:440px;margin:1rem;"
             onclick="event.stopPropagation()">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.35rem;">
                <div style="font-size:1rem;font-weight:700;color:var(--primary);">Add Service Site</div>
                <button type="button" onclick="closeAddSite()"
                        style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:.2rem;line-height:0;"
                        title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.companies.sites.store', $company) }}">
                @csrf
                <div style="display:grid;gap:.85rem;">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Label / Site Name *</label>
                        <input type="text" name="label" required maxlength="100"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Street Address *</label>
                        <input type="text" name="street" required maxlength="255"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 58px 96px;gap:.55rem;">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">City *</label>
                            <input type="text" name="city" required maxlength="100"
                                   style="width:100%;padding:.5rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">State *</label>
                            <input type="text" name="state" required maxlength="2" placeholder="TX"
                                   style="width:100%;padding:.5rem .4rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;text-align:center;text-transform:uppercase;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">ZIP *</label>
                            <input type="text" name="zip" required maxlength="10"
                                   style="width:100%;padding:.5rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">County</label>
                        <input type="text" name="county" maxlength="100"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                </div>
                <div style="margin-top:1.5rem;display:flex;gap:.65rem;justify-content:flex-end;">
                    <button type="button" onclick="closeAddSite()"
                            style="padding:.48rem 1.1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;font-size:.875rem;font-weight:600;cursor:pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:.48rem 1.35rem;border:none;border-radius:6px;background:var(--primary);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;transition:background .12s;"
                            onmouseover="this.style.background='var(--accent)'"
                            onmouseout="this.style.background='var(--primary)'">
                        Add Site
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Site Edit Modal --}}
    <div id="site-edit-modal"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.22);padding:1.75rem;width:100%;max-width:440px;margin:1rem;"
             onclick="event.stopPropagation()">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.35rem;">
                <div style="font-size:1rem;font-weight:700;color:var(--primary);">Edit Service Site</div>
                <button type="button" onclick="closeSiteEdit()"
                        style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:.2rem;line-height:0;"
                        title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="site-edit-form" method="POST" action="">
                @csrf
                @method('PATCH')
                <div style="display:grid;gap:.85rem;">
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Label / Site Name *</label>
                        <input type="text" name="label" id="site-edit-label" required maxlength="100"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Street Address *</label>
                        <input type="text" name="street" id="site-edit-street" required maxlength="255"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 58px 96px;gap:.55rem;">
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">City *</label>
                            <input type="text" name="city" id="site-edit-city" required maxlength="100"
                                   style="width:100%;padding:.5rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">State *</label>
                            <input type="text" name="state" id="site-edit-state" required maxlength="2" placeholder="TX"
                                   style="width:100%;padding:.5rem .4rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;text-align:center;text-transform:uppercase;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                        <div>
                            <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">ZIP *</label>
                            <input type="text" name="zip" id="site-edit-zip" required maxlength="10"
                                   style="width:100%;padding:.5rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                                   onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:.25rem;">County</label>
                        <input type="text" name="county" id="site-edit-county" maxlength="100"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.875rem;box-sizing:border-box;outline:none;"
                               onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
                    </div>
                </div>
                <div style="margin-top:1.5rem;display:flex;gap:.65rem;justify-content:flex-end;">
                    <button type="button" onclick="closeSiteEdit()"
                            style="padding:.48rem 1.1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;font-size:.875rem;font-weight:600;cursor:pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:.48rem 1.35rem;border:none;border-radius:6px;background:var(--primary);color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;transition:background .12s;"
                            onmouseover="this.style.background='var(--accent)'"
                            onmouseout="this.style.background='var(--primary)'">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

@endif

<script>
(function () {
    const input    = document.getElementById('co-search-input');
    const dropdown = document.getElementById('co-search-dropdown');
    const results  = document.getElementById('co-search-results');
    const clearBtn = document.getElementById('co-search-clear');
    const searchUrl = '{{ route('admin.analytics.companies.search') }}';
    const baseUrl   = '{{ route('admin.analytics.companies') }}';
    @if($company)
    const currentId = {{ $company->id }};
    @else
    const currentId = null;
    @endif

    let debounceTimer = null;
    let activeIndex   = -1;

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        clearBtn.style.display = q ? 'flex' : 'none';
        if (q.length < 2) { closeDropdown(); return; }
        debounceTimer = setTimeout(() => fetchResults(q), 220);
    });

    input.addEventListener('keydown', function (e) {
        const items = results.querySelectorAll('.co-result-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, items.length - 1);
            highlightItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, 0);
            highlightItem(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndex >= 0 && items[activeIndex]) items[activeIndex].click();
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) fetchResults(this.value.trim());
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('co-search-wrap').contains(e.target)) closeDropdown();
    });

    function fetchResults(q) {
        fetch(searchUrl + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => renderResults(data));
    }

    function renderResults(data) {
        activeIndex = -1;
        if (!data.length) {
            results.innerHTML = '<div style="padding:1rem 1.1rem;font-size:.85rem;color:#9ca3af;text-align:center;">No companies found</div>';
            openDropdown();
            return;
        }
        results.innerHTML = data.map(c => {
            const isActive = c.id === currentId;
            const loc = c.city ? escHtml(c.city) + (c.state ? ', ' + escHtml(c.state) : '') : '';
            return `<div class="co-result-item" data-id="${c.id}" data-name="${escHtml(c.name)}"
                style="padding:.65rem 1rem;cursor:pointer;display:flex;align-items:center;gap:.75rem;
                       border-bottom:1px solid #f3f4f6;background:${isActive ? '#f0f7ff' : '#fff'};transition:background .1s;"
                onmouseover="this.style.background='#f0f7ff'"
                onmouseout="this.style.background='${isActive ? '#f0f7ff' : '#fff'}'">
                <div style="width:38px;height:38px;border-radius:8px;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#fff;font-size:.72rem;font-weight:700;">${initials(c.name)}</span>
                </div>
                <div style="min-width:0;flex:1;">
                    <div style="font-size:.875rem;font-weight:600;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${escHtml(c.name)}${isActive ? ' <span style="font-size:.65rem;background:#dbeafe;color:#1e40af;padding:.1rem .4rem;border-radius:999px;margin-left:.3rem;">Current</span>' : ''}
                    </div>
                    <div style="font-size:.75rem;color:#6b7280;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${c.owner_name ? escHtml(c.owner_name) + ' · ' : ''}${loc ? loc + ' · ' : ''}<span style="color:#94a3b8;">${c.member_count} ${c.member_count === 1 ? 'member' : 'members'}</span>
                    </div>
                </div>
            </div>`;
        }).join('');

        results.querySelectorAll('.co-result-item').forEach(el => {
            el.addEventListener('click', function () {
                input.value = this.dataset.name;
                clearBtn.style.display = 'flex';
                closeDropdown();
                window.location.href = baseUrl + '?company_id=' + this.dataset.id;
            });
        });

        openDropdown();
    }

    function highlightItem(items) {
        items.forEach((el, i) => {
            el.style.background = i === activeIndex ? '#e0f0ff' : '#fff';
        });
        if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
    }

    function openDropdown()  { dropdown.style.display = 'block'; }
    function closeDropdown() { dropdown.style.display = 'none'; activeIndex = -1; }

    function initials(name) {
        return name.split(' ').slice(0, 2).map(w => (w[0] || '').toUpperCase()).join('');
    }

    function escHtml(s) {
        if (!s) return '';
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    window.clearCompanySearch = function () {
        input.value = '';
        clearBtn.style.display = 'none';
        closeDropdown();
        window.location.href = baseUrl;
    };

    // Row click → navigate
    document.querySelectorAll('tr[data-href]').forEach(row => {
        row.addEventListener('click', () => window.location.href = row.dataset.href);
        row.addEventListener('mouseenter', () => row.style.background = '#f8fafc');
        row.addEventListener('mouseleave', () => row.style.background = '');
    });

    // Unlink confirmation
    window._pendingUnlinkId = null;

    window.confirmUnlink = function (memberId, memberName) {
        window._pendingUnlinkId = memberId;
        document.getElementById('unlink-modal-name').textContent = memberName;
        document.getElementById('unlink-modal').style.display = 'flex';
    };

    window.closeUnlinkModal = function () {
        window._pendingUnlinkId = null;
        document.getElementById('unlink-modal').style.display = 'none';
    };

    window.doUnlink = function () {
        if (window._pendingUnlinkId) {
            document.getElementById('unlink-form-' + window._pendingUnlinkId)?.submit();
        }
    };
})();

// Recents — company analytics
(function () {
    const RECENTS_KEY    = 'datatel_co_recents';
    const recentsBaseUrl = '{{ route('admin.analytics.companies') }}';

    @if($company)
    (function () {
        const entry = { id: {{ $company->id }}, name: {!! json_encode($company->name) !!} };
        let stored = [];
        try { stored = JSON.parse(localStorage.getItem(RECENTS_KEY) || '[]'); } catch (e) {}
        stored = stored.filter(function (r) { return r.id !== entry.id; });
        stored.unshift(entry);
        localStorage.setItem(RECENTS_KEY, JSON.stringify(stored.slice(0, 3)));
    })();
    @endif

    var recents = [];
    try { recents = JSON.parse(localStorage.getItem(RECENTS_KEY) || '[]'); } catch (e) {}

    var currentCoId = @if($company){{ $company->id }}@else null @endif;
    var pills = recents.filter(function (r) { return r.id !== currentCoId; });

    var wrap      = document.getElementById('co-recents-wrap');
    var container = document.getElementById('co-recents');

    if (wrap && container && pills.length) {
        container.innerHTML = pills.map(function (r) {
            var parts = r.name.split(' ').slice(0, 2);
            var ini   = parts.map(function (w) { return (w[0] || '').toUpperCase(); }).join('');
            var label = r.name.length > 22 ? r.name.slice(0, 21) + '…' : r.name;
            return '<a href="' + recentsBaseUrl + '?company_id=' + r.id + '"'
                + ' style="display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .65rem .25rem .3rem;'
                + 'background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;'
                + 'text-decoration:none;font-size:.75rem;font-weight:600;color:#374151;'
                + 'transition:background .12s,border-color .12s;"'
                + ' onmouseover="this.style.background=\'#e0f2fe\';this.style.borderColor=\'var(--accent)\'"'
                + ' onmouseout="this.style.background=\'#f1f5f9\';this.style.borderColor=\'#e2e8f0\'">'
                + '<span style="width:20px;height:20px;border-radius:50%;background:var(--primary);'
                + 'display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
                + '<span style="color:#fff;font-size:.55rem;font-weight:700;">' + ini + '</span>'
                + '</span>'
                + label
                + '</a>';
        }).join('');
        wrap.style.display = 'flex';
    }
})();

@if($company)
// Service Site — hover + edit modal
(function () {
    document.querySelectorAll('.co-site-row').forEach(function (row) {
        var btn = row.querySelector('.co-site-edit-btn');
        if (!btn) return;
        row.addEventListener('mouseenter', function () { btn.style.opacity = '1'; });
        row.addEventListener('mouseleave', function () { btn.style.opacity = '0'; });
    });

    window.openAddSite = function () {
        document.getElementById('site-add-modal').style.display = 'flex';
        setTimeout(function () {
            var first = document.querySelector('#site-add-modal input[name="label"]');
            if (first) first.focus();
        }, 50);
    };

    window.closeAddSite = function () {
        document.getElementById('site-add-modal').style.display = 'none';
    };

    document.getElementById('site-add-modal').addEventListener('click', function (e) {
        if (e.target === this) closeAddSite();
    });

    window.openSiteEdit = function (row) {
        var data = JSON.parse(row.dataset.site);
        document.getElementById('site-edit-form').action = row.dataset.updateUrl;
        document.getElementById('site-edit-label').value  = data.label  || '';
        document.getElementById('site-edit-street').value = data.street || '';
        document.getElementById('site-edit-city').value   = data.city   || '';
        document.getElementById('site-edit-state').value  = (data.state || '').toUpperCase();
        document.getElementById('site-edit-zip').value    = data.zip    || '';
        document.getElementById('site-edit-county').value = data.county || '';
        document.getElementById('site-edit-modal').style.display = 'flex';
        setTimeout(function () { document.getElementById('site-edit-label').focus(); }, 50);
    };

    window.closeSiteEdit = function () {
        document.getElementById('site-edit-modal').style.display = 'none';
    };

    document.getElementById('site-edit-modal').addEventListener('click', function (e) {
        if (e.target === this) closeSiteEdit();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeSiteEdit(); closeAddSite(); }
    });
})();
@endif
</script>

{{-- Unlink confirmation modal --}}
<div id="unlink-modal"
     onclick="if(event.target===this) closeUnlinkModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.2);padding:1.75rem;width:100%;max-width:400px;margin:1rem;">
        <div style="display:flex;align-items:flex-start;gap:.85rem;margin-bottom:1.25rem;">
            <div style="width:40px;height:40px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <div style="font-size:1rem;font-weight:700;color:#111;margin-bottom:.3rem;">Unlink Customer</div>
                <div style="font-size:.875rem;color:#6b7280;">
                    Remove <strong id="unlink-modal-name"></strong> from this company? They will lose access to company work orders and can be re-linked later.
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:.65rem;">
            <button type="button" onclick="closeUnlinkModal()"
                    style="padding:.5rem 1.1rem;font-size:.875rem;font-weight:600;border:1px solid #d0d5dd;border-radius:7px;background:#fff;color:#374151;cursor:pointer;">
                Cancel
            </button>
            <button type="button" onclick="doUnlink()"
                    style="padding:.5rem 1.1rem;font-size:.875rem;font-weight:600;border:none;border-radius:7px;background:#dc2626;color:#fff;cursor:pointer;">
                Unlink Customer
            </button>
        </div>
    </div>
</div>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">ANALYTICS</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><line x1="9" y1="9" x2="9" y2="9.01"/><line x1="9" y1="12" x2="9" y2="12.01"/><line x1="9" y1="15" x2="9" y2="15.01"/></svg>
        Company Analytics
    </h1>
</div>
@endpush
