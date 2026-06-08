@extends('layouts.employee')
@section('title', $workOrder->woLabel())

@php
    $canComplete = !in_array($workOrder->status, [
        'services_performed','invoice_prepared','billed','completed','canceled'
    ]);
    $isSigned = (bool) $workOrder->completionSignature;

    $photos = $workOrder->attachments->filter(fn($a) => str_starts_with($a->mime_type, 'image/'));
    $docs   = $workOrder->attachments->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'));
    $previewable = ['application/pdf', 'text/plain'];

    $urgencyBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
    $urgencyColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';
@endphp

@section('content')

{{-- Header --}}
<div style="margin-bottom:1.5rem;">
    <a href="{{ route('employee.calendar') }}" style="color:var(--accent);text-decoration:none;font-size:.9rem;">← Calendar</a>
    <div style="display:flex;align-items:center;gap:.75rem;margin-top:.4rem;flex-wrap:wrap;">
        <h1 class="page-title" style="margin:0;">{{ $workOrder->woLabel() }}</h1>
        <span style="padding:.25rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700;background:{{ $urgencyBg }};color:{{ $urgencyColor }};">{{ ucfirst($workOrder->urgency) }}</span>
        <span class="badge badge-{{ $workOrder->status }}">{{ str_replace('_',' ',$workOrder->status) }}</span>
        <div style="margin-left:auto;">
            @if($canComplete)
            <button type="button" onclick="openSignatureModal()"
                    style="padding:.5rem 1.25rem;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;">
                ✓ Mark Services Performed
            </button>
            @elseif($isSigned)
            <span style="padding:.45rem 1rem;background:#d1fae5;color:#065f46;border-radius:6px;font-size:.85rem;font-weight:600;">
                ✓ Completed &amp; Signed
            </span>
            @endif
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .wo-grid { grid-template-columns: 1fr !important; }
}
</style>

