@extends('layouts.admin')
@section('title', 'Customer Analytics')

@section('content')
{{-- Live customer search + recents --}}
<div style="display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;margin-bottom:1.25rem;margin-top:.85rem;">
<div id="cust-search-wrap" style="position:relative;min-width:280px;max-width:400px;flex:0 1 400px;">
    <div style="position:relative;">
        <svg style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);pointer-events:none;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
        <input id="cust-search-input" type="text" autocomplete="off" spellcheck="false"
               value="{{ $customer ? $customer->name : '' }}"
               placeholder="Search by name, company, WO#, or phone…"
               style="width:100%;padding:.5rem .9rem .5rem 2.2rem;border:1px solid #d0d5dd;border-radius:6px;font-size:.875rem;color:#374151;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.05);outline:none;">
        <button id="cust-search-clear" type="button" title="Clear"
                style="display:{{ $customer ? 'flex' : 'none' }};position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:.15rem;line-height:0;align-items:center;justify-content:center;"
                onclick="clearCustomerSearch()">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div id="cust-search-dropdown"
         style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #d0d5dd;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:200;overflow:hidden;max-height:340px;overflow-y:auto;">
        <div id="cust-search-results"></div>
    </div>
</div>

{{-- Recents --}}
<div id="cust-recents-wrap" style="display:none;align-items:center;gap:.45rem;flex-wrap:wrap;">
    <span style="font-size:.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;">Recents:</span>
    <div id="cust-recents" style="display:flex;gap:.35rem;flex-wrap:wrap;"></div>
</div>
</div>{{-- end flex row --}}

@if(!$customer)
    <div style="text-align:center;padding:5rem 2rem;background:#fff;border-radius:12px;border:1px solid #e5e7eb;">
        <div style="font-size:3rem;margin-bottom:1rem;">📊</div>
        <div style="font-size:1.05rem;font-weight:600;color:#374151;margin-bottom:.4rem;">Select a customer to view analytics</div>
        <div style="font-size:.875rem;color:#9ca3af;">Choose from the dropdown above to load work order history, invoices, site details, and revenue metrics.</div>
    </div>
