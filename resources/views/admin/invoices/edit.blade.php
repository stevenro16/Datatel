@extends('layouts.admin')
@section('title', 'Edit Invoice')

@section('content')
@php
    $num        = 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
    $customer   = $invoice->workOrder?->customer;
    $isEditable = !in_array($invoice->status, [
        \App\Models\Invoice::STATUS_PAYMENT_RECEIVED,
        \App\Models\Invoice::STATUS_COMPLETED,
        \App\Models\Invoice::STATUS_CANCELED,
    ]);

    $badgeBg    = match($invoice->status) {
        'issued'           => '#dbeafe', 'payment_received' => '#fce7f3',
        'completed'        => '#d1fae5', 'canceled'         => '#fee2e2',
        default            => '#fef3c7',
    };
    $badgeColor = match($invoice->status) {
        'issued'           => '#1e40af', 'payment_received' => '#9d174d',
        'completed'        => '#065f46', 'canceled'         => '#991b1b',
        default            => '#92400e',
    };
    $statusLabel = match($invoice->status) {
        'issued'           => 'Issued', 'payment_received' => 'Payment Received',
        'completed'        => 'Completed', 'canceled'       => 'Canceled',
        default            => 'Draft',
    };

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
        'draft'            => ['status' => 'issued',           'label' => 'Issue to Customer',     'modal' => false],
        'issued'           => ['status' => 'payment_received', 'label' => 'Mark Payment Received',  'modal' => false],
        'payment_received' => ['status' => 'completed',        'label' => 'Mark Completed',         'modal' => true],
    ];
    $next       = $nextMap[$invoice->status] ?? null;
    $isTerminal = in_array($invoice->status, ['completed', 'canceled']);
@endphp

@if($errors->any())
<div class="alert alert-error" style="margin-bottom:1.25rem;">{{ $errors->first() }}</div>
@endif

