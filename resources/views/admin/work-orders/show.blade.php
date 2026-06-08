@extends('layouts.admin')
@section('title', $workOrder->woLabel())

@section('content')
<style>
.emp-assign-btn:hover { opacity: 1 !important; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes woToastFade {
    0%, 82% { opacity: 1; transform: translateY(0); }
    100%     { opacity: 0; transform: translateY(-4px); }
}
.wo-flash-toast {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .22rem .75rem;
    border-radius: 999px;
    background: #d1fae5;
    border: 1px solid #6ee7b7;
    color: #065f46;
    font-size: .72rem;
    font-weight: 600;
    white-space: nowrap;
    max-width: 340px;
    overflow: hidden;
    text-overflow: ellipsis;
    animation: woToastFade 10s ease forwards;
    flex-shrink: 0;
}
</style>
@php
    $urgencyBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
    $urgencyColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';
    $photos      = $workOrder->attachments->filter(fn($a) => str_starts_with($a->mime_type, 'image/'));
    $docs        = $workOrder->attachments->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'));
    $previewable = ['application/pdf', 'text/plain'];
    $woLocked    = in_array($workOrder->status, [
        \App\Models\WorkOrder::STATUS_COMPLETED,
        \App\Models\WorkOrder::STATUS_CANCELED,
    ]);
@endphp

<script>
(function () {
    var flash  = document.querySelector('.alert.alert-success');
    var target = document.getElementById('wo-flash-target');
    if (!flash || !target) return;
    var msg = flash.textContent.trim();
    flash.remove();
    var escaped = msg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    var toast = document.createElement('span');
    toast.className = 'wo-flash-toast';
    toast.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ' + escaped;
    target.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 10000);
})();
</script>

@if($workOrder->cancel_reason !== null && $workOrder->status === \App\Models\WorkOrder::STATUS_AWAITING_FEEDBACK)
<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;gap:.75rem;align-items:flex-start;">
    <span style="font-size:1.3rem;flex-shrink:0;">⚠</span>
    <div>
        <div style="font-weight:700;color:#78350f;font-size:.95rem;margin-bottom:.2rem;">Customer Requested Cancellation of Scheduled Visit</div>
        @if($workOrder->cancel_reason)
        <div style="font-size:.87rem;color:#92400e;margin-top:.35rem;padding:.55rem .8rem;background:#fffbeb;border:1px solid #fde68a;border-radius:5px;">
            <span style="font-weight:600;">Customer's instructions:</span> {{ $workOrder->cancel_reason }}
        </div>
        @else
        <div style="font-size:.87rem;color:#92400e;margin-top:.2rem;">No instructions provided — follow up with the customer on the next business day.</div>
        @endif
    </div>
</div>
@endif

@php
    $customerCompany = $workOrder->customer->companies->firstWhere('pivot.is_primary', true)
                       ?? $workOrder->customer->companies->first();
@endphp

