@extends('layouts.portal')
@section('title', $workOrder->woLabel())
@php use App\Models\Invoice; @endphp

@section('content')
@php
    $urgencyBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
    $urgencyColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';
@endphp
<div style="margin-bottom:1.5rem;">
    <a href="{{ route('portal.work-orders.index') }}" style="color:var(--accent);text-decoration:none;font-size:.9rem;">← My Work Orders</a>
    <div style="display:flex;align-items:center;gap:.75rem;margin-top:.4rem;flex-wrap:wrap;">
        <h1 class="page-title" style="margin:0;">{{ $workOrder->woLabel() }}</h1>
        <span style="padding:.25rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700;background:{{ $urgencyBg }};color:{{ $urgencyColor }};">{{ ucfirst($workOrder->urgency) }}</span>
        <span class="badge badge-{{ $workOrder->status }}">{{ str_replace('_',' ',$workOrder->status) }}</span>
    </div>
</div>


@if($workOrder->cancel_reason !== null && $workOrder->status === \App\Models\WorkOrder::STATUS_AWAITING_FEEDBACK)
<div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
    <div style="font-weight:700;color:#78350f;font-size:.95rem;margin-bottom:.35rem;">⚠ Cancellation Request Submitted</div>
    <p style="font-size:.88rem;color:#92400e;margin-bottom:{{ $workOrder->cancel_reason ? '.75rem' : '0' }};">
        Your request to cancel this scheduled visit has been received.
        @if(!$workOrder->cancel_reason)
            A team member will follow up with you on the next business day.
        @endif
    </p>
    @if($workOrder->cancel_reason)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:.65rem .9rem;font-size:.87rem;color:#78350f;">
        <span style="font-weight:600;">Your instructions:</span> {{ $workOrder->cancel_reason }}
    </div>
    @endif
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

    <div>
        @php
            $canEdit     = in_array($workOrder->status, ['new', 'triaged']);
            $photos      = $workOrder->attachments->filter(fn($a) => str_starts_with($a->mime_type, 'image/'));
            $docs        = $workOrder->attachments->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'));
            $previewable = ['application/pdf', 'text/plain'];
        @endphp

        {{-- Invoice panels: shown for each issued or pending-payment invoice --}}
        @foreach($workOrder->invoices->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PAYMENT_RECEIVED])->values() as $inv)
        @php
            $invNum  = 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT);
            $isPaid  = $inv->status === Invoice::STATUS_PAYMENT_RECEIVED;
            $invSub  = (float)($inv->subtotal  ?? $inv->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
            $invTax  = (float)($inv->tax_amount ?? round($invSub * (float)($inv->tax_rate ?? 0), 2));
            $invTot  = (float)($inv->total     ?? round($invSub + $invTax, 2));
        @endphp
        {{-- Only show the standalone card when there is exactly one total invoice on the WO --}}
        @if($workOrder->invoices->whereIn('status', [\App\Models\Invoice::STATUS_ISSUED, \App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED])->count() <= 1)

        @if($isPaid)
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem;">
            <span style="font-size:1.4rem;">✅</span>
            <div>
                <div style="font-weight:700;color:#166534;font-size:.95rem;">{{ $invNum }} — Payment Received, Thank You!</div>
                <div style="font-size:.85rem;color:#15803d;margin-top:.1rem;">We are verifying your payment and will mark your order complete shortly.</div>
            </div>
        </div>
        @else
        <div style="background:#fff;border:2px solid #2E86C1;border-radius:8px;padding:1.5rem;margin-bottom:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
                <div>
                    <h2 style="font-size:1.05rem;color:var(--primary);margin:0 0 .2rem;">
                        <a href="{{ route('portal.invoices.show', $inv) }}" style="color:var(--primary);text-decoration:none;">{{ $invNum }}</a> — Invoice Ready
                    </h2>
                    @if($inv->due_date)
                    <div style="font-size:.82rem;color:{{ $inv->due_date->isPast() ? '#dc2626' : '#6b7280' }};">
                        Due {{ $inv->due_date->format('M j, Y') }}{{ $inv->due_date->isPast() ? ' (overdue)' : '' }}
                    </div>
                    @endif
                </div>
                <div style="font-size:1.6rem;font-weight:800;color:var(--primary);">${{ number_format($invTot, 2) }}</div>
            </div>

            {{-- Line items --}}
            <table style="width:100%;border-collapse:collapse;font-size:.88rem;margin-bottom:1rem;">
                <thead>
                    <tr style="background:#f1f5f9;">
                        <th style="padding:.45rem .75rem;text-align:left;color:#475569;font-weight:600;">Description</th>
                        <th style="padding:.45rem .75rem;text-align:right;color:#475569;font-weight:600;white-space:nowrap;">Qty</th>
                        <th style="padding:.45rem .75rem;text-align:right;color:#475569;font-weight:600;white-space:nowrap;">Unit Price</th>
                        <th style="padding:.45rem .75rem;text-align:right;color:#475569;font-weight:600;white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inv->lineItems->sortBy('sort_order') as $item)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:.5rem .75rem;">{{ $item->description }}</td>
                        <td style="padding:.5rem .75rem;text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2),'0'),'.') }}</td>
                        <td style="padding:.5rem .75rem;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding:.5rem .75rem;text-align:right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div style="display:flex;justify-content:flex-end;margin-bottom:1.25rem;">
                <div style="width:220px;font-size:.87rem;">
                    <div style="display:flex;justify-content:space-between;padding:.25rem 0;color:#555;">
                        <span>Subtotal</span><span>${{ number_format($invSub, 2) }}</span>
                    </div>
                    @if($invTax > 0)
                    <div style="display:flex;justify-content:space-between;padding:.25rem 0;color:#555;">
                        <span>Tax ({{ number_format((float)($inv->tax_rate ?? 0) * 100, 2) }}%)</span>
                        <span>${{ number_format($invTax, 2) }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;padding:.45rem 0;border-top:2px solid #e5e7eb;margin-top:.2rem;font-weight:700;font-size:.95rem;color:var(--primary);">
                        <span>Total Due</span><span>${{ number_format($invTot, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment terms --}}
            @if($inv->payment_terms)
            <div style="padding:.75rem 1rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.85rem;color:#555;margin-bottom:1rem;">
                <strong>Payment Terms:</strong> {{ $inv->payment_terms }}
            </div>
            @endif

            {{-- Visits covered --}}
            @php
                $coveredIds    = $inv->covered_visit_ids ?? [];
                $coveredVisits = $coveredIds
                    ? $workOrder->visits->whereIn('id', $coveredIds)->sortBy('scheduled_at')->values()
                    : collect();
            @endphp
            @if($coveredVisits->isNotEmpty())
            <div style="margin-bottom:1.25rem;">
                <div style="font-weight:700;color:var(--primary);margin-bottom:.5rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Visits Covered</div>
                <div style="display:flex;flex-direction:column;gap:.4rem;">
                    @foreach($coveredVisits as $cv)
                    @php
                        $cvSig      = $cv->signature;
                        $cvEntries  = $cv->timeEntries ?? collect();
                        $cvArrival  = $cvEntries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                        $cvDepart   = $cvEntries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                        $cvSigPath  = $cvSig ? storage_path('app/signatures/work-orders/'.$cvSig->signature_path) : null;
                        $cvSigExists = $cvSigPath && file_exists($cvSigPath);
                    @endphp
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:.6rem .85rem;font-size:.85rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            <span style="color:#1e293b;font-weight:600;">{{ $cv->scheduled_at->format('l, M j, Y') }}</span>
                            @if($cvSig)
                            <span style="flex-shrink:0;font-size:.75rem;padding:.12rem .5rem;border-radius:999px;background:#d1fae5;color:#065f46;font-weight:700;border:1px solid #6ee7b7;">✓ Signed</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:.3rem;">
                            <div style="color:#6b7280;font-size:.8rem;">
                                Scheduled {{ $cv->scheduled_at->format('g:i A') }}
                                @if($cvArrival)
                                · Arrived: <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvArrival)->format('g:i A') }}</span>
                                @if($cvDepart) · Departed: <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvDepart)->format('g:i A') }}</span>@endif
                                @endif
                            </div>
                            @if($cvSigExists)
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($cvSigPath)) }}"
                                 alt="Visit signature"
                                 style="height:30px;max-width:100px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:4px;flex-shrink:0;">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Payment action --}}
            <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;">
                <p style="font-size:.88rem;color:#374151;margin-bottom:1rem;">
                    Once you have submitted payment using the terms above, click the button below to notify us and we will verify and close out your order.
                </p>
                <form method="POST" action="{{ route('portal.invoices.submit-payment', $inv) }}">
                    @csrf
                    <button type="submit"
                            style="width:100%;padding:.7rem;background:var(--accent);color:#fff;border:none;border-radius:7px;font-size:.95rem;font-weight:700;cursor:pointer;letter-spacing:.01em;">
                        ✓ I've Submitted My Payment
                    </button>
                </form>
            </div>
        </div>
        @endif {{-- single-invoice guard --}}
        @endif
        @endforeach

        {{-- Invoice-ready banner --}}
        @php $issuedInvs = $workOrder->invoices->where('status', \App\Models\Invoice::STATUS_ISSUED)->values(); @endphp
        @if($issuedInvs->isNotEmpty())
        <div style="background:#eff6ff;border:1.5px solid #93c5fd;border-radius:8px;padding:.8rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
            <div style="flex-shrink:0;width:26px;height:26px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;">
                <span style="color:#fff;font-weight:800;font-size:.85rem;line-height:1;">!</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;color:#1e40af;font-size:.88rem;">
                    {{ $issuedInvs->count() === 1 ? 'You have an invoice ready for payment.' : 'You have '.$issuedInvs->count().' invoices ready for payment.' }}
                </div>
                <div style="font-size:.78rem;color:#3b82f6;margin-top:.1rem;">Review and submit payment to complete your order.</div>
            </div>
            <div style="display:flex;gap:.4rem;flex-shrink:0;flex-wrap:wrap;">
                @foreach($issuedInvs as $issuedInv)
                @php $issuedNum = 'INV-' . str_pad($issuedInv->id, 4, '0', STR_PAD_LEFT); @endphp
                <button type="button"
                        onclick="openInvPreview('inv-preview-{{ $issuedInv->id }}')"
                        style="padding:.32rem .8rem;background:#2563eb;color:#fff;border:none;border-radius:6px;font-size:.8rem;font-weight:700;cursor:pointer;white-space:nowrap;letter-spacing:.01em;">
                    {{ $issuedInvs->count() === 1 ? 'View Invoice →' : $issuedNum.' →' }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Scheduled Visits --}}
        @php
            $today = \Carbon\Carbon::today();
            $sortedVisits = $workOrder->visits->sortBy(function($v) use ($today) {
                $isToday = $v->scheduled_at->isSameDay($today);
                return [$isToday ? 0 : 1, -$v->scheduled_at->timestamp];
            })->values();
            $durFmt = fn($m) => $m >= 60
                ? floor($m/60).'h'.($m%60 ? ' '.($m%60).'m' : '')
                : $m.'m';
        @endphp
        @if($sortedVisits->isNotEmpty())
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Scheduled {{ $sortedVisits->count() === 1 ? 'Visit' : 'Visits' }}</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Appointments · Time tracking</div>
                    </div>
                </div>
                @if($workOrder->site_street)
                <div style="font-size:.75rem;color:rgba(255,255,255,.65);display:flex;align-items:center;gap:.3rem;flex-shrink:0;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    {{ $workOrder->site_street }}
                </div>
                @endif
            </div>
            <div style="padding:1.25rem 1.5rem;">

            {{-- Visit cards grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.85rem;">
            @foreach($sortedVisits as $visit)
            @php
                $isToday      = $visit->scheduled_at->isSameDay($today);
                $vstatus      = $visit->confirmation_status;
                $visitTechs   = $visit->techs->map(fn($t) => $t->user)->filter();
                $vTimeEntries = $visit->timeEntries;
                $vSig         = $visit->signature;
                $vArrival     = $vTimeEntries->min('clocked_in_at');
                $vDeparture   = $vTimeEntries->filter(fn($t) => $t->clocked_out_at)->max('clocked_out_at');

                if ($isToday) {
                    $cardBg     = '#eff6ff';
                    $cardBorder = '2px solid #3b82f6';
                } else {
                    $cardBg     = '#f8fafc';
                    $cardBorder = '1px solid #e2e8f0';
                }
            @endphp
            <div style="background:{{ $cardBg }};border:{{ $cardBorder }};border-radius:10px;padding:1rem 1.1rem;display:flex;flex-direction:column;gap:.6rem;">

                {{-- Card header: date + tech avatar --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                    <div style="min-width:0;">
                        @if($isToday)
                        <div style="font-size:.62rem;font-weight:800;color:#1d4ed8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.15rem;">Today</div>
                        @endif
                        <div style="font-size:.92rem;font-weight:700;color:var(--primary);line-height:1.25;">
                            {{ $visit->scheduled_at->format('l') }}
                        </div>
                        <div style="font-size:.82rem;color:#64748b;margin-top:.05rem;">
                            {{ $visit->scheduled_at->format('F j, Y') }}
                        </div>
                    </div>
                    @if($visitTechs->count())
                    <div style="display:flex;gap:.2rem;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;max-width:96px;">
                        @foreach($visitTechs as $tech)
                        @php $hasPhoto = $tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)); @endphp
                        @if($hasPhoto)
                        <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}" title="{{ $tech->name }}"
                             style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #bfdbfe;display:block;cursor:default;">
                        @else
                        <div title="{{ $tech->name }}"
                             style="width:38px;height:38px;border-radius:50%;background:var(--primary);border:2px solid #bfdbfe;display:flex;align-items:center;justify-content:center;font-size:.88rem;font-weight:700;color:#fff;cursor:default;flex-shrink:0;">
                            {{ strtoupper(substr($tech->name, 0, 1)) }}
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Time + duration --}}
                @if($visit->scheduled_at->format('H:i') !== '00:00')
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span style="font-size:.95rem;font-weight:700;color:var(--accent);">{{ $visit->scheduled_at->format('g:i A') }}</span>
                    @if($visit->duration_estimate_minutes)
                    <span style="font-size:.72rem;color:#555;background:#dbeafe;padding:.1em .5em;border-radius:999px;">{{ $durFmt($visit->duration_estimate_minutes) }} est.</span>
                    @endif
                </div>
                @endif

                {{-- Status + actions --}}
                @if($vstatus === 'confirmed')
                <div style="display:inline-flex;align-items:center;gap:.3rem;background:#d1fae5;border:1px solid #6ee7b7;border-radius:5px;padding:.3rem .7rem;font-size:.78rem;color:#065f46;font-weight:600;">
                    ✓ Confirmed
                </div>
                @elseif($vstatus === 'pending')
                <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:5px;padding:.3rem .7rem;font-size:.78rem;color:#92400e;font-weight:600;margin-bottom:.1rem;">
                    ⏳ Awaiting your confirmation
                </div>
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('portal.work-orders.visits.confirm', [$workOrder, $visit]) }}">
                        @csrf
                        <button type="submit" style="padding:.3rem .75rem;background:#16a34a;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer;">✓ Confirm</button>
                    </form>
                    <button type="button" onclick="openDeclineModal({{ $visit->id }})"
                            style="padding:.3rem .75rem;background:#fff;color:#dc2626;border:1px solid #fca5a5;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer;">
                        ✕ Reschedule
                    </button>
                </div>
                @elseif($vstatus === 'declined')
                <div style="display:inline-flex;align-items:center;gap:.3rem;background:#fff7ed;border:1px solid #fdba74;border-radius:5px;padding:.3rem .7rem;font-size:.78rem;color:#9a3412;font-weight:600;">
                    Reschedule requested
                </div>
                @endif

                {{-- On-site times --}}
                @if($vArrival)
                <div style="padding:.4rem .65rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:5px;font-size:.75rem;color:#166534;">
                    @php $vMins = $vDeparture ? (int) $vArrival->diffInMinutes($vDeparture) : null; @endphp
                    <span>In {{ $vArrival->format('g:i A') }}</span>
                    @if($vDeparture)
                    <span style="margin:0 .3rem;color:#86efac;">·</span>
                    <span>Out {{ $vDeparture->format('g:i A') }}</span>
                    <span style="margin:0 .3rem;color:#86efac;">·</span>
                    <strong>{{ $vMins >= 60 ? floor($vMins/60).'h'.($vMins%60 ? ' '.($vMins%60).'m' : '') : $vMins.'m' }}</strong>
                    @endif
                </div>
                @endif

                {{-- Signature image --}}
                @if($vSig)
                @php
                    $vSigPath = storage_path('app/signatures/work-orders/' . $vSig->signature_path);
                    $vSigB64  = file_exists($vSigPath) ? base64_encode(file_get_contents($vSigPath)) : null;
                @endphp
                @if($vSigB64)
                <div>
                    <div style="font-size:.62rem;font-weight:700;color:#166534;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">✍ Signed · {{ $vSig->signed_at->format('M j, Y') }}</div>
                    <img src="data:image/png;base64,{{ $vSigB64 }}"
                         alt="Signature"
                         onclick="openSigLightbox(this.src)"
                         style="max-width:100%;border:1px solid #bbf7d0;border-radius:5px;background:#fff;display:block;cursor:zoom-in;transition:opacity .15s;"
                         onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                </div>
                @else
                <div style="display:inline-flex;align-items:center;gap:.3rem;background:#d1fae5;border:1px solid #6ee7b7;border-radius:999px;padding:.15rem .55rem;font-size:.7rem;color:#065f46;font-weight:700;">
                    ✍ Signed · {{ $vSig->signed_at->format('M j, Y') }}
                </div>
                @endif
                @endif

            </div>{{-- /visit card --}}
            @endforeach
            </div>{{-- /grid --}}

            </div>{{-- /inner padding --}}
        </div>
        @endif

        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Work Order Details</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Description · Services · Schedule · Site</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.4rem;">
                    @if($canEdit)
                    <button type="button" id="edit-toggle-btn" onclick="toggleEdit()" title="Edit Details"
                            style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.3);border-radius:4px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.85);font-size:.82rem;cursor:pointer;padding:0;transition:background .15s;flex-shrink:0;">
                        ✎
                    </button>
                    @endif
                    <button type="button" id="details-collapse-btn" onclick="toggleDetails()" title="Collapse / Expand"
                            style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.3);border-radius:4px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.85);font-size:.65rem;cursor:pointer;padding:0;transition:background .15s;flex-shrink:0;">
                        <span id="details-chevron" style="display:inline-block;transition:transform .28s ease;transform:rotate(180deg);">▲</span>
                    </button>
                </div>
            </div>
            <div style="padding:1.5rem;">

            {{-- Condensed summary shown when card is collapsed --}}
            <div id="details-collapsed-summary" style="display:block;padding-bottom:.85rem;border-bottom:1px solid #f0f0f0;margin-bottom:.5rem;">
                @if($workOrder->description)
                <p style="font-size:.88rem;color:#555;line-height:1.45;margin:0 0 .5rem;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $workOrder->description }}</p>
                @endif
                @if($workOrder->equipment_details)
                <p style="font-size:.8rem;color:#6b7280;line-height:1.4;margin:0 0 .5rem;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;background:#f8f9fa;border-left:3px solid var(--primary);padding:.35rem .7rem;border-radius:0 4px 4px 0;">{{ $workOrder->equipment_details }}</p>
                @endif
                @if($workOrder->serviceTypes->count())
                <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;margin-bottom:.5rem;">
                    <span style="font-size:.72rem;color:#999;margin-right:.05rem;">Services:</span>
                    @foreach($workOrder->serviceTypes as $svc)
                    <span style="background:#e0f2fe;color:#0369a1;padding:.12em .55em;border-radius:999px;font-size:.75rem;font-weight:600;">{{ $svc->name }}</span>
                    @endforeach
                </div>
                @endif
                @php $hasCollapsedMeta = $workOrder->site_street || $workOrder->site_contact_name || $workOrder->site_contact_phone; @endphp
                @if($hasCollapsedMeta)
                <div style="display:flex;flex-wrap:wrap;gap:.5rem .85rem;font-size:.82rem;color:#555;">
                    @if($workOrder->site_street)<span style="display:flex;align-items:center;gap:.3rem;"><span style="color:#aaa;font-size:.78rem;">📍</span>{{ $workOrder->site_street }}</span>@endif
                    @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
                    <span style="display:flex;align-items:center;gap:.3rem;"><span style="color:#aaa;font-size:.78rem;">👤</span>{{ $workOrder->site_contact_name }}@if($workOrder->site_contact_phone) <a href="tel:{{ $workOrder->site_contact_phone }}" style="color:var(--accent);text-decoration:none;">{{ $workOrder->site_contact_phone }}</a>@endif</span>
                    @endif
                </div>
                @endif

                {{-- Attachment thumbnails --}}
                @if($workOrder->attachments->count())
                <div style="display:flex;gap:.35rem;flex-wrap:wrap;align-items:flex-end;margin-top:.5rem;">
                    @foreach($photos->take(6) as $photo)
                    <img src="{{ route('attachments.view', $photo) }}" alt="{{ $photo->original_name }}" title="{{ $photo->original_name }}"
                         style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #e5e7eb;cursor:zoom-in;flex-shrink:0;"
                         onclick="openLightbox('{{ route('attachments.view', $photo) }}','{{ addslashes($photo->original_name) }}','{{ route('attachments.download', $photo) }}')">
                    @endforeach
                    @foreach($docs as $doc)
                    @php $ext = strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)); @endphp
                    <button type="button" onclick="openAttachModal()" title="{{ $doc->original_name }}"
                            style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:40px;height:40px;border-radius:4px;border:1px solid #e5e7eb;background:#f8f9fa;cursor:pointer;padding:2px;gap:1px;flex-shrink:0;">
                        <span style="font-size:1.05rem;line-height:1;">📄</span>
                        <span style="font-size:.5rem;color:#888;line-height:1;overflow:hidden;white-space:nowrap;max-width:36px;text-align:center;">{{ $ext }}</span>
                    </button>
                    @endforeach
                    <button type="button" onclick="openAttachModal()"
                            style="font-size:.73rem;color:var(--accent);background:none;border:none;cursor:pointer;padding:0 2px;font-weight:600;align-self:center;">
                        Manage →
                    </button>
                </div>
                @elseif($canEdit)
                <button type="button" onclick="openAttachModal()"
                        style="font-size:.73rem;color:var(--accent);background:none;border:none;cursor:pointer;padding:0;font-weight:600;margin-top:.4rem;">
                    + Add attachments
                </button>
                @endif
            </div>

            {{-- Expandable body --}}
            <div id="details-body"
                 style="display:grid;grid-template-rows:0fr;overflow:hidden;transition:grid-template-rows .3s ease, opacity .25s ease;opacity:0;"
                 data-collapsed="1">
            <div style="min-height:0;">

            {{-- Display view --}}
            <div id="details-display">
                <div style="border-bottom:1px solid #f0f0f0;padding-bottom:.85rem;margin-bottom:.85rem;display:flex;flex-wrap:wrap;gap:.35rem .1rem;font-size:.83rem;color:#555;">
                    <span style="color:#999;margin-right:.25rem;">Services:</span>
                    @forelse($workOrder->serviceTypes as $svc)
                    <span style="background:#e0f2fe;color:#0369a1;padding:.15em .6em;border-radius:999px;font-size:.78rem;font-weight:600;">{{ $svc->name }}</span>
                    @empty
                    <span style="color:#aaa;">None specified</span>
                    @endforelse
                </div>

                <p style="font-size:.93rem;color:#444;line-height:1.6;margin-bottom:1.1rem;">{{ $workOrder->description ?: '—' }}</p>

                @if($workOrder->equipment_details)
                <div style="background:#f8f9fa;border-left:3px solid var(--primary);padding:.75rem 1rem;border-radius:0 5px 5px 0;margin-bottom:1.1rem;">
                    <p style="font-size:.8rem;font-weight:700;color:var(--primary);margin-bottom:.3rem;">EQUIPMENT DETAILS</p>
                    <p style="font-size:.88rem;color:#444;white-space:pre-wrap;">{{ $workOrder->equipment_details }}</p>
                </div>
                @endif

                <div style="font-size:.78rem;color:#aaa;margin-top:.6rem;">
                    Submitted {{ $workOrder->created_at->format('M j, Y') }} at {{ $workOrder->created_at->format('g:i A') }}
                </div>

                @if($workOrder->site_contact_name || $workOrder->site_contact_phone || $workOrder->preferred_date)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:1rem;">
                    @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
                    <div style="padding:.75rem 1rem;background:#f8f9fa;border-radius:6px;border:1px solid #e5e7eb;">
                        <div style="font-size:.68rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Site Contact</div>
                        @if($workOrder->site_contact_name)<div style="font-size:.92rem;font-weight:600;color:#1e293b;">{{ $workOrder->site_contact_name }}</div>@endif
                        @if($workOrder->site_contact_phone)<a href="tel:{{ $workOrder->site_contact_phone }}" style="font-size:.85rem;color:var(--accent);text-decoration:none;display:block;margin-top:.1rem;">{{ $workOrder->site_contact_phone }}</a>@endif
                    </div>
                    @endif
                    @if($workOrder->preferred_date)
                    <div style="padding:.75rem 1rem;background:#f0f7ff;border-radius:6px;border:1px solid #bfdbfe;">
                        <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.4rem;">Preferred Date</div>
                        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">{{ $workOrder->preferred_date->format('l, F j, Y') }}</div>
                    </div>
                    @endif
                </div>
                @endif

                @if($workOrder->preferred_availability)
                @php
                    $custAvailDayNames = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday'];
                    $custAvailSlotDefs = ['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']];
                @endphp
                <div style="margin-top:.75rem;padding:.75rem 1rem;background:#f0f6ff;border-radius:6px;border:1px solid #bfdbfe;">
                    <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.55rem;">Preferred Availability</div>
                    @foreach($custAvailDayNames as $dayKey => $dayName)
                        @if(!empty($workOrder->preferred_availability[$dayKey]))
                        <div style="display:flex;align-items:center;gap:.45rem;margin-bottom:.35rem;flex-wrap:wrap;justify-content:center;">
                            <span style="font-size:.82rem;font-weight:700;color:var(--primary);min-width:90px;text-align:right;">{{ $dayName }}:</span>
                            @foreach($custAvailSlotDefs as $slot => $slotData)
                            @php $active = in_array($slot, $workOrder->preferred_availability[$dayKey]); @endphp
                            <span style="display:inline-flex;flex-direction:column;align-items:center;padding:.2rem .6rem;border-radius:6px;border:1.5px solid {{ $active ? '#86efac' : '#e5e7eb' }};background:{{ $active ? '#dcfce7' : '#f9fafb' }};min-width:92px;text-align:center;">
                                <span style="font-size:.72rem;font-weight:700;color:{{ $active ? '#15803d' : '#9ca3af' }};line-height:1.3;">{{ $slotData[0] }}</span>
                                <span style="font-size:.62rem;color:{{ $active ? '#16a34a' : '#d1d5db' }};line-height:1.2;">{{ $slotData[1] }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- Attachments quick-view --}}
                <div style="margin-top:.85rem;padding-top:.75rem;border-top:1px solid #f0f0f0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.45rem;">
                        <span style="font-size:.72rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.07em;">Attachments{{ $workOrder->attachments->count() ? ' ('.$workOrder->attachments->count().')' : '' }}</span>
                        <button type="button" onclick="openAttachModal()" style="font-size:.75rem;color:var(--accent);background:none;border:none;cursor:pointer;padding:0;font-weight:600;">{{ $canEdit ? 'Manage →' : 'View →' }}</button>
                    </div>
                    @if($workOrder->attachments->count())
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;align-items:flex-end;">
                        @foreach($photos->take(8) as $photo)
                        <img src="{{ route('attachments.view', $photo) }}" alt="{{ $photo->original_name }}" title="{{ $photo->original_name }}"
                             style="width:48px;height:48px;object-fit:cover;border-radius:5px;border:1px solid #e5e7eb;cursor:zoom-in;flex-shrink:0;"
                             onclick="openLightbox('{{ route('attachments.view', $photo) }}','{{ addslashes($photo->original_name) }}','{{ route('attachments.download', $photo) }}')">
                        @endforeach
                        @foreach($docs as $doc)
                        @php $ext = strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)); @endphp
                        <button type="button" onclick="openAttachModal()" title="{{ $doc->original_name }}"
                                style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:48px;height:48px;border-radius:5px;border:1px solid #e5e7eb;background:#f8f9fa;cursor:pointer;padding:3px;gap:1px;flex-shrink:0;">
                            <span style="font-size:1.2rem;line-height:1;">📄</span>
                            <span style="font-size:.52rem;color:#888;line-height:1;overflow:hidden;white-space:nowrap;max-width:42px;text-align:center;">{{ $ext }}</span>
                        </button>
                        @endforeach
                    </div>
                    @else
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span style="font-size:.82rem;color:#bbb;">No attachments yet.</span>
                        @if($canEdit)<button type="button" onclick="openAttachModal()" style="font-size:.75rem;color:var(--accent);background:none;border:none;cursor:pointer;padding:0;font-weight:600;">+ Add files</button>@endif
                    </div>
                    @endif
                </div>
            </div>{{-- /details-display --}}

            {{-- Edit form (hidden by default) --}}
            @if($canEdit)
            <form id="details-edit-form" method="POST" action="{{ route('portal.work-orders.update', $workOrder) }}"
                  style="display:none;margin-top:1rem;padding-top:1rem;border-top:1px solid #e5e7eb;">
                @csrf @method('PATCH')
                @if($errors->any())
                    <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif

                <div style="display:grid;gap:1rem;">

                    {{-- Services --}}
                    @if($serviceTypes->count())
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.5rem;">Services</label>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.4rem;">
                            @foreach($serviceTypes as $svc)
                            <label style="display:flex;align-items:center;gap:.5rem;font-size:.88rem;font-weight:400;cursor:pointer;">
                                <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                                       {{ in_array($svc->id, old('service_ids', $workOrder->serviceTypes->pluck('id')->toArray())) ? 'checked' : '' }}>
                                {{ $svc->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Description --}}
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Description *</label>
                        <textarea name="description" rows="4" required
                                  style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('description', $workOrder->description) }}</textarea>
                    </div>

                    {{-- Equipment Details --}}
                    <div>
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">
                            Equipment Details
                            <span style="font-weight:400;color:#9ca3af;font-size:.75rem;">— type <kbd style="font-size:.72rem;background:#f3f4f6;border:1px solid #d1d5db;border-radius:3px;padding:.05rem .3rem;font-family:monospace;">..</kbd> to search device catalog</span>
                        </label>
                        <textarea name="equipment_details" id="equip-details-ta" rows="3"
                                  style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('equipment_details', $workOrder->equipment_details) }}</textarea>
                    </div>

                    {{-- Scheduling Preferences --}}
                    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:1.1rem;background:#fafbfc;">
                        <p style="font-weight:700;font-size:.72rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin:0 0 .85rem;">Scheduling Preferences</p>

                        {{-- Availability picker --}}
                        <div style="margin-bottom:.9rem;">
                            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.25rem;">
                                Preferred Days &amp; Times
                                <span style="font-weight:400;color:#9ca3af;font-size:.75rem;">— leave blank if flexible</span>
                            </label>
                            <input type="hidden" name="preferred_availability" id="edit-avail-json"
                                   value="{{ old('preferred_availability', json_encode($workOrder->preferred_availability ?: (object)[])) }}">
                            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
                                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $label)
                                <button type="button" class="edit-avail-day-btn" data-day="{{ $day }}"
                                        style="padding:.3rem .8rem;border-radius:999px;border:2px solid #d1d5db;background:#fff;font-size:.8rem;font-weight:600;color:#6b7280;cursor:pointer;transition:all .12s;line-height:1.3;">
                                    {{ $label }}
                                </button>
                                @endforeach
                            </div>
                            <div id="edit-avail-panels" style="display:none;border:1px solid #bfdbfe;border-radius:6px;overflow:hidden;">
                                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $label)
                                <div class="edit-avail-day-panel" data-day="{{ $day }}"
                                     style="display:none;align-items:center;justify-content:center;gap:.6rem;padding:.5rem .85rem;border-bottom:1px solid #dbeafe;background:#f0f7ff;flex-wrap:wrap;">
                                    <span style="font-size:.78rem;font-weight:700;color:var(--primary);width:30px;flex-shrink:0;text-align:center;">{{ $label }}</span>
                                    @foreach(['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']] as $slot => $slotData)
                                    <button type="button" class="edit-avail-slot-btn" data-day="{{ $day }}" data-slot="{{ $slot }}"
                                            style="padding:.3rem .85rem;border-radius:8px;border:1.5px solid #93c5fd;background:#fff;cursor:pointer;transition:all .12s;text-align:center;min-width:108px;">
                                        <div class="sb-name" style="font-size:.74rem;font-weight:700;color:#3b82f6;line-height:1.3;">{{ $slotData[0] }}</div>
                                        <div class="sb-time" style="font-size:.62rem;color:#93c5fd;line-height:1.2;font-weight:500;">{{ $slotData[1] }}</div>
                                    </button>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                            <div id="edit-update-defaults-box" style="display:none;margin-top:.55rem;padding:.55rem .85rem;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;">
                                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.82rem;color:#78350f;">
                                    <input type="checkbox" name="update_customer_defaults" value="1" checked
                                           style="accent-color:var(--accent);width:14px;height:14px;flex-shrink:0;">
                                    <span>Also update my default availability</span>
                                </label>
                            </div>
                        </div>

                        {{-- Priority pills + Preferred Date --}}
                        <div style="display:flex;flex-wrap:wrap;gap:1.25rem;align-items:flex-start;padding-top:.85rem;border-top:1px solid #e5e7eb;">
                            <div>
                                <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Priority *</label>
                                <input type="hidden" name="urgency" id="cust-urgency-input" value="{{ old('urgency', $workOrder->urgency) }}">
                                <div style="display:flex;gap:.45rem;">
                                    <button type="button" class="cust-urgency-btn" data-value="routine"
                                            style="padding:.4rem .9rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:82px;transition:all .15s;">
                                        <div class="ub-label" style="font-size:.8rem;font-weight:700;color:#374151;line-height:1.2;">Routine</div>
                                        <div class="ub-sub" style="font-size:.66rem;color:#9ca3af;margin-top:.1rem;">No rush</div>
                                    </button>
                                    <button type="button" class="cust-urgency-btn" data-value="urgent"
                                            style="padding:.4rem .9rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:82px;transition:all .15s;">
                                        <div class="ub-label" style="font-size:.8rem;font-weight:700;color:#374151;line-height:1.2;">Urgent</div>
                                        <div class="ub-sub" style="font-size:.66rem;color:#9ca3af;margin-top:.1rem;">Within days</div>
                                    </button>
                                    <button type="button" class="cust-urgency-btn" data-value="emergency"
                                            style="padding:.4rem .9rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:82px;transition:all .15s;">
                                        <div class="ub-label" style="font-size:.8rem;font-weight:700;color:#374151;line-height:1.2;">Emergency</div>
                                        <div class="ub-sub" style="font-size:.66rem;color:#9ca3af;margin-top:.1rem;">ASAP</div>
                                    </button>
                                </div>
                            </div>
                            <div style="flex:1;min-width:170px;">
                                <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Preferred Date</label>
                                <input type="date" name="preferred_date" id="cust-preferred-date"
                                       value="{{ old('preferred_date', $workOrder->preferred_date?->format('Y-m-d')) }}"
                                       style="width:100%;padding:.5rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;background:#fff;">
                            </div>
                        </div>
                    </div>

                    {{-- Site Details --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">On-site Contact Name</label>
                            <input type="text" name="site_contact_name" value="{{ old('site_contact_name', $workOrder->site_contact_name ?: auth()->user()->name) }}"
                                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">On-site Contact Phone</label>
                            <input type="text" name="site_contact_phone" value="{{ old('site_contact_phone', $workOrder->site_contact_phone ?: auth()->user()->phone) }}"
                                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="grid-column:1/-1;">
                        <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.3rem;">Site Address</label>
                        <input type="text" name="site_street" id="customer-site-street"
                               value="{{ old('site_street', $workOrder->site_street ?: $siteAccountAddress) }}"
                               style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                        @if(!$siteAccountAddress && count($sitePriorAddresses) > 0)
                        <div style="margin-top:.45rem;">
                            <span style="font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">Prior Addresses:</span>
                            <div style="margin-top:.3rem;display:flex;flex-direction:column;gap:.2rem;">
                                @foreach($sitePriorAddresses as $addr)
                                <div style="display:flex;align-items:center;justify-content:space-between;padding:.28rem .65rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:5px;gap:.75rem;">
                                    <span style="font-size:.82rem;color:#374151;">{{ $addr }}</span>
                                    <button type="button" data-addr="{{ $addr }}"
                                            onclick="document.getElementById('customer-site-street').value=this.dataset.addr"
                                            title="Use this address"
                                            style="flex-shrink:0;width:22px;height:22px;border-radius:50%;border:1.5px solid var(--accent);background:#fff;color:var(--accent);font-size:1rem;font-weight:700;cursor:pointer;line-height:1;padding:0;display:flex;align-items:center;justify-content:center;">+</button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                </div>

                <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" onclick="toggleEdit()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
            @endif

            </div>{{-- /details-body inner --}}
            </div>{{-- /details-body --}}

            </div>{{-- /inner padding --}}
        </div>

        {{-- Completed invoice details (only shown when there is exactly one invoice) --}}
        @if($workOrder->invoices->whereIn('status', [\App\Models\Invoice::STATUS_ISSUED, \App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED])->count() <= 1)
        @foreach($workOrder->invoices->where('status', \App\Models\Invoice::STATUS_COMPLETED)->values() as $inv)
        @php
            $invNum = 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT);
            $invSub = (float)($inv->subtotal  ?? $inv->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
            $invTax = (float)($inv->tax_amount ?? round($invSub * (float)($inv->tax_rate ?? 0), 2));
            $invTot = (float)($inv->total     ?? round($invSub + $invTax, 2));
            $invCompletedAt = $inv->history
                ->where('field_name', 'status')
                ->where('new_value', 'completed')
                ->first()?->changed_at;
        @endphp
        <div style="background:#fff;padding:2rem;border-radius:8px;border:1px solid #c9d0d8;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">

            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
                <div>
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.3rem;">
                        <h2 style="font-size:.95rem;color:var(--primary);margin:0;">
                            <a href="{{ route('portal.invoices.show', $inv) }}" style="color:var(--primary);text-decoration:none;">{{ $invNum }}</a>
                        </h2>
                        <span style="font-size:.78rem;padding:.2rem .7rem;border-radius:999px;font-weight:700;background:#d1fae5;color:#065f46;border:1.5px solid #6ee7b7;">✓ Paid</span>
                    </div>
                    @if($invCompletedAt)
                    <div style="font-size:.8rem;color:#6b7280;">Completed {{ \Carbon\Carbon::parse($invCompletedAt)->format('F j, Y') }}</div>
                    @endif
                </div>
                <a href="{{ route('portal.invoices.print', $inv) }}" target="_blank"
                   style="flex-shrink:0;display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .9rem;border:1px solid #d1d5db;border-radius:6px;background:#f8f9fa;color:#374151;font-size:.82rem;text-decoration:none;font-weight:500;">
                    🖨 Print Invoice
                </a>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem;font-size:.9rem;margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
                <div><strong>Invoice #:</strong> {{ $invNum }}</div>
                <div><strong>Invoice Date:</strong> {{ $inv->created_at->format('M j, Y') }}</div>
                @if($inv->due_date)
                <div><strong>Due Date:</strong> {{ $inv->due_date->format('M j, Y') }}</div>
                @endif
                @if($inv->payment_terms)
                <div style="grid-column:1/-1;"><strong>Payment Terms:</strong> {{ $inv->payment_terms }}</div>
                @endif
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:.9rem;margin-bottom:1.5rem;">
                <thead>
                    <tr style="background:var(--primary);color:#fff;">
                        <th style="padding:.6rem 1rem;text-align:left;">Description</th>
                        <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Qty</th>
                        <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Unit Price</th>
                        <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inv->lineItems->sortBy('sort_order') as $item)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:.65rem 1rem;">{{ $item->description }}</td>
                        <td style="padding:.65rem 1rem;text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex;justify-content:flex-end;{{ $inv->footer_note ? 'margin-bottom:1.75rem;' : '' }}">
                <div style="width:240px;">
                    <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                        <span>Subtotal</span><span>${{ number_format($invSub, 2) }}</span>
                    </div>
                    @if($invTax > 0)
                    <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                        <span>Tax ({{ number_format((float)($inv->tax_rate ?? 0) * 100, 2) }}%)</span>
                        <span>${{ number_format($invTax, 2) }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;font-size:1.05rem;font-weight:700;padding:.6rem 0;border-top:2px solid #e5e7eb;margin-top:.25rem;color:var(--primary);">
                        <span>Total</span><span>${{ number_format($invTot, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($inv->footer_note)
            <div style="padding:.85rem 1rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.87rem;color:#555;margin-bottom:1.25rem;">
                {{ $inv->footer_note }}
            </div>
            @endif

            {{-- Visits covered --}}
            @php
                $coveredIds    = $inv->covered_visit_ids ?? [];
                $coveredVisits = $coveredIds
                    ? $workOrder->visits->whereIn('id', $coveredIds)->sortBy('scheduled_at')->values()
                    : collect();
            @endphp
            @if($coveredVisits->isNotEmpty())
            <div style="padding-top:1.25rem;border-top:1px solid #e5e7eb;">
                <div style="font-weight:700;color:var(--primary);margin-bottom:.5rem;font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;">Visits Covered</div>
                <div style="display:flex;flex-direction:column;gap:.4rem;">
                    @foreach($coveredVisits as $cv)
                    @php
                        $cvSig      = $cv->signature;
                        $cvEntries  = $cv->timeEntries ?? collect();
                        $cvArrival  = $cvEntries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                        $cvDepart   = $cvEntries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                        $cvSigPath  = $cvSig ? storage_path('app/signatures/work-orders/'.$cvSig->signature_path) : null;
                        $cvSigExists = $cvSigPath && file_exists($cvSigPath);
                    @endphp
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:.6rem .85rem;font-size:.86rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            <span style="color:#1e293b;font-weight:600;">{{ $cv->scheduled_at->format('l, M j, Y') }}</span>
                            @if($cvSig)
                            <span style="flex-shrink:0;font-size:.75rem;padding:.12rem .5rem;border-radius:999px;background:#d1fae5;color:#065f46;font-weight:700;border:1px solid #6ee7b7;">✓ Signed</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:.3rem;">
                            <div style="color:#6b7280;font-size:.8rem;">
                                Scheduled {{ $cv->scheduled_at->format('g:i A') }}
                                @if($cvArrival)
                                · Arrived: <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvArrival)->format('g:i A') }}</span>
                                @if($cvDepart) · Departed: <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvDepart)->format('g:i A') }}</span>@endif
                                @endif
                            </div>
                            @if($cvSigExists)
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($cvSigPath)) }}"
                                 alt="Visit signature"
                                 style="height:30px;max-width:100px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:4px;flex-shrink:0;">
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endforeach
        @endif {{-- single-invoice guard --}}

        @php $canNote = $workOrder->status !== \App\Models\WorkOrder::STATUS_CANCELED; @endphp
        @if($workOrder->notes->count() || $canNote)
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Notes</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Messages between you and our team</div>
                </div>
            </div>
            <div style="padding:1.5rem;">

            @foreach($workOrder->notes->sortBy('created_at') as $note)
            @php
                $isMine      = $note->user_id === auth()->id();
                $noteAuthor  = $note->author;
                $noteInitial = strtoupper(substr($noteAuthor?->name ?? 'D', 0, 1));
                $noteHasPhoto = $noteAuthor?->profile_photo
                             && file_exists(storage_path('app/profile-photos/' . $noteAuthor->profile_photo));
            @endphp
            @if($isMine)
            {{-- Customer-written note --}}
            <div style="display:flex;justify-content:flex-end;align-items:flex-end;gap:.5rem;margin-bottom:.75rem;">
                <div style="max-width:85%;padding:.75rem 1rem;background:#f0fdf4;border:1px solid #86efac;border-radius:8px 8px 2px 8px;">
                    <div style="font-size:.8rem;color:#15803d;font-weight:600;margin-bottom:.25rem;">You</div>
                    <div style="font-size:.9rem;color:#1e293b;white-space:pre-wrap;">{{ $note->body }}</div>
                    <div style="font-size:.73rem;color:#6b7280;margin-top:.35rem;text-align:right;">{{ $note->created_at->format('M j, Y g:i A') }}</div>
                </div>
                @if($noteHasPhoto)
                <img src="{{ route('users.photo', $noteAuthor) }}" alt="You"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                @else
                <div style="width:28px;height:28px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:.68rem;font-weight:700;color:#fff;line-height:1;">{{ $noteInitial }}</span>
                </div>
                @endif
            </div>
            @else
            {{-- Admin/Employee-written note --}}
            <div style="display:flex;align-items:flex-end;gap:.5rem;margin-bottom:.75rem;">
                @if($noteHasPhoto)
                <img src="{{ route('users.photo', $noteAuthor) }}" alt="DataTel"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                @else
                <div style="width:28px;height:28px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-size:.68rem;font-weight:700;color:#fff;line-height:1;">D</span>
                </div>
                @endif
                <div style="max-width:85%;padding:.75rem 1rem;background:#f0f6ff;border-left:3px solid var(--accent);border-radius:0 8px 8px 0;">
                    <div style="font-size:.8rem;color:var(--accent);font-weight:600;margin-bottom:.25rem;">DataTel</div>
                    <div style="font-size:.9rem;color:#1e293b;white-space:pre-wrap;">{{ $note->body }}</div>
                    <div style="font-size:.73rem;color:#6b7280;margin-top:.35rem;">{{ $note->created_at->format('M j, Y g:i A') }}</div>
                </div>
            </div>
            @endif
            @endforeach

            @if(!$workOrder->notes->count())
            <p style="font-size:.85rem;color:#aaa;margin-bottom:1rem;">No notes yet.</p>
            @endif

            @if($canNote)
            <form method="POST" action="{{ route('portal.work-orders.notes.add', $workOrder) }}"
                  style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid #f0f0f0;">
                @csrf
                @error('body')<div class="alert alert-error" style="margin-bottom:.6rem;font-size:.83rem;">{{ $message }}</div>@enderror
                <textarea name="body" rows="3" maxlength="2000"
                          placeholder="Add a note — ask a question, provide an update, or leave instructions for the team…"
                          style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;resize:vertical;box-sizing:border-box;margin-bottom:.6rem;">{{ old('body') }}</textarea>
                <div style="display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary" style="font-size:.88rem;">Send Note</button>
                </div>
            </form>
            @endif
            </div>{{-- /inner padding --}}
        </div>
        @endif

        @php
            $canCancel = $workOrder->status === \App\Models\WorkOrder::STATUS_SCHEDULED
                      && $workOrder->cancel_reason === null;
        @endphp
        @if($canCancel)
        <div style="background:#fff;border:1px solid #fca5a5;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1rem;">
            <h2 style="font-size:.95rem;color:#991b1b;margin:0 0 .4rem;">Cancel Work Order</h2>
            <p style="font-size:.85rem;color:#6b7280;margin-bottom:1rem;">Need to cancel this request? Let us know why and any instructions for next steps. If no instructions are provided, we will follow up with you on the next business day.</p>
            <button type="button" onclick="openCancelModal()"
                    style="padding:.45rem 1.1rem;border:1px solid #fca5a5;border-radius:6px;background:#fff;color:#dc2626;font-size:.88rem;font-weight:600;cursor:pointer;">
                Cancel This Work Order
            </button>
        </div>
        @endif
    </div>

    <div>
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Status Lifecycle</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Progress through your work order</div>
                </div>
            </div>
            <div style="padding:1.25rem;">
            @php
                $steps = [
                    'new'                => 'Submitted',
                    'triaged'            => 'Reviewed',
                    'scheduled'          => 'Scheduled',
                    'services_performed' => 'Services Performed',
                    'invoice_prepared'   => 'Invoice In Progress',
                    'billed'             => 'Invoice Ready',
                    'completed'          => 'Completed',
                ];
                $statuses = array_keys($steps);
                $current  = array_search($workOrder->status, $statuses);
            @endphp
            @foreach($steps as $key => $label)
            @php $idx = array_search($key, $statuses); @endphp
            <div style="display:flex;align-items:center;gap:.75rem;padding:.4rem 0;">
                <div style="width:22px;height:22px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;
                    background:{{ $idx < $current ? '#d1fae5' : ($idx === $current ? 'var(--accent)' : '#e5e7eb') }};
                    color:{{ $idx < $current ? '#065f46' : ($idx === $current ? '#fff' : '#9ca3af') }};">
                    {{ $idx < $current ? '✓' : ($idx === $current ? '●' : '') }}
                </div>
                <div style="display:flex;flex-direction:column;gap:.2rem;">
                    <span style="font-size:.85rem;color:{{ $idx <= $current ? '#333' : '#aaa' }};font-weight:{{ $idx === $current ? '700' : '400' }};">{{ $label }}</span>
                    @if($key === 'billed' && $workOrder->status === 'billed' && $workOrder->invoice)
                        @if($workOrder->invoice->status === 'payment_received')
                        <span style="font-size:.7rem;font-weight:600;color:#065f46;background:#d1fae5;border:1px solid #6ee7b7;padding:.1rem .45rem;border-radius:3px;width:fit-content;">✓ Payment Submitted – Verifying</span>
                        @else
                        <span style="font-size:.7rem;font-weight:600;color:#1e40af;background:#dbeafe;border:1px solid #93c5fd;padding:.1rem .45rem;border-radius:3px;width:fit-content;">Action Required</span>
                        @endif
                    @endif
                    @if($key === 'scheduled' && $workOrder->confirmation_status)
                        @if($workOrder->confirmation_status === 'pending')
                        <span style="font-size:.7rem;font-weight:600;color:#92400e;background:#fef3c7;border:1px solid #fcd34d;padding:.1rem .45rem;border-radius:3px;width:fit-content;">⏳ Awaiting Your Confirmation</span>
                        @elseif($workOrder->confirmation_status === 'confirmed')
                        <span style="font-size:.7rem;font-weight:600;color:#065f46;background:#d1fae5;border:1px solid #6ee7b7;padding:.1rem .45rem;border-radius:3px;width:fit-content;">✓ Visit Confirmed</span>
                        @elseif($workOrder->confirmation_status === 'declined')
                        <span style="font-size:.7rem;font-weight:600;color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;padding:.1rem .45rem;border-radius:3px;width:fit-content;">Reschedule Requested</span>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach

            </div>{{-- /inner padding --}}
        </div>

        {{-- Invoices list --}}
        @php
            $sidebarInvoices = $workOrder->invoices->whereIn('status', [
                \App\Models\Invoice::STATUS_ISSUED,
                \App\Models\Invoice::STATUS_PAYMENT_RECEIVED,
                \App\Models\Invoice::STATUS_COMPLETED,
            ])->sortBy('id')->values();
        @endphp
        @if($sidebarInvoices->isNotEmpty())
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-top:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">{{ Str::plural('Invoice', $sidebarInvoices->count()) }}</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing · Payment status</div>
                </div>
            </div>
            <div style="padding:1.25rem;">
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                @foreach($sidebarInvoices as $sideInv)
                @php
                    $sideNum   = 'INV-' . str_pad($sideInv->id, 4, '0', STR_PAD_LEFT);
                    $sideBg    = match($sideInv->status) {
                        'issued'           => '#dbeafe',
                        'payment_received' => '#fef3c7',
                        'completed'        => '#d1fae5',
                        default            => '#f3f4f6',
                    };
                    $sideColor = match($sideInv->status) {
                        'issued'           => '#1e40af',
                        'payment_received' => '#92400e',
                        'completed'        => '#065f46',
                        default            => '#6b7280',
                    };
                    $sideLabel = match($sideInv->status) {
                        'issued'           => 'Invoice Ready',
                        'payment_received' => 'Payment Submitted',
                        'completed'        => 'Paid',
                        default            => 'In Progress',
                    };
                @endphp
                <div style="display:flex;align-items:center;gap:.4rem;padding:.45rem .65rem;border-radius:6px;border:1px solid #d1d5db;background:#f9fafb;">
                    <a href="{{ route('portal.invoices.show', $sideInv) }}"
                       style="font-size:.87rem;font-weight:700;color:var(--primary);text-decoration:none;flex:1;min-width:0;">
                        {{ $sideNum }}
                    </a>
                    <span style="font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;background:{{ $sideBg }};color:{{ $sideColor }};border:1px solid {{ $sideColor }};white-space:nowrap;">{{ $sideLabel }}</span>
                    <button type="button"
                            onclick="openInvPreview('inv-preview-{{ $sideInv->id }}')"
                            title="Preview invoice"
                            style="flex-shrink:0;width:26px;height:26px;border:1px solid #d1d5db;border-radius:5px;background:#fff;color:#6b7280;font-size:.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1;padding:0;"
                            onmouseover="this.style.background='#f0f7ff';this.style.color='var(--accent)'" onmouseout="this.style.background='#fff';this.style.color='#6b7280'">
                        👁
                    </button>
                </div>
                @endforeach
            </div>
            </div>{{-- /inner padding --}}
        </div>
        @endif

        {{-- Completion Signature --}}
        @if($workOrder->completionSignature)
        @php $sig = $workOrder->completionSignature; @endphp
        @php $sigPath = storage_path('app/signatures/work-orders/' . $sig->signature_path); @endphp
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-top:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Service Completion Signature</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Collected on-site at completion</div>
                </div>
            </div>
            <div style="padding:1.25rem;">
            @if(file_exists($sigPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}"
                 alt="Your signature"
                 style="width:100%;border:1px solid #e5e7eb;border-radius:6px;background:#fafafa;display:block;margin-bottom:.75rem;">
            @endif
            <div style="font-size:.82rem;color:#374151;font-weight:600;">{{ $sig->signer_name }}</div>
            <div style="font-size:.78rem;color:#888;margin-top:.2rem;">
                Signed {{ \Carbon\Carbon::parse($sig->signed_at)->format('M j, Y \a\t g:i A') }}
            </div>
            </div>{{-- /inner padding --}}
        </div>
        @endif

    </div>

</div>

{{-- ── Attachment modal ── --}}
@php $photoCount = $photos->count(); $docCount = $docs->count(); @endphp
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

            {{-- Slot limits --}}
            <div style="display:flex;gap:1rem;margin-bottom:1rem;font-size:.78rem;">
                <span style="color:{{ $photoCount >= 3 ? '#dc2626' : '#6b7280' }};">
                    Photos: <strong>{{ $photoCount }}/3</strong>{{ $photoCount >= 3 ? ' — limit reached' : '' }}
                </span>
                <span style="color:{{ $docCount >= 3 ? '#dc2626' : '#6b7280' }};">
                    Documents: <strong>{{ $docCount }}/3</strong>{{ $docCount >= 3 ? ' — limit reached' : '' }}
                </span>
            </div>

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
                    @if($canEdit)
                    <form method="POST" action="{{ route('portal.work-orders.attachments.remove', [$workOrder, $a]) }}"
                          onsubmit="return confirm('Remove this photo?')" style="position:absolute;top:5px;right:5px;margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="width:24px;height:24px;border-radius:50%;background:rgba(220,38,38,.75);border:none;color:#fff;font-size:.8rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">✕</button>
                    </form>
                    @endif
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
                        @if($canEdit)
                        <form method="POST" action="{{ route('portal.work-orders.attachments.remove', [$workOrder, $a]) }}"
                              onsubmit="return confirm('Remove this document?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    style="padding:.25rem .6rem;border:1px solid #fca5a5;border-radius:4px;background:#fff;color:#dc2626;font-size:.76rem;cursor:pointer;white-space:nowrap;">Remove</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @if(!$photos->count() && !$docs->count())
            <div style="text-align:center;padding:2rem 1rem 1.25rem;color:#9ca3af;">
                <div style="font-size:2.5rem;margin-bottom:.5rem;">📂</div>
                <p style="font-size:.9rem;margin:0;">No attachments yet.@if($canEdit) Upload files below.@endif</p>
            </div>
            @endif

            {{-- Upload section — only available while editing is permitted --}}
            @if($canEdit)
            <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;margin-top:.25rem;">
                <p style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .85rem;">Upload Files</p>

                <div id="attach-drop-zone"
                     onclick="document.getElementById('attach-file-input').click()"
                     ondragover="attachDragOver(event)" ondragleave="attachDragLeave(event)" ondrop="attachDrop(event)"
                     style="border:2px dashed #d1d5db;border-radius:8px;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .2s,background .2s;background:#fafafa;margin-bottom:.75rem;user-select:none;">
                    <div style="font-size:1.8rem;margin-bottom:.35rem;pointer-events:none;">📁</div>
                    <div style="font-size:.88rem;color:#6b7280;font-weight:500;pointer-events:none;">Drop files here or <span style="color:var(--accent);text-decoration:underline;">click to browse</span></div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.3rem;pointer-events:none;">Photos: JPG, PNG, GIF, WebP · Docs: PDF, Word, Excel, TXT</div>
                </div>

                <input type="file" id="attach-file-input" multiple
                       accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.txt"
                       style="display:none;" onchange="attachFilesSelected(this.files)">

                <div id="attach-file-list" style="display:none;margin-bottom:.75rem;">
                    <p style="font-size:.72rem;font-weight:700;color:#555;margin:0 0 .4rem;">Selected files:</p>
                    <div id="attach-file-list-items" style="display:flex;flex-direction:column;gap:.3rem;max-height:140px;overflow-y:auto;"></div>
                </div>

                <div id="attach-prog-wrap" style="display:none;margin-bottom:.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem;">
                        <span style="font-size:.78rem;color:#6b7280;" id="attach-prog-label">Uploading…</span>
                        <span style="font-size:.78rem;font-weight:700;color:var(--accent);" id="attach-prog-pct">0%</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;">
                        <div id="attach-prog-bar" style="height:100%;width:0%;background:var(--accent);border-radius:999px;transition:width .1s linear;"></div>
                    </div>
                </div>

                <div id="attach-status-msg" style="display:none;font-size:.82rem;padding:.5rem .75rem;border-radius:5px;margin-bottom:.75rem;"></div>

                <div style="display:flex;align-items:center;gap:.75rem;">
                    <button type="button" id="attach-upload-btn" onclick="doAttachUpload()"
                            style="padding:.45rem 1.2rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:600;cursor:pointer;transition:opacity .15s;opacity:.45;"
                            disabled>
                        Upload
                    </button>
                    <span id="attach-file-count" style="font-size:.8rem;color:#9ca3af;"></span>
                </div>

                <form id="attach-upload-form" method="POST" action="{{ route('portal.work-orders.attachments.add', $workOrder) }}" enctype="multipart/form-data" style="display:none;">
                    @csrf
                </form>
            </div>
            @endif

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

{{-- Cancel modal --}}
<div id="cancel-modal" onclick="if(event.target===this)closeCancelModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="font-size:1rem;color:#991b1b;margin-top:0;margin-bottom:.5rem;">Cancel Work Order</h3>
        <p style="font-size:.88rem;color:#666;margin-bottom:1.25rem;">Please let us know why you are cancelling and any instructions for next steps. If no instructions are provided, a team member will follow up with you on the next business day.</p>
        <form method="POST" action="{{ route('portal.work-orders.cancel', $workOrder) }}">
            @csrf
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">
                    Reason &amp; Next Steps <span style="font-weight:400;color:#999;">(optional — defaults to next-business-day follow-up)</span>
                </label>
                <textarea name="cancel_reason" rows="5" maxlength="2000"
                          placeholder="e.g. We've decided to handle this in-house. Please close the ticket and refund any deposit."
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeCancelModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Go Back
                </button>
                <button type="submit"
                        style="padding:.45rem 1rem;border:none;border-radius:6px;background:#dc2626;color:#fff;font-size:.88rem;font-weight:600;cursor:pointer;">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Decline modal --}}
