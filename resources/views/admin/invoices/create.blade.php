@extends('layouts.admin')
@section('title', 'New Invoice')

@section('content')

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.25rem;">{{ $errors->first() }}</div>
@endif

@if($workOrder)
{{-- ══════════════════════════════════════════
     WORK-ORDER-LINKED FORM  (two-column)
══════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:340px 1fr;gap:1.5rem;align-items:start;max-width:1100px;">

    {{-- ── Left: reference panel ── --}}
    <div>

        {{-- Job details --}}
        <div style="background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <p style="font-size:.7rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">Work Order Reference</p>

            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.85rem;">
                <span style="font-size:.95rem;font-weight:700;color:var(--primary);">{{ $workOrder->woLabel() }}</span>
                <span class="badge badge-{{ $workOrder->status }}" style="font-size:.72rem;">{{ str_replace('_',' ',$workOrder->status) }}</span>
            </div>

            <div style="font-size:.82rem;color:#374151;font-weight:600;margin-bottom:.1rem;">{{ $workOrder->customer->name }}</div>
            @if($workOrder->customer->email)
            <div style="font-size:.78rem;color:#888;margin-bottom:.85rem;">{{ $workOrder->customer->email }}</div>
            @endif

            @if($workOrder->scheduled_at)
            <div style="font-size:.75rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Services Performed</div>
            <div style="font-size:.83rem;color:#374151;margin-bottom:.75rem;">{{ $workOrder->scheduled_at->format('M j, Y \a\t g:i A') }}</div>
            @endif

            @if($workOrder->serviceTypes->count())
            <div style="font-size:.75rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Services</div>
            <div style="display:flex;flex-wrap:wrap;gap:.35rem;margin-bottom:.85rem;">
                @foreach($workOrder->serviceTypes as $svc)
                <span style="background:#eff6ff;color:var(--accent);padding:.2rem .65rem;border-radius:999px;font-size:.75rem;border:1px solid #bfdbfe;">{{ $svc->name }}</span>
                @endforeach
            </div>
            @endif

            @if($workOrder->description)
            <div style="font-size:.75rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem;">Description of Work</div>
            <div style="font-size:.82rem;color:#444;line-height:1.5;white-space:pre-wrap;">{{ $workOrder->description }}</div>
            @endif
        </div>

        {{-- Site details --}}
        @if($workOrder->site_street || $workOrder->site_contact_name || $workOrder->site_contact_phone)
        <div style="background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <p style="font-size:.7rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">Site Details</p>
            @if($workOrder->site_street)
            <div style="font-size:.75rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">📍 Address</div>
            <div style="font-size:.85rem;color:#374151;margin-bottom:.75rem;">{{ $workOrder->site_street }}</div>
            @endif
            @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
            <div style="font-size:.75rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">👤 Site Contact</div>
            @if($workOrder->site_contact_name)
            <div style="font-size:.85rem;color:#374151;">{{ $workOrder->site_contact_name }}</div>
            @endif
            @if($workOrder->site_contact_phone)
            <a href="tel:{{ $workOrder->site_contact_phone }}" style="font-size:.83rem;color:var(--accent);text-decoration:none;">{{ $workOrder->site_contact_phone }}</a>
            @endif
            @endif
        </div>
        @endif

        {{-- Completion signature --}}
        @if($workOrder->completionSignature)
        @php
            $sig     = $workOrder->completionSignature;
            $sigPath = storage_path('app/signatures/work-orders/' . $sig->signature_path);
        @endphp
        <div style="background:#fff;padding:1.25rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <p style="font-size:.7rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem;">✍ Services Performed Signature</p>
            @if(file_exists($sigPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}"
                 alt="Customer signature"
                 style="width:100%;border:1px solid #e5e7eb;border-radius:6px;background:#fafafa;display:block;margin-bottom:.75rem;">
            @endif
            <div style="font-size:.85rem;font-weight:600;color:#374151;">{{ $sig->signer_name }}</div>
            <div style="font-size:.78rem;color:#888;margin-top:.2rem;">
                {{ \Carbon\Carbon::parse($sig->signed_at)->format('M j, Y \a\t g:i A') }}
            </div>
            @if($sig->collectedBy)
            <div style="font-size:.75rem;color:#aaa;margin-top:.15rem;">Collected by {{ $sig->collectedBy->name }}</div>
            @endif
        </div>
        @endif

    </div>

    {{-- ── Right: invoice form ── --}}
    <div style="background:#fff;padding:1.75rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
    <form method="POST" action="{{ route('admin.invoices.store') }}" id="invoice-form">
        @csrf
        <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">

        {{-- Bill To --}}
        @php
            $billCompany = $workOrder->customer->companies->firstWhere('pivot.is_primary', true)
                           ?? $workOrder->customer->companies->first();
        @endphp
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1.1rem 1.25rem;margin-bottom:1.5rem;">
            <div style="font-size:.65rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.6rem;">Bill To</div>
            <div style="display:flex;gap:1.25rem;align-items:flex-start;">

                {{-- Customer column --}}
                <div style="display:flex;align-items:flex-start;gap:.85rem;flex:1;min-width:0;">
                    <div style="width:38px;height:38px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;font-weight:700;">
                        {{ strtoupper(substr($workOrder->customer->name, 0, 1)) }}
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:.92rem;font-weight:700;color:var(--primary);line-height:1.3;">{{ $workOrder->customer->name }}</div>
                        @if($workOrder->customer->title)
                        <div style="font-size:.78rem;color:#6b7280;margin-top:.1rem;">{{ $workOrder->customer->title }}</div>
                        @endif
                        @if($workOrder->customer->phone)
                        <div style="font-size:.8rem;color:#555;margin-top:.2rem;">
                            <a href="tel:{{ $workOrder->customer->phone }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->phone }}</a>
                        </div>
                        @endif
                        @if($workOrder->customer->email)
                        <div style="font-size:.78rem;color:#888;margin-top:.05rem;">
                            <a href="mailto:{{ $workOrder->customer->email }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->email }}</a>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Company column --}}
                @if($billCompany)
                @php
                    $billAddr = collect([
                        $billCompany->address_street,
                        trim(collect([$billCompany->address_city, $billCompany->address_state])->filter()->join(', ')),
                        $billCompany->address_zip,
                    ])->filter()->join(', ');
                @endphp
                <div style="border-left:1px solid #e5e7eb;padding-left:1.1rem;min-width:150px;flex-shrink:0;">
                    <div style="font-size:.65rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.25rem;">Company</div>
                    <div style="font-size:.88rem;font-weight:700;color:var(--primary);line-height:1.3;">{{ $billCompany->name }}</div>
                    @if($billCompany->phone)
                    <div style="font-size:.8rem;color:#555;margin-top:.2rem;">
                        <a href="tel:{{ $billCompany->phone }}" style="color:inherit;text-decoration:none;">{{ $billCompany->phone }}</a>
                    </div>
                    @endif
                    @if($billCompany->email)
                    <div style="font-size:.78rem;color:#888;margin-top:.05rem;">
                        <a href="mailto:{{ $billCompany->email }}" style="color:inherit;text-decoration:none;">{{ $billCompany->email }}</a>
                    </div>
                    @endif
                    @if($billAddr)
                    <div style="font-size:.76rem;color:#999;margin-top:.2rem;line-height:1.4;">{{ $billAddr }}</div>
                    @endif
                </div>
                @endif

            </div>
        </div>

        {{-- Visits covered (only shown when WO has 2+ visits) --}}
        @if($workOrder->visits->count() > 1)
        <div style="margin-bottom:1.5rem;">
            <div style="font-size:.82rem;font-weight:600;color:#444;margin-bottom:.5rem;">
                Visits Covered by This Invoice
                <span style="font-size:.75rem;font-weight:400;color:#888;margin-left:.3rem;">Click to select / deselect</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.45rem;">
                @foreach($workOrder->visits->sortBy('scheduled_at') as $v)
                @php
                    $vSig        = $v->signature;
                    $vSigPath    = $vSig ? storage_path('app/signatures/work-orders/'.$vSig->signature_path) : null;
                    $vSigOk      = $vSigPath && file_exists($vSigPath);
                    $prevInvoice = ($visitInvoiceMap ?? [])[$v->id] ?? null;
                    $prevInvNum  = $prevInvoice ? 'INV-'.str_pad($prevInvoice->id, 4, '0', STR_PAD_LEFT) : null;
                    $isChecked   = !$prevInvoice;
                    $cardBorder  = $prevInvoice ? '#fcd34d' : ($isChecked ? 'var(--accent)' : '#e5e7eb');
                    $cardBg      = $prevInvoice ? '#fffbeb' : ($isChecked ? '#eff6ff' : '#fafafa');
                    $vEntries    = $v->timeEntries;
                    $vArrival    = $vEntries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                    $vDepart     = $vEntries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                    $vDurMins    = ($vArrival && $vDepart)
                        ? (int) \Carbon\Carbon::parse($vArrival)->diffInMinutes(\Carbon\Carbon::parse($vDepart))
                        : null;
                    $vDurFmt     = $vDurMins !== null
                        ? ($vDurMins >= 60 ? floor($vDurMins/60).'h'.($vDurMins%60 ? ' '.($vDurMins%60).'m':'') : $vDurMins.'m')
                        : null;
                    $vLateMins   = $vArrival
                        ? (int) $v->scheduled_at->diffInMinutes(\Carbon\Carbon::parse($vArrival), false)
                        : null;
                    $vTechs      = $v->techs->map(fn($t) => $t->user)->filter();
                @endphp
                <label data-prev="{{ $prevInvoice ? '1' : '0' }}"
                       style="display:block;position:relative;border:2px solid {{ $cardBorder }};border-radius:6px;padding:.45rem .6rem;background:{{ $cardBg }};cursor:pointer;transition:border-color .15s,background .15s;">
                    <input type="checkbox" name="covered_visit_ids[]" value="{{ $v->id }}"
                           {{ $isChecked ? 'checked' : '' }}
                           onchange="updateVisitCard(this)"
                           style="position:absolute;top:.5rem;right:.5rem;width:15px;height:15px;accent-color:var(--accent);cursor:pointer;z-index:1;">

                    {{-- Header: date/time + tech avatars --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.35rem;margin-bottom:.15rem;padding-right:1.25rem;">
                        <div>
                            <span style="font-size:.78rem;font-weight:700;color:#1e293b;">{{ $v->scheduled_at->format('M j, Y') }}</span>
                            <span style="font-size:.72rem;color:#6b7280;margin-left:.3rem;">{{ $v->scheduled_at->format('g:i A') }}</span>
                        </div>
                        @if($vTechs->isNotEmpty())
                        <div style="display:flex;gap:.18rem;flex-shrink:0;">
                            @foreach($vTechs->take(3) as $tech)
                            @php $tHasPhoto = $tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)); @endphp
                            @if($tHasPhoto)
                            <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}" title="{{ $tech->name }}"
                                 style="width:22px;height:22px;border-radius:50%;object-fit:cover;border:1.5px solid #bfdbfe;flex-shrink:0;">
                            @else
                            <div title="{{ $tech->name }}"
                                 style="width:22px;height:22px;border-radius:50%;background:var(--primary);border:1.5px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:.58rem;font-weight:700;color:#fff;line-height:1;">{{ strtoupper(substr($tech->name,0,1)) }}</span>
                            </div>
                            @endif
                            @endforeach
                            @if($vTechs->count() > 3)
                            <div title="{{ $vTechs->skip(3)->pluck('name')->join(', ') }}"
                                 style="width:22px;height:22px;border-radius:50%;background:#e5e7eb;border:1.5px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:.55rem;font-weight:700;color:#6b7280;line-height:1;">+{{ $vTechs->count() - 3 }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Address --}}
                    @if(!empty($workOrder->site_street))
                    <div style="font-size:.68rem;color:#6b7280;margin-bottom:.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $workOrder->site_street }}">
                        📍 {{ $workOrder->site_street }}
                    </div>
                    @endif

                    {{-- Arrived / Out / Duration --}}
                    <div style="display:flex;flex-wrap:wrap;gap:.15rem .6rem;font-size:.72rem;margin-bottom:.25rem;">
                        @if($vArrival)
                        <span style="white-space:nowrap;">
                            <span style="font-weight:600;color:#94a3b8;">Arrived</span>
                            <span style="color:{{ $vLateMins !== null && abs($vLateMins) > 15 ? ($vLateMins > 0 ? '#dc2626' : '#059669') : '#374151' }};">
                                {{ \Carbon\Carbon::parse($vArrival)->format('g:i A') }}@if($vLateMins !== null && abs($vLateMins) > 5) <span style="font-size:.67rem;">({{ $vLateMins > 0 ? '+' : '' }}{{ $vLateMins }}m)</span>@endif
                            </span>
                        </span>
                        @if($vDepart)
                        <span style="white-space:nowrap;color:#6b7280;">
                            <span style="font-weight:600;color:#94a3b8;">Out</span> {{ \Carbon\Carbon::parse($vDepart)->format('g:i A') }}
                        </span>
                        @endif
                        @if($vDurFmt)
                        <span style="font-weight:700;color:#059669;white-space:nowrap;">{{ $vDurFmt }}</span>
                        @endif
                        @else
                        <span style="color:#d1d5db;font-style:italic;">not yet clocked in</span>
                        @endif
                    </div>

                    {{-- Signature or already-billed notice --}}
                    @if($prevInvoice)
                    <div style="padding-top:.2rem;border-top:1px solid #fcd34d;font-size:.68rem;color:#92400e;">
                        Already billed on
                        <a href="{{ route('admin.invoices.show', $prevInvoice->id) }}" target="_blank"
                           style="color:#92400e;font-weight:700;text-decoration:underline;text-underline-offset:2px;"
                           onclick="event.stopPropagation()">{{ $prevInvNum }} ↗</a>
                    </div>
                    @elseif($vSigOk)
                    <div style="display:flex;align-items:center;gap:.4rem;padding-top:.25rem;border-top:1px solid #f0f0f0;">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($vSigPath)) }}"
                             alt="Signature"
                             style="height:26px;max-width:90px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:3px;padding:1px;flex-shrink:0;">
                        <span style="font-size:.68rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $vSig->signer_name }}</span>
                    </div>
                    @else
                    <div style="padding-top:.2rem;border-top:1px solid #f0f0f0;font-size:.68rem;color:#d1d5db;">— unsigned</div>
                    @endif

                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Dates --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Invoice Date</label>
                <div style="padding:.55rem .85rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:5px;font-size:.9rem;color:#555;">
                    {{ now()->format('M j, Y') }}
                </div>
            </div>
            <div>
                <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Due Date</label>
                <input type="date" name="due_date"
                       value="{{ old('due_date', now()->addDays($settings['due_days'])->toDateString()) }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
        </div>

        {{-- Payment terms --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Payment Terms</label>
            <textarea name="payment_terms" rows="2"
                      style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('payment_terms', $settings['payment_terms']) }}</textarea>
        </div>

        {{-- Line items --}}
        <h3 style="font-size:.95rem;color:var(--primary);margin-bottom:.75rem;">Line Items</h3>
        <div style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.4rem;font-size:.78rem;color:#888;font-weight:600;padding:0 .1rem;">
            <span>Description</span><span>Qty</span><span>Unit Price</span><span></span>
        </div>
        <div id="line-items">
            @php $lineIdx = 0; @endphp
            @forelse($workOrder->serviceTypes as $svc)
            <div class="line-item" style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;">
                <input type="text" name="items[{{ $lineIdx }}][description]" value="{{ old('items.'.$lineIdx.'.description', $svc->name) }}" required
                       style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <input type="number" name="items[{{ $lineIdx }}][quantity]" value="{{ old('items.'.$lineIdx.'.quantity', 1) }}" min="0.01" step="0.01" required
                       class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <input type="number" name="items[{{ $lineIdx }}][unit_price]" value="{{ old('items.'.$lineIdx.'.unit_price', $svc->default_unit_price !== null ? number_format($svc->default_unit_price, 2) : '0.00') }}" min="0" step="0.01" required
                       class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <button type="button" onclick="removeLine(this)" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>
            </div>
            @php $lineIdx++; @endphp
            @empty
            <div class="line-item" style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;">
                <input type="text" name="items[0][description]" placeholder="Service or part description" required
                       style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <input type="number" name="items[0][quantity]" value="1" min="0.01" step="0.01" required
                       class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <input type="number" name="items[0][unit_price]" value="0.00" min="0" step="0.01" required
                       class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <button type="button" onclick="removeLine(this)" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>
            </div>
            @endforelse
        </div>
        <button type="button" onclick="addLineItem()" class="btn btn-secondary btn-sm" style="margin-bottom:1.5rem;">+ Add Line</button>

        {{-- Tax rate & totals --}}
        <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;margin-bottom:1.5rem;">
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.75rem;margin-bottom:.75rem;">
                <label style="font-size:.85rem;font-weight:600;color:#444;white-space:nowrap;">Tax Rate %</label>
                <input type="number" id="tax-rate-input" name="tax_rate_pct"
                       value="{{ old('tax_rate_pct', number_format($settings['tax_rate_pct'], 4)) }}"
                       min="0" max="100" step="0.001"
                       style="width:90px;padding:.45rem .65rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;text-align:right;">
            </div>
            <div style="display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;font-size:.9rem;">
                <div style="display:flex;gap:2rem;">
                    <span style="color:#555;">Subtotal</span>
                    <span id="disp-subtotal" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
                </div>
                <div style="display:flex;gap:2rem;">
                    <span style="color:#555;">Tax (<span id="disp-rate-label">{{ number_format($settings['tax_rate_pct'], 2) }}</span>%)</span>
                    <span id="disp-tax" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
                </div>
                <div style="display:flex;gap:2rem;padding-top:.35rem;border-top:2px solid #e5e7eb;margin-top:.15rem;">
                    <span style="font-weight:700;color:var(--primary);">Total</span>
                    <span id="disp-total" style="min-width:90px;text-align:right;font-weight:700;font-size:1.05rem;color:var(--primary);">$0.00</span>
                </div>
            </div>
        </div>

        {{-- Footer note --}}
        <div style="margin-bottom:1.5rem;">
            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Footer / Additional Notes</label>
            <textarea name="footer_note" rows="2"
                      style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('footer_note', $settings['footer_note']) }}</textarea>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-primary">Create Invoice</button>
            <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    </div>