{{-- ── Main 3-column layout ── --}}
<div style="display:grid;grid-template-columns:15% 1fr 20%;gap:1rem;align-items:start;">

    {{-- ── Column 1: Customer · Company · Employees ── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

    {{-- Customer card --}}
    <div id="customer-card" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Customer</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Contact details</div>
            </div>
        </div>
        <div style="padding:1rem 1.25rem;">
            <div style="display:flex;align-items:flex-start;gap:.85rem;margin-bottom:.5rem;">
                <a href="{{ route('admin.analytics.customers', ['customer_id' => $workOrder->customer->id]) }}" title="View Customer Analytics" style="width:40px;height:40px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;font-weight:700;text-decoration:none;transition:opacity .15s;" onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                    {{ strtoupper(substr($workOrder->customer->name, 0, 1)) }}
                </a>
                <div style="min-width:0;">
                    <div style="display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;">
                        <span style="font-size:.95rem;font-weight:700;color:var(--primary);line-height:1.3;">{{ $workOrder->customer->name }}</span>
                        @if($completedCount)
                        <span style="font-size:.68rem;font-weight:700;color:#166534;background:#f0fdf4;border:1px solid #86efac;border-radius:999px;padding:.1em .45em;white-space:nowrap;">{{ $completedCount }} done</span>
                        @endif
                    </div>
                    @if($workOrder->customer->title)
                    <div style="font-size:.78rem;color:#6b7280;margin-top:.1rem;">{{ $workOrder->customer->title }}</div>
                    @endif
                    @if($workOrder->customer->phone)
                    <div style="font-size:.8rem;color:#555;margin-top:.2rem;">
                        <a href="tel:{{ $workOrder->customer->phone }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->phone }}</a>
                    </div>
                    @endif
                    @if($workOrder->customer->email)
                    <div style="font-size:.78rem;color:#888;">
                        <a href="mailto:{{ $workOrder->customer->email }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->email }}</a>
                    </div>
                    @endif
                </div>
            </div>
            {{-- Related work orders (collapsible) --}}
            @if($relatedOrders->count())
            <div style="margin-top:.5rem;padding-top:.45rem;border-top:1px solid #f0f0f0;text-align:center;">
                <button type="button" onclick="toggleRelatedOrders(this)"
                        style="display:inline-flex;align-items:center;gap:.35rem;border:1px solid #e5e7eb;border-radius:999px;background:#f8f9fa;color:#9ca3af;font-size:.72rem;font-weight:600;padding:.2rem .75rem;cursor:pointer;transition:color .15s,border-color .15s;">
                    <span class="rel-chevron" style="font-size:.6rem;display:inline-block;transition:transform .28s ease;transform:rotate(0deg);">▼</span>
                    <span class="rel-label">{{ $relatedOrders->count() }} recent {{ Str::plural('ticket', $relatedOrders->count()) }}</span>
                </button>
            </div>
            <div class="related-orders-body" style="overflow:hidden;max-height:0;transition:max-height .32s ease;margin-top:0;">
                <div style="padding-top:.5rem;">
                    @foreach($relatedOrders as $rel)
                    <a href="{{ route('admin.work-orders.show', $rel) }}" target="_blank"
                       style="display:flex;align-items:center;gap:.55rem;padding:.28rem 0;text-decoration:none;border-bottom:1px solid #fafafa;color:inherit;"
                       onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                        <span style="font-size:.72rem;font-weight:700;color:#94a3b8;white-space:nowrap;flex-shrink:0;">{{ $rel->woLabel() }}</span>
                        <span class="badge badge-{{ $rel->status }}" style="font-size:.63rem;padding:.08em .4em;flex-shrink:0;line-height:1.5;">{{ str_replace('_',' ',$rel->status) }}</span>
                        <span style="flex:1;min-width:0;font-size:.75rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ \Illuminate\Support\Str::limit($rel->description, 70) ?: '—' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>{{-- /customer card --}}

    {{-- Company card --}}
    @if($customerCompany)
    <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Account &amp; billing info</div>
                </div>
            </div>
            <a href="{{ route('admin.analytics.companies', ['company_id' => $customerCompany->id]) }}"
               style="font-size:.72rem;color:rgba(255,255,255,.75);text-decoration:none;border:1px solid rgba(255,255,255,.3);padding:.2rem .55rem;border-radius:4px;white-space:nowrap;flex-shrink:0;"
               onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background=''">View ↗</a>
        </div>
        <div style="padding:1rem 1.25rem;">
            <div style="font-size:.98rem;font-weight:700;color:var(--primary);margin-bottom:.5rem;">{{ $customerCompany->name }}</div>
            @if($customerCompany->phone)
            <div style="font-size:.82rem;color:#555;margin-bottom:.2rem;">
                <a href="tel:{{ $customerCompany->phone }}" style="color:inherit;text-decoration:none;">📞 {{ $customerCompany->phone }}</a>
            </div>
            @endif
            @if($customerCompany->email)
            <div style="font-size:.8rem;color:#888;margin-bottom:.2rem;">
                <a href="mailto:{{ $customerCompany->email }}" style="color:inherit;text-decoration:none;">✉ {{ $customerCompany->email }}</a>
            </div>
            @endif
            @php
                $compAddr = collect([
                    $customerCompany->address_street,
                    trim(collect([$customerCompany->address_city, $customerCompany->address_state])->filter()->join(', ')),
                    $customerCompany->address_zip,
                ])->filter()->join(', ');
            @endphp
            @if($compAddr)
            <div style="font-size:.78rem;color:#999;line-height:1.4;">📍 {{ $compAddr }}</div>
            @endif

            @if($companyAddresses->isNotEmpty())
            <div style="margin-top:.75rem;padding-top:.6rem;border-top:1px solid #f0f0f0;">
                <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#9ca3af;margin-bottom:.4rem;">Sites</div>
                <div style="display:flex;flex-direction:column;gap:.35rem;">
                    @foreach($companyAddresses as $site)
                    <div style="display:flex;align-items:flex-start;gap:.45rem;padding:.45rem .55rem;
                                background:{{ $site->is_default ? '#eff6ff' : '#f8f9fa' }};
                                border:1px solid {{ $site->is_default ? '#bfdbfe' : '#e5e7eb' }};
                                border-radius:6px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="{{ $site->is_default ? 'var(--accent)' : '#9ca3af' }}"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="flex-shrink:0;margin-top:1px;">
                            <rect x="2" y="7" width="20" height="15" rx="1"/>
                            <path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                            <line x1="12" y1="12" x2="12" y2="12"/>
                            <path d="M8 12h.01M12 12h.01M16 12h.01M8 16h.01M12 16h.01M16 16h.01"/>
                        </svg>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;align-items:center;gap:.3rem;flex-wrap:wrap;margin-bottom:.15rem;">
                                <span style="font-size:.7rem;font-weight:700;color:{{ $site->is_default ? 'var(--accent)' : '#374151' }};line-height:1.2;">
                                    {{ $site->label ?: 'Site' }}
                                </span>
                                @if($site->is_default)
                                <span style="font-size:.58rem;font-weight:700;background:var(--accent);color:#fff;padding:.05em .4em;border-radius:3px;line-height:1.4;white-space:nowrap;">
                                    Default
                                </span>
                                @endif
                            </div>
                            <div style="font-size:.72rem;color:#555;line-height:1.35;">{{ $site->street }}</div>
                            @if($site->city)
                            <div style="font-size:.68rem;color:#9ca3af;line-height:1.3;">{{ trim($site->city.($site->state ? ', '.$site->state : '')) }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Invoice card --}}
    <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Invoices
                        @if($workOrder->invoices->isNotEmpty())
                        <span style="font-size:.72rem;font-weight:400;opacity:.7;">({{ $workOrder->invoices->count() }})</span>
                        @endif
                    </div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing &amp; payments</div>
                </div>
            </div>
            <a href="{{ route('admin.invoices.create', ['work_order_id' => $workOrder->id]) }}"
               title="Add invoice"
               style="width:26px;height:26px;border-radius:50%;border:1.5px solid rgba(255,255,255,.45);background:rgba(255,255,255,.1);color:#fff;font-size:1.1rem;font-weight:300;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s;text-decoration:none;"
               onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">+</a>
        </div>
        @if($workOrder->invoices->isNotEmpty())
        @php
            $invTotalCost = $workOrder->invoices
                ->whereNotIn('status', [\App\Models\Invoice::STATUS_CANCELED])
                ->sum(fn($i) => (float)($i->total ?? 0));
            $invTotalCollected = $workOrder->invoices
                ->where('status', \App\Models\Invoice::STATUS_COMPLETED)
                ->sum(fn($i) => (float)($i->total ?? 0));
            $invTotalOutstanding = $workOrder->invoices
                ->whereIn('status', [\App\Models\Invoice::STATUS_ISSUED, \App\Models\Invoice::STATUS_PAYMENT_RECEIVED])
                ->sum(fn($i) => (float)($i->total ?? 0));
        @endphp
        <div style="padding:1rem 1.25rem;">
            {{-- Invoice links --}}
            <div style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:.85rem;">
                @foreach($workOrder->invoices->sortBy('id') as $inv)
                @php
                    $invNum      = 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT);
                    $invBadgeBg  = match($inv->status) {
                        'issued'           => '#dbeafe', 'payment_received' => '#fce7f3',
                        'completed'        => '#d1fae5', 'canceled'         => '#fee2e2',
                        default            => '#fef3c7',
                    };
                    $invBadgeColor = match($inv->status) {
                        'issued'           => '#1e40af', 'payment_received' => '#9d174d',
                        'completed'        => '#065f46', 'canceled'         => '#991b1b',
                        default            => '#92400e',
                    };
                    $invStatusLabel = match($inv->status) {
                        'issued'           => 'Issued',  'payment_received' => 'Received',
                        'completed'        => 'Completed', 'canceled'       => 'Canceled',
                        default            => 'Draft',
                    };
                @endphp
                <a href="{{ route('admin.invoices.show', $inv) }}"
                   style="display:flex;align-items:center;justify-content:space-between;gap:.4rem;padding:.4rem .6rem;border-radius:6px;border:1px solid #0f766e;background:#f0fdfa;color:#0f766e;font-size:.82rem;font-weight:600;text-decoration:none;"
                   onmouseover="this.style.background='#ccfbf1'" onmouseout="this.style.background='#f0fdfa'">
                    <span>📄 {{ $invNum }}</span>
                    <span style="font-size:.82rem;font-weight:800;color:#0f766e;margin-left:auto;">${{ number_format($inv->total ?? 0, 2) }}</span>
                    <span style="font-size:.68rem;font-weight:700;padding:.1rem .45rem;border-radius:999px;background:{{ $invBadgeBg }};color:{{ $invBadgeColor }};border:1px solid {{ $invBadgeColor }};flex-shrink:0;">{{ $invStatusLabel }}</span>
                </a>
                @endforeach
            </div>
            {{-- Financial summary --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:.75rem 1rem;">
                <div style="display:flex;flex-direction:column;gap:.35rem;font-size:.82rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">Job Value</span>
                        <span style="font-weight:700;color:var(--primary);">${{ number_format($invTotalCost, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#6b7280;">Collected</span>
                        <span style="font-weight:700;color:#059669;">${{ number_format($invTotalCollected, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:.35rem;border-top:1px solid #e2e8f0;">
                        <span style="color:#6b7280;">Outstanding</span>
                        <span style="font-weight:700;color:{{ $invTotalOutstanding > 0 ? '#dc2626' : '#6b7280' }};">${{ number_format($invTotalOutstanding, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Attachments card --}}
    <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Attachments
                        @if($workOrder->attachments->count())
                        <span style="font-size:.72rem;font-weight:400;opacity:.7;">({{ $workOrder->attachments->count() }})</span>
                        @endif
                    </div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Photos &amp; documents</div>
                </div>
            </div>
            <button type="button" onclick="openAttachModal()"
                    title="Add attachment"
                    style="width:26px;height:26px;border-radius:50%;border:1.5px solid rgba(255,255,255,.45);background:rgba(255,255,255,.1);color:#fff;font-size:1.1rem;font-weight:300;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s;"
                    onmouseover="this.style.background='rgba(255,255,255,.25)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">+</button>
        </div>
        <div style="padding:1rem 1.25rem;">
            @if($workOrder->attachments->isEmpty())
            <div style="text-align:center;padding:.75rem 0;">
                <div style="font-size:1.8rem;margin-bottom:.3rem;">📂</div>
                <p style="font-size:.8rem;color:#aaa;margin:0 0 .65rem;">No attachments yet.</p>
                <button type="button" onclick="openAttachModal()"
                        style="font-size:.78rem;color:var(--accent);background:none;border:1px solid var(--accent);border-radius:5px;padding:.3rem .75rem;cursor:pointer;font-weight:600;">+ Add Files</button>
            </div>
            @else
                {{-- Photo thumbnails --}}
                @if($photos->count())
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(60px,1fr));gap:.35rem;margin-bottom:{{ $docs->count() ? '.75rem' : '0' }};">
                    @foreach($photos as $photo)
                    <img src="{{ route('attachments.view', $photo) }}"
                         alt="{{ $photo->original_name }}" title="{{ $photo->original_name }}"
                         style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:5px;border:1px solid #e5e7eb;cursor:zoom-in;display:block;transition:opacity .15s;"
                         onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'"
                         onclick="openLightbox('{{ route('attachments.view', $photo) }}','{{ addslashes($photo->original_name) }}','{{ route('attachments.download', $photo) }}')">
                    @endforeach
                </div>
                @endif
                {{-- Document list --}}
                @if($docs->count())
                <div style="display:flex;flex-direction:column;gap:.3rem;">
                    @foreach($docs as $doc)
                    @php $ext = strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)); @endphp
                    <div style="display:flex;align-items:center;gap:.4rem;padding:.32rem .5rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:5px;">
                        <span style="font-size:.58rem;font-weight:700;background:#e0e7ff;color:#4338ca;padding:.12em .4em;border-radius:3px;flex-shrink:0;white-space:nowrap;letter-spacing:.02em;">{{ $ext }}</span>
                        <span style="font-size:.75rem;color:#374151;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $doc->original_name }}">{{ $doc->original_name }}</span>
                        <a href="{{ route('attachments.download', $doc) }}" title="Download" download
                           style="font-size:.8rem;color:#6b7280;text-decoration:none;flex-shrink:0;line-height:1;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#6b7280'">↓</a>
                    </div>
                    @endforeach
                </div>
                @endif
                <div style="margin-top:.6rem;padding-top:.5rem;border-top:1px solid #f0f0f0;text-align:right;">
                    <button type="button" onclick="openAttachModal()" style="font-size:.75rem;color:var(--accent);background:none;border:none;cursor:pointer;font-weight:600;">Manage all →</button>
                </div>
            @endif
        </div>
    </div>{{-- /attachments card --}}

    </div>{{-- /col-1 --}}

    {{-- ── Column 2: WO Details · Visits · Notes ── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Work Order Description --}}
        <div id="wo-details-card" style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">

            {{-- Section header --}}
            <div style="background:var(--primary);margin:-1.5rem -1.5rem 1rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Work Order Details</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Description · Services · Schedule · Site</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                    @if($woLocked)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;
                                  border:1px solid rgba(255,255,255,.25);border-radius:5px;
                                  background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);
                                  font-size:.72rem;font-weight:600;white-space:nowrap;user-select:none;"
                          title="Work order is {{ $workOrder->status }} — details are read-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        Read-only
                    </span>
                    @else
                    <button type="button" id="urgency-toggle-btn" onclick="cycleUrgency()"
                            title="Priority — click to cycle"
                            style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;
                                   border-radius:5px;border:1px solid rgba(255,255,255,.35);
                                   background:rgba(255,255,255,.1);cursor:pointer;
                                   font-size:.75rem;font-weight:700;color:#fff;
                                   transition:background .15s,border-color .15s;white-space:nowrap;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.8;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span id="urgency-toggle-label">Routine</span>
                    </button>
                    <button type="button" id="edit-toggle-btn" onclick="toggleEdit()"
                            title="Edit Details"
                            style="display:flex;align-items:center;gap:.3rem;padding:.28rem .65rem;
                                   border:1px solid rgba(255,255,255,.35);border-radius:5px;
                                   background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);
                                   font-size:.75rem;font-weight:600;cursor:pointer;
                                   transition:background .15s,border-color .15s,color .15s;white-space:nowrap;"
                            onmouseover="if(!this.classList.contains('is-active'))this.style.background='rgba(255,255,255,.2)'"
                            onmouseout="if(!this.classList.contains('is-active'))this.style.background='rgba(255,255,255,.08)'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </button>
                    @endif
                </div>
            </div>

            {{-- Locked notice --}}
            @if($woLocked)
            <div style="display:flex;align-items:center;gap:.6rem;padding:.55rem .85rem;margin-bottom:.85rem;
                         background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;
                         font-size:.78rem;color:#6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <span>This work order is <strong style="color:#374151;">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</strong> — details and visits are read-only. Notes, attachments, and invoicing remain available.</span>
            </div>
            @endif

            {{-- Condensed view shown when card is collapsed --}}
            @php
                // Next 3 dates matching the customer's day-of-week preferences
                $_collDayToNum   = ['monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
                $_collAvailDays  = $workOrder->preferred_availability ? array_keys($workOrder->preferred_availability) : [];
                $_collPrefNums   = array_values(array_filter(array_map(fn($d) => $_collDayToNum[$d] ?? null, $_collAvailDays)));
                $_collNextDates  = [];
                if ($_collPrefNums) {
                    $_collCursor = \Carbon\Carbon::tomorrow();
                    while (count($_collNextDates) < 3) {
                        if (in_array($_collCursor->dayOfWeekIso, $_collPrefNums)) {
                            $_collNextDates[] = $_collCursor->copy();
                        }
                        $_collCursor->addDay();
                    }
                }
            @endphp
            <div id="details-collapsed-summary" style="display:block;padding-bottom:.85rem;border-bottom:1px solid #f0f0f0;margin-bottom:.5rem;">
                <div style="display:flex;gap:1rem;align-items:flex-start;">

                    {{-- Left: text content --}}
                    <div style="flex:1;min-width:0;">

                        @if($workOrder->serviceTypes->count())
                        <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;margin-bottom:.5rem;">
                            <span style="font-size:.72rem;color:#999;margin-right:.05rem;">Services:</span>
                            @foreach($workOrder->serviceTypes as $svc)
                            <span style="display:inline-flex;align-items:center;gap:.3rem;background:#f0f6ff;color:var(--accent);padding:.12em .55em;border-radius:999px;font-size:.75rem;font-weight:600;">
                                @if($svc->icon){!! $svc->svgIcon(11) !!}@endif{{ $svc->name }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                        @if($workOrder->description)
                        <p style="font-size:.88rem;color:#555;line-height:1.45;margin:0 0 .5rem;
                                   overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $workOrder->description }}</p>
                        @endif

                        @if($workOrder->equipment_details)
                        <p style="font-size:.8rem;color:#6b7280;line-height:1.4;margin:0 0 .5rem;
                                   overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;
                                   background:#f8f9fa;border-left:3px solid var(--primary);padding:.35rem .7rem;border-radius:0 4px 4px 0;">{{ $workOrder->equipment_details }}</p>
                        @endif

                        {{-- Site / date info row --}}
                        @php
                            $hasCollapsedMeta = $workOrder->site_street || $workOrder->site_contact_name || $workOrder->site_contact_phone || $workOrder->preferred_date;
                        @endphp
                        @if($hasCollapsedMeta)
                        <div style="display:flex;flex-wrap:wrap;gap:.5rem .85rem;font-size:.82rem;color:#555;margin-bottom:.45rem;">
                            @if($workOrder->site_street)
                            <span style="display:flex;align-items:center;gap:.3rem;">
                                <span style="color:#aaa;font-size:.78rem;">📍</span>{{ $workOrder->site_street }}{{ $workOrder->site_city ? ', '.$workOrder->site_city : '' }}{{ $workOrder->site_state ? ', '.$workOrder->site_state : '' }}{{ $workOrder->site_zip ? ' '.$workOrder->site_zip : '' }}
                            </span>
                            @endif
                            @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
                            <span style="display:flex;align-items:center;gap:.3rem;">
                                <span style="color:#aaa;font-size:.78rem;">👤</span>
                                {{ $workOrder->site_contact_name }}
                                @if($workOrder->site_contact_phone)
                                <a href="tel:{{ $workOrder->site_contact_phone }}" style="color:var(--accent);text-decoration:none;">{{ $workOrder->site_contact_phone }}</a>
                                @endif
                            </span>
                            @endif
                            @if($workOrder->preferred_date)
                            <span style="display:flex;align-items:center;gap:.3rem;">
                                <span style="color:#aaa;font-size:.78rem;">📅</span>
                                <span style="color:#2563eb;font-weight:600;">{{ \Carbon\Carbon::parse($workOrder->preferred_date)->format('M j, Y') }}</span>
                            </span>
                            @endif
                        </div>
                        @endif

                        {{-- Preferred dates: next 3 matching days --}}
                        @if($_collNextDates)
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            <span style="font-size:.72rem;color:#6b7280;font-weight:600;white-space:nowrap;">Preferred Dates:</span>
                            @foreach($_collNextDates as $_nd)
                            <span style="font-size:.78rem;font-weight:600;color:#1d4ed8;background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;padding:.1rem .5rem;white-space:nowrap;">
                                {{ $_nd->format('D, M j') }}
                            </span>
                            @endforeach
                        </div>
                        @endif

                    </div>{{-- /left --}}

                    {{-- Right: mini availability dot matrix --}}
                    @if($workOrder->preferred_availability)
                    <div style="flex-shrink:0;background:#f0f6ff;border:1px solid #bfdbfe;border-radius:6px;padding:.5rem .6rem;">
                        <div style="font-size:.58rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.35rem;white-space:nowrap;">Availability</div>
                        <table style="border-collapse:collapse;">
                            <thead>
                                <tr>
                                    <td style="width:14px;"></td>
                                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat'] as $_dh)
                                    <td style="text-align:center;font-size:.6rem;color:#6b7280;font-weight:700;padding:0 .18rem;white-space:nowrap;">{{ $_dh }}</td>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['morning'=>'AM','lunch'=>'MID','afternoon'=>'PM'] as $_slot => $_slotLabel)
                                <tr>
                                    <td style="font-size:.58rem;color:#9ca3af;font-weight:600;padding-right:.25rem;white-space:nowrap;line-height:1;">{{ $_slotLabel }}</td>
                                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday'] as $_day)
                                    @php $_dotActive = in_array($_slot, $workOrder->preferred_availability[$_day] ?? []); @endphp
                                    <td style="text-align:center;padding:.12rem .18rem;">
                                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $_dotActive ? '#3b82f6' : '#e2e8f0' }};"></span>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>

            <div id="details-body"
                 style="display:grid;grid-template-rows:0fr;overflow:hidden;
                        transition:grid-template-rows .3s ease, opacity .25s ease;opacity:0;"
                 data-collapsed="1">
            <div style="min-height:0;">

            {{-- Display view --}}
            <div id="details-display">
                <div style="border-bottom:1px solid #f0f0f0;padding-bottom:.85rem;margin-bottom:.85rem;display:flex;flex-wrap:wrap;gap:.35rem .1rem;font-size:.83rem;color:#555;">
                    <span style="color:#999;margin-right:.25rem;">Services:</span>
                    @forelse($workOrder->serviceTypes as $svc)
                        <span style="display:inline-flex;align-items:center;gap:.3rem;background:#f0f6ff;color:var(--accent);padding:.15em .65em;border-radius:999px;font-size:.78rem;font-weight:600;">
                            @if($svc->icon){!! $svc->svgIcon(13) !!}@endif{{ $svc->name }}
                        </span>
                    @empty
                        <span style="color:#bbb;">None specified</span>
                    @endforelse
                </div>
                <p style="color:#555;font-size:.92rem;line-height:1.55;margin-bottom:1.1rem;">{{ $workOrder->description ?: '—' }}</p>
                @if($workOrder->equipment_details)
                <div style="margin-top:.75rem;background:#f8f9fa;border-left:3px solid var(--primary);padding:.6rem .85rem;border-radius:0 5px 5px 0;font-size:.85rem;color:#444;white-space:pre-wrap;">{{ $workOrder->equipment_details }}</div>
                @endif
                <div style="font-size:.78rem;color:#aaa;margin-top:.6rem;">
                    Submitted {{ $workOrder->created_at->format('M j, Y') }} at {{ $workOrder->created_at->format('g:i A') }}
                </div>
                @if($workOrder->site_contact_name || $workOrder->site_contact_phone || $workOrder->preferred_date)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:1rem;">
                    @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
                    <div style="padding:.75rem 1rem;background:#f8f9fa;border-radius:6px;border:1px solid #e5e7eb;">
                        <div style="font-size:.68rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Site Contact</div>
                        @if($workOrder->site_contact_name)
                        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">{{ $workOrder->site_contact_name }}</div>
                        @endif
                        @if($workOrder->site_contact_phone)
                        <a href="tel:{{ $workOrder->site_contact_phone }}" style="font-size:.85rem;color:var(--accent);text-decoration:none;display:block;margin-top:.1rem;">{{ $workOrder->site_contact_phone }}</a>
                        @endif
                    </div>
                    @endif
                    @if($workOrder->preferred_date)
                    <div style="padding:.75rem 1rem;background:#f0f7ff;border-radius:6px;border:1px solid #bfdbfe;">
                        <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Customer Preferred Date</div>
                        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">{{ \Carbon\Carbon::parse($workOrder->preferred_date)->format('l, F j, Y') }}</div>
                    </div>
                    @endif
                </div>
                @endif

                @if($workOrder->preferred_availability)
                @php
                    $adminAvailDayNames  = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday'];
                    $adminAvailSlotDefs  = ['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']];
                @endphp
                <div style="margin-top:.75rem;padding:.75rem 1rem;background:#f0f6ff;border-radius:6px;border:1px solid #bfdbfe;">
                    <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.55rem;">Customer Preferred Availability</div>
                    @foreach($adminAvailDayNames as $dayKey => $dayName)
                        @if(!empty($workOrder->preferred_availability[$dayKey]))
                        <div style="display:flex;align-items:center;gap:.45rem;margin-bottom:.35rem;flex-wrap:wrap;justify-content:center;">
                            <span style="font-size:.82rem;font-weight:700;color:var(--primary);min-width:90px;text-align:right;">{{ $dayName }}:</span>
                            @foreach($adminAvailSlotDefs as $slot => $slotData)
                            @php $active = in_array($slot, $workOrder->preferred_availability[$dayKey]); @endphp
                            <span style="display:inline-flex;flex-direction:column;align-items:center;padding:.2rem .6rem;border-radius:6px;
                                         border:1.5px solid {{ $active ? '#86efac' : '#e5e7eb' }};
                                         background:{{ $active ? '#dcfce7' : '#f9fafb' }};min-width:92px;text-align:center;">
                                <span style="font-size:.72rem;font-weight:700;color:{{ $active ? '#15803d' : '#9ca3af' }};line-height:1.3;">{{ $slotData[0] }}</span>
                                <span style="font-size:.62rem;color:{{ $active ? '#16a34a' : '#d1d5db' }};line-height:1.2;">{{ $slotData[1] }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif

            </div>

            {{-- Inline edit form (hidden by default) --}}
            <form id="details-edit-form" method="POST" action="{{ route('admin.work-orders.update', $workOrder) }}"
                  style="display:none;margin-top:.6rem;padding-top:.6rem;border-top:1px solid #e5e7eb;">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $workOrder->status }}">
                <input type="hidden" name="urgency" id="admin-urgency-input" value="{{ old('urgency', $workOrder->urgency) }}">

                @if($errors->any())
                    <div class="alert alert-error" style="margin-bottom:.65rem;">{{ $errors->first() }}</div>
                @endif

                <div style="display:grid;gap:.65rem;">

                    {{-- Services --}}
                    @if($serviceTypes->count())
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Services</label>
                        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                            @foreach($serviceTypes as $svc)
                            @php $svcChecked = in_array($svc->id, old('service_ids', $workOrder->serviceTypes->pluck('id')->toArray())); @endphp
                            <label class="svc-pill-inline" data-checked="{{ $svcChecked ? '1' : '0' }}"
                                   style="display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .8rem;border-radius:999px;border:2px solid {{ $svcChecked ? 'var(--accent)' : '#e5e7eb' }};background:{{ $svcChecked ? '#f0f6ff' : '#f9fafb' }};cursor:pointer;font-size:.84rem;font-weight:600;color:{{ $svcChecked ? 'var(--accent)' : '#6b7280' }};transition:border-color .12s,background .12s,color .12s;user-select:none;">
                                <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                                       {{ $svcChecked ? 'checked' : '' }}
                                       style="display:none;">
                                @if($svc->icon)
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                                     stroke="{{ $svcChecked ? 'var(--accent)' : '#9ca3af' }}" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round" class="svc-icon-inline">{!! \App\Models\ServiceType::iconSet()[$svc->icon]['paths'] ?? '' !!}</svg>
                                @endif
                                {{ $svc->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Description --}}
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.2rem;">Description</label>
                        <textarea name="description" id="wo-description" rows="3"
                                  style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('description', $workOrder->description) }}</textarea>
                        <div style="display:flex;justify-content:flex-end;align-items:center;gap:.4rem;margin-top:.3rem;">
                            <button type="button" id="desc-mic-btn" onclick="toggleDescMic()" title="Dictate description"
                                    style="display:inline-flex;align-items:center;gap:.28rem;padding:.22rem .5rem;border:1px solid #d1d5db;border-radius:5px;background:#f9fafb;color:#6b7280;font-size:.73rem;font-weight:600;cursor:pointer;transition:background .15s,color .15s,border-color .15s;">
                                <svg id="desc-mic-icon" xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                                <span id="desc-mic-label">Dictate</span>
                            </button>
                            <button type="button" onclick="openPullModal()"
                                    @disabled($completedCount === 0)
                                    title="{{ $completedCount === 0 ? 'No prior work orders for this customer' : 'Pull from a prior visit' }}"
                                    style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .6rem;border:1px solid {{ $completedCount > 0 ? 'var(--accent)' : '#d1d5db' }};border-radius:5px;background:{{ $completedCount > 0 ? '#f0f7ff' : '#f9fafb' }};color:{{ $completedCount > 0 ? 'var(--accent)' : '#9ca3af' }};font-size:.73rem;font-weight:600;cursor:{{ $completedCount > 0 ? 'pointer' : 'not-allowed' }};">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                Pull from Prior Visit
                            </button>
                        </div>
                    </div>

                    {{-- Equipment Details --}}
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.2rem;">
                            Equipment Details
                            <span style="font-weight:400;color:#9ca3af;font-size:.75rem;">— type <kbd style="font-size:.72rem;background:#f3f4f6;border:1px solid #d1d5db;border-radius:3px;padding:.05rem .3rem;font-family:monospace;">..</kbd> to search device catalog</span>
                        </label>
                        <textarea name="equipment_details" id="wo-equipment" rows="2"
                                  style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('equipment_details', $workOrder->equipment_details) }}</textarea>
                    </div>

                    @php
                        $urgSummaryBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
                        $urgSummaryColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';
                        $availDayLabels  = ['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'];
                        $availDays       = $workOrder->preferred_availability ? array_keys($workOrder->preferred_availability) : [];
                        // Compute next 3 upcoming dates that fall on a preferred day
                        $dayToNum = ['monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
                        $prefDayNums = array_values(array_filter(array_map(fn($d) => $dayToNum[$d] ?? null, $availDays)));
                        $nextPrefDates = [];
                        if ($prefDayNums) {
                            $cursor = \Carbon\Carbon::tomorrow();
                            while (count($nextPrefDates) < 3) {
                                if (in_array($cursor->dayOfWeekIso, $prefDayNums)) {
                                    $nextPrefDates[] = $cursor->copy();
                                }
                                $cursor->addDay();
                            }
                        }
                    @endphp

                    {{-- Site Details --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start;">
                        {{-- Left: Site Address --}}
                        <div>
                            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Site Address</label>
                            <input type="text" name="site_street" id="admin-site-street"
                                   value="{{ old('site_street', $workOrder->site_street ?: $siteAccountAddress) }}"
                                   placeholder="Street address"
                                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;margin-bottom:.4rem;">
                            <div style="display:grid;grid-template-columns:1fr .45fr .45fr;gap:.4rem;">
                                <input type="text" name="site_city"
                                       value="{{ old('site_city', $workOrder->site_city) }}"
                                       placeholder="City"
                                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                                <input type="text" name="site_state"
                                       value="{{ old('site_state', $workOrder->site_state) }}"
                                       placeholder="State"
                                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                                <input type="text" name="site_zip"
                                       value="{{ old('site_zip', $workOrder->site_zip) }}"
                                       placeholder="ZIP"
                                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                            </div>
                            @if($addressSuggestions->isNotEmpty())
                            <div style="margin-top:.45rem;">
                                <span style="font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">Suggested Addresses:</span>
                                <div style="margin-top:.3rem;display:flex;flex-direction:column;gap:.2rem;">
                                    @foreach($addressSuggestions as $suggestion)
                                    <div style="display:flex;align-items:center;padding:.28rem .65rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:5px;gap:.5rem;">
                                        <button type="button"
                                                data-street="{{ $suggestion['street'] }}"
                                                data-city="{{ $suggestion['city'] ?? '' }}"
                                                data-state="{{ $suggestion['state'] ?? '' }}"
                                                data-zip="{{ $suggestion['zip'] ?? '' }}"
                                                onclick="
                                                    document.getElementById('admin-site-street').value = this.dataset.street;
                                                    document.querySelector('[name=site_city]').value    = this.dataset.city;
                                                    document.querySelector('[name=site_state]').value   = this.dataset.state;
                                                    document.querySelector('[name=site_zip]').value     = this.dataset.zip;
                                                "
                                                title="Use this address"
                                                style="flex-shrink:0;width:22px;height:22px;border-radius:50%;border:1.5px solid var(--accent);background:#fff;color:var(--accent);font-size:1rem;font-weight:700;cursor:pointer;line-height:1;padding:0;display:flex;align-items:center;justify-content:center;">+</button>
                                        <span title="{{ $suggestion['source'] === 'company' ? 'Company address' : 'Customer address' }}"
                                              style="font-size:.9rem;flex-shrink:0;line-height:1;">{{ $suggestion['source'] === 'company' ? '🏢' : '👤' }}</span>
                                        @if($suggestion['label'])
                                        <span style="font-size:.68rem;font-weight:700;color:var(--accent);background:#e0f0ff;border-radius:3px;padding:.05rem .4rem;white-space:nowrap;">{{ $suggestion['label'] }}</span>
                                        @endif
                                        @if($suggestion['is_default'])
                                        <span style="font-size:.65rem;font-weight:700;color:#16a34a;background:#dcfce7;border-radius:3px;padding:.05rem .35rem;white-space:nowrap;">Default</span>
                                        @endif
                                        <span style="font-size:.82rem;color:#374151;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $suggestion['address'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        {{-- Right: Site Contact Name + Phone + Scheduling Preferences --}}
                        <div style="display:flex;flex-direction:column;gap:.75rem;">
                            <div>
                                <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Site Contact Name</label>
                                <input type="text" name="site_contact_name" value="{{ old('site_contact_name', $workOrder->site_contact_name ?: $workOrder->customer->name) }}"
                                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Site Contact Phone</label>
                                <input type="text" name="site_contact_phone" value="{{ old('site_contact_phone', $workOrder->site_contact_phone ?: $workOrder->customer->phone) }}"
                                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                            </div>

                            {{-- Scheduling Preferences --}}
                            <div style="border:1px solid #d1d5db;border-radius:8px;background:#f3f4f6;overflow:hidden;">

                                {{-- Collapsed header / summary --}}
                                <button type="button" onclick="toggleSchedPrefs()"
                                        style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.1rem;background:none;border:none;cursor:pointer;text-align:left;gap:.75rem;">
                                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;min-width:0;">
                                        <span style="font-weight:700;font-size:.72rem;color:#4b5563;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;">Scheduling Preferences</span>
                                        @if($nextPrefDates)
                                            @foreach($nextPrefDates as $npd)
                                            <span style="font-size:.69rem;color:#2563eb;font-weight:600;background:#eff6ff;border:1px solid #bfdbfe;border-radius:5px;padding:.1rem .45rem;white-space:nowrap;">{{ $npd->format('M j') }}</span>
                                            @endforeach
                                        @elseif($workOrder->preferred_date)
                                            <span style="font-size:.69rem;color:#6b7280;white-space:nowrap;">📅 {{ $workOrder->preferred_date->format('M j, Y') }}</span>
                                        @endif
                                    </div>
                                    <svg id="sched-prefs-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#4b5563" stroke-width="2.5" style="flex-shrink:0;transition:transform .2s;"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>

                                {{-- Expandable body --}}
                                <div id="sched-prefs-body" style="display:none;padding:0 1.1rem 1.1rem;border-top:1px solid #e5e7eb;">

                                    {{-- Availability picker --}}
                                    <div style="margin-bottom:.9rem;padding-top:.9rem;">
                                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.25rem;">
                                            Preferred Days &amp; Times
                                            <span style="font-weight:400;color:#9ca3af;font-size:.75rem;">— leave blank if flexible</span>
                                        </label>
                                        <input type="hidden" name="preferred_availability" id="admin-avail-json"
                                               value="{{ old('preferred_availability', json_encode($workOrder->preferred_availability ?: (object)[])) }}">

                                        <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
                                            @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                                            <button type="button" class="admin-avail-day-btn" data-day="{{ $day }}"
                                                    style="padding:.3rem .8rem;border-radius:999px;border:2px solid #d1d5db;
                                                           background:#fff;font-size:.8rem;font-weight:600;color:#6b7280;
                                                           cursor:pointer;transition:all .12s;line-height:1.3;">
                                                {{ $dayLabel }}
                                            </button>
                                            @endforeach
                                        </div>

                                        <div id="admin-avail-panels" style="display:none;border:1px solid #bfdbfe;border-radius:6px;overflow:hidden;">
                                            @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                                            <div class="admin-avail-day-panel" data-day="{{ $day }}"
                                                 style="display:none;align-items:center;justify-content:center;gap:.6rem;padding:.5rem .85rem;border-bottom:1px solid #dbeafe;background:#f0f7ff;flex-wrap:wrap;">
                                                <span style="font-size:.78rem;font-weight:700;color:var(--primary);width:30px;flex-shrink:0;text-align:center;">{{ $dayLabel }}</span>
                                                @foreach(['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']] as $slot => $slotData)
                                                <button type="button" class="admin-avail-slot-btn" data-day="{{ $day }}" data-slot="{{ $slot }}"
                                                        style="padding:.3rem .85rem;border-radius:8px;border:1.5px solid #93c5fd;
                                                               background:#fff;cursor:pointer;transition:all .12s;text-align:center;min-width:108px;">
                                                    <div class="sb-name" style="font-size:.74rem;font-weight:700;color:#3b82f6;line-height:1.3;">{{ $slotData[0] }}</div>
                                                    <div class="sb-time" style="font-size:.62rem;color:#93c5fd;line-height:1.2;font-weight:500;">{{ $slotData[1] }}</div>
                                                </button>
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>

                                        {{-- Shown when WO availability differs from customer's saved defaults --}}
                                        <div id="admin-update-defaults-box" style="display:none;margin-top:.55rem;padding:.55rem .85rem;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;">
                                            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.82rem;color:#78350f;">
                                                <input type="checkbox" name="update_customer_defaults" value="1" checked
                                                       style="accent-color:var(--accent);width:14px;height:14px;flex-shrink:0;">
                                                <span>Also update <strong>{{ $workOrder->customer->name }}</strong>'s default availability</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Preferred Date --}}
                                    <div style="padding-top:.85rem;border-top:1px solid #e5e7eb;">
                                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Preferred Date</label>
                                        <input type="date" name="preferred_date" id="admin-preferred-date"
                                               value="{{ old('preferred_date', $workOrder->preferred_date?->format('Y-m-d')) }}"
                                               style="width:100%;padding:.5rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;background:#fff;">
                                        <p id="admin-date-hint" style="font-size:.74rem;color:#2563eb;margin:.3rem 0 0;display:none;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div style="display:flex;gap:.75rem;margin-top:1.25rem;justify-content:flex-end;">
                    <button type="button" onclick="toggleEdit()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>

            </div>{{-- /details-body inner --}}
            </div>{{-- /details-body --}}

        </div>

        {{-- Scheduled Visits --}}
        @php
            $durFmt = fn($m) => $m >= 60
                ? floor($m/60).'h'.($m%60 ? ' '.($m%60).'m' : '')
                : $m.'m';
            $scheduledMins = $workOrder->visits->sum('duration_estimate_minutes');
            $actualMins    = $workOrder->visits->flatMap->timeEntries
                ->filter(fn($te) => $te->clocked_in_at && $te->clocked_out_at)
                ->sum(fn($te) => $te->clocked_in_at->diffInMinutes($te->clocked_out_at));
        @endphp
        <div style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Scheduled Visits</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Appointments · Time tracking</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-shrink:0;">
                    @if($scheduledMins > 0 || $actualMins > 0)
                    <div style="text-align:right;">
                        <div style="font-size:.68rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.15rem;">Hours</div>
                        <div style="display:flex;align-items:baseline;gap:.3rem;">
                            <span style="font-size:.92rem;font-weight:800;color:{{ $actualMins > $scheduledMins && $scheduledMins > 0 ? '#fca5a5' : '#fff' }};">{{ $durFmt($actualMins) }}</span>
                            <span style="font-size:.7rem;color:rgba(255,255,255,.45);">actual</span>
                            <span style="font-size:.7rem;color:rgba(255,255,255,.3);">/</span>
                            <span style="font-size:.82rem;font-weight:600;color:rgba(255,255,255,.65);">{{ $durFmt($scheduledMins) }}</span>
                            <span style="font-size:.7rem;color:rgba(255,255,255,.45);">sched.</span>
                        </div>
                    </div>
                    @endif
                    @if($woLocked)
                    <span style="display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;
                                  border:1px solid rgba(255,255,255,.25);border-radius:5px;
                                  background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);
                                  font-size:.72rem;font-weight:600;white-space:nowrap;user-select:none;"
                          title="Work order is {{ $workOrder->status }} — no new visits can be added">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        Locked
                    </span>
                    @else
                    <button type="button" onclick="openScheduleModal()"
                            style="display:flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border:1px solid rgba(255,255,255,.35);border-radius:5px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);font-size:.75rem;font-weight:600;cursor:pointer;"
                            onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
                        + Add Visit
                    </button>
                    @endif
                </div>
            </div>

            @if($workOrder->visits->isEmpty())
            <div style="padding:1rem 1.25rem;color:#aaa;font-size:.88rem;display:flex;align-items:center;gap:.5rem;">
                <span>📅</span> No visits scheduled yet.
            </div>
            @else
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;padding:.85rem 1rem;">
            @foreach($workOrder->visits as $visit)
            @php
                $isPast         = $visit->scheduled_at->isPast();
                $vConfirm       = $visit->confirmation_status;
                $statusTopColor = match($vConfirm) { 'confirmed' => '#16a34a', 'pending' => '#ca8a04', 'declined' => '#dc2626', default => '#eab308' };
                $visitTechUsers = $visit->techs->map(fn($t) => $t->user)->filter();
                $vSig           = $visit->signature;
                $vTimeEntries   = $visit->timeEntries;
                $vPaidInvoices  = $workOrder->invoices->whereIn('status', [\App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED]);
                $vBilled        = $vSig && $vPaidInvoices->isNotEmpty();
                $vPaidInvoice   = $vBilled ? $vPaidInvoices->first() : null;
                $vInvoiceNum    = $vPaidInvoice ? 'INV-' . str_pad($vPaidInvoice->id, 4, '0', STR_PAD_LEFT) : null;
            @endphp
            <div style="background:#fff;border:1px solid #e5e7eb;border-top:3px solid {{ $statusTopColor }};border-radius:8px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 1px 3px rgba(0,0,0,.05);">

                {{-- Card body --}}
                <div style="padding:.65rem .7rem .5rem;flex:1;display:flex;flex-direction:column;position:relative;">
                    {{-- Avatars + dollar: absolutely positioned so they don't affect row height --}}
                    <div style="position:absolute;top:.65rem;right:.7rem;display:flex;align-items:center;gap:.3rem;">
                        @if($visitTechUsers->isNotEmpty())
                        <div style="display:flex;align-items:center;">
                            @foreach($visitTechUsers as $tech)
                            @php $hasPhoto = $tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)); @endphp
                            <div title="{{ $tech->name }}"
                                 style="width:45px;height:45px;border-radius:50%;overflow:hidden;background:var(--primary);border:2px solid #fff;box-shadow:0 0 0 1px #e5e7eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-left:-10px;">
                                @if($hasPhoto)
                                    <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @else
                                    <span style="color:#fff;font-size:.7rem;font-weight:700;">{{ collect(explode(' ',$tech->name))->map(fn($w)=>strtoupper($w[0]??''))->take(2)->join('') }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Row 1: day + date --}}
                    <div style="font-size:.86rem;font-weight:800;color:{{ $isPast ? '#9ca3af' : 'var(--primary)' }};line-height:1.3;margin-bottom:.1rem;padding-right:3rem;">
                        {{ $visit->scheduled_at->format('l, M j, Y') }}
                    </div>

                    {{-- Row 2: time bold + duration pill --}}
                    <div style="display:flex;align-items:center;gap:.45rem;margin-bottom:.4rem;">
                        <span style="font-size:.95rem;font-weight:800;color:var(--accent);">{{ $visit->scheduled_at->format('g:i A') }}</span>
                        @if($visit->duration_estimate_minutes)
                        <span style="font-size:.7rem;background:#e0f2fe;color:#0369a1;padding:.1em .5em;border-radius:999px;font-weight:500;">{{ $durFmt($visit->duration_estimate_minutes) }} est.</span>
                        @endif
                        @if($isPast)<span style="font-size:.6rem;background:#f3f4f6;color:#9ca3af;padding:.08em .4em;border-radius:999px;font-weight:600;">past</span>@endif
                    </div>

                    {{-- Notes --}}
                    @if($visit->notes)
                    <div style="font-size:.74rem;color:#555;font-style:italic;line-height:1.4;margin-bottom:.35rem;">{{ $visit->notes }}</div>
                    @endif

                    {{-- Arrive/departure times (shows as visit completes) --}}
                    @if($vTimeEntries->isNotEmpty())
                    <div style="margin-bottom:.3rem;display:flex;flex-direction:column;gap:.2rem;">
                        @foreach($vTimeEntries as $te)
                        <div style="display:flex;align-items:center;gap:.35rem;font-size:.72rem;color:#374151;">
                            <span style="font-weight:600;color:#555;">{{ $te->user->name ?? '—' }}</span>
                            @if($te->clocked_in_at)
                            <span style="display:inline-flex;align-items:center;gap:.15rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;padding:.05rem .3rem;color:#15803d;font-size:.68rem;">
                                ↓ {{ $te->clocked_in_at->format('g:i A') }}
                            </span>
                            @endif
                            @if($te->clocked_out_at)
                            <span style="display:inline-flex;align-items:center;gap:.15rem;background:#fef2f2;border:1px solid #fecaca;border-radius:4px;padding:.05rem .3rem;color:#dc2626;font-size:.68rem;">
                                ↑ {{ $te->clocked_out_at->format('g:i A') }}
                            </span>
                            @php $m = $te->totalMinutes(); @endphp
                            @if($m)
                            <span style="font-size:.65rem;color:#9ca3af;">{{ $m >= 60 ? floor($m/60).'h'.($m%60 ? ' '.($m%60).'m':'') : $m.'m' }}</span>
                            @endif
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Completion signature (shows as visit completes) --}}
                    @if($vSig)
                    @php $vSigPath = storage_path('app/signatures/work-orders/' . $vSig->signature_path); @endphp
                    <div style="margin-bottom:.35rem;">
                        @if(file_exists($vSigPath))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($vSigPath)) }}"
                             alt="Completion signature"
                             data-sig-img
                             data-sig-caption="{{ $vSig->signer_name }} · {{ $vSig->signed_at->format('M j, g:i A') }}"
                             style="width:100%;max-height:64px;object-fit:contain;object-position:left center;border:1px solid #bbf7d0;border-radius:6px;background:#f0fdf4;display:block;margin-bottom:.3rem;cursor:zoom-in;">
                        @endif
                        <span style="display:inline-flex;align-items:center;gap:.25rem;background:#d1fae5;border:1px solid #6ee7b7;border-radius:999px;padding:.12rem .55rem;font-size:.68rem;color:#065f46;font-weight:600;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            {{ $vSig->signer_name }} · {{ $vSig->signed_at->format('M j, g:i A') }}
                        </span>
                    </div>
                    @endif

                    <div style="flex:1;"></div>

                    {{-- Footnote: scheduled --}}
                    <div style="font-size:.6rem;color:#9ca3af;margin-top:.35rem;text-align:right;">
                        Scheduled: {{ $visit->scheduled_at->format('M j, Y \a\t g:i A') }}
                    </div>
                </div>

                {{-- Action bar: Verify · Edit · Delete --}}
                <div style="border-top:1px solid #f0f0f0;padding:.4rem .65rem;background:#fafafa;display:flex;align-items:center;gap:.3rem;">

                    {{-- Verify button (first, checkbox icon) --}}
                    @if($vConfirm === 'confirmed')
                    <span style="display:inline-flex;align-items:center;gap:.25rem;color:#16a34a;font-size:.7rem;font-weight:700;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="6 12 10 16 18 8"/></svg>
                        Verified
                    </span>
                    @if($vBilled)
                    <a href="{{ route('admin.invoices.show', $vPaidInvoice) }}"
                       style="display:inline-flex;align-items:center;gap:.2rem;color:#16a34a;font-size:.7rem;font-weight:700;text-decoration:none;font-family:inherit;">
                        <span style="font-size:.8rem;line-height:1;">$</span>{{ $vInvoiceNum }}
                    </a>
                    @endif
                    @elseif(!$woLocked && $vConfirm === 'pending')
                    <button type="button"
                            onclick="openVisitOverrideModal({{ $visit->id }}, '{{ $visit->scheduled_at->format('M j, Y \a\t g:i A') }}')"
                            title="Customer confirmation pending — click to override"
                            style="display:inline-flex;align-items:center;gap:.25rem;color:#ca8a04;font-size:.7rem;font-weight:600;background:none;border:none;padding:0;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                        Awaiting
                    </button>
                    @elseif(!$woLocked)
                    <button type="button" title="Mark as Verified"
                            onclick="openVisitOverrideModal({{ $visit->id }}, '{{ $visit->scheduled_at->format('M j, Y \a\t g:i A') }}')"
                            style="display:inline-flex;align-items:center;gap:.25rem;padding:.22rem .55rem;border:1px solid #d1d5db;border-radius:5px;background:#fff;color:#374151;font-size:.7rem;font-weight:600;cursor:pointer;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                        Verify
                    </button>
                    @endif

                    {{-- Request confirmation (only when unverified & not yet pending & not locked) --}}
                    @if(!$woLocked && $vConfirm !== 'confirmed' && $vConfirm !== 'pending')
                    <form method="POST" action="{{ route('admin.work-orders.visits.request-confirm', [$workOrder, $visit]) }}" style="margin:0;">
                        @csrf
                        <button type="submit" title="Send confirmation request to customer"
                                style="display:inline-flex;align-items:center;padding:.22rem .45rem;border:1px solid #fed7aa;border-radius:5px;background:#fff7ed;color:#9a3412;font-size:.68rem;cursor:pointer;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </button>
                    </form>
                    @endif

                    <div style="flex:1;"></div>

                    @if(!$woLocked)
                    {{-- Edit (pencil) --}}
                    <button type="button" title="Edit visit"
                            onclick="openScheduleModal({id:{{ $visit->id }},date:'{{ $visit->scheduled_at->format('Y-m-d') }}',time:'{{ $visit->scheduled_at->format('H:i') }}',dur:{{ $visit->duration_estimate_minutes ?? 120 }},notes:'{{ addslashes($visit->notes ?? '') }}',techIds:{{ json_encode($visit->techs->pluck('user_id')->values()->all()) }}})"
                            style="width:26px;height:26px;padding:0;border:1px solid #d1d5db;border-radius:5px;background:#fff;color:#374151;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>

                    {{-- Delete (X) --}}
                    <form method="POST" action="{{ route('admin.work-orders.visits.destroy', [$workOrder, $visit]) }}"
                          onsubmit="return confirm('Remove this visit?')" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit" title="Remove visit"
                                style="width:26px;height:26px;padding:0;border:1px solid #fecaca;border-radius:5px;background:#fff;color:#dc2626;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
            </div>
            @endif

        </div>
        {{-- Notes --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.5rem -1.5rem 1rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Notes</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Customer updates · Internal notes</div>
                </div>
            </div>

            {{-- Add note form --}}
            <form method="POST" action="{{ route('admin.work-orders.notes.store', $workOrder) }}"
                  style="background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;padding:1rem;margin-bottom:1.25rem;">
                @csrf
                <input type="hidden" name="visibility" id="note-visibility" value="customer">
                <div style="display:flex;gap:.4rem;margin-bottom:.75rem;">
                    <button type="button" id="btn-customer" onclick="setVisibility('customer')"
                            style="padding:.3rem .85rem;border-radius:999px;border:1px solid var(--accent);background:var(--accent);color:#fff;font-size:.8rem;font-weight:600;cursor:pointer;">
                        Customer Update
                    </button>
                    <button type="button" id="btn-internal" onclick="setVisibility('internal')"
                            style="padding:.3rem .85rem;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#555;font-size:.8rem;cursor:pointer;">
                        Internal Note
                    </button>
                </div>
                <textarea name="body" rows="3" required placeholder="Write a note…"
                          style="width:100%;padding:.6rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;"></textarea>
                <div style="display:flex;justify-content:flex-end;margin-top:.6rem;">
                    <button type="submit" class="btn btn-primary btn-sm">Add Note →</button>
                </div>
            </form>

            {{-- Notes list --}}
            @forelse($workOrder->notes->sortByDesc('created_at') as $note)
            @php
                $noteAuthorName = $note->author?->name ?? 'Unknown';
                $noteInitial    = strtoupper(substr($noteAuthorName, 0, 1));
                $noteHasPhoto   = $note->author?->profile_photo
                               && file_exists(storage_path('app/profile-photos/' . $note->author->profile_photo));
            @endphp
            <div style="display:flex;gap:.6rem;align-items:flex-start;margin-bottom:.75rem;">
                {{-- Avatar --}}
                @if($noteHasPhoto)
                <img src="{{ route('users.photo', $note->author) }}" alt="{{ $noteAuthorName }}"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;margin-top:.15rem;">
                @else
                <div style="width:28px;height:28px;border-radius:50%;background:{{ $note->is_internal ? '#f59e0b' : 'var(--accent)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.15rem;">
                    <span style="font-size:.68rem;font-weight:700;color:#fff;line-height:1;">{{ $noteInitial }}</span>
                </div>
                @endif
                {{-- Content --}}
                <div style="flex:1;min-width:0;padding:.65rem .75rem;border-left:3px solid {{ $note->is_internal ? '#fbbf24' : 'var(--accent)' }};background:{{ $note->is_internal ? '#fefce8' : '#f0f6ff' }};border-radius:0 5px 5px 0;">
                    <div style="font-size:.82rem;color:#888;margin-bottom:.25rem;">
                        {{ $noteAuthorName }} · {{ $note->created_at->diffForHumans() }}
                        @if($note->is_internal)
                            <span style="color:#92400e;font-weight:600;background:#fef3c7;padding:.1rem .4rem;border-radius:3px;margin-left:.35rem;font-size:.75rem;">INTERNAL</span>
                        @else
                            <span style="color:#1e40af;font-weight:600;background:#dbeafe;padding:.1rem .4rem;border-radius:3px;margin-left:.35rem;font-size:.75rem;">CUSTOMER</span>
                        @endif
                    </div>
                    <div style="font-size:.9rem;">{{ $note->body }}</div>
                </div>
            </div>
            @empty
            <p style="color:#999;font-size:.9rem;">No notes yet.</p>
            @endforelse
        </div>

    </div>{{-- /col-2 --}}

    {{-- ── Column 3: Status · Signature · History ── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Status lifecycle --}}
        @php
            $steps = [
                'new'                => 'New',
                'triaged'            => 'Triaged',
                'scheduled'          => 'Scheduled',
                'services_performed' => 'Services Performed',
                'invoice_prepared'   => 'Invoice Prepared',
                'billed'             => 'Billed',
                'completed'          => 'Completed',
            ];
            $stepKeys   = array_keys($steps);
            $currentIdx = array_search($workOrder->status, $stepKeys);
            // awaiting_feedback is a side-state at the scheduled stage
            if ($workOrder->status === 'awaiting_feedback') $currentIdx = 2;

            $nextMap = [
                'new'                => ['status' => 'triaged',            'label' => 'Mark Triaged'],
                'triaged'            => ['status' => 'scheduled',          'label' => 'Mark Scheduled'],
                'scheduled'          => ['status' => 'services_performed', 'label' => 'Mark Services Performed'],
                'awaiting_feedback'  => ['status' => 'services_performed', 'label' => 'Mark Services Performed'],
                'services_performed' => ['status' => 'invoice_prepared',   'label' => 'Mark Invoice Prepared'],
                'invoice_prepared'   => ['status' => 'billed',             'label' => 'Mark Billed'],
                'billed'             => ['status' => 'completed',          'label' => 'Mark Completed'],
            ];
            $next = $nextMap[$workOrder->status] ?? null;
            $isTerminal = in_array($workOrder->status, ['completed', 'canceled']);
        @endphp

        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem 1rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Status Lifecycle</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Track progress · Advance status</div>
                    </div>
                </div>
                <button type="button" onclick="openStatusModal()" title="Override status"
                        style="display:flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border:1px solid rgba(255,255,255,.35);border-radius:5px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.85);font-size:.75rem;font-weight:600;cursor:pointer;flex-shrink:0;"
                        onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
                    ✎ Override
                </button>
            </div>

            {{-- Stepper --}}
            @foreach($steps as $key => $label)
            @php
                $idx    = array_search($key, $stepKeys);
                $isDone = $idx < $currentIdx;
                $isCurr = $workOrder->status === $key
                    || ($key === 'scheduled' && $workOrder->status === 'awaiting_feedback');
            @endphp
            <div style="display:flex;align-items:flex-start;gap:.65rem;padding:.3rem 0;position:relative;">
                {{-- Connector line --}}
                @if(!$loop->last)
                <div style="position:absolute;left:10px;top:22px;width:2px;height:calc(100% + 4px);background:{{ $isDone ? '#86efac' : '#e5e7eb' }};z-index:0;"></div>
                @endif
                {{-- Dot --}}
                <div style="width:20px;height:20px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;z-index:1;
                    background:{{ $isDone ? '#16a34a' : ($isCurr ? 'var(--accent)' : '#e5e7eb') }};
                    color:{{ $isDone ? '#fff' : ($isCurr ? '#fff' : '#9ca3af') }};
                    border:2px solid {{ $isDone ? '#16a34a' : ($isCurr ? 'var(--accent)' : '#d1d5db') }};">
                    {{ $isDone ? '✓' : '' }}
                </div>
                <div style="display:flex;flex-direction:column;gap:.2rem;padding-top:.1rem;">
                    <span style="font-size:.85rem;
                        color:{{ $isDone ? '#16a34a' : ($isCurr ? '#1A3C5E' : '#9ca3af') }};
                        font-weight:{{ $isCurr ? '700' : '400' }};">{{ $label }}</span>
                    @if($key === 'scheduled' && $workOrder->confirmation_status)
                        @if($workOrder->confirmation_status === 'pending')
                        <span style="font-size:.7rem;font-weight:600;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;padding:.1rem .45rem;border-radius:3px;width:fit-content;">⏳ Awaiting Customer</span>
                        @elseif($workOrder->confirmation_status === 'confirmed')
                        <span style="font-size:.7rem;font-weight:600;color:#065f46;background:#d1fae5;border:1px solid #6ee7b7;padding:.1rem .45rem;border-radius:3px;width:fit-content;">✓ Customer Confirmed</span>
                        @elseif($workOrder->confirmation_status === 'declined')
                        <span style="font-size:.7rem;font-weight:600;color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;padding:.1rem .45rem;border-radius:3px;width:fit-content;">✕ Customer Declined</span>
                        @endif
                    @endif
                    @if($key === 'scheduled' && $workOrder->status === 'awaiting_feedback')
                    <span style="font-size:.7rem;font-weight:600;color:#6d28d9;background:#ede9fe;border:1px solid #c4b5fd;padding:.1rem .45rem;border-radius:3px;width:fit-content;">⏸ Awaiting Customer Feedback</span>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Canceled indicator --}}
            @if($workOrder->status === 'canceled')
            <div style="margin-top:.75rem;padding:.5rem .75rem;background:#fee2e2;border-radius:5px;color:#991b1b;font-size:.85rem;font-weight:600;">
                ✕ Canceled
            </div>
            @endif

            {{-- Action buttons --}}
            @if(!$isTerminal)
            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:.5rem;">
                @if($next)
                <form method="POST" action="{{ route('admin.work-orders.status', $workOrder) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $next['status'] }}">
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        → {{ $next['label'] }}
                    </button>
                </form>
                @endif


                <form method="POST" action="{{ route('admin.work-orders.status', $workOrder) }}">
                    @csrf
                    <input type="hidden" name="status" value="awaiting_feedback">
                    <button type="submit" class="btn btn-secondary" style="width:100%;font-size:.83rem;">
                        Awaiting Customer Feedback
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.work-orders.status', $workOrder) }}"
                      onsubmit="return confirm('Cancel this work order?')">
                    @csrf
                    <input type="hidden" name="status" value="canceled">
                    <button type="submit" class="btn btn-danger" style="width:100%;font-size:.83rem;">
                        Cancel Work Order
                    </button>
                </form>
            </div>
            @endif

        </div>

        {{-- Status Tip --}}
        @php
            $hasUnconfirmedVisits = $workOrder->visits->whereNotIn('confirmation_status', ['confirmed'])->isNotEmpty();
            $tipData = match(true) {
                in_array($workOrder->status, ['new','triaged']) => [
                    'icon'       => '💡',
                    'title'      => 'Get This Order Moving',
                    'body'       => 'Complete the description, equipment details, and urgency level. Then assign a technician and schedule a visit — that\'s all it takes to get this order into the field.',
                    'bg'         => '#eff6ff',
                    'bar'        => '#2563eb',
                    'titleColor' => '#1d4ed8',
                    'bodyColor'  => '#1e3a8a',
                ],
                $workOrder->status === 'scheduled' && $hasUnconfirmedVisits => [
                    'icon'       => '📬',
                    'title'      => 'Confirm the Visit',
                    'body'       => 'One or more visits haven\'t been verified yet. Send a confirmation request so the customer can acknowledge the appointment before the service date.',
                    'bg'         => '#fffbeb',
                    'bar'        => '#d97706',
                    'titleColor' => '#b45309',
                    'bodyColor'  => '#78350f',
                ],
                $workOrder->status === 'scheduled' => [
                    'icon'       => '✅',
                    'title'      => 'All Set',
                    'body'       => 'All visits are confirmed and on the books. Nothing to action right now — check back after the technician performs the services.',
                    'bg'         => '#f0fdf4',
                    'bar'        => '#16a34a',
                    'titleColor' => '#15803d',
                    'bodyColor'  => '#166534',
                ],
                $workOrder->status === 'awaiting_feedback' => [
                    'icon'       => '💬',
                    'title'      => 'Waiting on the Customer',
                    'body'       => 'The customer has been asked to confirm or provide feedback. Follow up directly if you haven\'t heard back within a reasonable timeframe.',
                    'bg'         => '#fffbeb',
                    'bar'        => '#d97706',
                    'titleColor' => '#b45309',
                    'bodyColor'  => '#78350f',
                ],
                $workOrder->status === 'services_performed' => [
                    'icon'       => '🧾',
                    'title'      => 'Ready to Invoice',
                    'body'       => 'Services are marked complete. Generate an invoice from this work order to send to the customer and move into billing.',
                    'bg'         => '#f5f3ff',
                    'bar'        => '#7c3aed',
                    'titleColor' => '#6d28d9',
                    'bodyColor'  => '#4c1d95',
                ],
                in_array($workOrder->status, ['invoice_prepared','billed']) => [
                    'icon'       => '💳',
                    'title'      => 'Finalize Payment',
                    'body'       => 'The invoice has been issued. Confirm payment with the customer, mark all invoices complete, and close out this work order once everything is settled.',
                    'bg'         => '#fff7ed',
                    'bar'        => '#ea580c',
                    'titleColor' => '#c2410c',
                    'bodyColor'  => '#7c2d12',
                ],
                $workOrder->status === 'completed' => [
                    'icon'       => '🎉',
                    'title'      => 'All Done',
                    'body'       => 'Services were performed and all invoices are settled. This work order is fully closed out — great work.',
                    'bg'         => '#f0fdf4',
                    'bar'        => '#16a34a',
                    'titleColor' => '#15803d',
                    'bodyColor'  => '#166534',
                ],
                default => null,
            };
        @endphp
        @if($tipData)
        <div style="display:flex;align-items:flex-start;gap:.65rem;background:{{ $tipData['bg'] }};border-radius:7px;border-left:4px solid {{ $tipData['bar'] }};padding:.65rem .85rem .65rem .8rem;">
            <span style="font-size:.95rem;flex-shrink:0;line-height:1.5;">{{ $tipData['icon'] }}</span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.75rem;font-weight:700;color:{{ $tipData['titleColor'] }};margin-bottom:.15rem;">{{ $tipData['title'] }}</div>
                <div style="font-size:.74rem;color:{{ $tipData['bodyColor'] }};line-height:1.45;">{{ $tipData['body'] }}</div>
            </div>
            <span style="font-size:.55rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:{{ $tipData['bar'] }};opacity:.5;flex-shrink:0;padding-top:.2rem;">TIP</span>
        </div>
        @endif

        {{-- History --}}
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem 1rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">History</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Audit trail · Recent changes</div>
                </div>
            </div>
            @forelse($workOrder->history->sortByDesc('changed_at')->take(15) as $h)
            <div style="font-size:.8rem;color:#666;padding:.35rem 0;border-bottom:1px solid #f5f5f5;">
                @if($h->old_value)
                <span style="font-weight:600;">{{ $h->field_name }}</span>:
                <span style="color:#999;">{{ $h->old_value }}</span> →
                <span>{{ $h->new_value ?: '—' }}</span>
                @else
                <span style="font-weight:600;">{{ $h->field_name }}</span>:
                <span>{{ $h->new_value }}</span>
                @endif
                @if($h->comment)
                <div style="color:#555;font-size:.78rem;margin-top:.2rem;padding:.25rem .5rem;background:#f8f9fa;border-left:2px solid #d1d5db;border-radius:0 3px 3px 0;">
                    {{ $h->comment }}
                </div>
                @endif
                <div style="color:#bbb;font-size:.75rem;margin-top:.15rem;">{{ \Carbon\Carbon::parse($h->changed_at)->diffForHumans() }} · {{ $h->changedBy->name ?? 'Unknown' }}</div>
            </div>
            @empty
            <p style="font-size:.82rem;color:#999;">No history.</p>
            @endforelse
        </div>

    </div>{{-- /col-3 --}}