<div id="decline-modal" onclick="if(event.target===this)closeDeclineModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="font-size:1rem;color:var(--primary);margin-top:0;margin-bottom:.5rem;">Request a Reschedule</h3>
        <p style="font-size:.88rem;color:#666;margin-bottom:1.25rem;">Let us know why this time doesn't work and we'll reach out to find a better time.</p>
        <form id="decline-form" method="POST" action="#">
            @csrf
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">
                    Reason <span style="font-weight:400;color:#999;">(optional)</span>
                </label>
                <textarea name="decline_reason" rows="4" maxlength="1000"
                          placeholder="e.g. I won't be available that day, can we try next week?"
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeDeclineModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Go Back
                </button>
                <button type="submit"
                        style="padding:.45rem 1rem;border:none;border-radius:6px;background:#dc2626;color:#fff;font-size:.88rem;font-weight:600;cursor:pointer;">
                    Submit Reschedule Request
                </button>
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
function expandDetails() {
    const body    = document.getElementById('details-body');
    const summary = document.getElementById('details-collapsed-summary');
    const chevron = document.getElementById('details-chevron');
    if (!body || body.dataset.collapsed !== '1') return;
    body.style.gridTemplateRows = '1fr';
    body.style.opacity          = '1';
    body.dataset.collapsed      = '0';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (summary) summary.style.display  = 'none';
}