<div class="wo-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

    {{-- ── Left column ── --}}
    <div>

        {{-- Customer --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 1rem;">Customer</h3>
            <div style="display:flex;align-items:center;gap:1rem;">
                <div style="width:42px;height:42px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0;font-weight:700;">
                    {{ strtoupper(substr($workOrder->customer->name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div style="font-size:.98rem;font-weight:600;color:var(--primary);">
                        {{ $workOrder->customer->name }}
                        @if($workOrder->customer->title)
                        <span style="font-size:.78rem;font-weight:400;color:#6b7280;margin-left:.35rem;">{{ $workOrder->customer->title }}</span>
                        @endif
                    </div>
                    @if($workOrder->customer->phone)
                    <div style="font-size:.82rem;color:#555;margin-top:.15rem;">
                        <a href="tel:{{ $workOrder->customer->phone }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->phone }}</a>
                    </div>
                    @endif
                    @if($workOrder->customer->email)
                    <div style="font-size:.8rem;color:#888;margin-top:.05rem;">
                        <a href="mailto:{{ $workOrder->customer->email }}" style="color:inherit;text-decoration:none;">{{ $workOrder->customer->email }}</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Work Order Details --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h3 style="font-size:.95rem;color:var(--primary);margin:0;">Work Order Details</h3>
                <button type="button" onclick="toggleEmpDetails()" title="Collapse / Expand"
                        style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;
                               border:1px solid #d1d5db;border-radius:4px;background:#f9fafb;
                               color:#9ca3af;font-size:.65rem;cursor:pointer;padding:0;flex-shrink:0;">
                    <span id="emp-details-chevron" style="display:inline-block;transition:transform .28s ease;transform:rotate(0deg);">▲</span>
                </button>
            </div>

            {{-- Condensed summary — always visible, serves as the collapsed state --}}
            <div id="emp-details-summary" style="padding-bottom:.85rem;border-bottom:1px solid #f0f0f0;margin-bottom:.5rem;">
                @if($workOrder->description)
                <p style="font-size:.88rem;color:#555;line-height:1.45;margin:0 0 .5rem;
                           overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $workOrder->description }}</p>
                @endif
                @if($workOrder->equipment_details)
                <p style="font-size:.8rem;color:#6b7280;line-height:1.4;margin:0 0 .5rem;
                           overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;
                           background:#f8f9fa;border-left:3px solid var(--primary);padding:.35rem .7rem;border-radius:0 4px 4px 0;">{{ $workOrder->equipment_details }}</p>
                @endif
                @if($workOrder->serviceTypes->count())
                <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;margin-bottom:.5rem;">
                    <span style="font-size:.72rem;color:#999;margin-right:.05rem;">Services:</span>
                    @foreach($workOrder->serviceTypes as $svc)
                    <span style="background:#f0f6ff;color:var(--accent);padding:.12em .55em;border-radius:999px;font-size:.75rem;font-weight:600;">{{ $svc->name }}</span>
                    @endforeach
                </div>
                @endif
                @php $empHasMeta = $workOrder->site_street || $workOrder->site_contact_name || $workOrder->site_contact_phone || $workOrder->preferred_date; @endphp
                @if($empHasMeta)
                <div style="display:flex;flex-wrap:wrap;gap:.5rem .85rem;font-size:.82rem;color:#555;">
                    @if($workOrder->site_street)
                    <span style="display:flex;align-items:center;gap:.3rem;"><span style="color:#aaa;font-size:.78rem;">📍</span>{{ $workOrder->site_street }}</span>
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
                        <span style="color:#2563eb;font-weight:600;">{{ $workOrder->preferred_date->format('M j, Y') }}</span>
                    </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Expanded full-details body --}}
            <div id="emp-details-body" style="display:none;">

                <div style="border-bottom:1px solid #f0f0f0;padding-bottom:.85rem;margin-bottom:.85rem;display:flex;flex-wrap:wrap;gap:.35rem .1rem;font-size:.83rem;color:#555;">
                    <span style="color:#999;margin-right:.25rem;">Services:</span>
                    @forelse($workOrder->serviceTypes as $svc)
                        <span style="background:#f0f6ff;color:var(--accent);padding:.15em .6em;border-radius:999px;font-size:.78rem;font-weight:600;">{{ $svc->name }}</span>
                    @empty
                        <span style="color:#bbb;">None specified</span>
                    @endforelse
                </div>

                <p style="color:#555;font-size:.92rem;line-height:1.55;margin-bottom:1.1rem;">{{ $workOrder->description ?: '—' }}</p>

                @if($workOrder->equipment_details)
                <div style="background:#f8f9fa;border-left:3px solid var(--primary);padding:.6rem .85rem;border-radius:0 5px 5px 0;font-size:.85rem;color:#444;white-space:pre-wrap;margin-bottom:.85rem;">{{ $workOrder->equipment_details }}</div>
                @endif

                <div style="font-size:.78rem;color:#aaa;margin-bottom:.6rem;">
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
                        <div style="font-size:.92rem;font-weight:600;color:#1e293b;">{{ $workOrder->preferred_date->format('l, F j, Y') }}</div>
                    </div>
                    @endif
                </div>
                @endif

                @if($workOrder->preferred_availability)
                @php
                    $empAvailDays  = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday'];
                    $empAvailSlots = ['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']];
                @endphp
                <div style="margin-top:.75rem;padding:.75rem 1rem;background:#f0f6ff;border-radius:6px;border:1px solid #bfdbfe;">
                    <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.55rem;">Customer Preferred Availability</div>
                    @foreach($empAvailDays as $dayKey => $dayName)
                        @if(!empty($workOrder->preferred_availability[$dayKey]))
                        <div style="display:flex;align-items:center;gap:.45rem;margin-bottom:.35rem;flex-wrap:wrap;">
                            <span style="font-size:.82rem;font-weight:700;color:var(--primary);min-width:90px;text-align:right;">{{ $dayName }}:</span>
                            @foreach($empAvailSlots as $slot => $slotData)
                            @php $slotActive = in_array($slot, $workOrder->preferred_availability[$dayKey]); @endphp
                            <span style="display:inline-flex;flex-direction:column;align-items:center;padding:.2rem .6rem;border-radius:6px;
                                         border:1.5px solid {{ $slotActive ? '#86efac' : '#e5e7eb' }};
                                         background:{{ $slotActive ? '#dcfce7' : '#f9fafb' }};min-width:92px;text-align:center;">
                                <span style="font-size:.72rem;font-weight:700;color:{{ $slotActive ? '#15803d' : '#9ca3af' }};line-height:1.3;">{{ $slotData[0] }}</span>
                                <span style="font-size:.62rem;color:{{ $slotActive ? '#16a34a' : '#d1d5db' }};line-height:1.2;">{{ $slotData[1] }}</span>
                            </span>
                            @endforeach
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif


            </div>{{-- /emp-details-body --}}

        </div>

        {{-- Attachments --}}
        @if($workOrder->attachments->count())
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 1rem;">
                Attachments <span style="font-size:.8rem;font-weight:400;color:#9ca3af;">({{ $workOrder->attachments->count() }})</span>
            </h3>
            @if($photos->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.75rem;{{ $docs->count() ? 'margin-bottom:1rem;' : '' }}">
                @foreach($photos as $a)
                <div style="cursor:zoom-in;"
                     onclick="openLightbox('{{ route('attachments.view', $a) }}','{{ addslashes($a->original_name) }}','{{ route('attachments.download', $a) }}')">
                    <img src="{{ route('attachments.view', $a) }}" alt="{{ $a->original_name }}"
                         style="width:100%;height:110px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;display:block;transition:opacity .15s;"
                         onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
                    <div style="font-size:.7rem;color:#555;margin-top:.3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $a->original_name }}">{{ $a->original_name }}</div>
                    <div style="font-size:.68rem;color:#aaa;">{{ round($a->size_bytes/1024) }} KB</div>
                </div>
                @endforeach
            </div>
            @endif
            @if($docs->count())
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                @foreach($docs as $a)
                <div style="display:flex;align-items:center;gap:.5rem;background:#f8f9fa;padding:.5rem .8rem;border-radius:5px;font-size:.85rem;border:1px solid #e5e7eb;">
                    <span>📄</span>
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $a->original_name }}">{{ $a->original_name }}</span>
                    <span style="color:#aaa;font-size:.75rem;flex-shrink:0;">({{ round($a->size_bytes/1024) }} KB)</span>
                    @if(in_array($a->mime_type, $previewable))
                    <button type="button"
                            onclick="openFilePreview('{{ route('attachments.view', $a) }}','{{ addslashes($a->original_name) }}','{{ route('attachments.download', $a) }}')"
                            style="padding:.2rem .6rem;border:1px solid var(--accent);border-radius:4px;background:#fff;color:var(--accent);font-size:.78rem;cursor:pointer;">
                        Preview
                    </button>
                    @endif
                    <a href="{{ route('attachments.download', $a) }}"
                       style="padding:.2rem .6rem;border:1px solid #d1d5db;border-radius:4px;background:#fff;color:#555;font-size:.78rem;text-decoration:none;">
                        Download
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- Notes --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <h3 style="font-size:.95rem;color:var(--primary);margin-bottom:1rem;">Notes</h3>

            {{-- Add note form --}}
            <form method="POST" action="{{ route('employee.work-orders.notes.store', $workOrder) }}" style="margin-bottom:1.25rem;">
                @csrf
                <textarea name="body" rows="3" required
                          placeholder="Add a note…"
                          style="width:100%;padding:.6rem .8rem;border:1px solid #d1d5db;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;font-family:inherit;line-height:1.5;">{{ old('body') }}</textarea>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;gap:.65rem;flex-wrap:wrap;">
                    <div style="display:flex;gap:0;border:1px solid #d1d5db;border-radius:6px;overflow:hidden;flex-shrink:0;">
                        <label style="display:flex;align-items:center;gap:.35rem;padding:.32rem .75rem;cursor:pointer;font-size:.82rem;font-weight:600;
                                      background:{{ old('visibility','customer')==='customer' ? 'var(--accent)' : '#f9fafb' }};
                                      color:{{ old('visibility','customer')==='customer' ? '#fff' : '#555' }};">
                            <input type="radio" name="visibility" value="customer"
                                   {{ old('visibility','customer')==='customer' ? 'checked' : '' }}
                                   style="display:none;"
                                   onchange="updateVisToggle(this)">
                            👤 Customer
                        </label>
                        <label style="display:flex;align-items:center;gap:.35rem;padding:.32rem .75rem;cursor:pointer;font-size:.82rem;font-weight:600;border-left:1px solid #d1d5db;
                                      background:{{ old('visibility')==='internal' ? '#f59e0b' : '#f9fafb' }};
                                      color:{{ old('visibility')==='internal' ? '#fff' : '#555' }};">
                            <input type="radio" name="visibility" value="internal"
                                   {{ old('visibility')==='internal' ? 'checked' : '' }}
                                   style="display:none;"
                                   onchange="updateVisToggle(this)">
                            🔒 Internal
                        </label>
                    </div>
                    <button type="submit"
                            style="padding:.38rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                        + Add Note
                    </button>
                </div>
            </form>

            {{-- Existing notes --}}
            @forelse($workOrder->notes->sortByDesc('created_at') as $note)
            @php
                $noteAuthorName = $note->author?->name ?? 'Unknown';
                $noteInitial    = strtoupper(substr($noteAuthorName, 0, 1));
                $noteHasPhoto   = $note->author?->profile_photo
                               && file_exists(storage_path('app/profile-photos/' . $note->author->profile_photo));
                $noteIsInternal = $note->visibility === 'internal';
            @endphp
            <div style="display:flex;gap:.6rem;align-items:flex-start;margin-bottom:.75rem;">
                @if($noteHasPhoto)
                <img src="{{ route('users.photo', $note->author) }}" alt="{{ $noteAuthorName }}"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover;flex-shrink:0;margin-top:.1rem;">
                @else
                <div style="width:28px;height:28px;border-radius:50%;background:{{ $noteIsInternal ? '#f59e0b' : 'var(--accent)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.1rem;">
                    <span style="font-size:.68rem;font-weight:700;color:#fff;line-height:1;">{{ $noteInitial }}</span>
                </div>
                @endif
                <div style="flex:1;min-width:0;padding:.65rem .75rem;
                            border-left:3px solid {{ $noteIsInternal ? '#fbbf24' : 'var(--accent)' }};
                            background:{{ $noteIsInternal ? '#fefce8' : '#f0f6ff' }};
                            border-radius:0 5px 5px 0;">
                    <div style="font-size:.78rem;color:#888;margin-bottom:.25rem;">
                        {{ $noteAuthorName }} · {{ $note->created_at->format('M j, Y g:i A') }}
                        @if($noteIsInternal)
                        <span style="color:#92400e;font-weight:600;background:#fef3c7;padding:.1rem .4rem;border-radius:3px;margin-left:.35rem;font-size:.72rem;">INTERNAL</span>
                        @endif
                    </div>
                    <div style="font-size:.9rem;color:#333;">{{ $note->body }}</div>
                </div>
            </div>
            @empty
            <p style="color:#bbb;font-size:.88rem;margin:0;">No notes yet.</p>
            @endforelse
        </div>

    </div>

    {{-- ── Right sidebar ── --}}
    <div>

        {{-- Site Time — arrival & departure with manual override --}}
        <div style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.85rem;">⏱ Site Time</div>

            {{-- ARRIVAL --}}
            @if($timeEntry && $timeEntry->clocked_in_at)
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.5rem;">
                <div>
                    <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.15rem;">Arrived</div>
                    <div style="font-size:1rem;font-weight:700;color:#16a34a;">✓ {{ $timeEntry->clocked_in_at->format('g:i A') }}</div>
                    <div style="font-size:.75rem;color:#9ca3af;">{{ $timeEntry->clocked_in_at->format('M j, Y') }}</div>
                </div>
                <button onclick="this.parentElement.nextElementSibling.style.display=this.parentElement.nextElementSibling.style.display==='none'?'block':'none'"
                        style="padding:.25rem .55rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#555;font-size:.78rem;cursor:pointer;flex-shrink:0;margin-top:.1rem;">✎</button>
            </div>
            <div style="display:none;margin-bottom:.75rem;">
                <form method="POST" action="{{ route('employee.work-orders.time.arrive', $workOrder) }}">
                    @csrf
                    <input type="hidden" name="clocked_in_at" id="arrive-dt">
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        <input type="datetime-local" id="arrive-manual"
                               value="{{ $timeEntry->clocked_in_at->format('Y-m-d\TH:i') }}"
                               style="flex:1;padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;">
                        <button type="button" onclick="recordManual('arrive')"
                                style="padding:.4rem .65rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#374151;font-size:.8rem;cursor:pointer;white-space:nowrap;">Save</button>
                    </div>
                </form>
            </div>
            @else
            <form method="POST" action="{{ route('employee.work-orders.time.arrive', $workOrder) }}">
                @csrf
                <input type="hidden" name="clocked_in_at" id="arrive-dt">
                <button type="button" onclick="recordNow('arrive')"
                        style="width:100%;padding:.6rem 1rem;background:#0369a1;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;margin-bottom:.65rem;">
                    📍 Record Arrival Now
                </button>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <input type="datetime-local" id="arrive-manual"
                           style="flex:1;padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;">
                    <button type="button" onclick="recordManual('arrive')"
                            style="padding:.4rem .65rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#374151;font-size:.8rem;cursor:pointer;white-space:nowrap;">Save</button>
                </div>
            </form>
            @endif

            {{-- DEPARTURE (only shown once arrival is recorded) --}}
            @if($timeEntry && $timeEntry->clocked_in_at)
            <hr style="border:none;border-top:1px solid #e5e7eb;margin:.85rem 0;">

            @if($timeEntry->clocked_out_at)
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:.5rem;">
                <div>
                    <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.15rem;">Departed</div>
                    <div style="font-size:1rem;font-weight:700;color:#dc2626;">✓ {{ $timeEntry->clocked_out_at->format('g:i A') }}</div>
                    <div style="font-size:.75rem;color:#9ca3af;">{{ $timeEntry->clocked_out_at->format('M j, Y') }}</div>
                </div>
                <button onclick="this.parentElement.nextElementSibling.style.display=this.parentElement.nextElementSibling.style.display==='none'?'block':'none'"
                        style="padding:.25rem .55rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#555;font-size:.78rem;cursor:pointer;flex-shrink:0;margin-top:.1rem;">✎</button>
            </div>
            <div style="display:none;margin-bottom:.75rem;">
                <form method="POST" action="{{ route('employee.work-orders.time.depart', $workOrder) }}">
                    @csrf
                    <input type="hidden" name="clocked_out_at" id="depart-dt">
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        <input type="datetime-local" id="depart-manual"
                               value="{{ $timeEntry->clocked_out_at->format('Y-m-d\TH:i') }}"
                               style="flex:1;padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;">
                        <button type="button" onclick="recordManual('depart')"
                                style="padding:.4rem .65rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#374151;font-size:.8rem;cursor:pointer;white-space:nowrap;">Save</button>
                    </div>
                </form>
            </div>
            @php $mins = $timeEntry->totalMinutes(); @endphp
            @if($mins !== null)
            <div style="text-align:center;padding:.55rem;background:#f8f9fa;border-radius:6px;border:1px solid #e5e7eb;">
                <div style="font-size:.65rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Total On-Site</div>
                <div style="font-size:1.15rem;font-weight:700;color:var(--primary);margin-top:.15rem;">
                    {{ $mins >= 60 ? floor($mins/60).'h'.($mins%60 ? ' '.($mins%60).'m' : '') : $mins.'m' }}
                </div>
            </div>
            @endif
            @else
            <form method="POST" action="{{ route('employee.work-orders.time.depart', $workOrder) }}">
                @csrf
                <input type="hidden" name="clocked_out_at" id="depart-dt">
                <button type="button" onclick="recordNow('depart')"
                        style="width:100%;padding:.6rem 1rem;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;margin-bottom:.65rem;">
                    🚗 Record Departure Now
                </button>
                <div style="display:flex;gap:.4rem;align-items:center;">
                    <input type="datetime-local" id="depart-manual"
                           style="flex:1;padding:.4rem .55rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;">
                    <button type="button" onclick="recordManual('depart')"
                            style="padding:.4rem .65rem;border:1px solid #d1d5db;border-radius:5px;background:#f8fafc;color:#374151;font-size:.8rem;cursor:pointer;white-space:nowrap;">Save</button>
                </div>
            </form>
            @endif
            @endif
        </div>

        {{-- Scheduled visit --}}
        @if($workOrder->scheduled_at)
        <div style="background:#f0f7ff;border:1px solid #d0d5dd;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="font-size:.68rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.65rem;">📅 Scheduled Visit</div>
            <div style="font-size:1.2rem;font-weight:700;color:var(--primary);margin-bottom:.2rem;">
                {{ $workOrder->scheduled_at->format('l, F j, Y') }}
            </div>
            @if($workOrder->scheduled_at->format('H:i') !== '00:00')
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.75rem;">
                <span style="font-size:1rem;color:var(--accent);font-weight:600;">{{ $workOrder->scheduled_at->format('g:i A') }}</span>
                @if($workOrder->duration_estimate_minutes)
                <span style="font-size:.82rem;color:#555;background:#e0f2fe;padding:.15em .6em;border-radius:999px;">{{ $workOrder->duration_estimate_minutes >= 60 ? floor($workOrder->duration_estimate_minutes/60).'h'.($workOrder->duration_estimate_minutes%60 ? ' '.($workOrder->duration_estimate_minutes%60).'m' : '') : $workOrder->duration_estimate_minutes.'m' }} est.</span>
                @endif
            </div>
            @else
            <div style="margin-bottom:.75rem;"></div>
            @endif
            @if($workOrder->site_street)
            <div style="font-size:.83rem;margin-bottom:.5rem;">
                <div style="font-size:.68rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.15rem;">📍 Location</div>
                <div style="color:#333;">{{ $workOrder->site_street }}</div>
            </div>
            @endif
            @if($workOrder->site_contact_name || $workOrder->site_contact_phone)
            <div style="font-size:.83rem;">
                <div style="font-size:.68rem;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.15rem;">👤 Site Contact</div>
                @if($workOrder->site_contact_name)
                <div style="color:#333;">{{ $workOrder->site_contact_name }}</div>
                @endif
                @if($workOrder->site_contact_phone)
                <a href="tel:{{ $workOrder->site_contact_phone }}" style="color:var(--accent);text-decoration:none;display:block;margin-top:.1rem;">{{ $workOrder->site_contact_phone }}</a>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- Completion signature --}}
        @if($isSigned)
        @php $sig = $workOrder->completionSignature; @endphp
        <div style="background:#f0fdf4;border:1px solid #d0d5dd;border-radius:8px;padding:1.1rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <p style="font-size:.72rem;font-weight:700;color:#166634;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">✓ Services Performed</p>
            <p style="font-size:.85rem;color:#166534;margin-bottom:.3rem;font-weight:600;">{{ $sig->signer_name }}</p>
            <p style="font-size:.78rem;color:#15803d;margin-bottom:.5rem;">{{ $sig->signed_at->format('M j, Y g:i A') }}</p>
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(storage_path('app/signatures/work-orders/'.$sig->signature_path))) }}"
                 alt="Customer signature"
                 style="max-width:100%;border:1px solid #bbf7d0;border-radius:4px;background:#fff;display:block;">
        </div>
        @endif

        {{-- Team --}}
        @if($workOrder->assignments->count() > 1)
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 .75rem;">Team</h3>
            @foreach($workOrder->assignments as $a)
            <div style="display:flex;align-items:center;gap:.65rem;{{ !$loop->last ? 'margin-bottom:.65rem;padding-bottom:.65rem;border-bottom:1px solid #f0f0f0;' : '' }}">
                @if($a->employee->profile_photo)
                <img src="{{ route('users.photo', $a->employee) }}" alt="{{ $a->employee->name }}"
                     style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:1px solid #d1d5db;flex-shrink:0;">
                @else
                <div style="width:30px;height:30px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;">
                    {{ strtoupper(substr($a->employee->name,0,1)) }}
                </div>
                @endif
                <span style="font-size:.85rem;color:#333;">{{ $a->employee->name }}{{ $a->employee->id === auth()->id() ? ' <span style="color:#888;font-size:.78rem;">(you)</span>' : '' }}</span>
            </div>
            @endforeach
        </div>
        @endif

    </div>