@else

    {{-- Customer info strip --}}
    <div style="background:#fff;border:1px solid #d0d5dd;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);">
        <div style="display:flex;align-items:center;gap:1rem;">
            @php
                $photoPath = $customer->profile_photo ? storage_path('app/profile-photos/'.$customer->profile_photo) : null;
                $initials  = collect(explode(' ', $customer->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
            @endphp
            <div style="width:50px;height:50px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;border:2px solid #e5e7eb;">
                @if($photoPath && file_exists($photoPath))
                    <img src="{{ route('users.photo', $customer) }}" style="width:100%;height:100%;object-fit:cover;" alt="{{ $customer->name }}">
                @else
                    <span style="color:#fff;font-size:.9rem;font-weight:700;">{{ $initials }}</span>
                @endif
            </div>
            <div>
                <div style="font-size:1.05rem;font-weight:700;color:var(--primary);">{{ $customer->name }}</div>
                @if($customer->title)<div style="font-size:.78rem;color:#6b7280;font-weight:500;">{{ $customer->title }}</div>@endif
                <div style="font-size:.82rem;color:#555;">
                    {{ $customer->email }}
                    @if($customer->phone) &nbsp;·&nbsp; {{ $customer->phone }} @endif
                </div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">
            <button type="button" onclick="openEditCustomerModal()"
                    style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;background:#f8fafc;border:1px solid #d0d5dd;border-radius:7px;font-size:.82rem;font-weight:600;color:#374151;cursor:pointer;transition:background .15s,border-color .15s;"
                    onmouseover="this.style.background='#f0f7ff';this.style.borderColor='var(--accent)'"
                    onmouseout="this.style.background='#f8fafc';this.style.borderColor='#d0d5dd'">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Customer
            </button>
            @if($company)
                <a href="{{ route('admin.analytics.companies', ['company_id' => $company->id]) }}"
                   style="padding:.5rem .85rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;display:block;transition:border-color .15s,background .15s;"
                   onmouseover="this.style.borderColor='var(--accent)';this.style.background='#f0f7ff';"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';"
                   title="View Company Analytics">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:.15rem;">Company ↗</div>
                    <div style="font-size:.9rem;font-weight:600;color:var(--primary);">{{ $company->name }}</div>
                </a>
            @endif

            {{-- YTD Toggle --}}
            <div style="display:flex;border:1px solid #d0d5dd;border-radius:7px;overflow:hidden;box-shadow:0 1px 2px rgba(0,0,0,.05);">
                <a href="{{ route('admin.analytics.customers', ['customer_id' => $customer->id]) }}"
                   style="padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;
                          background:{{ !$ytd ? 'var(--primary)' : '#fff' }};
                          color:{{ !$ytd ? '#fff' : '#4b5563' }};transition:background .15s,color .15s;">All Time</a>
                <a href="{{ route('admin.analytics.customers', ['customer_id' => $customer->id, 'ytd' => 1]) }}"
                   style="padding:.45rem 1rem;font-size:.82rem;font-weight:600;text-decoration:none;
                          background:{{ $ytd ? 'var(--primary)' : '#fff' }};
                          color:{{ $ytd ? '#fff' : '#4b5563' }};
                          border-left:1px solid #d0d5dd;transition:background .15s,color .15s;">{{ now()->year }} YTD</a>
            </div>
        </div>
    </div>

    {{-- Metric cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:1rem;margin-bottom:1.25rem;">
        @php
            $cards = [
                ['label' => 'Completed Work Orders', 'value' => number_format($totalCompleted),        'prefix' => '',  'color' => '#059669'],
                ['label' => 'Avg Invoice Total',      'value' => number_format($avgInvoice, 2),         'prefix' => '$', 'color' => '#2E86C1'],
                ['label' => 'Highest Invoice',        'value' => number_format($highestInvoice, 2),     'prefix' => '$', 'color' => '#7c3aed'],
                ['label' => 'Total Revenue',          'value' => number_format($totalRevenue, 2),       'prefix' => '$', 'color' => '#1A3C5E'],
            ];
        @endphp
        @foreach($cards as $card)
        <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;border-top:4px solid {{ $card['color'] }};padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div style="font-size:1.7rem;font-weight:700;color:{{ $card['color'] }};line-height:1.15;">{{ $card['prefix'] }}{{ $card['value'] }}</div>
            <div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;font-weight:500;">{{ $card['label'] }}</div>
            @if($ytd)<div style="font-size:.7rem;color:#b0bac5;margin-top:.2rem;">{{ now()->year }} year-to-date</div>@endif
        </div>
        @endforeach

        {{-- Pending Payment card --}}
        @php
            $ppColor      = $hasPastDueInvoice ? '#dc2626' : '#ea580c';
            $ppFooterBg   = $hasPastDueInvoice ? '#fef2f2' : '#fff7ed';
            $ppFooterBdr  = $hasPastDueInvoice ? '#fecaca' : '#fed7aa';
            $ppLabelColor = $hasPastDueInvoice ? '#991b1b' : '#9a3412';
        @endphp
        <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;border-top:4px solid {{ $ppColor }};box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:1.1rem 1.25rem;flex:1;">
                <div style="font-size:1.7rem;font-weight:700;color:{{ $ppColor }};line-height:1.15;">{{ number_format($pendingPaymentOrders) }}</div>
                <div style="font-size:.78rem;color:#6b7280;margin-top:.3rem;font-weight:500;">
                    Pending Payment
                    @if($hasPastDueInvoice)
                    <span style="margin-left:.35rem;font-size:.68rem;font-weight:700;color:#dc2626;background:#fee2e2;border:1px solid #fca5a5;border-radius:999px;padding:.05rem .4rem;">Past Due</span>
                    @endif
                </div>
                @if($ytd)<div style="font-size:.7rem;color:#b0bac5;margin-top:.2rem;">{{ now()->year }} year-to-date</div>@endif
            </div>
            <div style="background:{{ $ppFooterBg }};border-top:1px solid {{ $ppFooterBdr }};padding:.5rem 1.25rem;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.72rem;font-weight:600;color:{{ $ppLabelColor }};">Uncollected</span>
                <span style="font-size:.82rem;font-weight:800;color:{{ $ppColor }};">${{ number_format($uncollectedRevenue, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Two-column layout --}}
    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.25rem;align-items:start;">

        {{-- LEFT — work orders + invoices --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Last 10 Work Orders --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Last 10 Work Orders</div>
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
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Services</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Status</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentWorkOrders as $wo)
                        <tr data-href="{{ route('admin.work-orders.show', $wo) }}"
                            style="border-bottom:1px solid #f3f4f6;cursor:pointer;">
                            <td style="padding:.65rem 1rem;font-size:.875rem;font-weight:600;color:var(--primary);white-space:nowrap;">{{ $wo->woLabel() }}</td>
                            <td style="padding:.65rem 1rem;font-size:.8rem;color:#555;">{{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
                            <td style="padding:.65rem 1rem;"><span class="badge badge-{{ $wo->status }}">{{ str_replace('_',' ',$wo->status) }}</span></td>
                            <td style="padding:.65rem 1rem;font-size:.8rem;color:#6b7280;white-space:nowrap;">{{ $wo->created_at->format('M j, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Top 5 Invoices --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Top 5 Invoices by Value</div>
                    </div>
                    <a href="{{ route('admin.invoices.index', ['customer_id' => $customer->id, 'tab' => 'all_active']) }}" style="font-size:.75rem;font-weight:600;color:rgba(255,255,255,.8);text-decoration:none;white-space:nowrap;flex-shrink:0;">View All →</a>
                </div>
                @if($topInvoices->isEmpty())
                    <div style="padding:2.5rem;text-align:center;color:#9ca3af;font-size:.875rem;">No invoices found.</div>
                @else
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Invoice</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Work Order</th>
                            <th style="padding:.55rem 1rem;text-align:left;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Status</th>
                            <th style="padding:.55rem 1rem;text-align:right;font-size:.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;text-transform:uppercase;letter-spacing:.04em;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topInvoices as $inv)
                        <tr data-href="{{ route('admin.invoices.show', $inv) }}"
                            style="border-bottom:1px solid #f3f4f6;cursor:pointer;">
                            <td style="padding:.65rem 1rem;font-size:.875rem;font-weight:600;color:var(--primary);">INV-{{ str_pad($inv->id,4,'0',STR_PAD_LEFT) }}</td>
                            <td style="padding:.65rem 1rem;font-size:.82rem;color:#555;">
                                @if($inv->work_order_id)
                                    {{ $inv->workOrder->woLabel() }}
                                @else
                                    <span style="font-size:.72rem;background:#fef3c7;color:#92400e;padding:.15rem .45rem;border-radius:999px;font-weight:700;">Standalone</span>
                                @endif
                            </td>
                            <td style="padding:.65rem 1rem;"><span class="badge badge-{{ $inv->status }}">{{ str_replace('_',' ',$inv->status) }}</span></td>
                            <td style="padding:.65rem 1rem;font-size:.9rem;font-weight:700;color:#111;text-align:right;">${{ number_format($inv->total,2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>{{-- end left column --}}

        {{-- RIGHT — company accounts + sites --}}
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

            {{-- Company Accounts --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">{{ $company ? $company->name : 'Company Accounts' }}</div>
                        @if($company)
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $companyMembers->count() }} active {{ Str::plural('member', $companyMembers->count()) }}</div>
                        @endif
                    </div>
                </div>
                @if(!$company)
                    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.82rem;">Customer is not linked to a company.</div>
                @elseif($companyMembers->isEmpty())
                    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.82rem;">No active members found.</div>
                @else
                    @foreach($companyMembers as $member)
                    <div style="padding:.7rem 1.1rem;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:.7rem;">
                        @php $mi = collect(explode(' ', $member->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join(''); @endphp
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <span style="color:#fff;font-size:.68rem;font-weight:700;">{{ $mi }}</span>
                        </div>
                        <div style="min-width:0;flex:1;">
                            <div style="font-size:.85rem;font-weight:600;color:#111;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                                <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $member->name }}</span>
                                @if($member->id === $customer->id)
                                    <span style="font-size:.68rem;background:#dbeafe;color:#1e40af;padding:.1rem .4rem;border-radius:999px;font-weight:700;flex-shrink:0;">Viewing</span>
                                @endif
                            </div>
                            @if($member->title)<div style="font-size:.73rem;color:#6b7280;">{{ $member->title }}</div>@endif
                            <div style="font-size:.73rem;color:#9ca3af;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $member->email }}</div>
                        </div>
                        <a href="{{ route('admin.users.show', $member) }}" style="font-size:.72rem;color:var(--accent);text-decoration:none;flex-shrink:0;" title="View account">→</a>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Service Sites --}}
            <div style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Service Sites</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $sites->count() }} active {{ Str::plural('site', $sites->count()) }}</div>
                    </div>
                </div>
                @if($sites->isEmpty())
                    <div style="padding:1.5rem;text-align:center;color:#9ca3af;font-size:.82rem;">No active sites on file.</div>
                @else
                    @foreach($sites as $site)
                    <div style="padding:.75rem 1.1rem;border-bottom:1px solid #f3f4f6;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.2rem;">
                            <span style="font-size:.875rem;font-weight:600;color:#111;">{{ $site->label ?: 'Unlabeled Site' }}</span>
                            @if($site->is_default)
                                <span style="font-size:.68rem;background:#d1fae5;color:#065f46;padding:.1rem .45rem;border-radius:999px;font-weight:700;">Default</span>
                            @endif
                        </div>
                        <div style="font-size:.78rem;color:#6b7280;">{{ $site->street }}</div>
                        <div style="font-size:.78rem;color:#6b7280;">{{ $site->city }}, {{ $site->state }} {{ $site->zip }}</div>
                    </div>
                    @endforeach
                @endif
            </div>

        </div>{{-- end right column --}}
    </div>{{-- end grid --}}

@if($customer)
{{-- ── Edit Customer Modal ── --}}
<div id="edit-customer-modal" onclick="if(event.target===this)closeEditCustomerModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:560px;max-height:90vh;display:flex;flex-direction:column;">

        {{-- Header --}}
        <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--primary);">Edit Customer — {{ $customer->name }}</h3>
            <button type="button" onclick="closeEditCustomerModal()"
                    style="background:none;border:none;font-size:1.4rem;color:#9ca3af;cursor:pointer;line-height:1;padding:.1rem .3rem;">×</button>
        </div>

        {{-- Form --}}
        <form id="edit-customer-form" method="POST" action="{{ route('admin.users.update', $customer) }}" enctype="multipart/form-data"
              style="overflow-y:auto;flex:1;padding:1.4rem;display:grid;gap:1rem;">
            @csrf @method('PATCH')
            <input type="hidden" name="_redirect_to" value="{{ url()->current() }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">

            {{-- Profile photo --}}
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.4rem;">Profile Photo</label>
                <div style="display:flex;align-items:center;gap:1rem;">
                    @php $photoPath = $customer->profile_photo ? storage_path('app/profile-photos/'.$customer->profile_photo) : null; @endphp
                    @if($photoPath && file_exists($photoPath))
                        <img src="{{ route('users.photo', $customer) }}" alt="{{ $customer->name }}"
                             style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;flex-shrink:0;">
                    @else
                        <div style="width:64px;height:64px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#9ca3af;flex-shrink:0;">👤</div>
                    @endif
                    <div style="flex:1;">
                        <input type="file" name="profile_photo" accept="image/*" style="font-size:.85rem;width:100%;">
                        <p style="font-size:.76rem;color:#999;margin:.25rem 0 0;">JPG, PNG, GIF or WebP · max 4 MB</p>
                        @if($customer->profile_photo)
                            <label style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;font-weight:400;color:#dc2626;cursor:pointer;margin-top:.2rem;">
                                <input type="checkbox" name="remove_photo" value="1" style="width:auto;margin:0;"> Remove current photo
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Name / Title --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           style="width:100%;padding:.52rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Title <span style="font-weight:400;color:#888;">(job title)</span></label>
                    <input type="text" name="title" value="{{ old('title', $customer->title) }}" placeholder="e.g. Network Engineer"
                           style="width:100%;padding:.52rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Email *</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" required
                       style="width:100%;padding:.52rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>

            {{-- Phone --}}
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                       style="width:100%;padding:.52rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>

            {{-- Status --}}
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Status *</label>
                <select name="status" required
                        style="width:100%;padding:.52rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;background:#fff;">
                    <option value="active"   {{ old('status', $customer->status) === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="pending"  {{ old('status', $customer->status) === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="inactive" {{ old('status', $customer->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            {{-- Role (hidden — always customer) --}}
            <input type="hidden" name="role" value="customer">

            {{-- Company --}}
            <div style="padding-top:.25rem;border-top:1px solid #f0f0f0;">
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.4rem;">Company</label>
                @php
                    $ecInitCoId   = old('company_id', $currentCompanyId ?? '');
                    $ecInitCoName = $ecInitCoId ? ($companies->firstWhere('id', $ecInitCoId)?->name ?? 'Unknown') : null;
                @endphp
                <div id="ec-company-display" onclick="openEcCompanyPicker()"
                     style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .85rem;
                            border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#fff;
                            font-size:.9rem;user-select:none;">
                    <span id="ec-company-display-name" style="color:{{ $ecInitCoName ? 'inherit' : '#aaa' }};">
                        {{ $ecInitCoName ?? '— No company selected —' }}
                    </span>
                    <span style="color:#aaa;font-size:.78rem;flex-shrink:0;margin-left:.5rem;">Select ▾</span>
                </div>
                <input type="hidden" name="company_id" id="ec-company-id-input" value="{{ $ecInitCoId }}">
                <p style="font-size:.76rem;color:#888;margin:.25rem 0 0;">Click to select or change. Leave blank to remove the association.</p>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:.75rem;padding-top:.5rem;">
                <button type="submit" style="padding:.55rem 1.25rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.9rem;font-weight:600;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='var(--primary)'">Save Changes</button>
                <button type="button" onclick="closeEditCustomerModal()"
                        style="padding:.55rem 1.1rem;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;font-weight:600;cursor:pointer;">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Company Picker for Edit Customer Modal ── --}}
<style>
.ec-cp-item { padding:.52rem .9rem; cursor:pointer; font-size:.9rem; border-radius:4px; transition:background .1s; }
.ec-cp-item:hover { background:#f0f7ff; }
.ec-cp-item.ec-cp-selected { background:#eff6ff; font-weight:600; color:var(--primary); }
</style>
<div id="ec-company-picker-modal" onclick="if(event.target===this)closeEcCompanyPicker()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9100;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:460px;display:flex;flex-direction:column;max-height:80vh;">
        <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <h3 style="margin:0;font-size:1rem;color:var(--primary);">Select Company</h3>
            <button type="button" onclick="closeEcCompanyPicker()" style="background:none;border:none;font-size:1.2rem;color:#888;cursor:pointer;line-height:1;">×</button>
        </div>
        <div style="padding:.65rem 1rem;border-bottom:1px solid #f0f0f0;flex-shrink:0;">
            <input type="text" id="ec-cp-search" placeholder="Search companies…"
                   oninput="filterEcCompanyList(this.value)"
                   style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
        </div>
        <div id="ec-cp-list" style="overflow-y:auto;flex:1;padding:.4rem .6rem;min-height:120px;max-height:300px;"></div>
    </div>
</div>

<script>
// ── Edit Customer Modal ──
const _ecCompanies = [
    {id: '', name: '— No Company —'},
    @foreach($companies as $co)
    {id: '{{ $co->id }}', name: @json($co->name)},
    @endforeach
];

function openEditCustomerModal() {
    document.getElementById('edit-customer-modal').style.display = 'flex';
}
function closeEditCustomerModal() {
    document.getElementById('edit-customer-modal').style.display = 'none';
}

function openEcCompanyPicker() {
    document.getElementById('ec-company-picker-modal').style.display = 'flex';
    document.getElementById('ec-cp-search').value = '';
    filterEcCompanyList('');
    setTimeout(() => document.getElementById('ec-cp-search').focus(), 50);
}
function closeEcCompanyPicker() {
    document.getElementById('ec-company-picker-modal').style.display = 'none';
}

function filterEcCompanyList(term) {
    const t   = term.toLowerCase();
    const cur = String(document.getElementById('ec-company-id-input').value);
    const list = document.getElementById('ec-cp-list');
    list.innerHTML = '';
    const matches = _ecCompanies.filter(c => !t || c.name.toLowerCase().includes(t));
    if (!matches.length) {
        list.innerHTML = '<p style="text-align:center;color:#aaa;padding:1.25rem;font-size:.88rem;margin:0;">No companies found.</p>';
        return;
    }
    matches.forEach(c => {
        const div = document.createElement('div');
        div.className = 'ec-cp-item' + (cur === String(c.id) ? ' ec-cp-selected' : '');
        div.textContent = c.name;
        div.onclick = () => {
            document.getElementById('ec-company-id-input').value = c.id;
            const span = document.getElementById('ec-company-display-name');
            span.textContent = c.name;
            span.style.color = c.id ? '' : '#aaa';
            closeEcCompanyPicker();
        };
        list.appendChild(div);
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (document.getElementById('ec-company-picker-modal').style.display !== 'none') {
            closeEcCompanyPicker();
        } else if (document.getElementById('edit-customer-modal').style.display !== 'none') {
            closeEditCustomerModal();
        }
    }
});
</script>
@endif

@endif

<script>
(function () {
    const input    = document.getElementById('cust-search-input');
    const dropdown = document.getElementById('cust-search-dropdown');
    const results  = document.getElementById('cust-search-results');
    const clearBtn = document.getElementById('cust-search-clear');
    const searchUrl = '{{ route('admin.analytics.customers.search') }}';
    const baseUrl   = '{{ route('admin.analytics.customers') }}';
    @if($customer)
    const currentId = {{ $customer->id }};
    @else
    const currentId = null;
    @endif

    let debounceTimer = null;
    let activeIndex   = -1;
    let lastResults   = [];

    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        clearBtn.style.display = q ? 'flex' : 'none';
        if (q.length < 2) { closeDropdown(); return; }
        debounceTimer = setTimeout(() => fetchResults(q), 220);
    });

    input.addEventListener('keydown', function (e) {
        const items = results.querySelectorAll('.cust-result-item');
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
            if (activeIndex >= 0 && items[activeIndex]) {
                items[activeIndex].click();
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) fetchResults(this.value.trim());
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('cust-search-wrap').contains(e.target)) closeDropdown();
    });

    function fetchResults(q) {
        fetch(searchUrl + '?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                lastResults = data;
                renderResults(data, q);
            });
    }

    function renderResults(data, q) {
        activeIndex = -1;
        if (!data.length) {
            results.innerHTML = '<div style="padding:1rem 1.1rem;font-size:.85rem;color:#9ca3af;text-align:center;">No customers found</div>';
            openDropdown();
            return;
        }
        results.innerHTML = data.map((c, i) => {
            const isActive = c.id === currentId;
            return `<div class="cust-result-item" data-id="${c.id}" data-name="${escHtml(c.name)}"
                style="padding:.65rem 1rem;cursor:pointer;display:flex;align-items:center;gap:.75rem;
                       border-bottom:1px solid #f3f4f6;background:${isActive ? '#f0f7ff' : '#fff'};
                       transition:background .1s;"
                onmouseover="this.style.background='#f0f7ff'"
                onmouseout="this.style.background='${isActive ? '#f0f7ff' : '#fff'}'">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#fff;font-size:.68rem;font-weight:700;">${initials(c.name)}</span>
                </div>
                <div style="min-width:0;flex:1;">
                    <div style="font-size:.875rem;font-weight:600;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${escHtml(c.name)}${isActive ? ' <span style="font-size:.68rem;background:#dbeafe;color:#1e40af;padding:.1rem .4rem;border-radius:999px;margin-left:.3rem;">Current</span>' : ''}</div>
                    <div style="font-size:.75rem;color:#6b7280;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        ${c.company ? '<span style="font-weight:600;color:#374151;">' + escHtml(c.company) + '</span> · ' : ''}${escHtml(c.email)}${c.phone ? ' · ' + escHtml(c.phone) : ''}
                    </div>
                </div>
            </div>`;
        }).join('');

        results.querySelectorAll('.cust-result-item').forEach(el => {
            el.addEventListener('click', function () {
                const id   = this.dataset.id;
                const name = this.dataset.name;
                input.value = name;
                clearBtn.style.display = 'flex';
                closeDropdown();
                window.location.href = baseUrl + '?customer_id=' + id;
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

    window.clearCustomerSearch = function () {
        input.value = '';
        clearBtn.style.display = 'none';
        closeDropdown();
        window.location.href = baseUrl;
    };
})();

// Recents — customer analytics
(function () {
    const RECENTS_KEY    = 'datatel_cust_recents';
    const recentsBaseUrl = '{{ route('admin.analytics.customers') }}';

    @if($customer)
    (function () {
        const entry = { id: {{ $customer->id }}, name: {!! json_encode($customer->name) !!} };
        let stored = [];
        try { stored = JSON.parse(localStorage.getItem(RECENTS_KEY) || '[]'); } catch (e) {}
        stored = stored.filter(function (r) { return r.id !== entry.id; });
        stored.unshift(entry);
        localStorage.setItem(RECENTS_KEY, JSON.stringify(stored.slice(0, 3)));
    })();
    @endif

    var recents = [];
    try { recents = JSON.parse(localStorage.getItem(RECENTS_KEY) || '[]'); } catch (e) {}

    var currentCustId = @if($customer){{ $customer->id }}@else null @endif;
    var pills = recents.filter(function (r) { return r.id !== currentCustId; });

    var wrap      = document.getElementById('cust-recents-wrap');
    var container = document.getElementById('cust-recents');

    if (wrap && container && pills.length) {
        container.innerHTML = pills.map(function (r) {
            var parts = r.name.split(' ').slice(0, 2);
            var ini   = parts.map(function (w) { return (w[0] || '').toUpperCase(); }).join('');
            var label = r.name.length > 22 ? r.name.slice(0, 21) + '…' : r.name;
            return '<a href="' + recentsBaseUrl + '?customer_id=' + r.id + '"'
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
</script>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">ANALYTICS</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Customer Analytics
    </h1>
</div>
@endpush
