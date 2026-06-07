@extends('layouts.employee')
@section('title', $workOrder->woLabel() . ' — Visit ' . $visit->scheduled_at->format('M j, Y'))

@php
    $canComplete = !$visit->signature;
    $isSigned    = (bool) $visit->signature;

    $urgencyBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
    $urgencyColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';

    $visitTechs = $visit->techs->map(fn($t) => $t->user)->filter();
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
                ✓ Mark Visit Complete
            </button>
            @elseif($isSigned)
            <span style="padding:.45rem 1rem;background:#d1fae5;color:#065f46;border-radius:6px;font-size:.85rem;font-weight:600;">
                ✓ Visit Signed &amp; Complete
            </span>
            @endif
        </div>
    </div>
    <div style="margin-top:.3rem;font-size:.88rem;color:#64748b;">
        Visit: {{ $visit->scheduled_at->format('l, F j, Y \a\t g:i A') }}
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

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

        {{-- Work Order Details (condensed) --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 1rem;">Work Order Details</h3>

            @if($workOrder->serviceTypes->count())
            <div style="display:flex;flex-wrap:wrap;gap:.3rem;align-items:center;margin-bottom:.75rem;">
                <span style="font-size:.72rem;color:#999;margin-right:.05rem;">Services:</span>
                @foreach($workOrder->serviceTypes as $svc)
                <span style="background:#f0f6ff;color:var(--accent);padding:.12em .55em;border-radius:999px;font-size:.75rem;font-weight:600;">{{ $svc->name }}</span>
                @endforeach
            </div>
            @endif

            @if($workOrder->description)
            <p style="font-size:.88rem;color:#555;line-height:1.45;margin:0 0 .75rem;">{{ $workOrder->description }}</p>
            @endif

            @if($workOrder->equipment_details)
            <div style="background:#f8f9fa;border-left:3px solid var(--primary);padding:.5rem .75rem;border-radius:0 4px 4px 0;font-size:.83rem;color:#444;white-space:pre-wrap;margin-bottom:.75rem;">{{ $workOrder->equipment_details }}</div>
            @endif

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
            </div>

            @if($visit->notes)
            <div style="margin-top:.85rem;padding:.6rem .85rem;background:#f0f7ff;border-left:3px solid var(--accent);border-radius:0 5px 5px 0;font-size:.85rem;color:#1e40af;">
                <div style="font-size:.68rem;font-weight:700;color:#3b82f6;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Visit Notes</div>
                {{ $visit->notes }}
            </div>
            @endif
        </div>

        {{-- Notes --}}
        <div style="background:#fff;padding:1.5rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <h3 style="font-size:.95rem;color:var(--primary);margin-bottom:1rem;">Notes</h3>

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
                                   style="display:none;" onchange="updateVisToggle(this)">
                            👤 Customer
                        </label>
                        <label style="display:flex;align-items:center;gap:.35rem;padding:.32rem .75rem;cursor:pointer;font-size:.82rem;font-weight:600;border-left:1px solid #d1d5db;
                                      background:{{ old('visibility')==='internal' ? '#f59e0b' : '#f9fafb' }};
                                      color:{{ old('visibility')==='internal' ? '#fff' : '#555' }};">
                            <input type="radio" name="visibility" value="internal"
                                   {{ old('visibility')==='internal' ? 'checked' : '' }}
                                   style="display:none;" onchange="updateVisToggle(this)">
                            🔒 Internal
                        </label>
                    </div>
                    <button type="submit"
                            style="padding:.38rem 1rem;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                        + Add Note
                    </button>
                </div>
            </form>

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

        {{-- Site Time (visit-scoped) --}}
        <div style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="font-size:.68rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.85rem;">⏱ Site Time</div>

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
                <form method="POST" action="{{ route('employee.work-orders.visits.time.arrive', [$workOrder, $visit]) }}">
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
            <form method="POST" action="{{ route('employee.work-orders.visits.time.arrive', [$workOrder, $visit]) }}">
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
                <form method="POST" action="{{ route('employee.work-orders.visits.time.depart', [$workOrder, $visit]) }}">
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
            <form method="POST" action="{{ route('employee.work-orders.visits.time.depart', [$workOrder, $visit]) }}">
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

        {{-- Visit details --}}
        <div style="background:#f0f7ff;border:1px solid #d0d5dd;border-radius:8px;padding:1.25rem 1.5rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="font-size:.68rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.65rem;">📅 This Visit</div>
            <div style="font-size:1.15rem;font-weight:700;color:var(--primary);margin-bottom:.2rem;">
                {{ $visit->scheduled_at->format('l, F j, Y') }}
            </div>
            @if($visit->scheduled_at->format('H:i') !== '00:00')
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:.75rem;">
                <span style="font-size:1rem;color:var(--accent);font-weight:600;">{{ $visit->scheduled_at->format('g:i A') }}</span>
                @if($visit->duration_estimate_minutes)
                <span style="font-size:.82rem;color:#555;background:#e0f2fe;padding:.15em .6em;border-radius:999px;">
                    {{ $visit->duration_estimate_minutes >= 60 ? floor($visit->duration_estimate_minutes/60).'h'.($visit->duration_estimate_minutes%60 ? ' '.($visit->duration_estimate_minutes%60).'m' : '') : $visit->duration_estimate_minutes.'m' }} est.
                </span>
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

        {{-- Visit signature (if done) --}}
        @if($isSigned)
        @php
            $sig     = $visit->signature;
            $sigPath = storage_path('app/signatures/work-orders/' . $sig->signature_path);
        @endphp
        <div style="background:#f0fdf4;border:1px solid #d0d5dd;border-radius:8px;padding:1.1rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <p style="font-size:.72rem;font-weight:700;color:#166634;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">✓ Visit Signed</p>
            <p style="font-size:.85rem;color:#166534;margin-bottom:.3rem;font-weight:600;">{{ $sig->signer_name }}</p>
            <p style="font-size:.78rem;color:#15803d;margin-bottom:.5rem;">{{ $sig->signed_at->format('M j, Y g:i A') }}</p>
            @if(file_exists($sigPath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($sigPath)) }}"
                 alt="Visit signature"
                 style="max-width:100%;border:1px solid #bbf7d0;border-radius:4px;background:#fff;display:block;">
            @endif
        </div>
        @endif

        {{-- Visit team --}}
        @if($visitTechs->count())
        <div style="background:#fff;padding:1.25rem;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:1rem;">
            <h3 style="font-size:.95rem;color:var(--primary);margin:0 0 .75rem;">Visit Team</h3>
            @foreach($visitTechs as $tech)
            <div style="display:flex;align-items:center;gap:.65rem;{{ !$loop->last ? 'margin-bottom:.65rem;padding-bottom:.65rem;border-bottom:1px solid #f0f0f0;' : '' }}">
                @if($tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)))
                <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}"
                     style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:1px solid #d1d5db;flex-shrink:0;">
                @else
                <div style="width:30px;height:30px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#fff;flex-shrink:0;">
                    {{ strtoupper(substr($tech->name,0,1)) }}
                </div>
                @endif
                <span style="font-size:.85rem;color:#333;">
                    {{ $tech->name }}
                    @if($tech->id === auth()->id())
                    <span style="color:#888;font-size:.78rem;">(you)</span>
                    @endif
                </span>
            </div>
            @endforeach
        </div>
        @endif

    </div>