</div>{{-- /main-3col-layout --}}

{{-- Attachments management modal --}}
<div id="attach-modal" onclick="if(event.target===this)closeAttachModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:100%;max-width:680px;margin:1rem;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">

        {{-- Header --}}
        <div style="background:var(--primary);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <span style="color:#fff;font-weight:700;font-size:1rem;">📎 Attachments
                @if($workOrder->attachments->count())
                <span style="font-size:.82rem;font-weight:400;opacity:.7;">({{ $workOrder->attachments->count() }})</span>
                @endif
            </span>
            <button type="button" onclick="closeAttachModal()"
                    style="background:none;border:none;color:rgba(255,255,255,.8);font-size:1.5rem;cursor:pointer;line-height:1;">×</button>
        </div>

        <div style="overflow-y:auto;padding:1.25rem 1.5rem;flex:1;">

            {{-- Photos --}}
            @if($photos->count())
            <p style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .65rem;">Photos</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.65rem;margin-bottom:1.5rem;">
                @foreach($photos as $a)
                <div style="border-radius:6px;overflow:hidden;border:1px solid #e5e7eb;position:relative;background:#f8f9fa;">
                    <img src="{{ route('attachments.view', $a) }}"
                         alt="{{ $a->original_name }}"
                         style="width:100%;height:100px;object-fit:cover;display:block;cursor:zoom-in;transition:opacity .15s;"
                         onclick="openLightbox('{{ route('attachments.view', $a) }}','{{ addslashes($a->original_name) }}','{{ route('attachments.download', $a) }}')"
                         onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                    <a href="{{ route('attachments.download', $a) }}" download title="Download"
                       style="position:absolute;top:5px;left:5px;width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,.5);color:#fff;font-size:.65rem;display:flex;align-items:center;justify-content:center;text-decoration:none;">⬇</a>
                    <form method="POST" action="{{ route('admin.work-orders.attachments.remove', [$workOrder, $a]) }}"
                          onsubmit="return confirm('Remove this photo?')" style="position:absolute;top:5px;right:5px;margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="width:24px;height:24px;border-radius:50%;background:rgba(220,38,38,.75);border:none;color:#fff;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
                    </form>
                    <div style="padding:.3rem .45rem .1rem;font-size:.64rem;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $a->original_name }}">{{ $a->original_name }}</div>
                    <div style="padding:0 .45rem .35rem;font-size:.62rem;color:#aaa;">{{ round($a->size_bytes/1024) }} KB</div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Documents --}}
            @if($docs->count())
            <p style="font-size:.72rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .5rem;">Documents</p>
            <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.5rem;">
                @foreach($docs as $a)
                <div style="display:flex;align-items:center;gap:.6rem;background:#f8f9fa;padding:.55rem .75rem;border-radius:6px;border:1px solid #e5e7eb;">
                    <span style="font-size:1.2rem;flex-shrink:0;">📄</span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.84rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $a->original_name }}">{{ $a->original_name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ round($a->size_bytes/1024) }} KB</div>
                    </div>
                    <div style="display:flex;gap:.35rem;flex-shrink:0;">
                        @if(in_array($a->mime_type, $previewable))
                        <button type="button"
                                onclick="openFilePreview('{{ route('attachments.view', $a) }}','{{ addslashes($a->original_name) }}','{{ route('attachments.download', $a) }}')"
                                style="padding:.25rem .6rem;border:1px solid var(--accent);border-radius:4px;background:#fff;color:var(--accent);font-size:.76rem;cursor:pointer;white-space:nowrap;">Preview</button>
                        @endif
                        <a href="{{ route('attachments.download', $a) }}"
                           style="padding:.25rem .6rem;border:1px solid #d1d5db;border-radius:4px;background:#fff;color:#374151;font-size:.76rem;text-decoration:none;white-space:nowrap;">↓ Download</a>
                        <form method="POST" action="{{ route('admin.work-orders.attachments.remove', [$workOrder, $a]) }}"
                              onsubmit="return confirm('Remove this document?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="padding:.25rem .6rem;border:1px solid #fca5a5;border-radius:4px;background:#fff;color:#dc2626;font-size:.76rem;cursor:pointer;white-space:nowrap;">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if(!$photos->count() && !$docs->count())
            <div style="text-align:center;padding:2rem 1rem 1.25rem;color:#9ca3af;">
                <div style="font-size:2.5rem;margin-bottom:.5rem;">📂</div>
                <p style="font-size:.9rem;margin:0;">No attachments yet. Upload files below.</p>
            </div>
            @endif

            {{-- Upload section --}}
            <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;margin-top:.25rem;">
                <p style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .85rem;">Upload Files</p>

                {{-- Drag & Drop Zone --}}
                <div id="attach-drop-zone"
                     onclick="document.getElementById('attach-file-input').click()"
                     ondragover="attachDragOver(event)" ondragleave="attachDragLeave(event)" ondrop="attachDrop(event)"
                     style="border:2px dashed #d1d5db;border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;background:#fafafa;margin-bottom:.75rem;user-select:none;">
                    <div style="font-size:1.8rem;margin-bottom:.35rem;pointer-events:none;">📁</div>
                    <div style="font-size:.88rem;color:#6b7280;font-weight:500;pointer-events:none;">Drop files here or <span style="color:var(--accent);text-decoration:underline;">click to browse</span></div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.3rem;pointer-events:none;">Photos: JPG, PNG, GIF, WebP (max 10 MB) · Docs: PDF, Word, Excel, TXT (max 20 MB)</div>
                </div>

                {{-- Hidden file input --}}
                <input type="file" id="attach-file-input" multiple
                       accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                       style="display:none;" onchange="attachFilesSelected(this.files)">

                {{-- Selected files list --}}
                <div id="attach-file-list" style="display:none;margin-bottom:.75rem;">
                    <p style="font-size:.72rem;font-weight:700;color:#555;margin:0 0 .4rem;">Selected files:</p>
                    <div id="attach-file-list-items" style="display:flex;flex-direction:column;gap:.3rem;max-height:140px;overflow-y:auto;"></div>
                </div>

                {{-- Progress bar --}}
                <div id="attach-prog-wrap" style="display:none;margin-bottom:.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem;">
                        <span style="font-size:.78rem;color:#6b7280;" id="attach-prog-label">Uploading…</span>
                        <span style="font-size:.78rem;font-weight:700;color:var(--accent);" id="attach-prog-pct">0%</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;">
                        <div id="attach-prog-bar" style="height:100%;width:0%;background:var(--accent);border-radius:999px;transition:width .1s linear;"></div>
                    </div>
                </div>

                {{-- Status message --}}
                <div id="attach-status-msg" style="display:none;font-size:.82rem;padding:.5rem .75rem;border-radius:5px;margin-bottom:.75rem;"></div>

                {{-- Upload button row --}}
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <button type="button" id="attach-upload-btn" onclick="doAttachUpload()"
                            style="padding:.45rem 1.2rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:600;cursor:pointer;transition:opacity .15s;opacity:.45;"
                            disabled>
                        Upload
                    </button>
                    <span id="attach-file-count" style="font-size:.8rem;color:#9ca3af;"></span>
                </div>

                {{-- Hidden form carrier (provides @csrf token + action URL for XHR) --}}
                <form id="attach-upload-form" method="POST" action="{{ route('admin.work-orders.attachments.add', $workOrder) }}" enctype="multipart/form-data" style="display:none;">
                    @csrf
                </form>
            </div>

        </div>
    </div>