<div style="display:grid;grid-template-columns:15% 1fr 20%;gap:1rem;align-items:start;">

    {{-- ── Column 1: Company · Customer · Stats ── --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Company card --}}
        @if($company)
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Account &amp; billing info</div>
                    </div>
                </div>
                <a href="{{ route('admin.analytics.companies', ['company_id' => $company->id]) }}"
                   style="font-size:.72rem;color:rgba(255,255,255,.75);text-decoration:none;border:1px solid rgba(255,255,255,.3);padding:.2rem .55rem;border-radius:4px;white-space:nowrap;flex-shrink:0;"
                   onmouseover="this.style.background='rgba(255,255,255,.15)'" onmouseout="this.style.background=''">View ↗</a>
            </div>
            <div style="padding:1rem 1.25rem;">
                <div style="font-size:.98rem;font-weight:700;color:var(--primary);margin-bottom:.5rem;">{{ $company->name }}</div>
                @if($company->phone)
                <div style="font-size:.82rem;color:#555;margin-bottom:.2rem;">
                    <a href="tel:{{ $company->phone }}" style="color:inherit;text-decoration:none;">📞 {{ $company->phone }}</a>
                </div>
                @endif
                @if($company->email)
                <div style="font-size:.8rem;color:#888;margin-bottom:.2rem;">
                    <a href="mailto:{{ $company->email }}" style="color:inherit;text-decoration:none;">✉ {{ $company->email }}</a>
                </div>
                @endif
                @php
                    $compAddr = collect([
                        $company->address_street,
                        trim(collect([$company->address_city, $company->address_state])->filter()->join(', ')),
                        $company->address_zip,
                    ])->filter()->join(', ');
                @endphp
                @if($compAddr)
                <div style="font-size:.78rem;color:#999;line-height:1.4;">📍 {{ $compAddr }}</div>
                @endif
            </div>
        </div>
        @endif

        {{-- Customer card --}}
        @if($customer)
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Customer</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Contact details</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;">
                <div style="display:flex;align-items:flex-start;gap:.85rem;">
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1rem;color:#fff;flex-shrink:0;font-weight:700;">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div style="min-width:0;">
                        <div style="font-size:.95rem;font-weight:700;color:var(--primary);line-height:1.3;">{{ $customer->name }}</div>
                        @if($customer->title)
                        <div style="font-size:.78rem;color:#6b7280;margin-top:.1rem;">{{ $customer->title }}</div>
                        @endif
                        @if($customer->phone)
                        <div style="font-size:.8rem;color:#555;margin-top:.2rem;">
                            <a href="tel:{{ $customer->phone }}" style="color:inherit;text-decoration:none;">{{ $customer->phone }}</a>
                        </div>
                        @endif
                        @if($customer->email)
                        <div style="font-size:.78rem;color:#888;margin-top:.05rem;">
                            <a href="mailto:{{ $customer->email }}" style="color:inherit;text-decoration:none;">{{ $customer->email }}</a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Account Summary card --}}
        @if($customerStats)
        <div style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Account Summary</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing history</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.6rem;">
                <div style="display:flex;justify-content:space-between;align-items:baseline;">
                    <span style="font-size:.78rem;color:#6b7280;">Collected</span>
                    <span style="font-size:.95rem;font-weight:700;color:#16a34a;">${{ number_format($customerStats['collected'], 2) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:baseline;">
                    <span style="font-size:.78rem;color:#6b7280;">Unpaid</span>
                    <span style="font-size:.95rem;font-weight:700;color:#d97706;">${{ number_format($customerStats['unpaid'], 2) }}</span>
                </div>
                <div style="border-top:1px solid #f0f0f0;padding-top:.6rem;display:flex;flex-direction:column;gap:.45rem;">
                    <div style="display:flex;justify-content:space-between;align-items:baseline;">
                        <span style="font-size:.78rem;color:#6b7280;">Visits Billed</span>
                        <span style="font-size:.88rem;font-weight:600;color:#374151;">{{ $customerStats['visitsBilled'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:baseline;">
                        <span style="font-size:.78rem;color:#6b7280;">Completed</span>
                        <span style="font-size:.88rem;font-weight:600;color:#374151;">{{ $customerStats['completedCount'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:baseline;">
                        <span style="font-size:.78rem;color:#6b7280;">Open</span>
                        <span style="font-size:.88rem;font-weight:600;color:#374151;">{{ $customerStats['openCount'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /col-1 --}}

    {{-- ── Column 2: Invoice Edit Form ── --}}
    <form method="POST" action="{{ route('admin.invoices.update', $invoice) }}" id="edit-inv-form"
          style="display:flex;flex-direction:column;gap:1rem;">
    @csrf @method('PATCH')

        {{-- ── Section 1: Invoice Details ── --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.5rem -1.5rem 1.25rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Invoice Details</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing · Dates · Status</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                    <span style="font-size:.72rem;font-weight:700;padding:.18rem .6rem;border-radius:999px;background:{{ $badgeBg }};color:{{ $badgeColor }};">{{ $statusLabel }}</span>
                    <span style="font-size:.82rem;font-weight:700;color:rgba(255,255,255,.85);">{{ $num }}</span>
                </div>
            </div>

            {{-- Work order reference --}}
            @if($invoice->work_order_id)
            <div style="margin-bottom:1.25rem;padding:.65rem .85rem;background:#f0f7ff;border:1px solid #bfdbfe;border-radius:6px;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:.82rem;color:#374151;">Work Order</span>
                <a href="{{ route('admin.work-orders.show', $invoice->work_order_id) }}"
                   style="font-size:.82rem;font-weight:700;color:var(--accent);text-decoration:none;">
                    WO-{{ str_pad($invoice->work_order_id, 5, '0', STR_PAD_LEFT) }} ↗
                </a>
            </div>
            @endif

            {{-- Dates --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Invoice Date</label>
                    <div style="padding:.55rem .85rem;background:#f8f9fa;border:1px solid #e5e7eb;border-radius:5px;font-size:.9rem;color:#555;">
                        {{ $invoice->created_at->format('M j, Y') }}
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
            </div>
        </div>

        {{-- ── Section 2: Line Items ── --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.5rem -1.5rem 1.25rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Line Items</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Services · Parts · Labor</div>
                    </div>
                </div>
                <button type="button" onclick="addLineItem()"
                        style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);cursor:pointer;font-size:.78rem;font-weight:600;color:#fff;white-space:nowrap;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Add Line
                </button>
            </div>

            <div style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.4rem;font-size:.78rem;color:#888;font-weight:600;padding:0 .1rem;">
                <span>Description</span><span>Qty</span><span>Unit Price</span><span></span>
            </div>

            <div id="line-items">
                @foreach($invoice->lineItems->sortBy('sort_order') as $item)
                <div class="line-item" style="display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;">
                    <input type="text" name="items[{{ $loop->index }}][description]"
                           value="{{ old('items.'.$loop->index.'.description', $item->description) }}" required
                           style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    <input type="number" name="items[{{ $loop->index }}][quantity]"
                           value="{{ old('items.'.$loop->index.'.quantity', rtrim(rtrim(number_format($item->quantity,4),'0'),'.')) }}"
                           min="0" step="any" required
                           class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    <input type="number" name="items[{{ $loop->index }}][unit_price]"
                           value="{{ old('items.'.$loop->index.'.unit_price', number_format($item->unit_price,2)) }}"
                           min="0" step="0.01" required
                           class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    <button type="button" onclick="removeLine(this)"
                            style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>
                </div>
                @endforeach
            </div>

            {{-- Tax rate & live totals --}}
            <div style="border-top:1px solid #e5e7eb;padding-top:1.25rem;margin-top:.75rem;">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:.75rem;margin-bottom:.75rem;">
                    <label style="font-size:.85rem;font-weight:600;color:#444;white-space:nowrap;">Tax Rate %</label>
                    <input type="number" id="tax-rate-input" name="tax_rate_pct"
                           value="{{ old('tax_rate_pct', number_format((float)$invoice->tax_rate * 100, 4)) }}"
                           min="0" max="100" step="0.001"
                           style="width:90px;padding:.45rem .65rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;text-align:right;">
                </div>
                <div style="display:flex;flex-direction:column;gap:.35rem;align-items:flex-end;font-size:.9rem;">
                    <div style="display:flex;gap:2rem;">
                        <span style="color:#555;">Subtotal</span>
                        <span id="disp-subtotal" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
                    </div>
                    <div style="display:flex;gap:2rem;">
                        <span style="color:#555;">Tax (<span id="disp-rate-label">{{ number_format((float)$invoice->tax_rate * 100, 2) }}</span>%)</span>
                        <span id="disp-tax" style="min-width:90px;text-align:right;font-weight:500;">$0.00</span>
                    </div>
                    <div style="display:flex;gap:2rem;padding-top:.35rem;border-top:2px solid #e5e7eb;margin-top:.15rem;">
                        <span style="font-weight:700;color:var(--primary);">Total</span>
                        <span id="disp-total" style="min-width:90px;text-align:right;font-weight:700;font-size:1.05rem;color:var(--primary);">$0.00</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Terms & Notes ── --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);margin:-1.5rem -1.5rem 1.25rem;padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Terms &amp; Notes</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Payment terms · Footer</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Payment Terms</label>
                    <textarea name="payment_terms" rows="2"
                              style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('payment_terms', $invoice->payment_terms) }}</textarea>
                </div>
                <div>
                    <label style="display:block;font-size:.82rem;font-weight:600;color:#444;margin-bottom:.3rem;">Footer / Additional Notes</label>
                    <textarea name="footer_note" rows="2"
                              style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;box-sizing:border-box;">{{ old('footer_note', $invoice->footer_note) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;gap:.75rem;padding-bottom:.5rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-secondary">Cancel</a>
        </div>

    </form>{{-- /col-2 --}}

    {{-- ── Column 3: Status Lifecycle ── --}}
    <div>
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
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
                    color:{{ $isDone ? '#fff' : ($isCurr ? '#fff' : '#9ca3af') }};
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

            {{-- Transaction ref (completed) --}}
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
                        <input type="text" id="inline-txn-ref" maxlength="100" placeholder="e.g. CHK-4821"
                               style="width:100%;padding:.45rem .65rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;font-family:monospace;color:#111;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#2E86C1'" onblur="this.style.borderColor='#d1d5db'">
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
    </div>{{-- /col-3 --}}

</div>

{{-- Cancel modal --}}
<div id="cancel-modal" onclick="if(event.target===this)closeCancelModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="font-size:1rem;color:#991b1b;margin-top:0;margin-bottom:.75rem;">Cancel Invoice</h3>
        <p style="font-size:.9rem;color:#555;margin-bottom:1rem;">Please provide a reason for canceling this invoice.</p>
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
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">Go Back</button>
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
                    @foreach(['draft'=>'Draft','issued'=>'Issued','payment_received'=>'Payment Received','completed'=>'Completed','canceled'=>'Canceled'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $invoice->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div id="override-cancel-reason" style="display:none;margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Cancellation Reason <span style="color:#dc2626;">*</span></label>
                <textarea name="cancel_reason" rows="2" placeholder="Required when canceling"
                          style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div id="override-txn-ref" style="display:none;margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Transaction / Check # <span style="font-weight:400;color:#888;">(optional)</span></label>
                <input type="text" name="transaction_reference" maxlength="100" placeholder="e.g. CHK-4821"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:.4rem;color:#333;">Comment <span style="font-weight:400;color:#888;">(optional)</span></label>
                <input type="text" name="comment" maxlength="1000"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:6px;font-size:.88rem;box-sizing:border-box;">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeStatusModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
            </div>
        </form>
    </div>
</div>

{{-- Completion modal --}}
<div id="completion-modal" onclick="if(event.target===this)closeCompletionModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);padding:1.75rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="font-size:1rem;color:var(--primary);margin-top:0;margin-bottom:.75rem;">Mark Invoice Completed</h3>
        <p style="font-size:.9rem;color:#555;margin-bottom:1.25rem;">This will mark the invoice as fully completed. Would you also like to mark the attached work order as completed?</p>
        <form method="POST" action="{{ route('admin.invoices.status', $invoice) }}">
            @csrf
            <input type="hidden" name="status" value="completed">
            <label style="display:flex;align-items:center;gap:.6rem;font-size:.9rem;margin-bottom:1.25rem;cursor:pointer;color:#333;">
                <input type="checkbox" name="also_complete_work_order" value="1" style="width:15px;height:15px;cursor:pointer;">
                Also mark {{ $invoice->workOrder?->woLabel() ?? 'the work order' }} as completed
            </label>
            <div style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Transaction / Check # <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                <input type="text" name="transaction_reference" maxlength="100" placeholder="e.g. CHK-4821"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:.6rem;">
                <button type="button" onclick="closeCompletionModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Completed</button>
            </div>
        </form>
    </div>
</div>

<script>
let lineIdx = {{ $invoice->lineItems->count() }};

function fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

function recalc() {
    let subtotal = 0;
    document.querySelectorAll('#line-items .line-item').forEach(row => {
        const qty   = parseFloat(row.querySelector('.qty-input')?.value)   || 0;
        const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
        subtotal += qty * price;
    });
    const rate  = parseFloat(document.getElementById('tax-rate-input')?.value) || 0;
    const tax   = subtotal * rate / 100;
    const total = subtotal + tax;
    document.getElementById('disp-subtotal').textContent = fmt(subtotal);
    document.getElementById('disp-tax').textContent      = fmt(tax);
    document.getElementById('disp-total').textContent    = fmt(total);
    const lbl = document.getElementById('disp-rate-label');
    if (lbl) lbl.textContent = rate.toFixed(2);
}

function attachListeners(row) {
    row.querySelectorAll('.qty-input, .price-input').forEach(el => el.addEventListener('input', recalc));
}

function removeLine(btn) {
    const rows = document.querySelectorAll('#line-items .line-item');
    if (rows.length <= 1) return;
    btn.closest('.line-item').remove();
    reindex();
    recalc();
}

function reindex() {
    document.querySelectorAll('#line-items .line-item').forEach((row, i) => {
        row.querySelectorAll('input[name]').forEach(inp => {
            inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
        });
    });
}

function addLineItem() {
    const el = document.createElement('div');
    el.className = 'line-item';
    el.style.cssText = 'display:grid;grid-template-columns:3fr 1fr 1.2fr auto;gap:.5rem;margin-bottom:.5rem;';
    el.innerHTML = `
        <input type="text" name="items[${lineIdx}][description]" placeholder="Description" required
               style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <input type="number" name="items[${lineIdx}][quantity]" value="1" min="0" step="any" required
               class="qty-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <input type="number" name="items[${lineIdx}][unit_price]" value="0.00" min="0" step="0.01" required
               class="price-input" style="padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        <button type="button" onclick="removeLine(this)"
                style="background:#fee2e2;color:#991b1b;border:none;border-radius:5px;padding:.5rem .6rem;cursor:pointer;font-size:1rem;line-height:1;">✕</button>`;
    document.getElementById('line-items').appendChild(el);
    attachListeners(el);
    lineIdx++;
    recalc();
}

document.querySelectorAll('#line-items .line-item').forEach(attachListeners);
document.getElementById('tax-rate-input')?.addEventListener('input', recalc);
recalc();

// Modal handlers
function openCancelModal()      { document.getElementById('cancel-modal').style.display = 'flex'; }
function closeCancelModal()     { document.getElementById('cancel-modal').style.display = 'none'; }
function openStatusModal()      { document.getElementById('status-modal').style.display = 'flex'; }
function closeStatusModal()     { document.getElementById('status-modal').style.display = 'none'; }
function openCompletionModal()  { document.getElementById('completion-modal').style.display = 'flex'; }
function closeCompletionModal() { document.getElementById('completion-modal').style.display = 'none'; }

function syncAndOpenCompletionModal() {
    const inlineField = document.getElementById('inline-txn-ref');
    const modalField  = document.querySelector('#completion-modal input[name="transaction_reference"]');
    if (inlineField && modalField) modalField.value = inlineField.value;
    openCompletionModal();
}

function toggleOverrideCancelReason(val) {
    const cancelEl  = document.getElementById('override-cancel-reason');
    const textarea  = cancelEl.querySelector('textarea');
    const isCanceled = val === 'canceled';
    cancelEl.style.display = isCanceled ? 'block' : 'none';
    textarea.required = isCanceled;
    document.getElementById('override-txn-ref').style.display = val === 'completed' ? 'block' : 'none';
}
toggleOverrideCancelReason(document.getElementById('override-status')?.value ?? '');

document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    closeCancelModal(); closeStatusModal(); closeCompletionModal();
});
</script>

@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.invoices.show', $invoice) }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← {{ $num }}</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">Edit Invoice</h1>
</div>
@endpush