</div>

@else
{{-- ══════════════════════════════════════════
     STANDALONE FORM  (no work order pre-selected)
══════════════════════════════════════════ --}}
<div style="max-width:760px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
<form method="POST" action="{{ route('admin.invoices.store') }}" id="invoice-form">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div style="grid-column:1/-1;">
            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Work Order *</label>
            <select name="work_order_id" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <option value="">— Select work order —</option>
                @foreach($workOrders as $wo)
                <option value="{{ $wo->id }}" {{ old('work_order_id') == $wo->id ? 'selected' : '' }}>
                    #{{ $wo->id }} – {{ $wo->customer->name }}{{ $wo->title ? ' – '.$wo->title : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Due Date</label>
            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays($settings['due_days'])->toDateString()) }}"
                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
        <div>
            <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Payment Terms</label>
            <input type="text" name="payment_terms" value="{{ old('payment_terms', $settings['payment_terms']) }}"
                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
    </div>

    <h3 style="font-size:.95rem;color:var(--primary);margin-bottom:.75rem;">Line Items</h3>
    <div style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.4rem;font-size:.78rem;color:#888;font-weight:600;">
        <span>Description</span><span>Qty</span><span>Unit Price</span><span></span>
    </div>
    <div id="line-items">
        <div class="line-item" style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;">
            <input type="text" name="items[0][description]" placeholder="Service or part description" required
                   style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            <input type="number" name="items[0][quantity]" value="1" min="0.01" step="0.01" required
                   class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            <input type="number" name="items[0][unit_price]" value="0.00" min="0" step="0.01" required
                   class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            <button type="button" onclick="removeLine(this)" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>
        </div>
    </div>
    <button type="button" onclick="addLineItem()" class="btn btn-secondary btn-sm" style="margin-bottom:1.5rem;">+ Add Line</button>

    {{-- Tax rate & totals --}}
    <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;margin-bottom:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.75rem;margin-bottom:.75rem;">
            <label style="font-size:.85rem;font-weight:600;color:#444;white-space:nowrap;">Tax Rate %</label>
            <input type="number" id="tax-rate-input" name="tax_rate_pct"
                   value="{{ old('tax_rate_pct', number_format($settings['tax_rate_pct'], 4)) }}"
                   min="0" max="100" step="0.001"
                   style="width:90px;padding:.45rem .65rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;text-align:right;">
        </div>
        <div style="display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;font-size:.9rem;">
            <div style="display:flex;gap:2rem;">
                <span style="color:#555;">Subtotal</span>
                <span id="disp-subtotal" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
            </div>
            <div style="display:flex;gap:2rem;">
                <span style="color:#555;">Tax (<span id="disp-rate-label">{{ number_format($settings['tax_rate_pct'], 2) }}</span>%)</span>
                <span id="disp-tax" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
            </div>
            <div style="display:flex;gap:2rem;padding-top:.35rem;border-top:2px solid #e5e7eb;margin-top:.15rem;">
                <span style="font-weight:700;color:var(--primary);">Total</span>
                <span id="disp-total" style="min-width:90px;text-align:right;font-weight:700;font-size:1.05rem;color:var(--primary);">$0.00</span>
            </div>
        </div>
    </div>

    <div style="margin-bottom:1.5rem;">
        <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Footer / Additional Notes</label>
        <textarea name="footer_note" rows="2"
                  style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('footer_note', $settings['footer_note']) }}</textarea>
    </div>

    <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-primary">Create Invoice</button>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>