</div>

{{-- File preview modal --}}
<div id="file-preview-modal" onclick="if(event.target===this)closeFilePreview()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9998;flex-direction:column;">
    <div style="display:flex;align-items:center;gap:.75rem;background:#1e293b;padding:.65rem 1rem;flex-shrink:0;">
        <span id="fp-name" style="color:#e2e8f0;font-size:.88rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
        <a id="fp-download" href="#" download
           style="padding:.3rem .8rem;border:1px solid #475569;border-radius:5px;color:#cbd5e1;font-size:.82rem;text-decoration:none;flex-shrink:0;">
            Download
        </a>
        <button onclick="closeFilePreview()"
                style="padding:.3rem .8rem;border:1px solid #475569;border-radius:5px;background:transparent;color:#cbd5e1;font-size:.82rem;cursor:pointer;flex-shrink:0;">
            Close
        </button>
    </div>
    <iframe id="fp-frame" src="" style="flex:1;border:none;background:#fff;"></iframe>
</div>

{{-- Status override modal --}}
<div id="status-modal" onclick="if(event.target===this)closeStatusModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="font-size:1rem;color:var(--primary);margin-top:0;margin-bottom:1.25rem;">Override Status</h3>
        <form method="POST" action="{{ route('admin.work-orders.status', $workOrder) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">New Status</label>
                <select name="status" required
                        style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.9rem;background:#fff;">
                    @foreach([
                        'new'                => 'New',
                        'triaged'            => 'Triaged',
                        'scheduled'          => 'Scheduled',
                        'awaiting_feedback'  => 'Awaiting Customer Feedback',
                        'services_performed' => 'Services Performed',
                        'invoice_prepared'   => 'Invoice Prepared',
                        'billed'             => 'Billed',
                        'completed'          => 'Completed',
                        'canceled'           => 'Canceled',
                    ] as $val => $lbl)
                    <option value="{{ $val }}" {{ $workOrder->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">
                    Reason <span style="font-weight:400;color:#999;">(optional)</span>
                </label>
                <textarea name="comment" rows="3" maxlength="1000"
                          placeholder="Explain why the status is being manually changed…"
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeStatusModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Apply Override</button>
            </div>
        </form>
    </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="if(event.target===this)closeLightbox()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:9999;align-items:center;justify-content:center;flex-direction:column;padding:1.5rem;">
    <div style="position:relative;max-width:90vw;max-height:82vh;">
        <img id="lightbox-img" src="" alt=""
             style="max-width:100%;max-height:82vh;border-radius:8px;display:block;box-shadow:0 8px 40px rgba(0,0,0,.5);">
    </div>
    <div style="display:flex;align-items:center;gap:1rem;margin-top:1rem;">
        <span id="lightbox-name" style="color:#ddd;font-size:.88rem;"></span>
        <a id="lightbox-download" href="#" download
           style="color:#fff;background:rgba(255,255,255,.15);padding:.35rem .9rem;border-radius:5px;text-decoration:none;font-size:.83rem;border:1px solid rgba(255,255,255,.3);">
            Download
        </a>
        <button onclick="closeLightbox()"
                style="color:#fff;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);padding:.35rem .9rem;border-radius:5px;font-size:.83rem;cursor:pointer;">
            Close
        </button>
    </div>
</div>

<script>
// Inline details toggle
function expandDetails() {
    const body    = document.getElementById('details-body');
    const summary = document.getElementById('details-collapsed-summary');
    if (!body || body.dataset.collapsed !== '1') return;
    document.getElementById('wo-details-card').style.minHeight = '';
    document.getElementById('customer-card').style.minHeight   = '';
    body.style.gridTemplateRows = '1fr';
    body.style.opacity          = '1';
    body.dataset.collapsed      = '0';
    if (summary) summary.style.display = 'none';
}

function collapseDetails() {
    const body    = document.getElementById('details-body');
    const summary = document.getElementById('details-collapsed-summary');
    if (!body) return;
    body.style.gridTemplateRows = '0fr';
    body.style.opacity          = '0';
    body.dataset.collapsed      = '1';
    if (summary) summary.style.display = 'block';
    requestAnimationFrame(syncCardHeights);
}

function toggleRelatedOrders(btn) {
    const body    = btn.closest('div').nextElementSibling;
    const chevron = btn.querySelector('.rel-chevron');
    const open    = body.style.maxHeight && body.style.maxHeight !== '0px';
    if (open) {
        body.style.maxHeight = '0';
        chevron.style.transform = 'rotate(0deg)';
    } else {
        body.style.maxHeight = body.scrollHeight + 'px';
        chevron.style.transform = 'rotate(180deg)';
    }
}

function syncCardHeights() {
    const woCard  = document.getElementById('wo-details-card');
    const cusCard = document.getElementById('customer-card');
    const body    = document.getElementById('details-body');
    if (!woCard || !cusCard || !body) return;
    if (body.dataset.collapsed === '1') {
        woCard.style.minHeight  = '';
        cusCard.style.minHeight = '';
        const target = Math.max(
            cusCard.getBoundingClientRect().height,
            woCard.getBoundingClientRect().height
        );
        woCard.style.minHeight  = target + 'px';
        cusCard.style.minHeight = target + 'px';
    } else {
        woCard.style.minHeight  = '';
        cusCard.style.minHeight = '';
    }
}


function setEditBtnActive(active) {
    const btn = document.getElementById('edit-toggle-btn');
    if (!btn) return;
    if (active) {
        btn.classList.add('is-active');
        btn.style.background  = 'rgba(255,255,255,.95)';
        btn.style.color       = 'var(--primary)';
        btn.style.borderColor = 'rgba(255,255,255,.95)';
    } else {
        btn.classList.remove('is-active');
        btn.style.background  = 'rgba(255,255,255,.08)';
        btn.style.color       = 'rgba(255,255,255,.85)';
        btn.style.borderColor = 'rgba(255,255,255,.35)';
    }
}

function toggleEdit() {
    const display = document.getElementById('details-display');
    const form    = document.getElementById('details-edit-form');
    const editing = form.style.display === 'none';
    if (editing) {
        expandDetails();
        display.style.display = 'none';
        form.style.display    = '';
    } else {
        display.style.display = '';
        form.style.display    = 'none';
        collapseDetails();
    }
    setEditBtnActive(editing);
}

function initServicePills() {
    document.querySelectorAll('.svc-pill-inline').forEach(function(pill) {
        var inp = pill.querySelector('input[type="checkbox"]');
        var ico = pill.querySelector('.svc-icon-inline');
        function update() {
            if (inp.checked) {
                pill.style.borderColor = 'var(--accent)';
                pill.style.background  = '#f0f6ff';
                pill.style.color       = 'var(--accent)';
                if (ico) ico.setAttribute('stroke', 'var(--accent)');
            } else {
                pill.style.borderColor = '#e5e7eb';
                pill.style.background  = '#f9fafb';
                pill.style.color       = '#6b7280';
                if (ico) ico.setAttribute('stroke', '#9ca3af');
            }
        }
        pill.addEventListener('click', function(e) {
            e.preventDefault();
            inp.checked = !inp.checked;
            update();
        });
        update();
    });
}
initServicePills();

function toggleSchedPrefs() {
    const body    = document.getElementById('sched-prefs-body');
    const chevron = document.getElementById('sched-prefs-chevron');
    const open    = body.style.display !== 'none';
    body.style.display     = open ? 'none' : 'block';
    chevron.style.transform = open ? '' : 'rotate(180deg)';
}

@if($errors->any() || session('auto_edit'))
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('details-edit-form');
    if (form) {
        document.getElementById('details-display').style.display = 'none';
        form.style.display = '';
        setEditBtnActive(true);
        expandDetails();
    }
});
@endif

// ── Admin edit-form urgency toggle ────────────────────────────────────────
(function () {
    const LEVELS = ['routine', 'urgent', 'emergency'];
    const STYLES = {
        routine:   { bg: 'rgba(255,255,255,.1)', border: 'rgba(255,255,255,.35)', label: 'Routine' },
        urgent:    { bg: '#d97706',              border: '#d97706',               label: 'Urgent' },
        emergency: { bg: '#dc2626',              border: '#dc2626',               label: 'Emergency' },
    };
    const input = document.getElementById('admin-urgency-input');
    const btn   = document.getElementById('urgency-toggle-btn');
    const lbl   = document.getElementById('urgency-toggle-label');
    if (!input || !btn) return;

    function applyUrgency(val) {
        input.value = val;
        const s = STYLES[val] || STYLES.routine;
        btn.style.background  = s.bg;
        btn.style.borderColor = s.border;
        lbl.textContent = s.label;
    }

    window.cycleUrgency = function () {
        const cur  = input.value || 'routine';
        const next = LEVELS[(LEVELS.indexOf(cur) + 1) % LEVELS.length];
        applyUrgency(next);
        fetch('{{ route('admin.work-orders.urgency', $workOrder) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ urgency: next }),
        });
    };

    applyUrgency(input.value || 'routine');
})();

// ── Admin edit-form availability picker + smart date ──────────────────────
(function () {
    const DAY_TO_JS    = { monday:1, tuesday:2, wednesday:3, thursday:4, friday:5, saturday:6 };
    const DAY_NAMES    = { monday:'Monday', tuesday:'Tuesday', wednesday:'Wednesday', thursday:'Thursday', friday:'Friday', saturday:'Saturday' };
    const jsonInput    = document.getElementById('admin-avail-json');
    if (!jsonInput) return;

    const defaultAvail = @json($workOrder->customer->preferred_availability ?? (object)[]);
    const state        = {};
    const hasSavedDate = {{ $workOrder->preferred_date ? 'true' : 'false' }};

    try {
        const initial = JSON.parse(jsonInput.value || '{}');
        Object.entries(initial).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    } catch (e) {}

    // For new work orders with no saved availability, pre-fill from customer defaults
    if (Object.keys(state).length === 0 && defaultAvail && typeof defaultAvail === 'object') {
        Object.entries(defaultAvail).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    }

    function toYMD(d) {
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function updateSmartDate() {
        const dateInput = document.getElementById('admin-preferred-date');
        const hint      = document.getElementById('admin-date-hint');
        if (!dateInput || !hint) return;
        if (dateInput.dataset.userSet === '1') return;

        const selectedDays = Object.keys(state);
        const today = new Date(); today.setHours(0,0,0,0);
        const start = new Date(today); start.setDate(start.getDate()+1);

        let targetDate = null, hintText = '';

        if (selectedDays.length > 0) {
            const nums = selectedDays.map(d => DAY_TO_JS[d]).filter(Boolean);
            const d = new Date(start);
            for (let i = 0; i < 14; i++) {
                if (nums.includes(d.getDay())) {
                    const key = Object.entries(DAY_TO_JS).find(([,n]) => n === d.getDay())?.[0];
                    hintText = `Next available ${DAY_NAMES[key]} — based on preferred availability`;
                    targetDate = d;
                    break;
                }
                d.setDate(d.getDate()+1);
            }
        }

        if (!targetDate) {
            const d = new Date(start);
            while (d.getDay() === 0 || d.getDay() === 6) d.setDate(d.getDate()+1);
            targetDate = d;
            hintText = '';
        }

        if (!hasSavedDate || selectedDays.length > 0) {
            dateInput.value = toYMD(targetDate);
        }
        hint.textContent = hintText;
        hint.style.display = hintText ? '' : 'none';
    }

    function syncJson() {
        const out = {};
        Object.entries(state).forEach(([day, slots]) => { if (slots.size) out[day] = [...slots]; });
        jsonInput.value = JSON.stringify(out);
    }

    function checkDefaultsDiff() {
        const box  = document.getElementById('admin-update-defaults-box');
        if (!box) return;
        const DAYS  = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        const SLOTS = ['morning','lunch','afternoon'];
        function normalize(obj) {
            const r = {};
            DAYS.forEach(d => {
                const arr   = obj[d];
                const items = arr instanceof Set ? [...arr] : (Array.isArray(arr) ? arr : []);
                const f     = items.filter(x => SLOTS.includes(x)).sort();
                if (f.length) r[d] = f;
            });
            return JSON.stringify(r);
        }
        box.style.display = normalize(state) === normalize(defaultAvail || {}) ? 'none' : '';
    }

    function renderDayBtn(btn) {
        const active = !!state[btn.dataset.day];
        btn.style.background  = active ? 'var(--primary)' : '#fff';
        btn.style.color       = active ? '#fff'           : '#6b7280';
        btn.style.borderColor = active ? 'var(--primary)' : '#d1d5db';
    }

    function renderSlotBtn(btn) {
        const active = state[btn.dataset.day]?.has(btn.dataset.slot);
        btn.style.background  = active ? '#3b82f6' : '#fff';
        btn.style.borderColor = active ? '#3b82f6' : '#93c5fd';
        const name = btn.querySelector('.sb-name');
        const time = btn.querySelector('.sb-time');
        if (name) name.style.color = active ? '#fff'                  : '#3b82f6';
        if (time) time.style.color = active ? 'rgba(255,255,255,.75)' : '#93c5fd';
    }

    function applyState() {
        const panels    = document.querySelectorAll('.admin-avail-day-panel');
        const container = document.getElementById('admin-avail-panels');
        let anyVisible  = false;

        document.querySelectorAll('.admin-avail-day-btn').forEach(renderDayBtn);

        panels.forEach(panel => {
            const show = !!state[panel.dataset.day];
            panel.style.display = show ? 'flex' : 'none';
            if (show) anyVisible = true;
        });

        let lastVisible = null;
        panels.forEach(p => { if (p.style.display !== 'none') lastVisible = p; });
        panels.forEach(p => { p.style.borderBottom = p === lastVisible ? 'none' : '1px solid #dbeafe'; });

        container.style.display = anyVisible ? '' : 'none';
        document.querySelectorAll('.admin-avail-slot-btn').forEach(renderSlotBtn);
        syncJson();
        updateSmartDate();
        checkDefaultsDiff();
    }

    document.querySelectorAll('.admin-avail-day-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            if (state[day]) delete state[day]; else state[day] = new Set();
            applyState();
        });
    });

    document.querySelectorAll('.admin-avail-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const { day, slot } = btn.dataset;
            if (!state[day]) state[day] = new Set();
            if (state[day].has(slot)) state[day].delete(slot); else state[day].add(slot);
            renderSlotBtn(btn);
            syncJson();
            checkDefaultsDiff();
        });
    });

    // Let user manually override the date without auto-recalculating
    document.getElementById('admin-preferred-date')?.addEventListener('change', function() {
        this.dataset.userSet = '1';
        document.getElementById('admin-date-hint').style.display = 'none';
    });

    applyState();
})();