function toggleDetails() {
    const body    = document.getElementById('details-body');
    const summary = document.getElementById('details-collapsed-summary');
    const chevron = document.getElementById('details-chevron');
    if (!body) return;
    const collapsed = body.dataset.collapsed === '1';
    if (collapsed) {
        body.style.gridTemplateRows = '1fr';
        body.style.opacity          = '1';
        body.dataset.collapsed      = '0';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        if (summary) summary.style.display  = 'none';
    } else {
        body.style.gridTemplateRows = '0fr';
        body.style.opacity          = '0';
        body.dataset.collapsed      = '1';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (summary) summary.style.display  = 'block';
    }
}

function setEditBtnActive(active) {
    const btn = document.getElementById('edit-toggle-btn');
    if (!btn) return;
    btn.style.background  = active ? 'var(--accent)' : '#f9fafb';
    btn.style.color       = active ? '#fff'          : '#9ca3af';
    btn.style.borderColor = active ? 'var(--accent)' : '#d1d5db';
}

function toggleEdit() {
    const display = document.getElementById('details-display');
    const form    = document.getElementById('details-edit-form');
    const editing = form.style.display === 'none';
    if (editing) expandDetails();
    display.style.display = editing ? 'none' : '';
    form.style.display    = editing ? '' : 'none';
    setEditBtnActive(editing);
}