</div>

{{-- ═══════════════════════════════════════════════
     SIGNATURE MODAL
═══════════════════════════════════════════════ --}}
@if($canComplete)
<div id="sig-modal" onclick="if(event.target===this)closeSignatureModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.22);width:100%;max-width:520px;overflow:hidden;">

        {{-- Modal header --}}
        <div style="background:var(--primary);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="color:#fff;margin:0;font-size:1rem;">Customer Signature Required</h3>
            <button type="button" onclick="closeSignatureModal()"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:5px;padding:.25rem .65rem;cursor:pointer;font-size:.9rem;">✕</button>
        </div>

        {{-- Modal body --}}
        <form method="POST" action="{{ route('employee.work-orders.complete', $workOrder) }}" id="sig-form">
            @csrf
            <input type="hidden" name="signature_data" id="signature_data">

            <div style="padding:1.5rem;">
                <p style="font-size:.88rem;color:#555;margin-bottom:1.25rem;">
                    Please have the on-site contact sign below to confirm that work has been completed to their satisfaction.
                </p>

                @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif

                <div style="margin-bottom:1.1rem;">
                    <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Printed Name *</label>
                    <input type="text" name="signer_name" id="signer_name"
                           value="{{ old('signer_name', $workOrder->site_contact_name) }}"
                           placeholder="Full name of person signing" required
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>

                <div>
                    <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Signature *</label>
                    <div style="position:relative;border:2px solid #ccc;border-radius:6px;background:#fafafa;touch-action:none;">
                        <canvas id="sig-canvas" width="472" height="180"
                                style="display:block;width:100%;height:180px;cursor:crosshair;border-radius:4px;"></canvas>
                        <span style="position:absolute;bottom:6px;left:50%;transform:translateX(-50%);font-size:.72rem;color:#ccc;pointer-events:none;white-space:nowrap;">
                            Sign above
                        </span>
                    </div>
                    <button type="button" onclick="clearSignature()"
                            style="margin-top:.45rem;font-size:.78rem;color:#888;border:none;background:none;cursor:pointer;padding:0;text-decoration:underline;">
                        Clear signature
                    </button>
                </div>
            </div>

            {{-- Modal footer --}}
            <div style="padding:1rem 1.5rem;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;gap:.75rem;">
                <button type="button" onclick="closeSignatureModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitSignature()"
                        style="padding:.5rem 1.4rem;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    Submit &amp; Complete
                </button>
            </div>
        </form>

    </div>