// File preview modal
function openFilePreview(viewUrl, name, downloadUrl) {
    const m = document.getElementById('file-preview-modal');
    document.getElementById('fp-frame').src = viewUrl;
    document.getElementById('fp-name').textContent = name;
    document.getElementById('fp-download').href = downloadUrl;
    m.style.display = 'flex';
    document.addEventListener('keydown', fpKeyHandler);
}
function closeFilePreview() {
    const m = document.getElementById('file-preview-modal');
    m.style.display = 'none';
    document.getElementById('fp-frame').src = '';
    document.removeEventListener('keydown', fpKeyHandler);
}
function fpKeyHandler(e) { if (e.key === 'Escape') closeFilePreview(); }

// Status override modal
function openStatusModal() {
    const m = document.getElementById('status-modal');
    m.style.display = 'flex';
    document.addEventListener('keydown', statusModalKeyHandler);
}
function closeStatusModal() {
    document.getElementById('status-modal').style.display = 'none';
    document.removeEventListener('keydown', statusModalKeyHandler);
}
function statusModalKeyHandler(e) { if (e.key === 'Escape') closeStatusModal(); }

// Employee assignment toggle
function toggleAssignForm() {
    const f = document.getElementById('assign-form');
    if (!f) return;
    const open = f.style.display !== 'none';
    f.style.display = open ? 'none' : 'block';
}

// Note visibility toggle
function setVisibility(v) {
    document.getElementById('note-visibility').value = v;
    const accent = 'var(--accent)', gray = '#d1d5db';
    const btnC = document.getElementById('btn-customer');
    const btnI = document.getElementById('btn-internal');
    if (v === 'customer') {
        btnC.style.cssText = 'padding:.3rem .85rem;border-radius:999px;border:1px solid var(--accent);background:var(--accent);color:#fff;font-size:.8rem;font-weight:600;cursor:pointer;';
        btnI.style.cssText = 'padding:.3rem .85rem;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#555;font-size:.8rem;cursor:pointer;';
    } else {
        btnI.style.cssText = 'padding:.3rem .85rem;border-radius:999px;border:1px solid #fbbf24;background:#fbbf24;color:#78350f;font-size:.8rem;font-weight:600;cursor:pointer;';
        btnC.style.cssText = 'padding:.3rem .85rem;border-radius:999px;border:1px solid #d1d5db;background:#fff;color:#555;font-size:.8rem;cursor:pointer;';
    }
}

// Lightbox
function openLightbox(viewUrl, name, downloadUrl) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lightbox-img').src = viewUrl;
    document.getElementById('lightbox-name').textContent = name;
    document.getElementById('lightbox-download').href = downloadUrl;
    lb.style.display = 'flex';
    document.addEventListener('keydown', lbKeyHandler);
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.getElementById('lightbox-img').src = '';
    document.removeEventListener('keydown', lbKeyHandler);
}
function lbKeyHandler(e) { if (e.key === 'Escape') closeLightbox(); }

// ── Attachments modal ─────────────────────────────────────────
let attachPendingFiles = [];

function openAttachModal() {
    document.getElementById('attach-modal').style.display = 'flex';
    document.addEventListener('keydown', attachModalKeyHandler);
}
function closeAttachModal() {
    document.getElementById('attach-modal').style.display = 'none';
    document.removeEventListener('keydown', attachModalKeyHandler);
    // reset upload state
    attachPendingFiles = [];
    renderAttachFileList();
    const inp = document.getElementById('attach-file-input');
    if (inp) inp.value = '';
    const prog = document.getElementById('attach-prog-wrap');
    if (prog) prog.style.display = 'none';
    const msg = document.getElementById('attach-status-msg');
    if (msg) msg.style.display = 'none';
    const bar = document.getElementById('attach-prog-bar');
    if (bar) { bar.style.width = '0%'; bar.style.background = 'var(--accent)'; }
    const btn = document.getElementById('attach-upload-btn');
    if (btn) { btn.disabled = true; btn.style.opacity = '.45'; btn.textContent = 'Upload'; }
}
function attachModalKeyHandler(e) { if (e.key === 'Escape') closeAttachModal(); }

function attachDragOver(e) {
    e.preventDefault();
    const z = document.getElementById('attach-drop-zone');
    z.style.borderColor = 'var(--accent)';
    z.style.background  = '#f0f6ff';
}
function attachDragLeave() {
    const z = document.getElementById('attach-drop-zone');
    z.style.borderColor = '#d1d5db';
    z.style.background  = '#fafafa';
}
function attachDrop(e) {
    e.preventDefault();
    attachDragLeave();
    attachFilesSelected(e.dataTransfer.files);
}
function attachFilesSelected(fileList) {
    const imageTypes = ['image/jpeg','image/png','image/gif','image/webp'];
    Array.from(fileList).forEach(f => {
        attachPendingFiles.push({ file: f, type: imageTypes.includes(f.type) ? 'photo' : 'doc' });
    });
    renderAttachFileList();
}
function renderAttachFileList() {
    const list  = document.getElementById('attach-file-list');
    const items = document.getElementById('attach-file-list-items');
    const btn   = document.getElementById('attach-upload-btn');
    const count = document.getElementById('attach-file-count');
    if (!attachPendingFiles.length) {
        list.style.display  = 'none';
        btn.disabled        = true;
        btn.style.opacity   = '.45';
        count.textContent   = '';
        return;
    }
    list.style.display = '';
    btn.disabled       = false;
    btn.style.opacity  = '1';
    const n = attachPendingFiles.length;
    count.textContent = n + ' file' + (n > 1 ? 's' : '') + ' ready to upload';
    items.innerHTML = attachPendingFiles.map((pf, i) => `
        <div style="display:flex;align-items:center;gap:.5rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:5px;padding:.35rem .65rem;font-size:.82rem;">
            <span>${pf.type === 'photo' ? '🖼️' : '📄'}</span>
            <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151;">${pf.file.name}</span>
            <span style="color:#9ca3af;flex-shrink:0;font-size:.76rem;">${(pf.file.size/1024).toFixed(0)} KB</span>
            <button type="button" onclick="removeAttachFile(${i})"
                    style="border:none;background:none;color:#9ca3af;cursor:pointer;font-size:.9rem;padding:0 2px;line-height:1;flex-shrink:0;" title="Remove">✕</button>
        </div>`).join('');
}
function removeAttachFile(i) {
    attachPendingFiles.splice(i, 1);
    renderAttachFileList();
}

function doAttachUpload() {
    if (!attachPendingFiles.length) return;

    const form  = document.getElementById('attach-upload-form');
    const fd    = new FormData(form); // carries @csrf token
    attachPendingFiles.forEach(pf => fd.append(pf.type === 'photo' ? 'photos[]' : 'documents[]', pf.file, pf.file.name));

    const btn   = document.getElementById('attach-upload-btn');
    const wrap  = document.getElementById('attach-prog-wrap');
    const bar   = document.getElementById('attach-prog-bar');
    const pct   = document.getElementById('attach-prog-pct');
    const label = document.getElementById('attach-prog-label');
    const msg   = document.getElementById('attach-status-msg');

    btn.disabled      = true;
    btn.style.opacity = '.6';
    btn.textContent   = 'Uploading…';
    wrap.style.display = '';
    bar.style.width   = '0%';
    bar.style.background = 'var(--accent)';
    pct.textContent   = '0%';
    label.textContent = 'Uploading…';
    msg.style.display = 'none';

    const xhr = new XMLHttpRequest();
    xhr.upload.addEventListener('progress', ev => {
        if (ev.lengthComputable) {
            const p = Math.round(ev.loaded / ev.total * 100);
            bar.style.width  = p + '%';
            pct.textContent  = p + '%';
        }
    });
    xhr.addEventListener('loadend', () => {
        if (xhr.status >= 200 && xhr.status < 400) {
            bar.style.width      = '100%';
            bar.style.background = '#16a34a';
            pct.textContent      = '100%';
            label.textContent    = 'Upload complete!';
            btn.textContent      = '✓ Done';
            setTimeout(() => window.location.reload(), 700);
        } else {
            wrap.style.display = 'none';
            btn.disabled       = false;
            btn.style.opacity  = '1';
            btn.textContent    = 'Upload';
            showAttachMsg('error', 'Upload failed (HTTP ' + xhr.status + '). Please try again.');
        }
    });
    xhr.addEventListener('error', () => {
        wrap.style.display = 'none';
        btn.disabled       = false;
        btn.style.opacity  = '1';
        btn.textContent    = 'Upload';
        showAttachMsg('error', 'Network error. Please try again.');
    });
    xhr.open('POST', form.action);
    xhr.send(fd);
}

function showAttachMsg(type, text) {
    const el = document.getElementById('attach-status-msg');
    el.style.display    = '';
    el.style.background = type === 'error' ? '#fef2f2' : '#f0fdf4';
    el.style.color      = type === 'error' ? '#dc2626' : '#16a34a';
    el.style.border     = '1px solid ' + (type === 'error' ? '#fca5a5' : '#86efac');
    el.textContent      = text;
}

// ── Schedule modal ────────────────────────────────────────────
// visit: null → Add mode, object {id, date, time, dur, notes} → Edit mode
let _editingVisitTechIds = [];
let _origVisitDate       = null;
let _origVisitTime       = null;
let _origVisitDur        = null;
let _isEditMode          = false;

@php
    $allEmployeesJs = $employees->map(fn($e) => [
        'id'       => $e->id,
        'first'    => explode(' ', $e->name)[0],
        'initials' => strtoupper(substr($e->name, 0, 1)),
        'photo'    => $e->profile_photo ? route('users.photo', $e) : null,
    ])->values();
@endphp
const _allEmployees = @json($allEmployeesJs);

let _selectedTechIds = new Set();
let _lastFetchKey    = null;

function setConfPillsEnabled(enabled) {
    const pillConfirmed = document.getElementById('conf-pill-confirmed');
    const pillRequest   = document.getElementById('conf-pill-request');
    const noChangesNote = document.getElementById('conf-no-changes-note');
    [pillConfirmed, pillRequest].forEach(btn => {
        btn.disabled      = !enabled;
        btn.style.opacity = enabled ? '1' : '0.38';
        btn.style.cursor  = enabled ? 'pointer' : 'not-allowed';
    });
    if (noChangesNote) noChangesNote.style.display = enabled ? 'none' : '';
}

function syncConfSection() {
    const confInput = document.getElementById('conf-action-input');
    if (!confInput) return;

    if (!_isEditMode) {
        // Add Visit: always enabled
        setConfPillsEnabled(true);
        return;
    }

    const curDate   = document.getElementById('sch_date').value;
    const curTime   = document.getElementById('sch_time').value;
    const isChanged = curDate !== _origVisitDate
                   || curTime !== _origVisitTime
                   || _durVal !== _origVisitDur;

    if (isChanged) {
        // Something differs from saved — enable and default to Confirmed
        setConfPillsEnabled(true);
        if (!confInput.value) setConfirmPill('confirmed');
    } else {
        // All fields match saved values — disable, no confirmation action needed
        setConfPillsEnabled(false);
        confInput.value = '';
    }
}

function onVisitDateTimeChange() { syncConfSection(); }
function onVisitDurationChange()  { syncConfSection(); }

function openScheduleModal(visit) {
    const form     = document.getElementById('schedule-form');
    const methodEl = document.getElementById('visit-method-input');
    const idEl     = document.getElementById('visit-id-input');
    const titleEl  = document.getElementById('schedule-modal-title');
    const saveBtn  = document.getElementById('schedule-save-btn');

    _editingVisitTechIds = (visit && visit.techIds) ? visit.techIds : [];

    // Seed tech chip selection: edit → visit's own techs; add → start empty
    _selectedTechIds = new Set(
        (visit && visit.techIds && visit.techIds.length) ? visit.techIds : []
    );
    _lastFetchKey = null;

    const dateInput = document.getElementById('sch_date');
    const timeInput = document.getElementById('sch_time');
    dateInput.removeEventListener('change', onVisitDateTimeChange);
    timeInput.removeEventListener('change', onVisitDateTimeChange);

    if (visit) {
        // Edit mode: PATCH to updateVisit/{id}
        _isEditMode    = true;
        _origVisitDate = visit.date;
        _origVisitTime       = visit.time;
        _origVisitDur        = visit.dur;

        form.action  = '{{ route('admin.work-orders.visits.store', $workOrder) }}'.replace(/\/visits$/, '/visits/' + visit.id);
        methodEl.value = 'PATCH';
        idEl.value   = visit.id;
        const _custName = @json($workOrder->customer->name);
        const _d = new Date(visit.date + 'T00:00:00');
        const _dateLbl = _d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
        const [_hh, _mm] = (visit.time || '').split(':').map(Number);
        const _timeLbl = visit.time
            ? ((_hh % 12) || 12) + ':' + String(_mm).padStart(2, '0') + ' ' + (_hh < 12 ? 'AM' : 'PM')
            : '';
        titleEl.innerHTML = '📅 Edit Visit &mdash; <strong>' + _custName + '</strong> on <strong>' + _dateLbl + '</strong>' + (_timeLbl ? ' at <strong>' + _timeLbl + '</strong>' : '') + ' for <strong>' + _fmtDur(visit.dur) + '</strong>';
        saveBtn.textContent = 'Save Changes';

        document.getElementById('sch_date').value  = visit.date;
        document.getElementById('sch_time').value  = visit.time;
        document.getElementById('sch_notes').value = visit.notes;
        setDur(visit.dur);
        updateSchDateLabel(visit.date);
        loadTimeline(visit.date);

        dateInput.addEventListener('change', onVisitDateTimeChange);
        timeInput.addEventListener('change', onVisitDateTimeChange);
    } else {
        // Add mode: POST to storeVisit
        _isEditMode    = false;
        _origVisitDate = null;
        _origVisitTime       = null;
        _origVisitDur        = null;

        form.action    = '{{ route('admin.work-orders.visits.store', $workOrder) }}';
        methodEl.value = '';
        idEl.value     = '';
        titleEl.textContent = '📅 Add Visit';
        saveBtn.textContent = 'Add Visit';

        @php
            $nextBizDay = \Carbon\Carbon::today()->addWeekday()->format('Y-m-d');
            $schDefault = $workOrder->preferred_date?->format('Y-m-d') ?? $nextBizDay;
        @endphp
        const defaultDate = '{{ $schDefault }}';
        document.getElementById('sch_date').value  = defaultDate;
        document.getElementById('sch_time').value  = '';
        document.getElementById('sch_notes').value = '';
        setDur(120);
        updateSchDateLabel(defaultDate);
        loadTimeline(defaultDate);
    }

    document.getElementById('schedule-modal').style.display = 'flex';
    document.addEventListener('keydown', schedKeyHandler);
    renderDur();
    initConfirmPill();
    syncConfSection();

    // Always open with schedules visible
    _schedulesVisible = true;
    const schedBody = document.getElementById('tech-schedule-body');
    const schedIcon = document.getElementById('toggle-schedules-icon');
    const schedLabel = document.getElementById('toggle-schedules-label');
    if (schedBody)  schedBody.style.display = '';
    if (schedIcon)  schedIcon.style.transform = '';
    if (schedLabel) schedLabel.textContent = 'Hide Schedules';

    renderTechChips();
}
function closeScheduleModal() {
    document.getElementById('schedule-modal').style.display = 'none';
    document.removeEventListener('keydown', schedKeyHandler);
}
function schedKeyHandler(e) { if (e.key === 'Escape') closeScheduleModal(); }

function renderTechChips() {
    const strip = document.getElementById('tech-chip-strip');
    if (!strip) return;
    strip.innerHTML = '';
    _allEmployees.forEach(emp => {
        const on = _selectedTechIds.has(emp.id);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.title = on ? 'Remove from schedule view' : 'Add to schedule view';
        btn.style.cssText = 'background:none;border:none;cursor:pointer;padding:0;text-align:center;width:52px;' +
            'opacity:' + (on ? '1' : '0.35') + ';transition:opacity .15s;flex-shrink:0;';

        let avatarHtml;
        const borderStyle = on
            ? '3px solid var(--accent)'
            : '2px dashed #94a3b8';
        const bgColor = on ? 'var(--accent)' : '#94a3b8';

        if (emp.photo) {
            avatarHtml = `<img src="${emp.photo}" alt="${emp.first}"
                style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:${borderStyle};display:block;margin:0 auto 3px;">`;
        } else {
            avatarHtml = `<div style="width:40px;height:40px;border-radius:50%;background:${bgColor};
                display:flex;align-items:center;justify-content:center;
                font-size:.9rem;font-weight:700;color:#fff;margin:0 auto 3px;
                border:${borderStyle};">${emp.initials}</div>`;
        }

        btn.innerHTML = avatarHtml +
            `<div style="font-size:.65rem;font-weight:${on ? '700' : '500'};
                color:${on ? 'var(--primary)' : '#9ca3af'};
                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${emp.first}</div>`;

        btn.addEventListener('click', () => {
            if (_selectedTechIds.has(emp.id)) {
                _selectedTechIds.delete(emp.id);
            } else {
                _selectedTechIds.add(emp.id);
            }
            renderTechChips();
            _lastFetchKey = null;
            const date = document.getElementById('sch_date').value;
            if (date) loadTimeline(date);
        });
        strip.appendChild(btn);
    });
}


// ── Duration stepper ──────────────────────────────────────────
// Initialized in openScheduleModal() — HTML is below the script block
var _durVal = {{ $workOrder->duration_estimate_minutes ?? 120 }};
var _custHasConfirmed = {{ $customerHasConfirmed ? 'true' : 'false' }};
function _fmtDur(m) {
    const h = Math.floor(m / 60), r = m % 60;
    return h && r ? h + 'h ' + r + 'm' : h ? h + 'h' : r + 'm';
}
function renderDur() {
    const inp  = document.getElementById('sch_duration');
    const disp = document.getElementById('dur-display');
    if (!inp || !disp) return;
    inp.value = _durVal;
    disp.textContent = _fmtDur(_durVal);
    document.querySelectorAll('.dur-shortcut').forEach(b => {
        const active = parseInt(b.dataset.min) === _durVal;
        b.style.background  = active ? 'var(--accent)' : '#f9fafb';
        b.style.color       = active ? '#fff' : '#555';
        b.style.borderColor = active ? 'var(--accent)' : '#d1d5db';
    });
}
function changeDur(delta) {
    _durVal = Math.max(15, Math.min(480, _durVal + delta));
    renderDur();
    if (typeof updateGhost === 'function') updateGhost();
    onVisitDurationChange();
}
function setDur(minutes) {
    _durVal = Math.max(15, Math.min(480, minutes));
    renderDur();
    if (typeof updateGhost === 'function') updateGhost();
}
// User-initiated shortcut — same as setDur but triggers the confirmation check
function userSetDur(minutes) {
    setDur(minutes);
    onVisitDurationChange();
}

// Advance date by |delta| business days (skips Sat/Sun)
function shiftSchDate(delta) {
    const inp = document.getElementById('sch_date');
    let d = inp.value ? new Date(inp.value + 'T00:00:00') : new Date();
    let count = Math.abs(delta);
    const dir = delta > 0 ? 1 : -1;
    while (count > 0) {
        d.setDate(d.getDate() + dir);
        const dow = d.getDay();
        if (dow !== 0 && dow !== 6) count--; // skip Sunday(0) and Saturday(6)
    }
    inp.value = d.toISOString().slice(0, 10);
    updatePrefStrip();
    loadTimeline(inp.value);
    updateSchDateLabel(inp.value);
}

// Set start-time select from a clicked timeline block or label (HH:MM value)
function setSchTime(val) {
    const sel = document.getElementById('sch_time');
    sel.value = val;
    updateGhost();
    syncConfSection();
}

// Step start-time by ±1 slot (30 min) using the existing select options
function shiftSchTime(delta) {
    const sel  = document.getElementById('sch_time');
    const opts = Array.from(sel.options).filter(o => o.value);
    let idx    = opts.findIndex(o => o.value === sel.value);
    if (idx === -1) idx = delta > 0 ? -1 : opts.length; // snap to first/last
    const next = idx + delta;
    if (next >= 0 && next < opts.length) {
        sel.value = opts[next].value;
        updateGhost();
        onVisitDateTimeChange();
    }
}

const TIMELINE_START   = 7;   // 7 AM
const TIMELINE_END     = 20;  // 8 PM
const TIMELINE_MINS    = (TIMELINE_END - TIMELINE_START) * 60;
const THIS_WO_ADDRESS  = @json($workOrder->site_street ?? '');
@php
    // Build a map of techId → formatted home address for ALL employees (not just
    // those already assigned to this WO) so the chip-selector can add any tech
    // and still get travel-time estimates from their home address.
    $techHomeAddresses = [];
    foreach ($employees as $_emp) {
        $_stateZip = trim(($_emp->home_state ?? '') . ' ' . ($_emp->home_zip ?? ''));
        $_parts    = array_filter([$_emp->home_street, $_emp->home_city, $_stateZip ?: null]);
        if (!empty($_parts)) {
            $techHomeAddresses[$_emp->id] = implode(', ', $_parts);
        }
    }
@endphp
const TECH_HOME_ADDRS = @json($techHomeAddresses);
@php
    $_pdm = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $_pbd = [];
    foreach ($workOrder->preferred_availability ?? [] as $_day => $_slots) {
        $_n = $_pdm[$_day] ?? -1;
        if ($_n >= 0) $_pbd[$_n] = $_slots;
    }
@endphp
const PREF_BY_DAY = @json($_pbd);
const PREF_SLOTS  = {
    morning:   { start:   0, dur: 240, label: 'Morning · 7–11am' },
    lunch:     { start: 240, dur: 180, label: 'Lunch · 11am–2pm' },
    afternoon: { start: 420, dur: 240, label: 'Afternoon · 2–6pm' },
};

function minuteOffset(h, m) {
    return Math.max(0, Math.min(TIMELINE_MINS, (h - TIMELINE_START) * 60 + m));
}
function pct(mins) { return (mins / TIMELINE_MINS * 100).toFixed(2) + '%'; }

let _timelineData = [];

