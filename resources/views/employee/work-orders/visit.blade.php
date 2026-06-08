@extends('layouts.employee')
@section('title', $workOrder->woLabel() . ' — Visit ' . $visit->scheduled_at->format('M j, Y'))

@php
    $canComplete = !$visit->signature;
    $isSigned    = (bool) $visit->signature;
    $signerDefault = $isSigned ? $visit->signature->signer_name : $workOrder->site_contact_name;

    $urgencyBg    = ['emergency'=>'#fee2e2','urgent'=>'#fef3c7','routine'=>'#f3f4f6'][$workOrder->urgency] ?? '#f3f4f6';
    $urgencyColor = ['emergency'=>'#991b1b','urgent'=>'#92400e','routine'=>'#374151'][$workOrder->urgency] ?? '#374151';

    $photos      = $workOrder->attachments->filter(fn($a) => str_starts_with($a->mime_type, 'image/'));
    $docs        = $workOrder->attachments->filter(fn($a) => !str_starts_with($a->mime_type, 'image/'));
    $previewable = ['application/pdf', 'text/plain'];

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

<style>
@media (max-width: 768px) {
    .visit-grid { grid-template-columns: 1fr !important; }
    /* Unwrap the two column wrappers so every card becomes an item of the single-column grid */
    .visit-grid > div { display: contents; }
    /* Mobile order: Site Time moves directly below Work Order Details */
    .vc-customer  { order: 1; }
    .vc-wodetails { order: 2; }
    .vc-sitetime  { order: 3; }
    .vc-attach    { order: 4; }
    .vc-notes     { order: 5; }
    .vc-visit     { order: 6; }
    .vc-signed    { order: 7; }
    .vc-team      { order: 8; }
}
</style>

<div class="visit-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

    {{-- ── Left column ── --}}
    <div>

        {{-- Customer --}}
        <div class="vc-customer" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Customer</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Contact details</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;">
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
        </div>

        {{-- Work Order Details (condensed) --}}
        <div class="vc-wodetails" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Work Order Details</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Scope &amp; site information</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;">

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
        </div>

        {{-- Attachments card (matches admin work order view) --}}
        <div class="vc-attach" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
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
            <div style="padding:1rem 1.25rem;">
                @if($workOrder->attachments->isEmpty())
                <div style="text-align:center;padding:.75rem 0;">
                    <div style="font-size:1.8rem;margin-bottom:.3rem;">📂</div>
                    <p style="font-size:.8rem;color:#aaa;margin:0;">No attachments yet.</p>
                </div>
                @else
                    {{-- Photo thumbnails --}}
                    @if($photos->count())
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:.5rem;margin-bottom:{{ $docs->count() ? '.85rem' : '0' }};">
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
                            @if(in_array($doc->mime_type, $previewable))
                            <button type="button"
                                    onclick="openFilePreview('{{ route('attachments.view', $doc) }}','{{ addslashes($doc->original_name) }}','{{ route('attachments.download', $doc) }}')"
                                    title="Preview"
                                    style="font-size:.8rem;color:#6b7280;background:none;border:none;cursor:pointer;flex-shrink:0;line-height:1;padding:0;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#6b7280'">👁</button>
                            @endif
                            <a href="{{ route('attachments.download', $doc) }}" title="Download" download
                               style="font-size:.8rem;color:#6b7280;text-decoration:none;flex-shrink:0;line-height:1;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='#6b7280'">↓</a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                @endif
            </div>
        </div>{{-- /attachments card --}}

        {{-- Notes --}}
        <div class="vc-notes" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Notes</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Updates &amp; communication</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;">

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

    </div>

    {{-- ── Right sidebar ── --}}
    <div>

        {{-- Site Time (visit-scoped) — arrival & departure with manual override --}}
        <div class="vc-sitetime" style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;overflow:hidden;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Site Time</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Arrival &amp; departure</div>
                </div>
            </div>
            <div style="padding:1.25rem 1.5rem;">

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
        </div>

        {{-- Visit details --}}
        <div class="vc-visit" style="background:#fff;border:1px solid #d0d5dd;border-radius:8px;overflow:hidden;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">This Visit</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Schedule &amp; location</div>
                </div>
            </div>
            <div style="padding:1.25rem 1.5rem;background:#f0f7ff;">
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
        </div>

        {{-- Visit signature (if done) --}}
        @if($isSigned)
        @php
            $sig     = $visit->signature;
            $sigPath = storage_path('app/signatures/work-orders/' . $sig->signature_path);
        @endphp
        <div class="vc-signed" style="position:relative;background:#f0fdf4;border:1px solid #d0d5dd;border-radius:8px;padding:1.1rem;margin-bottom:1rem;box-shadow:0 1px 4px rgba(0,0,0,.07);">
            <button type="button" onclick="openSignatureModal()" title="Re-sign — collect a new signature and overwrite this one"
                    style="position:absolute;top:.6rem;right:.6rem;width:28px;height:28px;border-radius:50%;border:1px solid #bbf7d0;background:#fff;color:#166534;cursor:pointer;display:flex;align-items:center;justify-content:center;padding:0;transition:background .15s;"
                    onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#fff'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
            </button>
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
        <div class="vc-team" style="background:#fff;border-radius:8px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;margin-bottom:1rem;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Visit Team</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Assigned technicians</div>
                </div>
            </div>
            <div style="padding:1rem 1.25rem;">
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
        </div>
        @endif

    </div>

</div>

{{-- ═══════════════════════════════════════════════
     SIGNATURE MODAL (visit-scoped)
═══════════════════════════════════════════════ --}}
@if($canComplete || $isSigned)
<div id="sig-modal" onclick="if(event.target===this)closeSignatureModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,.22);width:100%;max-width:520px;overflow:hidden;">

        <div style="background:var(--primary);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="color:#fff;margin:0;font-size:1rem;">{{ $isSigned ? 'Re-sign Visit' : 'Customer Signature Required' }}</h3>
            <button type="button" onclick="closeSignatureModal()"
                    style="background:rgba(255,255,255,.15);border:none;color:#fff;border-radius:5px;padding:.25rem .65rem;cursor:pointer;font-size:.9rem;">✕</button>
        </div>

        <form method="POST" action="{{ route('employee.work-orders.visits.complete', [$workOrder, $visit]) }}" id="sig-form">
            @csrf
            <input type="hidden" name="signature_data" id="signature_data">

            <div style="padding:1.5rem;">
                <p style="font-size:.88rem;color:#555;margin-bottom:1.25rem;">
                    @if($isSigned)
                    Collect a new signature below. This will <strong>replace the signature currently on file</strong> for this visit.
                    @else
                    Please have the on-site contact sign below to confirm that work has been completed to their satisfaction.
                    @endif
                </p>

                @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif

                <div style="margin-bottom:1.1rem;">
                    <label style="font-size:.85rem;font-weight:600;display:block;margin-bottom:.4rem;">Printed Name *</label>
                    <input type="text" name="signer_name" id="signer_name"
                           value="{{ old('signer_name', $signerDefault) }}"
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
                    {{ $isSigned ? 'Update Signature' : 'Submit & Complete Visit' }}
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

@if($canComplete || $isSigned)
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