</div>
@endif

{{-- File preview modal --}}
<div id="file-preview-modal" onclick="if(event.target===this)closeFilePreview()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9998;flex-direction:column;">
    <div style="display:flex;align-items:center;gap:.75rem;background:#1e293b;padding:.65rem 1rem;flex-shrink:0;">
        <span id="fp-name" style="color:#e2e8f0;font-size:.88rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
        <a id="fp-download" href="#" download style="padding:.3rem .8rem;border:1px solid #475569;border-radius:5px;color:#cbd5e1;font-size:.82rem;text-decoration:none;flex-shrink:0;">Download</a>
        <button onclick="closeFilePreview()" style="padding:.3rem .8rem;border:1px solid #475569;border-radius:5px;background:transparent;color:#cbd5e1;font-size:.82rem;cursor:pointer;flex-shrink:0;">Close</button>
    </div>
    <iframe id="fp-frame" src="" style="flex:1;border:none;background:#fff;"></iframe>
</div>

{{-- Lightbox --}}
<div id="lightbox" onclick="if(event.target===this)closeLightbox()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.82);z-index:9999;align-items:center;justify-content:center;flex-direction:column;padding:1.5rem;">
    <div style="position:relative;max-width:90vw;max-height:82vh;">
        <img id="lightbox-img" src="" alt="" style="max-width:100%;max-height:82vh;border-radius:8px;display:block;">
    </div>
    <div style="display:flex;align-items:center;gap:1rem;margin-top:1rem;">
        <span id="lightbox-name" style="color:#ddd;font-size:.88rem;"></span>
        <a id="lightbox-download" href="#" download style="color:#fff;background:rgba(255,255,255,.15);padding:.35rem .9rem;border-radius:5px;text-decoration:none;font-size:.83rem;border:1px solid rgba(255,255,255,.3);">Download</a>
        <button onclick="closeLightbox()" style="color:#fff;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);padding:.35rem .9rem;border-radius:5px;font-size:.83rem;cursor:pointer;">Close</button>
    </div>