function renderTimeline(data) {
    _timelineData = data || [];
    const container = document.getElementById('timeline-container');

    // Compute preference slots up front — used in both the empty and full render paths.
    const schDateVal  = document.getElementById('sch_date')?.value;
    const schDayNum   = schDateVal ? new Date(schDateVal + 'T00:00:00').getDay() : -1;
    const schDaySlots = (schDayNum >= 0 && PREF_BY_DAY[schDayNum]) ? PREF_BY_DAY[schDayNum] : [];

    function _prefStripHtml() {
        let h = `<div data-pref-strip style="position:relative;height:22px;margin-bottom:5px;${schDaySlots.length ? '' : 'display:none;'}" title="Customer's preferred time windows">`;
        schDaySlots.forEach(slot => {
            const r = PREF_SLOTS[slot];
            if (!r) return;
            h += `<div style="position:absolute;left:${pct(r.start)};width:${pct(Math.min(r.dur, TIMELINE_MINS - r.start))};height:100%;
                              background:rgba(134,239,172,0.6);border-left:2px solid #16a34a;border-radius:3px;
                              display:flex;align-items:center;padding:0 6px;overflow:hidden;box-sizing:border-box;">
                      <span style="font-size:.6rem;font-weight:700;color:#14532d;white-space:nowrap;display:flex;align-items:center;gap:3px;">
                          <span>★</span> <span>${r.label}</span>
                      </span>
                  </div>`;
        });
        h += `</div>`;
        return h;
    }

    if (!data || data.length === 0) {
        const managedFlagEl = document.getElementById('employees-managed-flag');
        if (managedFlagEl) managedFlagEl.value = '0';
        document.querySelectorAll('#schedule-form input[name="keep_employees[]"][type="hidden"]').forEach(i => i.remove());
        container.innerHTML = _prefStripHtml()
            + '<p style="color:#aaa;font-size:.83rem;text-align:center;padding:.75rem 0;">No assigned techs, or no bookings found for this date.</p>';
        updateTravelTimes();
        return;
    }

    const hours = [];
    for (let h = TIMELINE_START; h <= TIMELINE_END; h++) {
        const label = h === 12 ? '12pm' : h < 12 ? h + 'am' : (h - 12) + 'pm';
        hours.push({ h, label });
    }

    // Always tell the controller we're managing tech assignment explicitly
    const managedFlag = document.getElementById('employees-managed-flag');
    if (managedFlag) managedFlag.value = '1';

    // For single tech: inject a hidden keep_employees input (no checkbox needed)
    // For 2+ techs: the checkbox column handles it — clear any stale hidden inputs first
    document.querySelectorAll('#schedule-form input[name="keep_employees[]"][type="hidden"]').forEach(i => i.remove());
    if (data.length === 1) {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'keep_employees[]';
        inp.value = data[0].id;
        document.getElementById('schedule-form').appendChild(inp);
    }

    // Two-column flex layout: [timeline (flex:1)] [checkbox column (36px)]
    // Matching explicit heights on header and rows guarantee perfect vertical alignment.
    const ROW_H  = 44; // px — tech bar height
    const ROW_MB = 8;  // px — margin-bottom between rows
    const HDR_H  = 24; // px — hour-labels row height

    let html = `<div style="display:flex;gap:0;">`;

    // ── Left: name gutter + timeline ─────────────────────────────
    html += `<div style="position:relative;padding-left:80px;flex:1;min-width:0;">`;

    // Hour tick marks
    html += `<div style="position:relative;height:${HDR_H}px;margin-bottom:4px;">`;
    hours.forEach(({ h, label }) => {
        const left    = pct(minuteOffset(h, 0));
        const timeVal = String(h).padStart(2, '0') + ':00';
        html += `<span onclick="setSchTime('${timeVal}')"
                       title="Set start time to ${label}"
                       style="position:absolute;left:${left};transform:translateX(-50%);font-size:.68rem;
                              color:var(--accent);cursor:pointer;font-weight:600;user-select:none;">${label}</span>`;
    });
    html += `</div>`;

    // Preference indicator strip — shows which time windows the customer requested
    // (schDaySlots computed at top of renderTimeline, before the early-return check)
    html += `<div data-pref-strip style="position:relative;height:22px;margin-bottom:5px;${schDaySlots.length ? '' : 'display:none;'}" title="Customer's preferred time windows">`;
    schDaySlots.forEach(slot => {
        const r = PREF_SLOTS[slot];
        if (!r) return;
        const left  = pct(r.start);
        const width = pct(Math.min(r.dur, TIMELINE_MINS - r.start));
        html += `<div style="position:absolute;left:${left};width:${width};height:100%;
                             background:rgba(134,239,172,0.6);border-left:2px solid #16a34a;
                             border-radius:3px;display:flex;align-items:center;padding:0 6px;
                             overflow:hidden;box-sizing:border-box;">
                     <span style="font-size:.6rem;font-weight:700;color:#14532d;white-space:nowrap;
                                  display:flex;align-items:center;gap:3px;">
                         <span>★</span> <span>${r.label}</span>
                     </span>
                 </div>`;
    });
    html += `</div>`;

    // Timeline rows + grid lines
    html += `<div id="timeline-rows" style="position:relative;">`;
    html += `<div style="position:absolute;inset:0;pointer-events:none;">`;
    hours.forEach(({ h }) => {
        const left = pct(minuteOffset(h, 0));
        html += `<div style="position:absolute;top:0;bottom:0;left:${left};width:1px;background:#e5e7eb;"></div>`;
    });
    html += `</div>`;

    data.forEach(tech => {
        html += `<div style="display:flex;align-items:center;margin-bottom:${ROW_MB}px;position:relative;z-index:1;">`;
        html += `<div style="position:absolute;left:-80px;width:76px;font-size:.78rem;color:#444;font-weight:600;
                             white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${tech.name}</div>`;
        html += `<div data-tech-bar="1" data-tech-id="${tech.id}"
                      style="flex:1;height:${ROW_H}px;background:#f1f5f9;border-radius:4px;position:relative;">`;
        html += `<div data-pref-tint style="position:absolute;inset:0;z-index:0;pointer-events:none;">`;
        schDaySlots.forEach(slot => {
            const r = PREF_SLOTS[slot];
            if (!r) return;
            html += `<div style="position:absolute;left:${pct(r.start)};
                                 width:${pct(Math.min(r.dur, TIMELINE_MINS - r.start))};
                                 height:100%;background:rgba(134,239,172,0.18);
                                 border-left:1px solid rgba(21,128,61,0.25);
                                 border-radius:2px;"></div>`;
        });
        html += `</div>`;
        tech.orders.forEach(order => {
            const startMin = minuteOffset(order.start_h, order.start_m);
            const dur      = Math.min(order.duration, TIMELINE_MINS - startMin);
            const timeVal  = String(order.start_h).padStart(2,'0') + ':' + String(order.start_m).padStart(2,'0');
            html += `<div title="Click to set start time — WO-${order.wo_number} @ ${order.time}${order.address ? ' · ' + order.address : ''}"
                          onclick="setSchTime('${timeVal}')"
                          style="position:absolute;left:${pct(startMin)};width:${pct(dur)};height:100%;
                                 background:#bfdbfe;border:1px solid #93c5fd;border-radius:3px;
                                 font-size:.65rem;color:#1e3a5f;overflow:hidden;padding:2px 4px;
                                 display:flex;flex-direction:column;justify-content:center;cursor:pointer;">
                         <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;line-height:1.2;">WO-${order.wo_number} · ${order.time}</span>
                         ${order.address ? `<span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:.75;line-height:1.2;">${order.address}</span>` : ''}
                     </div>`;
        });
        html += `</div></div>`;
    });

    html += `</div></div>`; // close timeline-rows + left column

    // ── Right: checkbox column (only when 2+ techs are visible) ─────────────
    if (data.length > 1) {
        html += `<div style="width:36px;flex-shrink:0;padding-left:8px;display:flex;flex-direction:column;align-items:center;">`;
        html += `<div style="height:${HDR_H}px;margin-bottom:4px;display:flex;align-items:center;justify-content:center;">
                     <input type="checkbox" id="tech-cb-all" title="Select / deselect all"
                            style="width:15px;height:15px;cursor:pointer;accent-color:var(--accent);">
                 </div>`;
        html += `<div data-pref-spacer style="height:22px;margin-bottom:5px;${schDaySlots.length ? '' : 'display:none;'}"></div>`;
        data.forEach(tech => {
            html += `<div style="height:${ROW_H}px;margin-bottom:${ROW_MB}px;display:flex;align-items:center;justify-content:center;">
                         <input type="checkbox" name="keep_employees[]" value="${tech.id}"
                                class="tech-keep-cb"
                                style="width:15px;height:15px;cursor:pointer;accent-color:var(--accent);">
                     </div>`;
        });
        html += `</div>`;
    }

    html += `</div>`; // close outer flex
    container.innerHTML = html;

    if (data.length > 1) {
        const allCbs = document.querySelectorAll('.tech-keep-cb');

        // Pre-check: edit mode → only the visit's current techs; add mode → all visible techs
        allCbs.forEach(cb => {
            const id = parseInt(cb.value);
            cb.checked = (_isEditMode && _editingVisitTechIds.length)
                ? _editingVisitTechIds.includes(id)
                : true;
        });

        // Select-all header checkbox
        const allCb = document.getElementById('tech-cb-all');
        const syncAllCb = () => {
            const checked = document.querySelectorAll('.tech-keep-cb:checked');
            allCb.indeterminate = checked.length > 0 && checked.length < allCbs.length;
            allCb.checked       = checked.length === allCbs.length;
        };
        allCb.addEventListener('change', () => {
            allCbs.forEach(cb => cb.checked = allCb.checked);
        });
        allCbs.forEach(cb => cb.addEventListener('change', syncAllCb));
        syncAllCb();
    }

    updateGhost();
}

// Update only the preference-strip and tech-bar tints based on the current date.
// Called immediately on any date change so the indicators feel instant,
// even before the async timeline fetch resolves (or when the cache is hit).
function updatePrefStrip() {
    const container   = document.getElementById('timeline-container');
    if (!container) return;
    const schDateVal  = document.getElementById('sch_date')?.value;
    const schDayNum   = schDateVal ? new Date(schDateVal + 'T00:00:00').getDay() : -1;
    const schDaySlots = (schDayNum >= 0 && PREF_BY_DAY[schDayNum]) ? PREF_BY_DAY[schDayNum] : [];

    const slotHtml = slot => {
        const r = PREF_SLOTS[slot];
        if (!r) return '';
        return `<div style="position:absolute;left:${pct(r.start)};
                             width:${pct(Math.min(r.dur, TIMELINE_MINS - r.start))};
                             height:100%;background:rgba(134,239,172,0.18);
                             border-left:1px solid rgba(21,128,61,0.25);
                             border-radius:2px;"></div>`;
    };

    const strip = container.querySelector('[data-pref-strip]');
    if (strip) {
        if (schDaySlots.length) {
            strip.style.display = '';
            strip.innerHTML = schDaySlots.map(slot => {
                const r = PREF_SLOTS[slot];
                if (!r) return '';
                const left  = pct(r.start);
                const width = pct(Math.min(r.dur, TIMELINE_MINS - r.start));
                return `<div style="position:absolute;left:${left};width:${width};height:100%;
                                    background:rgba(134,239,172,0.6);border-left:2px solid #16a34a;
                                    border-radius:3px;display:flex;align-items:center;padding:0 6px;
                                    overflow:hidden;box-sizing:border-box;">
                            <span style="font-size:.6rem;font-weight:700;color:#14532d;white-space:nowrap;
                                         display:flex;align-items:center;gap:3px;">
                                <span>★</span> <span>${r.label}</span>
                            </span>
                        </div>`;
            }).join('');
        } else {
            strip.style.display = 'none';
            strip.innerHTML = '';
        }
    }

    container.querySelectorAll('[data-pref-tint]').forEach(tintEl => {
        tintEl.innerHTML = schDaySlots.map(slotHtml).join('');
    });

    const spacer = container.querySelector('[data-pref-spacer]');
    if (spacer) spacer.style.display = schDaySlots.length ? '' : 'none';
}

// ── Ghost drag-to-schedule ────────────────────────────────────────────────────
// Allows dragging the "This visit" ghost block left/right on the timeline to
// change the scheduled start time. Snaps to 30-min intervals (matching the
// time select options). No travel-time API calls fire during the drag itself —
// only on release.
let _ghostDragActive = false;

function _repositionGhostsOnly(leftPct, widthPct) {
    document.querySelectorAll('[data-ghost]').forEach(g => {
        g.style.left  = leftPct;
        g.style.width = widthPct;
    });
    updateTravelBlocks(); // reposition from cache — no API
}

function _startGhostDrag(e, sourceGhost) {
    e.preventDefault();
    e.stopPropagation();

    const inner = sourceGhost.closest('[data-tech-bar]');
    if (!inner) return;

    const timeVal = document.getElementById('sch_time').value;
    const durVal  = parseInt(document.getElementById('sch_duration').value) || 0;
    if (!timeVal) return;

    const barRect  = inner.getBoundingClientRect();
    const [h, m]   = timeVal.split(':').map(Number);
    const startMin = minuteOffset(h, m);

    _ghostDragActive = true;
    document.body.style.userSelect = 'none';
    document.querySelectorAll('[data-ghost]').forEach(g => { g.style.cursor = 'grabbing'; g.style.opacity = '.85'; });

    const drag = { startX: e.clientX, startMin, durVal, barRect };

    function onMove(ev) {
        const deltaMin = (ev.clientX - drag.startX) / drag.barRect.width * TIMELINE_MINS;
        const snapped  = Math.round((drag.startMin + deltaMin) / 30) * 30;
        const clamped  = Math.max(0, Math.min(TIMELINE_MINS - drag.durVal, snapped));

        const newH    = TIMELINE_START + Math.floor(clamped / 60);
        const newM    = clamped % 60;
        const timeStr = String(newH).padStart(2, '0') + ':' + String(newM).padStart(2, '0');

        const sel = document.getElementById('sch_time');
        if (sel.value !== timeStr && Array.from(sel.options).some(o => o.value === timeStr)) {
            sel.value = timeStr;
            _repositionGhostsOnly(pct(clamped), pct(Math.min(drag.durVal, TIMELINE_MINS - clamped)));
        }
    }

    function onUp() {
        _ghostDragActive = false;
        document.body.style.userSelect = '';
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup',   onUp);
        document.querySelectorAll('[data-ghost]').forEach(g => { g.style.cursor = 'ew-resize'; g.style.opacity = '.65'; });
        // Full update — fires travel-time recalc and syncs confirmation section
        updateGhost();
        syncConfSection();
    }

    document.addEventListener('mousemove', onMove);
    document.addEventListener('mouseup',   onUp);
}

function updateGhost() {
    const rows = document.querySelectorAll('#timeline-rows > div');
    if (!rows.length) return;

    const timeVal = document.getElementById('sch_time').value;
    const durVal  = parseInt(document.getElementById('sch_duration').value) || 0;
    if (!timeVal || !durVal) { removeGhosts(); return; }

    const [h, m] = timeVal.split(':').map(Number);
    const startMin = minuteOffset(h, m);
    const dur      = Math.min(durVal, TIMELINE_MINS - startMin);
    const left     = pct(startMin);
    const width    = pct(dur);

    rows.forEach(row => {
        let ghost = row.querySelector('[data-ghost]');
        if (!ghost) {
            const inner = row.querySelector('[data-tech-bar]');
            if (!inner) return;
            ghost = document.createElement('div');
            ghost.setAttribute('data-ghost', '1');
            ghost.style.cssText = `position:absolute;height:100%;border-radius:3px;opacity:.65;
                background:var(--accent);border:2px solid var(--accent);z-index:3;
                overflow:hidden;padding:2px 4px;display:flex;flex-direction:column;justify-content:center;
                cursor:ew-resize;touch-action:none;`;
            ghost.addEventListener('mousedown', ev => _startGhostDrag(ev, ghost));
            inner.appendChild(ghost);
        }
        ghost.style.left  = left;
        ghost.style.width = width;
        ghost.innerHTML = `<span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.63rem;font-weight:700;color:#fff;line-height:1.2;pointer-events:none;">This visit</span>`
                        + (THIS_WO_ADDRESS ? `<span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.61rem;color:rgba(255,255,255,.85);line-height:1.2;pointer-events:none;">${THIS_WO_ADDRESS}</span>` : '');
    });
    // Keep preference strip in sync with the current date (handles the cache-hit path
    // where renderTimeline is skipped and the strip could otherwise go stale).
    updatePrefStrip();
    // Reposition travel blocks from cache immediately (keeps them in sync with the ghost),
    // then fire async per-tech checks to update if any address changed.
    updateTravelBlocks();
    if (!_ghostDragActive) updateTravelTimes();
}

function removeGhosts() {
    document.querySelectorAll('[data-ghost]').forEach(el => el.remove());
    removeTravelBlocks();
}

function removeTravelBlocks() {
    document.querySelectorAll('[data-travel]').forEach(el => el.remove());
}

// Per-tech travel cache: { techId: { from: address, minutes: N } }
// Seeded from the work order's persisted cache so re-opening the modal skips API calls.
let _travelCache  = @json($workOrder->travel_time_cache ?: (object)[]);
let _travelAborts = {};  // techId → AbortController

function formatTravelMinutes(mins) {
    if (!mins) return '';
    const h = Math.floor(mins / 60), m = mins % 60;
    return h > 0 ? h + ' hr' + (m ? ' ' + m + ' min' : '') : m + ' min';
}

// Returns { techId: { name, from, isHome? } } for each assigned tech.
// Uses the last completed job address as the origin, or falls back to the
// tech's home address when this would be their first job of the day.
function getCurrentTechPrevs() {
    const timeVal = document.getElementById('sch_time').value;
    if (!timeVal || !_timelineData.length) return {};
    const [h, m] = timeVal.split(':').map(Number);
    const selectedMin = (h - TIMELINE_START) * 60 + m;
    const prevs = {};
    _timelineData.forEach(tech => {
        let prevAddr = null, latestEnd = -1;
        tech.orders.forEach(order => {
            const orderEnd = minuteOffset(order.start_h, order.start_m) + order.duration;
            if (orderEnd <= selectedMin && orderEnd > latestEnd && order.address) {
                latestEnd = orderEnd;
                prevAddr  = order.address;
            }
        });
        if (prevAddr) {
            prevs[tech.id] = { name: tech.name, from: prevAddr };
        } else if (TECH_HOME_ADDRS[tech.id]) {
            // First job of the day — depart from home
            prevs[tech.id] = { name: tech.name, from: TECH_HOME_ADDRS[tech.id], isHome: true };
        }
    });
    return prevs;
}

// Draw a yellow travel block on each tech's bar using the current cache.
function updateTravelBlocks() {
    removeTravelBlocks();
    const timeVal = document.getElementById('sch_time').value;
    if (!timeVal) return;
    const [h, m] = timeVal.split(':').map(Number);
    const startMin = minuteOffset(h, m);

    _timelineData.forEach(tech => {
        const cached = _travelCache[tech.id];
        if (!cached || !cached.minutes) return;

        // Only draw if the cached from-address matches the tech's current origin
        // (prior job address, or home address when this is their first job today)
        let latestEnd = -1, prevAddr = null;
        tech.orders.forEach(order => {
            const orderEnd = minuteOffset(order.start_h, order.start_m) + order.duration;
            if (orderEnd <= startMin && orderEnd > latestEnd && order.address) {
                latestEnd = orderEnd;
                prevAddr  = order.address;
            }
        });
        const effectiveFrom = prevAddr || TECH_HOME_ADDRS[tech.id] || null;
        if (!effectiveFrom || effectiveFrom !== cached.from) return;

        const tStart = Math.max(0, startMin - cached.minutes);
        const tDur   = startMin - tStart;
        if (tDur <= 0) return;

        const bar = document.querySelector(`[data-tech-bar][data-tech-id="${tech.id}"]`);
        if (!bar) return;

        const block = document.createElement('div');
        block.setAttribute('data-travel', '1');
        block.style.cssText = `position:absolute;left:${pct(tStart)};width:${pct(tDur)};height:100%;
            border-radius:3px;background:#fef08a;border:1px solid #eab308;z-index:2;
            pointer-events:none;overflow:hidden;padding:2px 4px;
            display:flex;flex-direction:column;justify-content:center;`;
        block.innerHTML = `<span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
            font-size:.61rem;font-weight:600;color:#713f12;line-height:1.2;">Drive</span>`;
        bar.appendChild(block);
    });
}

// Render the travel-time strip below the timeline with one row per tech.
// Rows follow the same top-to-bottom order as the timeline. The tech with the
// shortest resolved travel time gets a green highlight.
function renderTravelDisplay(techPrevs) {
    const el = document.getElementById('travel-time-display');
    if (!el) return;

    // Build rows in timeline order so names match the tech rows above
    const rows = [];
    _timelineData.forEach(tech => {
        const info = techPrevs[tech.id];
        if (!info) return;
        const cached    = _travelCache[tech.id];
        const isMatch   = cached && cached.from === info.from;
        const hasResult = isMatch && cached.minutes;
        const hasError  = isMatch && cached.error;
        rows.push({
            name:    info.name,
            from:    info.from,
            isHome:  info.isHome || false,
            minutes: hasResult ? cached.minutes : null,
            text:    hasResult ? formatTravelMinutes(cached.minutes) : null,
            error:   hasError || false,
        });
    });

    if (!rows.length) { el.style.display = 'none'; return; }

    // Find shortest among resolved rows (only meaningful when 2+ have results)
    const resolved  = rows.filter(r => r.minutes !== null);
    const sortedMins = resolved.map(r => r.minutes).sort((a, b) => a - b);
    const minMins    = sortedMins.length > 1 ? sortedMins[0] : null;
    const secondMins = sortedMins.length > 1 ? sortedMins[1] : null;
    const diffMins   = (minMins !== null && secondMins !== null) ? secondMins - minMins : 0;

    let html = '';
    rows.forEach(row => {
        const isBest = minMins !== null && row.minutes === minMins;

        let timeCell;
        if (row.error) {
            timeCell = `<span style="font-size:.75rem;color:#ef4444;">unavailable</span>`;
        } else if (row.minutes !== null) {
            if (isBest) {
                const diffLabel = diffMins > 0
                    ? `<span style="font-size:.68rem;color:#15803d;">(+${formatTravelMinutes(diffMins)} faster)</span>`
                    : '';
                timeCell = `<span style="display:inline-flex;align-items:center;gap:.3rem;">`
                         + `<span style="background:#16a34a;color:#fff;font-size:.72rem;font-weight:700;`
                         + `padding:.15rem .45rem;border-radius:4px;white-space:nowrap;">${row.text}</span>`
                         + `<span style="font-size:.68rem;color:#15803d;font-weight:600;">Shortest</span>`
                         + diffLabel
                         + `</span>`;
            } else {
                timeCell = `<span style="font-size:.82rem;font-weight:700;color:#374151;">${row.text}</span>`;
            }
        } else {
            timeCell = `<span style="font-size:.78rem;color:#9ca3af;">calculating…</span>`;
        }

        html += `<div style="display:flex;align-items:center;gap:.6rem;padding:.3rem .5rem;border-radius:5px;`
              + `background:${isBest ? '#f0fdf4' : 'transparent'};margin-bottom:1px;">`
              + `<span style="font-size:.78rem;font-weight:600;color:#374151;min-width:100px;`
              + `white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${row.isHome ? '🏠' : '🚗'} ${row.name}</span>`
              + `<span style="flex-shrink:0;">${timeCell}</span>`
              + `<span style="font-size:.68rem;color:#9ca3af;font-family:monospace;flex:1;min-width:0;`
              + `white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">`
              + (row.from ? `${row.from} → ${THIS_WO_ADDRESS || '?'}` : '')
              + `</span></div>`;
    });

    el.style.display = 'flex';
    el.innerHTML = html;
}

// Fetch travel time for one tech; update cache, blocks, and display on success.
async function fetchTechTravel(techId, from) {
    try {
        const url = `{{ route('admin.work-orders.travel-time', $workOrder) }}`
                  + `?from=${encodeURIComponent(from)}&tech_id=${encodeURIComponent(techId)}`;
        const res  = await fetch(url, {
            signal:  _travelAborts[techId].signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        if (data.minutes) {
            _travelCache[techId] = { from, minutes: data.minutes };
            updateTravelBlocks();
            renderTravelDisplay(getCurrentTechPrevs());
        } else {
            // API returned but no route — mark cache so we don't retry endlessly
            _travelCache[techId] = { from, minutes: null, error: true };
            renderTravelDisplay(getCurrentTechPrevs());
        }
    } catch (e) {
        if (e.name !== 'AbortError') {
            // Network error or other failure — show in display
            _travelCache[techId] = { from, minutes: null, error: true };
            renderTravelDisplay(getCurrentTechPrevs());
        }
    }
}

// Called whenever start time changes. Renders from cache immediately, then fires
// async fetches only for techs whose from-address is new or changed.
function updateTravelTimes() {
    const techPrevs = getCurrentTechPrevs();

    if (!Object.keys(techPrevs).length) {
        // Show the panel with an explanation rather than hiding it silently
        const el = document.getElementById('travel-time-display');
        if (el) {
            const timeSet = !!document.getElementById('sch_time')?.value;
            const techsLoaded = _timelineData.length > 0;
            if (timeSet && techsLoaded) {
                el.style.display = 'flex';
                el.innerHTML = '<span style="font-size:.75rem;color:#64748b;font-style:italic;">No origin address available — set a home address for the tech or ensure there is a prior job earlier that day.</span>';
            } else {
                el.style.display = 'none';
            }
        }
        return;
    }

    renderTravelDisplay(techPrevs);

    Object.entries(techPrevs).forEach(([techId, info]) => {
        const cached = _travelCache[techId];
        if (cached && cached.from === info.from) return; // cache hit — no fetch needed
        if (_travelAborts[techId]) _travelAborts[techId].abort();
        _travelAborts[techId] = new AbortController();
        fetchTechTravel(techId, info.from);
    });
}

// Validates that at least one tech checkbox is checked before submitting.
function submitScheduleForm() {
    const cbs = document.querySelectorAll('.tech-keep-cb');
    // Only validate when the checkbox column is actually visible (2+ techs)
    if (cbs.length > 0) {
        const anyChecked = Array.from(cbs).some(cb => cb.checked);
        if (!anyChecked) {
            document.getElementById('tech-selection-error').style.display = 'block';
            cbs.forEach(cb => { cb.style.boxShadow = '0 0 0 3px rgba(220,38,38,0.4)'; });
            return;
        }
    }
    clearTechSelectionError();
    document.getElementById('schedule-form').submit();
}

function clearTechSelectionError() {
    const el = document.getElementById('tech-selection-error');
    if (el) el.style.display = 'none';
    document.querySelectorAll('.tech-keep-cb').forEach(cb => {
        cb.style.boxShadow = '';
    });
}

// Force-clears the travel cache for all currently displayed techs and re-fetches.
function recalcTravel(btn) {
    const el = document.getElementById('travel-time-display');

    // If the timeline hasn't loaded yet, show a status and bail
    if (!_timelineData.length) {
        if (el) {
            el.style.display = 'flex';
            el.innerHTML = '<span style="font-size:.75rem;color:#64748b;font-style:italic;">Loading schedule… please try again in a moment.</span>';
        }
        return;
    }

    // Abort any in-flight requests and wipe cache entries for visible techs
    _timelineData.forEach(tech => {
        if (_travelAborts[tech.id]) _travelAborts[tech.id].abort();
        delete _travelCache[tech.id];
    });

    // Show the panel immediately so the user sees activity
    if (el) {
        el.style.display = 'flex';
        el.innerHTML = '<span style="font-size:.75rem;color:#64748b;">↻ Recalculating travel times…</span>';
    }

    // Visual feedback on the button
    const origHtml = btn.innerHTML;
    btn.disabled   = true;
    btn.innerHTML  = '<span style="font-size:1rem;line-height:1;display:inline-block;animation:spin .7s linear infinite;">↻</span><span>Recalculating…</span>';

    // Fire fresh fetches; renderTravelDisplay will update the panel as each result arrives
    updateTravelTimes();

    // Restore button after fetches have had time to settle
    setTimeout(() => {
        btn.disabled  = false;
        btn.innerHTML = origHtml;
    }, 5000);
}

function loadTimeline(date) {
    const ids = Array.from(_selectedTechIds).sort((a, b) => a - b);
    const cacheKey = date + '|' + ids.join(',');
    if (cacheKey === _lastFetchKey) { updateGhost(); return; }
    _lastFetchKey = cacheKey;

    const container = document.getElementById('timeline-container');
    if (!date || ids.length === 0) {
        container.innerHTML = '<p style="color:#aaa;font-size:.83rem;text-align:center;padding:.75rem 0;">Select at least one tech above to see their schedule.</p>';
        updateTravelTimes();
        return;
    }

    const params = new URLSearchParams({ date });
    ids.forEach(id => params.append('tech_ids[]', id));
    // When editing an existing visit, pass its id so the controller excludes only that visit
    // (not the entire work order) and any other visits on this WO still appear in the timeline.
    const editingId = document.getElementById('visit-id-input')?.value;
    if (editingId) params.append('visit_id', editingId);
    const url = `{{ route('admin.work-orders.tech-schedule', $workOrder) }}?${params}`;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => renderTimeline(data))
        .catch(() => {
            container.innerHTML = '<p style="color:#e55;font-size:.82rem;">Could not load tech schedule.</p>';
        });
}

// Page loads with WO details collapsed — sync heights on first paint
requestAnimationFrame(syncCardHeights);

</script>

{{-- ── Schedule Visit Modal ── --}}
<div id="schedule-modal" onclick="if(event.target===this)closeScheduleModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:100%;max-width:780px;margin:1rem;overflow:hidden;max-height:90vh;display:flex;flex-direction:column;">

        <div style="background:var(--primary);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <span id="schedule-modal-title" style="color:#fff;font-weight:700;font-size:1rem;">📅 Add Visit</span>
            <button type="button" onclick="closeScheduleModal()"
                    style="background:none;border:none;color:rgba(255,255,255,.8);font-size:1.5rem;cursor:pointer;line-height:1;">×</button>
        </div>

        <div style="padding:1.5rem;overflow-y:auto;flex:1;">
            <form method="POST" action="{{ route('admin.work-orders.visits.store', $workOrder) }}" id="schedule-form">
                @csrf
                <input type="hidden" name="_method" id="visit-method-input" value="">
                <input type="hidden" name="visit_id" id="visit-id-input" value="">
                {{-- Set to 1 by JS when the tech timeline renders; tells the controller to process the checkbox selections --}}
                <input type="hidden" name="employees_managed" value="0" id="employees-managed-flag">

                @php
                    $nextBizDay     = \Carbon\Carbon::today()->addWeekday()->format('Y-m-d');
                    $schDateDefault = $workOrder->preferred_date?->format('Y-m-d') ?? $nextBizDay;
                    $schTime        = '';
                @endphp

                {{-- Two-column top layout --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.25rem;align-items:start;">

                    {{-- Left: scheduling fields --}}
                    <div style="display:grid;gap:.9rem;">

                        {{-- Date --}}
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Date *</label>
                            <div style="display:flex;align-items:center;gap:.3rem;">
                                <button type="button" onclick="shiftSchDate(-1)"
                                        style="flex-shrink:0;width:28px;height:34px;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:.95rem;color:#444;display:flex;align-items:center;justify-content:center;padding:0;">&#8592;</button>
                                <input type="date" name="scheduled_date" id="sch_date"
                                       value="{{ $schDateDefault }}"
                                       required
                                       style="flex:1;min-width:0;padding:.45rem .35rem;border:1px solid #ccc;border-radius:5px;font-size:.82rem;">
                                <button type="button" onclick="shiftSchDate(1)"
                                        style="flex-shrink:0;width:28px;height:34px;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:.95rem;color:#444;display:flex;align-items:center;justify-content:center;padding:0;">&#8594;</button>
                            </div>
                            @if(!empty($workOrder->preferred_availability))
                            <div style="margin-top:.5rem;">
                                <div style="font-size:.68rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">Next available preferred dates</div>
                                <div id="preferred-date-shortcuts" style="display:flex;flex-wrap:wrap;gap:.35rem;"></div>
                            </div>
                            @endif
                        </div>

                        {{-- Start Time + Duration side by side --}}
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Start Time *</label>
                                <div style="display:flex;align-items:center;gap:.3rem;">
                                    <button type="button" onclick="shiftSchTime(-1)"
                                            style="flex-shrink:0;padding:.45rem .4rem;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:.72rem;font-weight:700;color:#444;white-space:nowrap;height:34px;">−.5</button>
                                    <select name="scheduled_time" id="sch_time" required
                                            style="flex:1;min-width:0;padding:.45rem .2rem;border:1px solid #ccc;border-radius:5px;font-size:.78rem;background:#fff;">
                                        <option value="">— select —</option>
                                        @for($h = 6; $h <= 20; $h++)
                                            @foreach([0, 30] as $m)
                                                @if($h === 20 && $m === 30) @continue @endif
                                                @php $val = sprintf('%02d:%02d', $h, $m); @endphp
                                                <option value="{{ $val }}" {{ $schTime === $val ? 'selected' : '' }}>
                                                    {{ \Carbon\Carbon::createFromTime($h, $m)->format('g:i A') }}
                                                </option>
                                            @endforeach
                                        @endfor
                                    </select>
                                    <button type="button" onclick="shiftSchTime(1)"
                                            style="flex-shrink:0;padding:.45rem .4rem;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:.72rem;font-weight:700;color:#444;white-space:nowrap;height:34px;">+.5</button>
                                </div>
                            </div>
                            <div>
                                <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Duration</label>
                                <input type="hidden" name="duration_estimate_minutes" id="sch_duration"
                                       value="{{ $workOrder->duration_estimate_minutes ?? 120 }}">
                                <div style="display:flex;align-items:center;gap:.35rem;">
                                    <button type="button" onclick="changeDur(-15)"
                                            style="flex-shrink:0;width:32px;height:34px;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:1.15rem;font-weight:700;color:#555;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;">−</button>
                                    @php
                                    $initDurMin = $workOrder->duration_estimate_minutes ?? 120;
                                    $initDurH   = intdiv($initDurMin, 60);
                                    $initDurR   = $initDurMin % 60;
                                    $initDurLbl = ($initDurH && $initDurR) ? "{$initDurH}h {$initDurR}m" : ($initDurH ? "{$initDurH}h" : "{$initDurR}m");
                                @endphp
                                <div id="dur-display"
                                         style="flex:1;text-align:center;font-size:.88rem;font-weight:700;color:var(--primary);height:34px;border:1px solid #ccc;border-radius:5px;display:flex;align-items:center;justify-content:center;background:#fff;">{{ $initDurLbl }}</div>
                                    <button type="button" onclick="changeDur(15)"
                                            style="flex-shrink:0;width:32px;height:34px;border:1px solid #ccc;border-radius:5px;background:#f8f9fa;cursor:pointer;font-size:1.15rem;font-weight:700;color:#555;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;">+</button>
                                </div>
                                <div style="display:flex;gap:.3rem;margin-top:.4rem;flex-wrap:wrap;">
                                    @foreach([4=>'4h',5=>'5h',6=>'6h',7=>'7h',8=>'8h'] as $hrs=>$lbl)
                                    <button type="button" class="dur-shortcut" data-min="{{ $hrs*60 }}" onclick="userSetDur({{ $hrs*60 }})"
                                            style="padding:.2rem .6rem;border:1px solid #d1d5db;border-radius:5px;background:#f9fafb;font-size:.75rem;font-weight:600;color:#555;cursor:pointer;transition:all .1s;">{{ $lbl }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div>
                            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Visit Note <span style="font-weight:400;color:#888;">(optional)</span></label>
                            <input type="text" name="notes" id="sch_notes" placeholder="e.g. Morning slot confirmed with customer"
                                   style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                        </div>

                    </div>{{-- /left --}}

                    {{-- Right: customer preferred availability --}}
                    @if($workOrder->preferred_availability)
                    @php
                        $modalAvailDayNames = ['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'];
                        $modalAvailSlotDefs = ['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']];
                    @endphp
                    <div style="padding:.85rem 1rem;background:#f0f6ff;border-radius:6px;border:1px solid #bfdbfe;height:100%;box-sizing:border-box;">
                        <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.6rem;">Customer Preferred Availability</div>
                        @foreach($modalAvailDayNames as $dayKey => $dayName)
                            @if(!empty($workOrder->preferred_availability[$dayKey]))
                            <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.4rem;flex-wrap:wrap;">
                                <span style="font-size:.78rem;font-weight:700;color:var(--primary);min-width:32px;">{{ $dayName }}:</span>
                                @foreach($modalAvailSlotDefs as $slot => $slotData)
                                @php $active = in_array($slot, $workOrder->preferred_availability[$dayKey]); @endphp
                                <span style="display:inline-flex;flex-direction:column;align-items:center;padding:.18rem .45rem;border-radius:6px;
                                             border:1.5px solid {{ $active ? '#86efac' : '#e5e7eb' }};
                                             background:{{ $active ? '#dcfce7' : '#f9fafb' }};flex:1;min-width:70px;text-align:center;">
                                    <span style="font-size:.68rem;font-weight:700;color:{{ $active ? '#15803d' : '#9ca3af' }};line-height:1.3;">{{ $slotData[0] }}</span>
                                    <span style="font-size:.58rem;color:{{ $active ? '#16a34a' : '#d1d5db' }};line-height:1.2;">{{ $slotData[1] }}</span>
                                </span>
                                @endforeach
                            </div>
                            @endif
                        @endforeach
                    </div>
                    @else
                    <div></div>
                    @endif

                </div>{{-- /two-column --}}

                {{-- Tech timeline --}}
                @if($employees->count())
                <div style="border-top:1px solid #e5e7eb;padding-top:1.1rem;margin-top:.25rem;">
                    <div style="margin-bottom:.85rem;padding:.55rem .85rem;background:var(--primary);border-radius:7px;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                        <p style="margin:0;font-size:.82rem;font-weight:400;color:rgba(255,255,255,.75);line-height:1.4;">
                            Schedules for <span id="sch-date-label" style="font-weight:700;color:#fff;"></span>
                        </p>
                        <button type="button" id="toggle-schedules-btn" onclick="toggleTechSchedules()"
                                style="flex-shrink:0;display:inline-flex;align-items:center;gap:.35rem;
                                       padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);
                                       background:rgba(255,255,255,.1);color:rgba(255,255,255,.9);
                                       font-size:.75rem;font-weight:600;cursor:pointer;white-space:nowrap;
                                       transition:background .12s;">
                            <span id="toggle-schedules-icon" style="font-size:.7rem;transition:transform .2s;display:inline-block;">▼</span>
                            <span id="toggle-schedules-label">Hide Schedules</span>
                        </button>
                    </div>
                    <div id="tech-schedule-body">
                        {{-- Tech chip selector --}}
                        <div id="tech-chip-strip"
                             style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:.85rem;
                                    padding:.6rem .75rem;background:#f8f9fa;border-radius:6px;border:1px solid #e5e7eb;">
                        </div>
                        <div id="timeline-container" style="min-height:40px;">
                            <p style="color:#aaa;font-size:.83rem;text-align:center;padding:.75rem 0;">Select at least one tech above to see their schedule.</p>
                        </div>
                        <div style="display:flex;gap:1rem;margin-top:.75rem;font-size:.72rem;color:#666;flex-wrap:wrap;">
                            <span style="display:inline-flex;align-items:center;gap:.3rem;">
                                <span style="width:12px;height:12px;border-radius:2px;background:#bfdbfe;border:1px solid #93c5fd;display:inline-block;"></span> Existing booking
                            </span>
                            <span style="display:inline-flex;align-items:center;gap:.3rem;">
                                <span style="width:12px;height:12px;border-radius:2px;background:#fef08a;border:1px solid #eab308;display:inline-block;"></span> Travel time
                            </span>
                            <span style="display:inline-flex;align-items:center;gap:.3rem;">
                                <span style="width:12px;height:12px;border-radius:2px;background:var(--accent);opacity:.55;display:inline-block;"></span> This visit
                            </span>
                        </div>
                        <div id="travel-time-display"
                             style="display:none;align-items:center;gap:.25rem;margin-top:.6rem;
                                    padding:.4rem .65rem;background:#f0f9ff;border-left:3px solid #38bdf8;
                                    border-radius:4px;flex-wrap:wrap;">
                        </div>
                    </div>
                </div>
                @endif

            </form>
        </div>

        <div style="padding:.75rem 1.5rem;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-shrink:0;">
            <div id="conf-section" style="display:flex;flex-direction:column;gap:.4rem;min-width:0;">
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <input type="hidden" name="confirmation_action" id="conf-action-input" form="schedule-form" value="confirmed">
                    <div style="display:inline-flex;border:1.5px solid #d1d5db;border-radius:6px;overflow:hidden;font-size:.78rem;font-weight:600;">
                        <button type="button" id="conf-pill-confirmed" onclick="setConfirmPill('confirmed')"
                                style="padding:.3rem .75rem;border:none;cursor:pointer;background:var(--accent);color:#fff;transition:background .12s,color .12s;white-space:nowrap;">
                            Confirmed
                        </button>
                        <button type="button" id="conf-pill-request" onclick="setConfirmPill('request')"
                                style="padding:.3rem .75rem;border:none;border-left:1.5px solid #d1d5db;cursor:pointer;background:#f9fafb;color:#555;transition:background .12s,color .12s;white-space:nowrap;">
                            Request Confirmation
                        </button>
                    </div>
                    <button type="button" onclick="recalcTravel(this)"
                            style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .65rem;
                                   border-radius:6px;border:1.5px solid #d1d5db;background:#f9fafb;
                                   font-size:.78rem;color:#374151;cursor:pointer;transition:border-color .12s,background .12s,color .12s;white-space:nowrap;flex-shrink:0;"
                            onmouseenter="this.style.borderColor='var(--accent)';this.style.background='#f0f7ff';this.style.color='var(--accent)';"
                            onmouseleave="this.style.borderColor='#d1d5db';this.style.background='#f9fafb';this.style.color='#374151';">
                        <span style="font-size:.9rem;line-height:1;">↻</span>
                        <span>Recalc Travel</span>
                    </button>
                </div>
                <div id="conf-no-changes-note" style="display:none;">
                    <span style="font-size:.72rem;color:#9ca3af;font-style:italic;">*Disabled, no changes made to date, time or duration.</span>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.35rem;flex-shrink:0;">
                <div id="tech-selection-error"
                     style="display:none;font-size:.75rem;font-weight:600;color:#dc2626;white-space:nowrap;">
                    Please confirm which tech will be assigned to the work order.
                </div>
                <div style="display:flex;gap:.75rem;">
                    <button type="button" onclick="closeScheduleModal()" class="btn btn-secondary">Cancel</button>
                    <button type="button" id="schedule-save-btn" onclick="submitScheduleForm()" class="btn btn-primary">Add Visit</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Event listeners must come AFTER the modal HTML exists in the DOM --}}
<script>
function updateSchDateLabel(val) {
    const el = document.getElementById('sch-date-label');
    if (!el) return;
    if (val) {
        const d = new Date(val + 'T00:00:00');
        el.textContent = d.toLocaleDateString('en-US', { weekday:'long', month:'long', day:'numeric', year:'numeric' });
        el.style.display = '';
    } else {
        el.textContent = '';
        el.style.display = 'none';
    }
}

@php
    $prefDayMap  = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
    $prefDayNums = array_values(array_filter(
        array_map(fn($d) => $prefDayMap[$d] ?? -1, array_keys($workOrder->preferred_availability ?? [])),
        fn($n) => $n >= 0
    ));
@endphp
(function () {
    const prefDayNums = @json($prefDayNums);

    function renderPreferredShortcuts() {
        const el = document.getElementById('preferred-date-shortcuts');
        if (!el || !prefDayNums.length) return;
        el.innerHTML = '';
        const found = [];
        const cursor = new Date();
        cursor.setDate(cursor.getDate() + 1);
        while (found.length < 3) {
            if (prefDayNums.includes(cursor.getDay())) found.push(new Date(cursor));
            cursor.setDate(cursor.getDate() + 1);
        }
        found.forEach(date => {
            const iso   = date.toISOString().slice(0, 10);
            const label = date.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric' });
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = label;
            btn.style.cssText = 'padding:.28rem .75rem;border-radius:999px;border:1.5px solid var(--accent);' +
                                 'background:#f0f7ff;color:var(--accent);font-size:.75rem;font-weight:700;' +
                                 'cursor:pointer;white-space:nowrap;transition:background .12s,color .12s;';
            btn.addEventListener('mouseenter', () => { btn.style.background = 'var(--accent)'; btn.style.color = '#fff'; });
            btn.addEventListener('mouseleave', () => { btn.style.background = '#f0f7ff'; btn.style.color = 'var(--accent)'; });
            btn.addEventListener('click', () => {
                document.getElementById('sch_date').value = iso;
                updatePrefStrip();
                loadTimeline(iso);
                updateSchDateLabel(iso);
            });
            el.appendChild(btn);
        });
    }
    renderPreferredShortcuts();
})();

document.getElementById('sch_date').addEventListener('change', e => {
    updatePrefStrip();
    loadTimeline(e.target.value);
    updateSchDateLabel(e.target.value);
});
updateSchDateLabel(document.getElementById('sch_date').value);
document.getElementById('sch_time').addEventListener('change', updateGhost);
document.getElementById('sch_duration').addEventListener('change', updateGhost);

// Preserve scroll position across assign/unassign form submits.
// We fire scrollTo twice: once via rAF (catches fast/cached loads) and once on
// the window load event (corrects for layout shift caused by images finishing).
(function () {
    const key = 'wo_scroll_' + location.pathname;
    const saved = sessionStorage.getItem(key);
    if (saved !== null) {
        sessionStorage.removeItem(key);
        const target = +saved;
        requestAnimationFrame(() => window.scrollTo(0, target));
        window.addEventListener('load', () => window.scrollTo(0, target));
    }
    document.querySelectorAll('form.preserve-scroll').forEach(f => {
        f.addEventListener('submit', () => sessionStorage.setItem(key, window.scrollY));
    });
})();
</script>

<script>
function setConfirmPill(val) {
    document.getElementById('conf-action-input').value = val;
    const pillConfirmed = document.getElementById('conf-pill-confirmed');
    const pillRequest   = document.getElementById('conf-pill-request');
    if (val === 'confirmed') {
        pillConfirmed.style.background = 'var(--accent)';
        pillConfirmed.style.color      = '#fff';
        pillRequest.style.background   = '#f9fafb';
        pillRequest.style.color        = '#555';
    } else {
        pillConfirmed.style.background = '#f9fafb';
        pillConfirmed.style.color      = '#555';
        pillRequest.style.background   = 'var(--accent)';
        pillRequest.style.color        = '#fff';
    }
}
function initConfirmPill() {
    setConfirmPill(_custHasConfirmed ? 'request' : 'confirmed');
}

let _schedulesVisible = true;
function toggleTechSchedules() {
    _schedulesVisible = !_schedulesVisible;
    const body  = document.getElementById('tech-schedule-body');
    const icon  = document.getElementById('toggle-schedules-icon');
    const label = document.getElementById('toggle-schedules-label');
    if (!body) return;
    body.style.display  = _schedulesVisible ? '' : 'none';
    icon.style.transform = _schedulesVisible ? '' : 'rotate(-90deg)';
    label.textContent    = _schedulesVisible ? 'Hide Schedules' : 'Show Schedules';
}
</script>

{{-- ── Pull from Prior Visit Modal ── --}}
@if($completedCount > 0)
<div id="pull-modal" onclick="if(event.target===this)closePullModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9200;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:100%;max-width:960px;max-height:88vh;display:flex;flex-direction:column;overflow:hidden;">

        <div style="background:var(--primary);padding:1rem 1.4rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-shrink:0;">
            <div>
                <h3 style="color:#fff;margin:0;font-size:.97rem;font-weight:700;">Prior Visits — {{ $workOrder->customer->name }}</h3>
                <p style="color:rgba(255,255,255,.65);margin:.15rem 0 0;font-size:.75rem;">{{ $completedCount }} {{ Str::plural('order', $completedCount) }} — choose what to pull into the current work order</p>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                <button type="button" id="pull-override-btn" onclick="togglePullOverride()"
                        style="display:inline-flex;align-items:center;gap:.35rem;padding:.28rem .75rem;border-radius:999px;border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);font-size:.75rem;font-weight:700;cursor:pointer;transition:all .15s;white-space:nowrap;">
                    <span id="pull-override-dot" style="width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.35);transition:background .15s;flex-shrink:0;"></span>
                    Override
                </button>
                <button onclick="closePullModal()" style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:5px;padding:.25rem .65rem;cursor:pointer;font-size:.9rem;">✕</button>
            </div>
        </div>

        <div style="overflow:auto;flex:1;">
            <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
                <thead>
                    <tr style="position:sticky;top:0;background:#f8f9fa;border-bottom:2px solid #e5e7eb;z-index:1;">
                        <th style="padding:.6rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;white-space:nowrap;width:110px;">Date</th>
                        <th style="padding:.6rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;width:240px;">Equipment &amp; Site</th>
                        <th style="padding:.6rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;">Full Description</th>
                        <th style="padding:.6rem 1rem;text-align:center;font-size:.75rem;font-weight:700;color:#6b7280;white-space:nowrap;width:140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($previousOrders as $po)
                    <tr style="border-bottom:1px solid #f0f0f0;vertical-align:top;"
                        data-desc="{{ $po->description }}"
                        data-equip="{{ $po->equipment_details }}"
                        data-label="{{ $po->woLabel() }}"
                        data-date="{{ $po->updated_at->format('M j, Y') }}">
                        <td style="padding:.8rem 1rem;white-space:nowrap;">
                            <span style="font-weight:700;color:var(--primary);font-size:.8rem;">{{ $po->woLabel() }}</span><br>
                            <span class="badge badge-{{ $po->status }}" style="font-size:.62rem;margin-top:.25rem;display:inline-block;">{{ str_replace('_',' ',$po->status) }}</span>
                        </td>
                        <td style="padding:.8rem 1rem;">
                            @if($po->equipment_details)
                            <p style="margin:0 0 .35rem;color:#374151;line-height:1.45;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;">{{ $po->equipment_details }}</p>
                            @endif
                            @if($po->site_street || $po->site_contact_name)
                            <p style="margin:0;color:#9ca3af;font-size:.75rem;display:flex;flex-direction:column;gap:.15rem;">
                                @if($po->site_street)<span>📍 {{ $po->site_street }}</span>@endif
                                @if($po->site_contact_name)<span>👤 {{ $po->site_contact_name }}@if($po->site_contact_phone) · {{ $po->site_contact_phone }}@endif</span>@endif
                            </p>
                            @endif
                            @if(!$po->equipment_details && !$po->site_street && !$po->site_contact_name)
                            <span style="color:#d1d5db;font-size:.78rem;font-style:italic;">—</span>
                            @endif
                        </td>
                        <td style="padding:.8rem 1rem;">
                            @if($po->description)
                            <p style="margin:0;color:#374151;line-height:1.5;overflow:hidden;display:-webkit-box;-webkit-line-clamp:5;-webkit-box-orient:vertical;">{{ $po->description }}</p>
                            @else
                            <span style="color:#d1d5db;font-size:.78rem;font-style:italic;">No description</span>
                            @endif
                        </td>
                        <td style="padding:.8rem 1rem;text-align:center;">
                            <div style="display:flex;flex-direction:column;gap:.35rem;">
                                <button type="button" onclick="pullDescription(this.closest('tr'))"
                                        style="padding:.3rem .55rem;border:1px solid var(--accent);border-radius:4px;background:#fff;color:var(--accent);font-size:.72rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Pull Description
                                </button>
                                <button type="button" onclick="pullEquipment(this.closest('tr'))"
                                        style="padding:.3rem .55rem;border:1px solid #7c3aed;border-radius:4px;background:#fff;color:#7c3aed;font-size:.72rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Pull Equipment
                                </button>
                                <button type="button" onclick="pullBoth(this.closest('tr'))"
                                        style="padding:.3rem .55rem;border:none;border-radius:4px;background:var(--primary);color:#fff;font-size:.72rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Pull Both
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:2rem;font-size:.88rem;">No completed prior orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
function openPullModal() {
    if (_pullOverride) togglePullOverride();
    document.getElementById('pull-modal').style.display = 'flex';
    document.addEventListener('keydown', pullKeyHandler);
}
function closePullModal() {
    document.getElementById('pull-modal').style.display = 'none';
    document.removeEventListener('keydown', pullKeyHandler);
}
function pullKeyHandler(e) { if (e.key === 'Escape') closePullModal(); }

function animateTextarea(ta) {
    const startH = ta.offsetHeight;
    ta.style.height = 'auto';
    const endH = Math.max(ta.scrollHeight + 2, startH);
    ta.style.overflow   = 'hidden';
    ta.style.height     = startH + 'px';
    ta.style.transition = 'height .38s cubic-bezier(.4,0,.2,1)';
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { ta.style.height = endH + 'px'; });
    });
    setTimeout(() => { ta.style.transition = ''; ta.style.overflow = ''; }, 420);
}

