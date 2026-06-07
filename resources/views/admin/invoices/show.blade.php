@extends('layouts.admin')
@section('title', 'Invoice INV-'.str_pad($invoice->id,4,'0',STR_PAD_LEFT))

@section('content')
@php $num = 'INV-'.str_pad($invoice->id,4,'0',STR_PAD_LEFT); @endphp

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.25rem;">{{ $errors->first() }}</div>
@endif

@php
    $badgeBg    = match($invoice->status) {
        'issued'           => '#dbeafe',
        'payment_received' => '#fce7f3',
        'completed'        => '#d1fae5',
        'canceled'         => '#fee2e2',
        default            => '#fef3c7',
    };
    $badgeColor = match($invoice->status) {
        'issued'           => '#1e40af',
        'payment_received' => '#9d174d',
        'completed'        => '#065f46',
        'canceled'         => '#991b1b',
        default            => '#92400e',
    };
    $statusLabel = match($invoice->status) {
        'issued'           => 'Issued',
        'payment_received' => 'Payment Received',
        'completed'        => 'Completed',
        'canceled'         => 'Canceled',
        default            => 'Draft',
    };
@endphp


<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

    {{-- ── Main content ── --}}
    <div>
    <div id="inv-display" style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">

        {{-- Header meta --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem;font-size:.9rem;margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
            <div><strong>Bill To:</strong> {{ $invoice->workOrder->customer->name ?? '—' }}</div>
            <div><strong>Invoice #:</strong> {{ $num }}</div>
            <div><strong>Invoice Date:</strong> {{ $invoice->created_at->format('M j, Y') }}</div>
            <div><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M j, Y') : '—' }}</div>
            @if($invoice->payment_terms)
            <div style="grid-column:1/-1;"><strong>Payment Terms:</strong> {{ $invoice->payment_terms }}</div>
            @endif
        </div>

        {{-- Line items --}}
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;margin-bottom:1.5rem;">
            <thead>
                <tr style="background:var(--primary);color:#fff;">
                    <th style="padding:.6rem 1rem;text-align:left;">Description</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Qty</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Unit Price</th>
                    <th style="padding:.6rem 1rem;text-align:right;white-space:nowrap;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems->sortBy('sort_order') as $item)
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:.65rem 1rem;">{{ $item->description }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding:.65rem 1rem;text-align:right;">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        @php
            $subtotal  = (float)($invoice->subtotal  ?: $invoice->lineItems->sum(fn($i) => $i->quantity * $i->unit_price));
            $taxRate   = (float)($invoice->tax_rate  ?? 0);
            $taxAmount = (float)($invoice->tax_amount ?: round($subtotal * $taxRate, 2));
            $total     = (float)($invoice->total     ?: round($subtotal + $taxAmount, 2));
        @endphp
        <div style="display:flex;justify-content:flex-end;margin-bottom:1.75rem;">
            <div style="width:260px;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                    <span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:.3rem 0;color:#555;">
                    <span>Tax ({{ number_format($taxRate * 100, 2) }}%)</span><span>${{ number_format($taxAmount, 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:1.05rem;font-weight:700;padding:.6rem 0;border-top:2px solid #e5e7eb;margin-top:.25rem;color:var(--primary);">
                    <span>Total</span><span>${{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Transaction reference --}}
        @if($invoice->transaction_reference)
        <div style="display:flex;align-items:center;gap:.6rem;padding:.6rem .85rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;font-size:.87rem;margin-bottom:.85rem;">
            <span style="font-weight:700;color:#166534;">Transaction / Check #:</span>
            <span style="color:#166534;font-family:monospace;font-size:.92rem;">{{ $invoice->transaction_reference }}</span>
        </div>
        @endif

        {{-- Footer note --}}
        @if($invoice->footer_note)
        <div style="padding:.85rem 1rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:6px;font-size:.87rem;color:#555;margin-bottom:1.25rem;">
            {{ $invoice->footer_note }}
        </div>
        @endif

        {{-- Visits covered --}}
        @php
            $allVisits     = $invoice->workOrder->visits ?? collect();
            $coveredIds    = $invoice->covered_visit_ids ?? [];
            $coveredVisits = $coveredIds
                ? $allVisits->whereIn('id', $coveredIds)->sortBy('scheduled_at')->values()
                : $allVisits->sortBy('scheduled_at')->values();
        @endphp
        @if($coveredVisits->isNotEmpty())
        <div style="margin-bottom:1.25rem;padding-top:1.25rem;border-top:1px solid #e5e7eb;">
            <div style="font-size:.82rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.65rem;">
                Visits Covered{{ $coveredIds ? '' : ' (all)' }}
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.45rem;">
                @foreach($coveredVisits as $cv)
                @php
                    $cvSig    = $cv->signature;
                    $sigPath  = $cvSig ? storage_path('app/signatures/work-orders/'.$cvSig->signature_path) : null;
                    $sigOk    = $sigPath && file_exists($sigPath);
                    $entries  = $cv->timeEntries;
                    $arrival  = $entries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                    $depart   = $entries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                    $durMins  = ($arrival && $depart)
                        ? (int) \Carbon\Carbon::parse($arrival)->diffInMinutes(\Carbon\Carbon::parse($depart))
                        : null;
                    $cvDurFmt = $durMins !== null
                        ? ($durMins >= 60 ? floor($durMins/60).'h'.($durMins%60 ? ' '.($durMins%60).'m':'') : $durMins.'m')
                        : null;
                    $lateMins = $arrival
                        ? (int) $cv->scheduled_at->diffInMinutes(\Carbon\Carbon::parse($arrival), false)
                        : null;
                    $cvTechs  = $cv->techs->map(fn($t) => $t->user)->filter();
                @endphp
                <div style="border:1px solid #e5e7eb;border-radius:6px;padding:.45rem .6rem;background:#fafafa;">

                    {{-- Header: date/time + tech avatars --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.35rem;margin-bottom:.15rem;">
                        <div>
                            <span style="font-size:.78rem;font-weight:700;color:#1e293b;">{{ $cv->scheduled_at->format('M j, Y') }}</span>
                            <span style="font-size:.72rem;color:#6b7280;margin-left:.3rem;">{{ $cv->scheduled_at->format('g:i A') }}</span>
                        </div>
                        @if($cvTechs->isNotEmpty())
                        <div style="display:flex;gap:.18rem;flex-shrink:0;">
                            @foreach($cvTechs->take(3) as $tech)
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
                            @if($cvTechs->count() > 3)
                            <div title="{{ $cvTechs->skip(3)->pluck('name')->join(', ') }}"
                                 style="width:22px;height:22px;border-radius:50%;background:#e5e7eb;border:1.5px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:.55rem;font-weight:700;color:#6b7280;line-height:1;">+{{ $cvTechs->count() - 3 }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Address --}}
                    @if(!empty($invoice->workOrder->site_street))
                    <div style="font-size:.68rem;color:#6b7280;margin-bottom:.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $invoice->workOrder->site_street }}">
                        📍 {{ $invoice->workOrder->site_street }}
                    </div>
                    @endif

                    {{-- Arrived / Out / Duration --}}
                    <div style="display:flex;flex-wrap:wrap;gap:.15rem .6rem;font-size:.72rem;margin-bottom:.25rem;">
                        @if($arrival)
                        <span style="white-space:nowrap;">
                            <span style="font-weight:600;color:#94a3b8;">Arrived</span>
                            <span style="color:{{ $lateMins !== null && abs($lateMins) > 15 ? ($lateMins > 0 ? '#dc2626' : '#059669') : '#374151' }};">
                                {{ \Carbon\Carbon::parse($arrival)->format('g:i A') }}@if($lateMins !== null && abs($lateMins) > 5) <span style="font-size:.67rem;">({{ $lateMins > 0 ? '+' : '' }}{{ $lateMins }}m)</span>@endif
                            </span>
                        </span>
                        @if($depart)
                        <span style="white-space:nowrap;color:#6b7280;">
                            <span style="font-weight:600;color:#94a3b8;">Out</span> {{ \Carbon\Carbon::parse($depart)->format('g:i A') }}
                        </span>
                        @endif
                        @if($cvDurFmt)
                        <span style="font-weight:700;color:#059669;white-space:nowrap;">{{ $cvDurFmt }}</span>
                        @endif
                        @else
                        <span style="color:#d1d5db;font-style:italic;">not yet clocked in</span>
                        @endif
                    </div>

                    {{-- Signature --}}
                    @if($sigOk)
                    <div style="display:flex;align-items:center;gap:.4rem;padding-top:.25rem;border-top:1px solid #f0f0f0;">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}"
                             alt="Signature" data-sig-img
                             data-sig-caption="{{ $cvSig->signer_name }} · {{ $cvSig->signed_at->format('M j, g:i A') }}"
                             style="height:26px;max-width:90px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:3px;padding:1px;flex-shrink:0;cursor:zoom-in;">
                        <span style="font-size:.68rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cvSig->signer_name }}</span>
                    </div>
                    @else
                    <div style="padding-top:.2rem;border-top:1px solid #f0f0f0;font-size:.68rem;color:#d1d5db;">— unsigned</div>
                    @endif

                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Customer signature --}}
        @if($invoice->signature)
        <div style="background:#f0f6ff;padding:1rem;border-radius:6px;font-size:.88rem;">
            <strong>Signed by:</strong> {{ $invoice->signature->signer_name }}
            on {{ \Carbon\Carbon::parse($invoice->signature->signed_at)->format('M j, Y g:i A') }}
        </div>
        @endif

    </div>

    @php $isEditable = !in_array($invoice->status, [\App\Models\Invoice::STATUS_PAYMENT_RECEIVED, \App\Models\Invoice::STATUS_COMPLETED, \App\Models\Invoice::STATUS_CANCELED]); @endphp
    {{-- ── Inline Edit Form ── --}}
    <div id="inv-edit-form" style="display:none;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}">
            @csrf @method('PATCH')

            {{-- Header meta --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem 2rem;font-size:.9rem;margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid #e5e7eb;">
                <div><strong>Bill To:</strong> {{ $invoice->workOrder->customer->name ?? '—' }}</div>
                <div><strong>Invoice #:</strong> {{ $num }}</div>
                <div><strong>Invoice Date:</strong> {{ $invoice->created_at->format('M j, Y') }}</div>
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Due Date</label>
                    <input type="date" name="due_date" value="{{ $invoice->due_date?->format('Y-m-d') }}"
                           style="width:100%;padding:.3rem .55rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
                </div>
            </div>

            {{-- Editable line items --}}
            <table style="width:100%;border-collapse:collapse;font-size:.88rem;margin-bottom:.75rem;">
                <thead>
                    <tr style="background:var(--primary);color:#fff;">
                        <th style="padding:.55rem .9rem;text-align:left;">Description</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;width:80px;">Qty</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;width:130px;">Unit Price</th>
                        <th style="padding:.55rem .9rem;text-align:right;white-space:nowrap;width:120px;">Line Total</th>
                        <th style="padding:.55rem .5rem;width:32px;"></th>
                    </tr>
                </thead>
                <tbody id="inv-items-body">
                    @foreach($invoice->lineItems->sortBy('sort_order') as $item)
                    <tr class="inv-item-row" style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:.45rem .6rem;">
                            <input type="text" name="items[{{ $loop->index }}][description]"
                                   value="{{ old('items.'.$loop->index.'.description', $item->description) }}"
                                   required placeholder="Description"
                                   style="width:100%;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;box-sizing:border-box;"
                                   oninput="updateInvTotals()">
                        </td>
                        <td style="padding:.45rem .6rem;">
                            <input type="number" name="items[{{ $loop->index }}][quantity]"
                                   value="{{ old('items.'.$loop->index.'.quantity', rtrim(rtrim(number_format($item->quantity,4),'0'),'.')) }}"
                                   min="0" step="any" required
                                   style="width:72px;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;text-align:right;"
                                   oninput="updateInvTotals()">
                        </td>
                        <td style="padding:.45rem .6rem;">
                            <input type="number" name="items[{{ $loop->index }}][unit_price]"
                                   value="{{ old('items.'.$loop->index.'.unit_price', number_format($item->unit_price,2)) }}"
                                   min="0" step="0.01" required
                                   style="width:110px;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;text-align:right;"
                                   oninput="updateInvTotals()">
                        </td>
                        <td style="padding:.45rem .6rem;text-align:right;white-space:nowrap;color:#374151;">
                            $<span class="inv-line-total">{{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                        </td>
                        <td style="padding:.45rem .3rem;text-align:center;">
                            <button type="button" onclick="removeInvRow(this)"
                                    style="border:none;background:none;color:#dc2626;font-size:1rem;cursor:pointer;padding:.1rem .25rem;line-height:1;" title="Remove row">✕</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-bottom:1.5rem;">
                <button type="button" onclick="addInvLineItem()"
                        style="font-size:.8rem;color:var(--accent);background:none;border:1px dashed var(--accent);border-radius:5px;padding:.3rem .9rem;cursor:pointer;">
                    + Add Line Item
                </button>
            </div>

            {{-- Tax rate + live totals --}}
            <div style="display:flex;justify-content:flex-end;margin-bottom:1.75rem;">
                <div style="width:280px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:.9rem;padding:.3rem 0;color:#555;">
                        <span>Subtotal</span>
                        <span id="edit-disp-subtotal" style="font-family:monospace;">$0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:.9rem;padding:.3rem 0;color:#555;">
                        <span style="display:flex;align-items:center;gap:.4rem;">
                            Tax&nbsp;
                            <input type="number" name="tax_rate_pct" id="inv-tax-rate-pct"
                                   value="{{ number_format((float)$invoice->tax_rate * 100, 2) }}"
                                   min="0" max="100" step="0.01"
                                   style="width:62px;padding:.2rem .4rem;border:1px solid #d1d5db;border-radius:4px;font-size:.85rem;text-align:right;"
                                   oninput="updateInvTotals()">%
                        </span>
                        <span id="edit-disp-tax" style="font-family:monospace;">$0.00</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:1.05rem;font-weight:700;padding:.6rem 0;border-top:2px solid #e5e7eb;margin-top:.25rem;color:var(--primary);">
                        <span>Total</span>
                        <span id="edit-disp-total" style="font-family:monospace;">$0.00</span>
                    </div>
                </div>
            </div>

            {{-- Payment terms + footer note --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Payment Terms</label>
                    <textarea name="payment_terms" rows="3"
                              style="width:100%;padding:.5rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.87rem;resize:vertical;box-sizing:border-box;">{{ old('payment_terms', $invoice->payment_terms) }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Footer Note</label>
                    <textarea name="footer_note" rows="3"
                              style="width:100%;padding:.5rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.87rem;resize:vertical;box-sizing:border-box;">{{ old('footer_note', $invoice->footer_note) }}</textarea>
                </div>
            </div>

            <div style="display:flex;gap:.65rem;align-items:center;padding-top:1rem;border-top:1px solid #e5e7eb;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" onclick="toggleInvoiceEdit(false)" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
    </div>{{-- /main column wrapper --}}

    {{-- ── Right sidebar ── --}}
    <div>

        {{-- Status lifecycle --}}
        @php
            $steps = [
                'draft'            => 'Draft',
                'issued'           => 'Issued',
                'payment_received' => 'Payment Received',
                'completed'        => 'Completed',
            ];
            $stepKeys   = array_keys($steps);
            $currentIdx = array_search($invoice->status, $stepKeys);
            if ($currentIdx === false) $currentIdx = 0;
            if ($invoice->status === 'canceled') $currentIdx = -1;

            $nextMap = [
                'draft'            => ['status' => 'issued',           'label' => 'Issue to Customer',    'modal' => false],
                'issued'           => ['status' => 'payment_received', 'label' => 'Mark Payment Received', 'modal' => false],
                'payment_received' => ['status' => 'completed',        'label' => 'Mark Completed',        'modal' => true],
            ];
            $next       = $nextMap[$invoice->status] ?? null;
            $isTerminal = in_array($invoice->status, ['completed', 'canceled']);
        @endphp

        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem 1rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:700;color:#fff;line-height:1.2;">Status Lifecycle</div>
                        <div style="font-size:.68rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Track progress · Advance status</div>
                    </div>
                </div>
                @if(!$isTerminal)
                <button type="button" onclick="openStatusModal()"
                        title="Override status"
                        style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);cursor:pointer;font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Override
                </button>
                @endif
            </div>

            {{-- Stepper --}}
            @foreach($steps as $key => $label)
            @php
                $idx    = array_search($key, $stepKeys);
                $isDone = $invoice->status !== 'canceled' && $idx < $currentIdx;
                $isCurr = $invoice->status === $key;
            @endphp
            <div style="display:flex;align-items:flex-start;gap:.65rem;padding:.3rem 0;position:relative;">
                @if(!$loop->last)
                <div style="position:absolute;left:10px;top:22px;width:2px;height:calc(100% + 4px);background:{{ $isDone ? '#86efac' : '#e5e7eb' }};z-index:0;"></div>
                @endif
                <div style="width:20px;height:20px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;z-index:1;
                    background:{{ $isDone ? '#16a34a' : ($isCurr ? 'var(--accent)' : '#e5e7eb') }};
                    color:{{ $isDone ? '#fff'   : ($isCurr ? '#fff'          : '#9ca3af') }};
                    border:2px solid {{ $isDone ? '#16a34a' : ($isCurr ? 'var(--accent)' : '#d1d5db') }};">
                    {{ $isDone ? '✓' : '' }}
                </div>
                <span style="font-size:.85rem;padding-top:.1rem;
                    color:{{ $isDone ? '#16a34a' : ($isCurr ? '#1A3C5E' : '#9ca3af') }};
                    font-weight:{{ $isCurr ? '700' : '400' }};">{{ $label }}</span>
            </div>
            @endforeach

            {{-- Canceled indicator --}}
            @if($invoice->status === 'canceled')
            <div style="margin-top:.75rem;padding:.65rem .75rem;background:#fee2e2;border-radius:5px;color:#991b1b;font-size:.85rem;">
                <div style="font-weight:700;margin-bottom:.2rem;">✕ Canceled</div>
                @if($invoice->cancel_reason)
                <div style="font-size:.82rem;color:#b91c1c;">{{ $invoice->cancel_reason }}</div>
                @endif
            </div>
            @endif

            {{-- Transaction/Check # (completed — read-only) --}}
            @if($invoice->status === 'completed')
            <div style="margin-top:1rem;padding:.65rem .75rem;background:#f0fdf4;border:1px solid #86efac;border-radius:6px;">
                <div style="font-size:.78rem;font-weight:600;color:#166534;margin-bottom:.2rem;">Transaction / Check #</div>
                <div style="font-size:.88rem;color:#166534;font-family:monospace;">{{ $invoice->transaction_reference ?: '—' }}</div>
            </div>
            @endif

            {{-- Action buttons --}}
            @if(!$isTerminal)
            <div style="margin-top:1.25rem;display:flex;flex-direction:column;gap:.5rem;">
                @if($next)
                    @if($next['modal'])
                    <div style="margin-bottom:.1rem;">
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.3rem;">
                            Transaction / Check # <span style="font-weight:400;color:#9ca3af;">(optional)</span>
                        </label>
                        <input type="text" id="inline-txn-ref" maxlength="100"
                               placeholder="e.g. CHK-4821"
                               style="width:100%;padding:.45rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;font-family:monospace;color:#111;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                               onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                    </div>
                    <button type="button" class="btn btn-primary" style="width:100%;" onclick="syncAndOpenCompletionModal()">
                        → {{ $next['label'] }}
                    </button>
                    @else
                    <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $next['status'] }}">
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            → {{ $next['label'] }}
                        </button>
                    </form>
                    @endif
                @endif

                <button type="button" class="btn btn-danger" style="width:100%;font-size:.83rem;" onclick="openCancelModal()">
                    Cancel Invoice
                </button>
            </div>
            @endif
        </div>

        {{-- Other invoices on this work order --}}
        @php
            $siblingInvoices = $invoice->workOrder->invoices->where('id', '!=', $invoice->id)->sortBy('id')->values();
        @endphp
        @if($siblingInvoices->isNotEmpty())
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem .85rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <div>
                    <div style="font-size:.88rem;font-weight:700;color:#fff;line-height:1.2;">Other Invoices</div>
                    <div style="font-size:.68rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $invoice->workOrder->woLabel() }}</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                @foreach($siblingInvoices as $sib)
                @php
                    $sibNum = 'INV-' . str_pad($sib->id, 4, '0', STR_PAD_LEFT);
                    $sibBg  = match($sib->status) {
                        'issued'           => '#dbeafe', 'payment_received' => '#fce7f3',
                        'completed'        => '#d1fae5', 'canceled'         => '#fee2e2',
                        default            => '#fef3c7',
                    };
                    $sibColor = match($sib->status) {
                        'issued'           => '#1e40af', 'payment_received' => '#9d174d',
                        'completed'        => '#065f46', 'canceled'         => '#991b1b',
                        default            => '#92400e',
                    };
                    $sibLabel = match($sib->status) {
                        'issued'           => 'Issued',           'payment_received' => 'Payment Received',
                        'completed'        => 'Completed',        'canceled'         => 'Canceled',
                        default            => 'Draft',
                    };
                @endphp
                <a href="{{ route('admin.invoices.show', $sib) }}"
                   style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;border:1px solid #e5e7eb;border-radius:6px;text-decoration:none;background:#f9fafb;transition:background .12s;"
                   onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#f9fafb'">
                    <span style="font-size:.87rem;font-weight:700;color:var(--primary);">{{ $sibNum }}</span>
                    <span style="font-size:.72rem;font-weight:700;padding:.15rem .55rem;border-radius:999px;background:{{ $sibBg }};color:{{ $sibColor }};border:1px solid {{ $sibColor }};">{{ $sibLabel }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Invoice details card --}}
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem .85rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/></svg>
                    <div>
                        <div style="font-size:.88rem;font-weight:700;color:#fff;line-height:1.2;">Invoice Details</div>
                        <div style="font-size:.68rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Dates · Totals · Reference</div>
                    </div>
                </div>
                @if($isEditable)
                <a href="{{ route('admin.invoices.edit', $invoice) }}"
                   title="Edit invoice"
                   style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;text-decoration:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </a>
                @endif
            </div>
            <div style="font-size:.85rem;color:#555;display:flex;flex-direction:column;gap:.4rem;">
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Invoice #</span>
                    <span style="font-weight:600;">{{ $num }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Created</span>
                    <span>{{ $invoice->created_at->format('M j, Y') }}</span>
                </div>
                @if($invoice->due_date)
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Due</span>
                    <span style="{{ $invoice->due_date->isPast() && !$isTerminal ? 'color:#dc2626;font-weight:600;' : '' }}">
                        {{ $invoice->due_date->format('M j, Y') }}
                        @if($invoice->due_date->isPast() && !$isTerminal)
                            <span style="font-size:.72rem;">(overdue)</span>
                        @endif
                    </span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;">
                    <span style="color:#888;">Total</span>
                    <span style="font-weight:700;font-size:.95rem;color:var(--primary);">${{ number_format($total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Audit history --}}
        @if($invoice->history->count())
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.25rem -1.25rem .85rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <div>
                    <div style="font-size:.88rem;font-weight:700;color:#fff;line-height:1.2;">History</div>
                    <div style="font-size:.68rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Audit trail · Recent changes</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.6rem;">
                @foreach($invoice->history as $entry)
                @php
                    $entryLabel = match($entry->new_value) {
                        'issued'           => 'Issued',
                        'payment_received' => 'Payment Received',
                        'completed'        => 'Completed',
                        'canceled'         => 'Canceled',
                        'draft'            => 'Draft',
                        default            => ucfirst(str_replace('_', ' ', $entry->new_value ?? '')),
                    };
                    $entryColor = match($entry->new_value) {
                        'issued'           => '#1e40af',
                        'payment_received' => '#9d174d',
                        'completed'        => '#065f46',
                        'canceled'         => '#991b1b',
                        default            => '#92400e',
                    };
                @endphp
                <div style="font-size:.82rem;border-left:3px solid {{ $entryColor }};padding-left:.65rem;">
                    <div style="font-weight:600;color:{{ $entryColor }};">{{ $entryLabel }}</div>
                    <div style="color:#6b7280;margin-top:.1rem;">
                        {{ $entry->changedBy->name ?? 'System' }} · {{ $entry->changed_at->format('M j, Y g:i A') }}
                    </div>
                    @if($entry->comment)
                    <div style="color:#555;margin-top:.2rem;font-style:italic;">{{ $entry->comment }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>

{{-- Cancel Invoice modal --}}
<div id="cancel-modal" onclick="if(event.target===this)closeCancelModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="font-size:1rem;color:#991b1b;margin-top:0;margin-bottom:.75rem;">Cancel Invoice</h3>
        <p style="font-size:.9rem;color:#555;margin-bottom:1rem;">
            Please provide a reason for canceling this invoice. This will be recorded in the audit trail.
        </p>
        <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">
            @csrf
            <input type="hidden" name="status" value="canceled">
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Cancellation Reason <span style="color:#dc2626;">*</span></label>
                <textarea name="cancel_reason" rows="3" required
                          placeholder="e.g. Customer requested cancellation, duplicate invoice, etc."
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeCancelModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Go Back
                </button>
                <button type="submit" class="btn btn-danger btn-sm">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

{{-- Status override modal --}}
<div id="status-modal" onclick="if(event.target===this)closeStatusModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="font-size:1rem;color:var(--primary);margin-top:0;margin-bottom:1.25rem;">Override Status</h3>
        <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">New Status</label>
                <select name="status" id="override-status" required onchange="toggleOverrideCancelReason(this.value)"
                        style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.9rem;background:#fff;">
                    @foreach(['draft' => 'Draft', 'issued' => 'Issued', 'payment_received' => 'Payment Received', 'completed' => 'Completed', 'canceled' => 'Canceled'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $invoice->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div id="override-cancel-reason" style="display:none;margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Cancellation Reason <span style="color:#dc2626;">*</span></label>
                <textarea name="cancel_reason" rows="2"
                          placeholder="Required when canceling"
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div id="override-txn-ref" style="display:none;margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Transaction / Check Number <span style="font-weight:400;color:#888;">(optional)</span></label>
                <input type="text" name="transaction_reference" maxlength="100"
                       placeholder="e.g. CHK-4821 or TXN-00293"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Comment <span style="font-weight:400;color:#888;">(optional)</span></label>
                <input type="text" name="comment" maxlength="1000"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;box-sizing:border-box;">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeStatusModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
        </form>
    </div>
</div>

{{-- Completion confirmation modal --}}
<div id="completion-modal" onclick="if(event.target===this)closeCompletionModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="font-size:1rem;color:var(--primary);margin-top:0;margin-bottom:.75rem;">Mark Invoice Completed</h3>
        <p style="font-size:.9rem;color:#555;margin-bottom:1.25rem;">
            This will mark the invoice as fully completed. Would you also like to mark the attached work order as completed?
        </p>
        <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">
            @csrf
            <input type="hidden" name="status" value="completed">
            <label style="display:flex;align-items:center;gap:.6rem;font-size:.9rem;margin-bottom:1.25rem;cursor:pointer;color:#333;">
                <input type="checkbox" name="also_complete_work_order" value="1" style="width:15px;height:15px;cursor:pointer;">
                Also mark {{ $invoice->workOrder?->woLabel() ?? 'the work order' }} as completed
            </label>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">
                    Transaction / Check Number <span style="font-weight:400;color:#9ca3af;">(optional)</span>
                </label>
                <input type="text" name="transaction_reference" maxlength="100"
                       placeholder="e.g. CHK-4821 or TXN-00293"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;font-family:inherit;color:#111;outline:none;"
                       onfocus="this.style.borderColor='#2E86C1';this.style.boxShadow='0 0 0 3px rgba(46,134,193,.12)'"
                       onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeCompletionModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Completed</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Inline invoice editing ────────────────────────────────────────────────────
let _invItemIdx = {{ $invoice->lineItems->count() }};

function toggleInvoiceEdit(on) {
    document.getElementById('inv-display').style.display  = on ? 'none'  : 'block';
    document.getElementById('inv-edit-form').style.display = on ? 'block' : 'none';
    const pencil = document.getElementById('inv-pencil-btn');
    if (pencil) pencil.style.display = on ? 'none' : 'flex';
    if (on) updateInvTotals();
}

function updateInvTotals() {
    let subtotal = 0;
    document.querySelectorAll('#inv-items-body .inv-item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('input[name*="[quantity]"]')?.value)   || 0;
        const price = parseFloat(row.querySelector('input[name*="[unit_price]"]')?.value) || 0;
        const lt    = qty * price;
        subtotal   += lt;
        const el = row.querySelector('.inv-line-total');
        if (el) el.textContent = lt.toFixed(2);
    });
    const taxPct = parseFloat(document.getElementById('inv-tax-rate-pct')?.value) || 0;
    const taxAmt = subtotal * (taxPct / 100);
    document.getElementById('edit-disp-subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('edit-disp-tax').textContent      = '$' + taxAmt.toFixed(2);
    document.getElementById('edit-disp-total').textContent    = '$' + (subtotal + taxAmt).toFixed(2);
}

function removeInvRow(btn) {
    btn.closest('tr').remove();
    reindexInvRows();
    updateInvTotals();
}

function reindexInvRows() {
    document.querySelectorAll('#inv-items-body .inv-item-row').forEach((row, i) => {
        row.querySelectorAll('input[name]').forEach(inp => {
            inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
        });
    });
}

function addInvLineItem() {
    const idx   = _invItemIdx++;
    const tbody = document.getElementById('inv-items-body');
    const tr    = document.createElement('tr');
    tr.className = 'inv-item-row';
    tr.style.cssText = 'border-bottom:1px solid #f0f0f0;';
    tr.innerHTML = `
        <td style="padding:.45rem .6rem;">
            <input type="text" name="items[${idx}][description]" required placeholder="Description"
                   style="width:100%;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;box-sizing:border-box;"
                   oninput="updateInvTotals()">
        </td>
        <td style="padding:.45rem .6rem;">
            <input type="number" name="items[${idx}][quantity]" value="1" min="0" step="any" required
                   style="width:72px;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;text-align:right;"
                   oninput="updateInvTotals()">
        </td>
        <td style="padding:.45rem .6rem;">
            <input type="number" name="items[${idx}][unit_price]" value="0.00" min="0" step="0.01" required
                   style="width:110px;padding:.3rem .5rem;border:1px solid #d1d5db;border-radius:4px;font-size:.87rem;text-align:right;"
                   oninput="updateInvTotals()">
        </td>
        <td style="padding:.45rem .6rem;text-align:right;white-space:nowrap;color:#374151;">
            $<span class="inv-line-total">0.00</span>
        </td>
        <td style="padding:.45rem .3rem;text-align:center;">
            <button type="button" onclick="removeInvRow(this)"
                    style="border:none;background:none;color:#dc2626;font-size:1rem;cursor:pointer;padding:.1rem .25rem;line-height:1;" title="Remove row">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    tr.querySelector('input').focus();
}

// ── Modal handlers ────────────────────────────────────────────────────────────
function openCancelModal()     { document.getElementById('cancel-modal').style.display = 'flex'; document.addEventListener('keydown', _cmKey); }
function closeCancelModal()    { document.getElementById('cancel-modal').style.display = 'none'; document.removeEventListener('keydown', _cmKey); }
function _cmKey(e)             { if (e.key === 'Escape') closeCancelModal(); }

function openStatusModal()     { document.getElementById('status-modal').style.display = 'flex'; document.addEventListener('keydown', _smKey); }
function closeStatusModal()    { document.getElementById('status-modal').style.display = 'none'; document.removeEventListener('keydown', _smKey); }
function _smKey(e)             { if (e.key === 'Escape') closeStatusModal(); }

function openCompletionModal() { document.getElementById('completion-modal').style.display = 'flex'; document.addEventListener('keydown', _pmKey); }
function closeCompletionModal(){ document.getElementById('completion-modal').style.display = 'none'; document.removeEventListener('keydown', _pmKey); }
function _pmKey(e)             { if (e.key === 'Escape') closeCompletionModal(); }
function syncAndOpenCompletionModal() {
    const inlineField = document.getElementById('inline-txn-ref');
    const modalField  = document.querySelector('#completion-modal input[name="transaction_reference"]');
    if (inlineField && modalField) modalField.value = inlineField.value;
    openCompletionModal();
}

function toggleOverrideCancelReason(val) {
    const cancelEl = document.getElementById('override-cancel-reason');
    const textarea = cancelEl.querySelector('textarea');
    const isCanceled = val === 'canceled';
    cancelEl.style.display = isCanceled ? 'block' : 'none';
    textarea.required = isCanceled;

    const txnEl = document.getElementById('override-txn-ref');
    txnEl.style.display = val === 'completed' ? 'block' : 'none';
}
toggleOverrideCancelReason(document.getElementById('override-status')?.value ?? '');
</script>

@push('topbar-actions')
    <a href="{{ route('admin.invoices.print', $invoice) }}" target="_blank"
       style="padding:.35rem .8rem;border:1px solid #d1d5db;border-radius:6px;background:#f8f9fa;color:#374151;font-size:.83rem;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem;white-space:nowrap;">
        🖨 Print
    </a>
    <a href="{{ route('admin.work-orders.show', $invoice->work_order_id) }}"
       style="padding:.38rem .9rem;border-radius:6px;background:var(--primary);color:#fff;font-size:.83rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap;box-shadow:0 2px 6px rgba(26,60,94,.25);letter-spacing:.01em;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
        {{ $invoice->workOrder?->woLabel() ?? 'Work Order' }}
    </a>
@endpush

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.invoices.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;flex-shrink:0;">← Invoices</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        {{ $num }}
        <span style="font-size:.72rem;padding:.15rem .55rem;border-radius:999px;font-weight:700;background:{{ $badgeBg }};color:{{ $badgeColor }};">{{ $statusLabel }}</span>
    </h1>
</div>
@endpush
@endsection