</div>

<script>
// ── Site time recording ───────────────────────────────────────────
function recordNow(type) {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const dtStr = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate())
                + 'T' + pad(now.getHours()) + ':' + pad(now.getMinutes());
    const hidden = document.getElementById(type + '-dt');
    hidden.value = dtStr;
    hidden.closest('form').submit();
}

function recordManual(type) {
    const manual = document.getElementById(type + '-manual');
    if (!manual.value) { alert('Please select a date and time.'); return; }
    const hidden = document.getElementById(type + '-dt');
    hidden.value = manual.value;
    hidden.closest('form').submit();
}

// ── Signature canvas ──────────────────────────────────────────────
@if($canComplete)
const canvas  = document.getElementById('sig-canvas');
const ctx     = canvas ? canvas.getContext('2d') : null;
let drawing   = false;

function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const scaleX = canvas.width  / r.width;
    const scaleY = canvas.height / r.height;
    const src = e.touches ? e.touches[0] : e;
    return {
        x: (src.clientX - r.left) * scaleX,
        y: (src.clientY - r.top)  * scaleY,
    };
}

if (canvas) {
    ctx.strokeStyle = '#1a1a2e';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';

    canvas.addEventListener('mousedown',  e => { drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); });
    canvas.addEventListener('mousemove',  e => { if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => drawing = false);
    canvas.addEventListener('mouseleave', () => drawing = false);

    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; ctx.beginPath(); const p = getPos(e); ctx.moveTo(p.x, p.y); }, { passive: false });
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); if (!drawing) return; const p = getPos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); }, { passive: false });
    canvas.addEventListener('touchend',   () => drawing = false);
}