var _pullOverride = false;

function togglePullOverride() {
    _pullOverride = !_pullOverride;
    const btn = document.getElementById('pull-override-btn');
    const dot = document.getElementById('pull-override-dot');
    if (_pullOverride) {
        btn.style.background    = 'rgba(255,255,255,.25)';
        btn.style.borderColor   = '#fff';
        btn.style.color         = '#fff';
        dot.style.background    = '#fff';
    } else {
        btn.style.background    = 'rgba(255,255,255,.1)';
        btn.style.borderColor   = 'rgba(255,255,255,.35)';
        btn.style.color         = 'rgba(255,255,255,.6)';
        dot.style.background    = 'rgba(255,255,255,.35)';
    }
}

function isPullOverride() {
    return _pullOverride;
}

function applyPull(ta, ref, pulled) {
    const header   = `########## ${ref} ##########`;
    const indented = pulled ? pulled.split('\n').map(l => '\t' + l).join('\n') : '';
    const incoming = header + (indented ? '\n' + indented : '');
    if (isPullOverride() || !ta.value.trim()) {
        ta.value = incoming;
    } else {
        ta.value = ta.value.trimEnd() + '\n\n' + incoming;
    }
    animateTextarea(ta);
}

function pullDescription(row) {
    const label = row.dataset.label || '';
    const date  = row.dataset.date  || '';
    applyPull(document.getElementById('wo-description'), `REF: ${label} - completed on ${date}`, row.dataset.desc || '');
    closePullModal();
}