@if($errors->any())
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

// ── Urgency pills ─────────────────────────────────────────────
(function () {
    const COLORS = {
        routine:   { bg:'#1A3C5E', border:'#1A3C5E', lc:'#fff', sc:'rgba(255,255,255,.7)' },
        urgent:    { bg:'#b45309', border:'#b45309', lc:'#fff', sc:'rgba(255,255,255,.7)' },
        emergency: { bg:'#b91c1c', border:'#b91c1c', lc:'#fff', sc:'rgba(255,255,255,.7)' },
    };
    const input = document.getElementById('cust-urgency-input');
    if (!input) return;

    function applyUrgency(val) {
        input.value = val;
        document.querySelectorAll('.cust-urgency-btn').forEach(btn => {
            const active = btn.dataset.value === val;
            const c = COLORS[btn.dataset.value] || {};
            btn.style.background  = active ? c.bg     : '#fff';
            btn.style.borderColor = active ? c.border : '#d1d5db';
            btn.querySelector('.ub-label').style.color = active ? c.lc : '#374151';
            btn.querySelector('.ub-sub').style.color   = active ? c.sc : '#9ca3af';
        });
    }

    document.querySelectorAll('.cust-urgency-btn').forEach(btn => {
        btn.addEventListener('click', () => applyUrgency(btn.dataset.value));
    });

    applyUrgency(input.value || 'routine');
})();