function clearSignature() {
    if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function isCanvasBlank() {
    if (!canvas) return true;
    const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
    return !data.some(ch => ch !== 0);
}

function openSignatureModal() {
    clearSignature();
    document.getElementById('sig-modal').style.display = 'flex';
    document.addEventListener('keydown', sigKeyHandler);
}
function closeSignatureModal() {
    document.getElementById('sig-modal').style.display = 'none';
    document.removeEventListener('keydown', sigKeyHandler);
}
function sigKeyHandler(e) { if (e.key === 'Escape') closeSignatureModal(); }

function submitSignature() {
    const name = document.getElementById('signer_name').value.trim();
    if (!name) { alert('Please enter the signer\'s printed name.'); return; }
    if (isCanvasBlank()) { alert('Please provide a signature before submitting.'); return; }
    document.getElementById('signature_data').value = canvas.toDataURL('image/png');
    document.getElementById('sig-form').submit();
}

// Auto-open if errors returned
@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openSignatureModal());
@endif
@endif

// ── File preview ────────────────────────────────────────────────
function openFilePreview(viewUrl, name, downloadUrl) {
    document.getElementById('fp-frame').src  = viewUrl;
    document.getElementById('fp-name').textContent = name;
    document.getElementById('fp-download').href    = downloadUrl;
    document.getElementById('file-preview-modal').style.display = 'flex';
    document.addEventListener('keydown', fpKeyHandler);
}
function closeFilePreview() {
    document.getElementById('file-preview-modal').style.display = 'none';
    document.getElementById('fp-frame').src = '';
    document.removeEventListener('keydown', fpKeyHandler);
}
function fpKeyHandler(e) { if (e.key === 'Escape') closeFilePreview(); }