function pullEquipment(row) {
    const label = row.dataset.label || '';
    const date  = row.dataset.date  || '';
    applyPull(document.getElementById('wo-equipment'), `REF: ${label} - completed on ${date}`, row.dataset.equip || '');
    closePullModal();
}

function pullBoth(row) {
    const label = row.dataset.label || '';
    const date  = row.dataset.date  || '';
    const ref   = `REF: ${label} - completed on ${date}`;
    applyPull(document.getElementById('wo-description'), ref, row.dataset.desc  || '');
    applyPull(document.getElementById('wo-equipment'),   ref, row.dataset.equip || '');
    closePullModal();
}
</script>
@endif

{{-- ── Per-Visit Verification Override Modal ── --}}
@if($workOrder->visits->isNotEmpty())
<div id="visit-override-modal" onclick="if(event.target===this)closeVisitOverrideModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9100;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:100%;max-width:460px;overflow:hidden;">

        <div style="background:#1A3C5E;padding:1rem 1.4rem;display:flex;align-items:center;justify-content:space-between;">
            <h3 id="visit-override-title" style="color:#fff;margin:0;font-size:.97rem;font-weight:700;">Mark Visit as Verified</h3>
            <button onclick="closeVisitOverrideModal()" style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:5px;padding:.25rem .65rem;cursor:pointer;font-size:.9rem;">✕</button>
        </div>

        <form method="POST" id="visit-override-form" action="">
            @csrf
            <div style="padding:1.4rem;">
                <div style="display:flex;gap:.65rem;align-items:flex-start;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;padding:.75rem .9rem;margin-bottom:1.1rem;">
                    <span style="font-size:1.1rem;flex-shrink:0;">✓</span>
                    <p style="font-size:.83rem;color:#166534;margin:0;line-height:1.5;">
                        Confirm that you have verified this visit with the customer directly (e.g. by phone or in person). Document how you confirmed below.
                    </p>
                </div>

                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.4rem;">How was the visit confirmed? <span style="font-size:.78rem;font-weight:400;color:#9ca3af;">(optional)</span></label>
                <textarea name="override_reason" rows="4" maxlength="1000"
                          placeholder="e.g. Spoke with customer by phone — confirmed verbally. Moving forward."
                          style="width:100%;padding:.6rem .8rem;border:1px solid #d1d5db;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;line-height:1.5;font-family:inherit;"></textarea>
            </div>

            <div style="padding:.9rem 1.4rem;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;gap:.65rem;">
                <button type="button" onclick="closeVisitOverrideModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit"
                        style="padding:.5rem 1.25rem;background:#166634;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    ✓ Mark as Verified
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function openVisitOverrideModal(visitId, visitLabel) {
    const baseUrl = '{{ rtrim(route('admin.work-orders.visits.admin-confirm', [$workOrder, '__VISIT__']), '') }}';
    document.getElementById('visit-override-form').action = baseUrl.replace('__VISIT__', visitId);
    document.getElementById('visit-override-title').textContent = '✓ Mark as Verified — ' + visitLabel;
    const modal = document.getElementById('visit-override-modal');
    modal.style.display = 'flex';
    const ta = modal.querySelector('textarea');
    if (ta) { ta.value = ''; setTimeout(() => ta.focus(), 60); }
    document.addEventListener('keydown', visitOverrideKeyHandler);
}
function closeVisitOverrideModal() {
    document.getElementById('visit-override-modal').style.display = 'none';
    document.removeEventListener('keydown', visitOverrideKeyHandler);
}
function visitOverrideKeyHandler(e) { if (e.key === 'Escape') closeVisitOverrideModal(); }
</script>
@endif

@push('topbar-actions')
    <form method="POST" action="{{ route('admin.work-orders.destroy', $workOrder) }}" onsubmit="return confirm('Delete this work order?')" style="margin:0;">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
    </form>
@endpush

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;min-width:0;">
    <a href="{{ $backUrl }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;flex-shrink:0;">← {{ $backLabel }}</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;min-width:0;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        {{ $workOrder->woLabel() }}
        <span class="badge badge-{{ $workOrder->status }}" style="font-size:.7rem;flex-shrink:0;">{{ str_replace('_',' ',$workOrder->status) }}</span>
        <span id="wo-flash-target" style="display:inline-flex;align-items:center;"></span>
    </h1>
</div>
@endpush

{{-- ── Equipment Autocomplete ── --}}
@push('scripts')
<div id="equip-ac" style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.14);width:420px;max-height:340px;overflow:hidden;">
    <div style="padding:.5rem .85rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input id="equip-ac-search" type="text" placeholder="Search equipment…"
               autocomplete="off" spellcheck="false"
               style="flex:1;border:none;outline:none;font-size:.85rem;background:transparent;color:#111;">
        <span style="font-size:.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0;">esc to cancel</span>
    </div>
    <div id="equip-ac-results" style="overflow-y:auto;max-height:285px;"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    let EQUIP = [];
    fetch('/device-catalog/data').then(r => r.json()).then(d => { EQUIP = d; });

    const ta      = document.getElementById('wo-equipment');
    const panel   = document.getElementById('equip-ac');
    const search  = document.getElementById('equip-ac-search');
    const results = document.getElementById('equip-ac-results');
    if (!ta || !panel) return;

    let active     = false;
    let triggerPos = -1;
    let hiIdx      = -1;

    function checkTrigger() {
        const pos = ta.selectionStart;
        const val = ta.value;
        if (!active && pos >= 2 && val.slice(pos - 2, pos) === '..') {
            triggerPos = pos - 2;
            active = true;
            openPanel();
        } else if (active && pos <= triggerPos) {
            closePanel(false);
        }
    }
    ta.addEventListener('input',  checkTrigger);
    ta.addEventListener('keyup',  checkTrigger);

    function openPanel() {
        const rect   = ta.getBoundingClientRect();
        const panelW = 420;
        let left = rect.left;
        if (left + panelW > window.innerWidth - 8) left = window.innerWidth - panelW - 8;
        panel.style.top     = (rect.bottom + 4) + 'px';
        panel.style.left    = Math.max(8, left) + 'px';
        panel.style.display = 'block';
        search.value = '';
        renderResults(EQUIP.slice(0, 10));
        search.focus();
    }

    function closePanel(removeToken) {
        panel.style.display = 'none';
        active  = false;
        hiIdx   = -1;
        if (removeToken && triggerPos >= 0) {
            const val = ta.value;
            ta.value  = val.slice(0, triggerPos) + val.slice(triggerPos + 2);
            ta.selectionStart = ta.selectionEnd = triggerPos;
        }
        triggerPos = -1;
        ta.focus();
    }

    search.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        const filtered = q
            ? EQUIP.filter(e => (e.label + ' ' + e.q).toLowerCase().includes(q)).slice(0, 10)
            : EQUIP.slice(0, 10);
        renderResults(filtered);
    });

    function renderResults(items) {
        hiIdx = -1;
        if (!items.length) {
            results.innerHTML = '<div style="padding:.75rem 1rem;color:#9ca3af;font-size:.85rem;">No matches — keep typing</div>';
            return;
        }
        results.innerHTML = items.map((e, i) => {
            const lbl = e.label.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            return `<div class="eq-row" data-idx="${i}"
                        style="padding:.52rem 1rem;cursor:pointer;font-size:.875rem;color:#111;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;gap:.75rem;line-height:1.35;">
                        <span>${lbl}</span>
                        <span style="font-size:.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0;background:#f3f4f6;border-radius:4px;padding:.1rem .4rem;">${e.type}</span>
                    </div>`;
        }).join('');

        const rows = results.querySelectorAll('.eq-row');
        rows.forEach((row, i) => {
            row.addEventListener('mouseenter', () => setHi(i));
            row.addEventListener('click',      () => selectItem(items[i].label));
        });
    }

    function setHi(idx) {
        const rows = [...results.querySelectorAll('.eq-row')];
        rows.forEach((r, i) => r.style.background = i === idx ? '#eff6ff' : '');
        hiIdx = idx;
        if (rows[idx]) rows[idx].scrollIntoView({ block: 'nearest' });
    }

    function navKeyHandler(e) {
        if (!active) return;
        const rows = [...results.querySelectorAll('.eq-row')];
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHi(Math.min(hiIdx + 1, rows.length - 1));
            if (document.activeElement !== search) search.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHi(Math.max(hiIdx - 1, 0));
            if (document.activeElement !== search) search.focus();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (rows[hiIdx]) selectItem(rows[hiIdx].dataset.label);
            else if (rows.length === 1) selectItem(rows[0].dataset.label);
        } else if (e.key === 'Escape') {
            closePanel(true);
        }
    }
    search.addEventListener('keydown', navKeyHandler);
    ta.addEventListener('keydown', navKeyHandler);

    function selectItem(label) {
        const val = ta.value;
        ta.value  = val.slice(0, triggerPos) + label + val.slice(triggerPos + 2);
        ta.selectionStart = ta.selectionEnd = triggerPos + label.length;
        closePanel(false);
    }

    document.addEventListener('mousedown', function (e) {
        if (active && !panel.contains(e.target) && e.target !== ta) closePanel(true);
    });
})();
}); // DOMContentLoaded
</script>
@endpush

{{-- ── Description Dictation (Web Speech API) ── --}}
@push('scripts')
<style>
@keyframes mic-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .4; }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const SR  = window.SpeechRecognition || window.webkitSpeechRecognition;
    const btn = document.getElementById('desc-mic-btn');
    const lbl = document.getElementById('desc-mic-label');
    const ico = document.getElementById('desc-mic-icon');
    const ta  = document.getElementById('wo-description');
    if (!btn || !ta) return;

    if (!SR) {
        btn.disabled = true;
        btn.title    = 'Speech recognition is not supported in this browser (try Chrome or Edge)';
        btn.style.opacity = '.4';
        btn.style.cursor  = 'not-allowed';
        return;
    }

    let recog     = null;
    let listening = false;
    let baseText  = '';

    function setListening(on) {
        listening = on;
        if (on) {
            btn.style.cssText += 'background:#fef2f2;color:#dc2626;border-color:#fecaca;';
            ico.style.animation = 'mic-pulse 1s ease-in-out infinite';
            if (lbl) lbl.textContent = 'Stop';
            btn.title = 'Click to stop dictation';
        } else {
            btn.style.background  = '#f9fafb';
            btn.style.color       = '#6b7280';
            btn.style.borderColor = '#d1d5db';
            ico.style.animation   = '';
            if (lbl) lbl.textContent = 'Dictate';
            btn.title = 'Dictate description';
        }
    }

    function startListening() {
        baseText = ta.value;
        recog    = new SR();
        recog.continuous      = false;  // end naturally; onend restarts below
        recog.interimResults  = true;
        recog.maxAlternatives = 1;
        recog.lang            = 'en-US';

        recog.onresult = function (e) {
            let interim = '', final = '';
            for (let i = e.resultIndex; i < e.results.length; i++) {
                if (e.results[i].isFinal) final  += e.results[i][0].transcript;
                else                      interim += e.results[i][0].transcript;
            }
            if (final) {
                const sep = baseText && !/\s$/.test(baseText) ? ' ' : '';
                baseText += sep + final.trim();
                ta.value  = baseText;
            } else if (interim) {
                const sep = baseText && !/\s$/.test(baseText) ? ' ' : '';
                ta.value  = baseText + sep + interim;
            }
        };

        recog.onerror = function (e) {
            if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                stopListening();
                alert('Microphone access was denied. Please allow microphone access in your browser settings.');
            } else if (e.error === 'network') {
                stopListening();
                alert('Speech recognition requires internet access. If you\'re on HTTP, try switching to HTTPS.');
            } else if (e.error !== 'no-speech') {
                stopListening();
            }
        };

        // slight delay before restarting avoids Chrome's rapid-restart silent failure
        recog.onend = function () {
            if (listening) setTimeout(startListening, 150);
        };

        try {
            recog.start();
            if (!listening) setListening(true);
        } catch (err) {
            console.error('Speech recognition start error:', err);
            setListening(false);
        }
    }

    function stopListening() {
        setListening(false);
        if (recog) {
            recog.onend = null;  // prevent auto-restart
            recog.stop();
            recog = null;
        }
    }

    window.toggleDescMic = function () {
        listening ? stopListening() : startListening();
    };
});
</script>
@endpush

{{-- ── Signature preview popup ── --}}
@push('scripts')
<div id="sig-preview-popup"
     style="display:none;position:fixed;z-index:10000;pointer-events:none;
            background:#fff;border:1px solid #d1d5db;border-radius:10px;
            box-shadow:0 8px 32px rgba(0,0,0,.2);padding:.85rem 1rem;
            min-width:240px;max-width:380px;">
    <img id="sig-preview-img" src="" alt="Signature preview"
         style="width:100%;max-height:200px;object-fit:contain;display:block;
                background:#f8fafc;border-radius:5px;">
    <div id="sig-preview-cap"
         style="font-size:.72rem;color:#6b7280;margin-top:.55rem;text-align:center;line-height:1.4;"></div>
</div>
<script>
(function () {
    var popup = document.getElementById('sig-preview-popup');
    var pImg  = document.getElementById('sig-preview-img');
    var pCap  = document.getElementById('sig-preview-cap');
    if (!popup) return;

    function wire() {
        document.querySelectorAll('[data-sig-img]').forEach(function (img) {
            if (img._sigPopupWired) return;
            img._sigPopupWired = true;
            img.addEventListener('mouseenter', function (e) {
                pImg.src        = img.src;
                pCap.textContent = img.dataset.sigCaption || '';
                popup.style.display = 'block';
                reposition(e);
            });
            img.addEventListener('mousemove', reposition);
            img.addEventListener('mouseleave', function () {
                popup.style.display = 'none';
            });
        });
    }

    function reposition(e) {
        var pw = 380;
        var ph = popup.offsetHeight || 270;
        var x  = e.clientX + 18;
        var y  = e.clientY - Math.round(ph / 2);
        if (x + pw > window.innerWidth  - 8) x = e.clientX - pw - 10;
        if (y < 8)                           y = 8;
        if (y + ph > window.innerHeight - 8) y = window.innerHeight - ph - 8;
        popup.style.left = x + 'px';
        popup.style.top  = y + 'px';
    }

    wire();
    // Re-wire after any SPA navigation that swaps content
    document.addEventListener('DOMContentLoaded', wire);
})();
</script>
@endpush

@endsection