// ── Edit-form availability picker ─────────────────────────────────────────
(function () {
    const jsonInput    = document.getElementById('edit-avail-json');
    if (!jsonInput) return;

    const defaultAvail = @json(auth()->user()->preferred_availability ?? (object)[]);
    const state        = {};
    try {
        const initial = JSON.parse(jsonInput.value || '{}');
        Object.entries(initial).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    } catch (e) {}

    function checkDefaultsDiff() {
        const box  = document.getElementById('edit-update-defaults-box');
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

    function syncJson() {
        const out = {};
        Object.entries(state).forEach(([day, slots]) => { if (slots.size) out[day] = [...slots]; });
        jsonInput.value = JSON.stringify(out);
    }

    function renderDayBtn(btn) {
        const active = !!state[btn.dataset.day];
        btn.style.background  = active ? 'var(--primary)' : '#fff';
        btn.style.color       = active ? '#fff' : '#555';
        btn.style.borderColor = active ? 'var(--primary)' : '#cbd5e1';
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
        const panels = document.querySelectorAll('.edit-avail-day-panel');
        const container = document.getElementById('edit-avail-panels');
        let anyVisible = false;

        document.querySelectorAll('.edit-avail-day-btn').forEach(renderDayBtn);

        panels.forEach(panel => {
            const show = !!state[panel.dataset.day];
            panel.style.display = show ? 'flex' : 'none';
            if (show) anyVisible = true;
        });

        let lastVisible = null;
        panels.forEach(p => { if (p.style.display !== 'none') lastVisible = p; });
        panels.forEach(p => {
            p.style.borderBottom = (p === lastVisible) ? 'none' : '1px solid #dbeafe';
        });

        container.style.display = anyVisible ? '' : 'none';
        document.querySelectorAll('.edit-avail-slot-btn').forEach(renderSlotBtn);
        syncJson();
        checkDefaultsDiff();
    }

    document.querySelectorAll('.edit-avail-day-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            if (state[day]) delete state[day]; else state[day] = new Set();
            applyState();
        });
    });

    document.querySelectorAll('.edit-avail-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const { day, slot } = btn.dataset;
            if (!state[day]) state[day] = new Set();
            if (state[day].has(slot)) state[day].delete(slot); else state[day].add(slot);
            renderSlotBtn(btn);
            syncJson();
            checkDefaultsDiff();
        });
    });

    applyState();
})();