</div>

{{-- ═══════════════════════════════════════════════
     SIGNATURE MODAL (visit-scoped)
═══════════════════════════════════════════════ --}}
@if($canComplete)
<div id="sig-modal" onclick="if(event.target===this)closeSignatureModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.22);width:100%;max-width:520px;overflow:hidden;">

        <div style="background:var(--primary);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="color:#fff;margin:0;font-size:1rem;">Customer Signature Required</h3>
            <button type="button" onclick="closeSignatureModal()"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:5px;padding:.25rem .65rem;cursor:pointer;font-size:.9rem;">✕</button>
        </div>

        <form method="POST" action="{{ route('employee.work-orders.visits.complete', [$workOrder, $visit]) }}" id="sig-form">
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

            <div style="padding:1rem 1.5rem;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;gap:.75rem;">
                <button type="button" onclick="closeSignatureModal()" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitSignature()"
                        style="padding:.5rem 1.4rem;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    Submit &amp; Complete Visit
                </button>
            </div>
        </form>

    </div>
</div>
@endif

<script>
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

@if($canComplete)
const canvas  = document.getElementById('sig-canvas');
const ctx     = canvas ? canvas.getContext('2d') : null;
let drawing   = false;

function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const scaleX = canvas.width  / r.width;
    const scaleY = canvas.height / r.height;
    const src = e.touches ? e.touches[0] : e;
    return { x: (src.clientX - r.left) * scaleX, y: (src.clientY - r.top) * scaleY };
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

function clearSignature() { if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height); }

function isCanvasBlank() {
    if (!canvas) return true;
    return !ctx.getImageData(0, 0, canvas.width, canvas.height).data.some(ch => ch !== 0);
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

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => openSignatureModal());
@endif
@endif

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