// ── Lightbox ────────────────────────────────────────────────────
function openLightbox(viewUrl, name, downloadUrl) {
    document.getElementById('lightbox-img').src  = viewUrl;
    document.getElementById('lightbox-name').textContent = name;
    document.getElementById('lightbox-download').href    = downloadUrl;
    document.getElementById('lightbox').style.display = 'flex';
    document.addEventListener('keydown', lbKeyHandler);
}
function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.getElementById('lightbox-img').src = '';
    document.removeEventListener('keydown', lbKeyHandler);
}
function lbKeyHandler(e) { if (e.key === 'Escape') closeLightbox(); }

// ── Details collapse/expand ──────────────────────────────────────
function toggleEmpDetails() {
    const body    = document.getElementById('emp-details-body');
    const chevron = document.getElementById('emp-details-chevron');
    const hidden  = body.style.display === 'none';
    body.style.display      = hidden ? 'block' : 'none';
    chevron.style.transform = hidden ? 'rotate(180deg)' : 'rotate(0deg)';
}

// ── Note visibility toggle ───────────────────────────────────────
function updateVisToggle(radio) {
    const labels = radio.closest('div').querySelectorAll('label');
    labels.forEach(label => {
        const r = label.querySelector('input[type=radio]');
        const isInternal = r.value === 'internal';
        const isSelected = r.checked;
        label.style.background = isSelected ? (isInternal ? '#f59e0b' : 'var(--accent)') : '#f9fafb';
        label.style.color      = isSelected ? '#fff' : '#555';
    });
}
</script>

@endsection