@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('details-edit-form');
    if (form) {
        document.getElementById('details-display').style.display = 'none';
        form.style.display = '';
        const btn = document.getElementById('edit-toggle-btn');
        if (btn) btn.textContent = 'Cancel';
    }
});
@endif

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

function openCancelModal() {
    document.getElementById('cancel-modal').style.display = 'flex';
    document.addEventListener('keydown', cancelKeyHandler);
}
function closeCancelModal() {
    document.getElementById('cancel-modal').style.display = 'none';
    document.removeEventListener('keydown', cancelKeyHandler);
}
function cancelKeyHandler(e) { if (e.key === 'Escape') closeCancelModal(); }

function openDeclineModal(visitId) {
    const base = '{{ route("portal.work-orders.visits.decline", [$workOrder, "__VID__"]) }}';
    document.getElementById('decline-form').action = base.replace('__VID__', visitId);
    document.getElementById('decline-modal').style.display = 'flex';
    document.addEventListener('keydown', declineKeyHandler);
}
function closeDeclineModal() {
    document.getElementById('decline-modal').style.display = 'none';
    document.removeEventListener('keydown', declineKeyHandler);
}
function declineKeyHandler(e) { if (e.key === 'Escape') closeDeclineModal(); }

function openSigLightbox(dataUrl) {
    const lb  = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    img.src = dataUrl;
    img.style.background    = '#fff';
    img.style.padding       = '1.5rem';
    img.style.maxWidth      = 'min(900px, 90vw)';
    img.style.maxHeight     = 'min(600px, 80vh)';
    img.style.width         = 'min(900px, 90vw)';
    img.style.objectFit     = 'contain';
    document.getElementById('lightbox-name').textContent = 'Customer Signature';
    document.getElementById('lightbox-download').style.display = 'none';
    lb.style.display = 'flex';
    document.addEventListener('keydown', lbKeyHandler);
}

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
    const img = document.getElementById('lightbox-img');
    img.src = '';
    img.style.background = '';
    img.style.padding    = '';
    img.style.maxWidth   = '';
    img.style.maxHeight  = '';
    img.style.width      = '';
    img.style.objectFit  = '';
    document.getElementById('lightbox-download').style.display = '';
    document.removeEventListener('keydown', lbKeyHandler);
}
function lbKeyHandler(e) { if (e.key === 'Escape') closeLightbox(); }