@endif

<script>
let lineIdx = {{ $workOrder ? max(1, $workOrder->serviceTypes->count()) : 1 }};

function updateVisitCard(cb) {
    var card = cb.closest('label');
    var prev = card.dataset.prev === '1';
    if (cb.checked) {
        card.style.borderColor = 'var(--accent)';
        card.style.background  = '#eff6ff';
    } else {
        card.style.borderColor = prev ? '#fcd34d' : '#e5e7eb';
        card.style.background  = prev ? '#fffbeb' : '#fafafa';
    }
}

function fmt(n) {
    return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function recalc() {
    let subtotal = 0;
    document.querySelectorAll('#line-items .line-item').forEach(row => {
        const qty   = parseFloat(row.querySelector('.qty-input')?.value)   || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        subtotal += qty * price;
    });
    const rate     = parseFloat(document.getElementById('tax-rate-input')?.value) || 0;
    const tax      = subtotal * rate / 100;
    const total    = subtotal + tax;

    document.getElementById('disp-subtotal').textContent = fmt(subtotal);
    document.getElementById('disp-tax').textContent      = fmt(tax);
    document.getElementById('disp-total').textContent    = fmt(total);
    const lbl = document.getElementById('disp-rate-label');
    if (lbl) lbl.textContent = rate.toFixed(2);
}

function attachListeners(row) {
    row.querySelectorAll('.qty-input, .price-input').forEach(el => {
        el.addEventListener('input', recalc);
    });
}

function removeLine(btn) {
    const rows = document.querySelectorAll('#line-items .line-item');
    if (rows.length <= 1) return;
    btn.closest('.line-item').remove();
    recalc();
}

function addLineItem() {
    const el = document.createElement('div');
    el.className = 'line-item';
    el.style.cssText = 'display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;';
    el.innerHTML = `
        <input type="text" name="items[${lineIdx}][description]" placeholder="Description" required style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <input type="number" name="items[${lineIdx}][quantity]" value="1" min="0.01" step="0.01" required class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <input type="number" name="items[${lineIdx}][unit_price]" value="0.00" min="0" step="0.01" required class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <button type="button" onclick="removeLine(this)" style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>`;
    document.getElementById('line-items').appendChild(el);
    attachListeners(el);
    lineIdx++;
}

// Attach listeners to existing rows and initial recalc
document.querySelectorAll('#line-items .line-item').forEach(attachListeners);
document.getElementById('tax-rate-input')?.addEventListener('input', recalc);
recalc();
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    @if($workOrder)
    <a href="{{ route('admin.work-orders.show', $workOrder) }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← {{ $workOrder->woLabel() }}</a>
    @else
    <a href="{{ route('admin.invoices.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← Invoices</a>
    @endif
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/></svg>
        New Invoice
    </h1>
</div>
@endpush