// ── Attachments modal ─────────────────────────────────────────
var _photoSlots = {{ max(0, 3 - $photoCount) }};
var _docSlots   = {{ max(0, 3 - $docCount) }};
let attachPendingFiles = [];

function openAttachModal() {
    document.getElementById('attach-modal').style.display = 'flex';
    document.addEventListener('keydown', attachModalKeyHandler);
}
function closeAttachModal() {
    document.getElementById('attach-modal').style.display = 'none';
    document.removeEventListener('keydown', attachModalKeyHandler);
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
    if (z) { z.style.borderColor = 'var(--accent)'; z.style.background = '#f0f6ff'; }
}
function attachDragLeave() {
    const z = document.getElementById('attach-drop-zone');
    if (z) { z.style.borderColor = '#d1d5db'; z.style.background = '#fafafa'; }
}
function attachDrop(e) {
    e.preventDefault();
    attachDragLeave();
    attachFilesSelected(e.dataTransfer.files);
}
function attachFilesSelected(fileList) {
    const imageTypes = ['image/jpeg','image/png','image/gif','image/webp'];
    const skipped = [];
    Array.from(fileList).forEach(f => {
        const isPhoto = imageTypes.includes(f.type);
        const pendingPhotos = attachPendingFiles.filter(p => p.type === 'photo').length;
        const pendingDocs   = attachPendingFiles.filter(p => p.type === 'doc').length;
        if (isPhoto && (_photoSlots - pendingPhotos) <= 0) {
            skipped.push(f.name + ' (photo limit reached)');
        } else if (!isPhoto && (_docSlots - pendingDocs) <= 0) {
            skipped.push(f.name + ' (document limit reached)');
        } else {
            attachPendingFiles.push({ file: f, type: isPhoto ? 'photo' : 'doc' });
        }
    });
    if (skipped.length) {
        showAttachMsg('error', 'Skipped: ' + skipped.join(', '));
    }
    renderAttachFileList();
}
function renderAttachFileList() {
    const list  = document.getElementById('attach-file-list');
    const items = document.getElementById('attach-file-list-items');
    const btn   = document.getElementById('attach-upload-btn');
    const count = document.getElementById('attach-file-count');
    if (!attachPendingFiles.length) {
        if (list)  list.style.display = 'none';
        if (btn)   { btn.disabled = true; btn.style.opacity = '.45'; }
        if (count) count.textContent = '';
        return;
    }
    if (list)  list.style.display = '';
    if (btn)   { btn.disabled = false; btn.style.opacity = '1'; }
    const n = attachPendingFiles.length;
    if (count) count.textContent = n + ' file' + (n > 1 ? 's' : '') + ' ready to upload';
    if (items) items.innerHTML = attachPendingFiles.map((pf, i) => `
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
    const fd    = new FormData(form);
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
            bar.style.width = p + '%';
            pct.textContent = p + '%';
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
    if (!el) return;
    el.style.display    = '';
    el.style.background = type === 'error' ? '#fef2f2' : '#f0fdf4';
    el.style.color      = type === 'error' ? '#dc2626' : '#16a34a';
    el.style.border     = '1px solid ' + (type === 'error' ? '#fca5a5' : '#86efac');
    el.textContent      = text;
}
</script>

{{-- ── Invoice Preview Modals ── --}}
@foreach($workOrder->invoices->whereIn('status', [\App\Models\Invoice::STATUS_ISSUED, \App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED])->values() as $inv)
@php
    $mNum  = 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT);
    $mSub  = (float)($inv->subtotal  ?? $inv->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
    $mTax  = (float)($inv->tax_amount ?? round($mSub * (float)($inv->tax_rate ?? 0), 2));
    $mTot  = (float)($inv->total     ?? round($mSub + $mTax, 2));
    $mIsPaid = in_array($inv->status, [\App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED]);
    $mBg    = match($inv->status) {
        'issued'           => '#dbeafe', 'payment_received' => '#fef3c7',
        'completed'        => '#d1fae5', default            => '#f3f4f6',
    };
    $mColor = match($inv->status) {
        'issued'           => '#1e40af', 'payment_received' => '#92400e',
        'completed'        => '#065f46', default            => '#6b7280',
    };
    $mLabel = match($inv->status) {
        'issued'           => 'Invoice Ready', 'payment_received' => 'Payment Submitted',
        'completed'        => 'Paid',          default            => 'In Progress',
    };
    $mCompletedAt = $inv->history
        ->where('field_name', 'status')->where('new_value', 'completed')
        ->first()?->changed_at;
    $mCoveredIds    = $inv->covered_visit_ids ?? [];
    $mCoveredVisits = $mCoveredIds
        ? $workOrder->visits->whereIn('id', $mCoveredIds)->sortBy('scheduled_at')->values()
        : collect();
@endphp
<div id="inv-preview-{{ $inv->id }}"
     onclick="if(event.target===this)closeInvPreview('inv-preview-{{ $inv->id }}')"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9100;align-items:flex-start;justify-content:center;padding:2rem 1rem;overflow-y:auto;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.2);width:100%;max-width:660px;margin:auto;">

        {{-- Modal header --}}
        <div style="background:var(--primary);border-radius:10px 10px 0 0;padding:.85rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
            <img src="{{ route('site.logo') }}" alt="DataTel"
                 style="height:38px;max-width:130px;object-fit:contain;object-position:left;filter:brightness(0) invert(1);flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                <a href="{{ route('portal.invoices.print', $inv) }}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .75rem;border:1px solid rgba(255,255,255,.35);border-radius:6px;background:rgba(255,255,255,.12);color:#fff;font-size:.8rem;text-decoration:none;font-weight:500;">
                    🖨 Print
                </a>
                <button type="button" onclick="closeInvPreview('inv-preview-{{ $inv->id }}')"
                        style="width:30px;height:30px;border:none;background:rgba(255,255,255,.12);border-radius:6px;font-size:1.2rem;color:rgba(255,255,255,.8);cursor:pointer;line-height:1;padding:0;display:flex;align-items:center;justify-content:center;">×</button>
            </div>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.5rem;border-bottom:1px solid #e5e7eb;gap:.5rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;">
                <span style="font-size:1.05rem;font-weight:800;color:var(--primary);">{{ $mNum }}</span>
                <span style="font-size:.8rem;padding:.2rem .65rem;border-radius:999px;font-weight:700;background:{{ $mBg }};color:{{ $mColor }};border:1.5px solid {{ $mColor }};">{{ $mLabel }}</span>
                @if($mCompletedAt)
                <span style="font-size:.78rem;color:#6b7280;">Completed {{ \Carbon\Carbon::parse($mCompletedAt)->format('M j, Y') }}</span>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div style="padding:1.5rem;">

            {{-- Meta --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.4rem 2rem;font-size:.88rem;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
                <div><strong>Invoice #:</strong> {{ $mNum }}</div>
                <div><strong>Invoice Date:</strong> {{ $inv->created_at->format('M j, Y') }}</div>
                @if($inv->due_date)
                <div>
                    <strong>Due Date:</strong>
                    @php $mDue = \Carbon\Carbon::parse($inv->due_date); @endphp
                    <span style="{{ $mDue->isPast() && !$mIsPaid ? 'color:#dc2626;font-weight:600;' : '' }}">
                        {{ $mDue->format('M j, Y') }}{{ $mDue->isPast() && !$mIsPaid ? ' (overdue)' : '' }}
                    </span>
                </div>
                @endif
                @if($inv->payment_terms)
                <div style="grid-column:1/-1;"><strong>Payment Terms:</strong> {{ $inv->payment_terms }}</div>
                @endif
            </div>

            {{-- Line items --}}
            <table style="width:100%;border-collapse:collapse;font-size:.88rem;margin-bottom:1.25rem;">
                <thead>
                    <tr style="background:var(--primary);color:#fff;">
                        <th style="padding:.55rem .9rem;text-align:left;">Description</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;">Qty</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;">Unit Price</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inv->lineItems->sortBy('sort_order') as $item)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:.55rem .9rem;">{{ $item->description }}</td>
                        <td style="padding:.55rem .9rem;text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td style="padding:.55rem .9rem;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding:.55rem .9rem;text-align:right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Totals --}}
            <div style="display:flex;justify-content:flex-end;margin-bottom:1.25rem;">
                <div style="width:220px;">
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;padding:.28rem 0;color:#555;">
                        <span>Subtotal</span><span>${{ number_format($mSub, 2) }}</span>
                    </div>
                    @if($mTax > 0)
                    <div style="display:flex;justify-content:space-between;font-size:.88rem;padding:.28rem 0;color:#555;">
                        <span>Tax ({{ number_format((float)($inv->tax_rate ?? 0) * 100, 2) }}%)</span>
                        <span>${{ number_format($mTax, 2) }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:700;padding:.55rem 0;border-top:2px solid #e5e7eb;margin-top:.2rem;color:var(--primary);">
                        <span>Total</span><span>${{ number_format($mTot, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer note --}}
            @if($inv->footer_note)
            <div style="padding:.75rem .9rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.85rem;color:#555;margin-bottom:1.25rem;">
                {{ $inv->footer_note }}
            </div>
            @endif

            {{-- Visits covered --}}
            @if($mCoveredVisits->isNotEmpty())
            <div style="margin-bottom:1.25rem;">
                <div style="font-size:.72rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;">Visits Covered</div>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    @foreach($mCoveredVisits as $cv)
                    @php
                        $cvSig      = $cv->signature;
                        $cvEntries  = $cv->timeEntries ?? collect();
                        $cvArrival  = $cvEntries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                        $cvDepart   = $cvEntries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                        $cvSigPath  = $cvSig ? storage_path('app/signatures/work-orders/'.$cvSig->signature_path) : null;
                        $cvSigExists = $cvSigPath && file_exists($cvSigPath);
                        $cvTechs    = $cv->techs->map(fn($t) => $t->user)->filter();
                        $cvTotalMins = ($cvArrival && $cvDepart)
                            ? \Carbon\Carbon::parse($cvArrival)->diffInMinutes(\Carbon\Carbon::parse($cvDepart))
                            : null;
                        $cvDurFmt   = $cvTotalMins !== null
                            ? ($cvTotalMins >= 60
                                ? floor($cvTotalMins/60).'h'.($cvTotalMins%60 ? ' '.($cvTotalMins%60).'m' : '')
                                : $cvTotalMins.'m')
                            : null;
                    @endphp
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:.75rem 1rem;font-size:.84rem;">

                        {{-- Date + signed badge --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;">
                            <span style="color:#1e293b;font-weight:700;font-size:.88rem;">{{ $cv->scheduled_at->format('l, F j, Y') }}</span>
                            @if($cvSig)
                            <span style="flex-shrink:0;font-size:.75rem;padding:.12rem .55rem;border-radius:999px;background:#d1fae5;color:#065f46;font-weight:700;border:1px solid #6ee7b7;">✓ Signed</span>
                            @endif
                        </div>

                        {{-- Address & contact --}}
                        @if($workOrder->site_street || $workOrder->site_contact_name)
                        <div style="display:flex;flex-wrap:wrap;gap:.25rem .85rem;margin-bottom:.45rem;font-size:.8rem;color:#4b5563;">
                            @if($workOrder->site_street)
                            <span>📍 {{ $workOrder->site_street }}</span>
                            @endif
                            @if($workOrder->site_contact_name)
                            <span>👤 {{ $workOrder->site_contact_name }}{{ $workOrder->site_contact_phone ? ' · '.$workOrder->site_contact_phone : '' }}</span>
                            @endif
                        </div>
                        @endif

                        {{-- Times --}}
                        <div style="font-size:.79rem;color:#6b7280;margin-bottom:.55rem;">
                            Scheduled {{ $cv->scheduled_at->format('g:i A') }}
                            @if($cvArrival)
                            · Arrived <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvArrival)->format('g:i A') }}</span>
                            @if($cvDepart) · Departed <span style="color:#374151;font-weight:600;">{{ \Carbon\Carbon::parse($cvDepart)->format('g:i A') }}</span>@endif
                            @if($cvDurFmt) · <span style="color:#059669;font-weight:700;">{{ $cvDurFmt }} on-site</span>@endif
                            @endif
                        </div>

                        {{-- Bottom row: tech avatars + signature --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                @forelse($cvTechs as $tech)
                                @php $techPhoto = $tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)); @endphp
                                <div style="display:flex;align-items:center;gap:.35rem;">
                                    @if($techPhoto)
                                    <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}"
                                         style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1.5px solid #bfdbfe;flex-shrink:0;">
                                    @else
                                    <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);border:1.5px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <span style="font-size:.7rem;font-weight:700;color:#fff;">{{ strtoupper(substr($tech->name,0,1)) }}</span>
                                    </div>
                                    @endif
                                    <span style="font-size:.8rem;color:#374151;font-weight:500;">{{ $tech->name }}</span>
                                </div>
                                @empty
                                <span style="font-size:.78rem;color:#9ca3af;">No tech assigned</span>
                                @endforelse
                            </div>
                            @if($cvSigExists)
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($cvSigPath)) }}"
                                 alt="Customer signature"
                                 style="height:34px;max-width:120px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:4px;padding:2px;flex-shrink:0;">
                            @endif
                        </div>

                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Payment action --}}
            @if($inv->status === \App\Models\Invoice::STATUS_ISSUED)
            <div style="padding-top:1.25rem;border-top:1px solid #e5e7eb;">
                <p style="font-size:.85rem;color:#374151;margin-bottom:.85rem;">
                    Once you have submitted payment using the terms above, click below to notify us.
                </p>
                <form method="POST" action="{{ route('portal.invoices.submit-payment', $inv) }}">
                    @csrf
                    <button type="submit"
                            style="width:100%;padding:.65rem;background:var(--accent);color:#fff;border:none;border-radius:7px;font-size:.92rem;font-weight:700;cursor:pointer;">
                        ✓ I've Submitted My Payment
                    </button>
                </form>
            </div>
            @endif

        </div>{{-- /body --}}
    </div>
</div>
@endforeach

<script>
function openInvPreview(id)  { const el = document.getElementById(id); if(el){ el.style.display='flex'; document.body.style.overflow='hidden'; } }
function closeInvPreview(id) { const el = document.getElementById(id); if(el){ el.style.display='none'; document.body.style.overflow=''; } }
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('[id^="inv-preview-"]').forEach(m=>{ if(m.style.display==='flex') closeInvPreview(m.id); }); });
</script>

{{-- ── Equipment Autocomplete ── --}}
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

    const ta      = document.getElementById('equip-details-ta');
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

@endsection
