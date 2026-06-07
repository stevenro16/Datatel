@extends('layouts.employee')
@section('title', 'My Calendar')

@php
    $dayUrl  = fn ($d) => route('employee.calendar', ['view' => 'day',  'date' => (is_string($d) ? $d : $d->toDateString())]);
    $weekUrl = fn ($d) => route('employee.calendar', ['view' => 'week', 'date' => (is_string($d) ? $d : $d->toDateString())]);

    $statusLabel = fn ($s) => match ($s) {
        'new'                => 'New',
        'triaged'            => 'Triaged',
        'scheduled'          => 'Scheduled',
        'awaiting_feedback'  => 'Awaiting Feedback',
        'services_performed' => 'Services Performed',
        'invoice_prepared'   => 'Invoice Prepared',
        'billed'             => 'Billed',
        'completed'          => 'Completed',
        'canceled'           => 'Canceled',
        default              => ucfirst($s),
    };

    $urgencyColor = fn ($u) => match ($u) {
        'emergency' => '#dc2626',
        'urgent'    => '#d97706',
        default     => '#2E86C1',
    };
    $urgencyBg = fn ($u) => match ($u) {
        'emergency' => '#fee2e2',
        'urgent'    => '#fef3c7',
        default     => '#eff6ff',
    };

    // Day view navigation
    $prevDay = $focusDate->copy()->subDay();
    $nextDay = $focusDate->copy()->addDay();

    // Week view navigation
    if ($view === 'week') {
        $prevWeek = $weekStart->copy()->subWeek();
        $nextWeek = $weekStart->copy()->addWeek();
    }

    // Timeline constants: 6 AM – 8 PM, 80px per hour
    $timelineStart = 6;   // 6 AM
    $timelineEnd   = 20;  // 8 PM
    $pxPerHour     = 80;
    $totalHours    = $timelineEnd - $timelineStart;
    $totalHeight   = $totalHours * $pxPerHour;
@endphp

@section('content')

{{-- ── Toolbar ── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">

    {{-- View toggle --}}
    <div style="display:flex;border:1px solid #d1d5db;border-radius:6px;overflow:hidden;">
        <a href="{{ $dayUrl($focusDate) }}"
           style="padding:.4rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none;
                  background:{{ $view === 'day' ? 'var(--primary)' : '#fff' }};
                  color:{{ $view === 'day' ? '#fff' : '#555' }};">Day</a>
        <a href="{{ $weekUrl($focusDate) }}"
           style="padding:.4rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none;border-left:1px solid #d1d5db;
                  background:{{ $view === 'week' ? 'var(--primary)' : '#fff' }};
                  color:{{ $view === 'week' ? '#fff' : '#555' }};">Week</a>
    </div>

    {{-- Date navigation --}}
    @if($view === 'day')
    <div style="display:flex;align-items:center;gap:.75rem;">
        <a href="{{ $dayUrl($prevDay) }}" style="padding:.4rem .85rem;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#555;font-size:.9rem;background:#fff;">&#8592;</a>
        <span style="font-size:1rem;font-weight:700;color:var(--primary);min-width:220px;text-align:center;">
            {{ $focusDate->format('l, F j, Y') }}
            @if($focusDate->isToday())<span style="font-size:.75rem;font-weight:600;background:var(--accent);color:#fff;padding:.1rem .5rem;border-radius:4px;margin-left:.5rem;">Today</span>@endif
        </span>
        <a href="{{ $dayUrl($nextDay) }}" style="padding:.4rem .85rem;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#555;font-size:.9rem;background:#fff;">&#8594;</a>
    </div>
    @else
    <div style="display:flex;align-items:center;gap:.75rem;">
        <a href="{{ $weekUrl($prevWeek) }}" style="padding:.4rem .85rem;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#555;font-size:.9rem;background:#fff;">&#8592;</a>
        <span style="font-size:1rem;font-weight:700;color:var(--primary);min-width:240px;text-align:center;">
            {{ $weekStart->format('M j') }} – {{ $weekEnd->format('M j, Y') }}
        </span>
        <a href="{{ $weekUrl($nextWeek) }}" style="padding:.4rem .85rem;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#555;font-size:.9rem;background:#fff;">&#8594;</a>
    </div>
    @endif

    {{-- Today shortcut --}}
    <a href="{{ $view === 'week' ? $weekUrl(today()) : $dayUrl(today()) }}"
       style="padding:.4rem .9rem;border:1px solid #d1d5db;border-radius:6px;text-decoration:none;color:#555;font-size:.85rem;background:#fff;">
        Today
    </a>
</div>

{{-- ══════════════════════════════════════════
     DAY VIEW
══════════════════════════════════════════ --}}
@if($view === 'day')

<div style="display:flex;gap:1.5rem;align-items:flex-start;">

    {{-- Timeline --}}
    <div id="cal-timeline" style="flex:1;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow-y:auto;height:calc(100vh - 220px);min-height:400px;">
        <div style="position:relative;height:{{ $totalHeight }}px;">

            {{-- Hour grid lines & labels --}}
            @for($h = $timelineStart; $h <= $timelineEnd; $h++)
            @php $top = ($h - $timelineStart) * $pxPerHour; @endphp
            <div style="position:absolute;top:{{ $top }}px;left:0;right:0;border-top:1px solid {{ $h === $timelineStart ? 'transparent' : '#e5e7eb' }};display:flex;">
                <div style="width:56px;flex-shrink:0;padding:.2rem .5rem 0 .5rem;font-size:.72rem;color:#9ca3af;text-align:right;line-height:1;">
                    {{ $h === 12 ? '12 PM' : ($h < 12 ? $h.' AM' : ($h - 12).' PM') }}
                </div>
                <div style="flex:1;border-left:1px solid #e5e7eb;"></div>
            </div>
            @endfor

            {{-- Visit blocks --}}
            @foreach($visits as $visit)
            @php
                $wo        = $visit->workOrder;
                $startH    = (int) $visit->scheduled_at->format('G');
                $startM    = (int) $visit->scheduled_at->format('i');
                $dur       = $visit->duration_estimate_minutes ?? 60;

                $topPx     = (($startH - $timelineStart) * 60 + $startM) / 60 * $pxPerHour;
                $htPx      = max(40, $dur / 60 * $pxPerHour);

                // Clamp to visible range
                $topPx     = max(0, min($topPx, $totalHeight - 40));
                $htPx      = min($htPx, $totalHeight - $topPx);

                $bg           = $urgencyBg($wo->urgency);
                $color        = $urgencyColor($wo->urgency);
                $isUnverified = $wo->confirmation_status !== \App\Models\WorkOrder::CONFIRMATION_CONFIRMED;
            @endphp
            <a href="{{ route('employee.work-orders.visits.show', [$wo, $visit]) }}"
               style="position:absolute;top:{{ $topPx }}px;left:64px;right:12px;height:{{ $htPx }}px;
                        background:{{ $bg }};border-left:3px solid {{ $color }};border-radius:0 6px 6px 0;
                        padding:.35rem .6rem .35rem {{ $isUnverified ? '1.6rem' : '.6rem' }};overflow:hidden;cursor:pointer;z-index:1;box-shadow:0 1px 3px rgba(0,0,0,.08);text-decoration:none;display:block;"
               title="{{ $visit->scheduled_at->format('g:i A') }} — {{ $wo->woLabel() }} {{ $wo->customer->name }}{{ $isUnverified ? ' ⚠ Visit not verified' : '' }}">
                @if($isUnverified)
                <div style="position:absolute;left:0;top:0;bottom:0;width:20px;background:#f59e0b;display:flex;align-items:center;justify-content:center;z-index:2;">
                    <span style="font-size:.8rem;font-weight:900;color:#fff;line-height:1;">!</span>
                </div>
                @endif
                <div style="display:flex;align-items:center;justify-content:space-between;gap:.3rem;min-width:0;">
                    <div style="font-size:.78rem;font-weight:700;color:{{ $color }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0;">
                        {{ $visit->scheduled_at->format('g:i A') }}
                        @if($visit->duration_estimate_minutes)
                            – {{ $visit->scheduled_at->copy()->addMinutes($visit->duration_estimate_minutes)->format('g:i A') }}
                        @endif
                    </div>
                    <div style="display:flex;gap:.2rem;flex-shrink:0;align-items:center;">
                        @php
                            $urgencyBadgeBg = match($wo->urgency) { 'emergency' => '#dc2626', 'urgent' => '#d97706', default => '#2E86C1' };
                        @endphp
                        <span style="font-size:.58rem;font-weight:700;padding:.07rem .32rem;border-radius:999px;background:{{ $urgencyBadgeBg }};color:#fff;white-space:nowrap;line-height:1.4;">{{ ucfirst($wo->urgency) }}</span>
                        <span style="font-size:.58rem;font-weight:700;padding:.07rem .32rem;border-radius:999px;background:rgba(0,0,0,.13);color:#1e293b;white-space:nowrap;line-height:1.4;">{{ $statusLabel($wo->status) }}</span>
                    </div>
                </div>
                <div style="font-size:.82rem;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.1rem;">
                    {{ $wo->woLabel() }} &mdash; {{ $wo->customer->name }}
                </div>
                @if($wo->site_street)
                <div style="font-size:.75rem;color:#555;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.05rem;">
                    {{ $wo->site_street }}
                </div>
                @endif
                @if($htPx >= 88 && $wo->serviceTypes->count())
                <div style="font-size:.72rem;color:#777;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:.1rem;">
                    {{ $wo->serviceTypes->pluck('name')->join(', ') }}
                </div>
                @endif
                @php $visitTechUsers = $visit->techs->map(fn($t) => $t->user)->filter(); @endphp
                @if($visitTechUsers->count() > 0 && $htPx >= 60)
                <div style="display:flex;gap:.15rem;margin-top:.18rem;flex-wrap:wrap;align-items:center;">
                    @foreach($visitTechUsers as $tech)
                    @php $ti=collect(explode(' ',$tech->name))->map(fn($w)=>strtoupper($w[0]??''))->take(2)->join(''); @endphp
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;background:{{ $color }};opacity:.75;color:#fff;font-size:.48rem;font-weight:700;flex-shrink:0;" title="{{ $tech->name }}">{{ $ti }}</span>
                    @endforeach
                </div>
                @endif
            </a>
            @endforeach

            @if($visits->isEmpty())
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding-left:64px;">
                <p style="color:#bbb;font-size:.9rem;">No visits scheduled for this day.</p>
            </div>
            @endif

        </div>
    </div>

    {{-- Right sidebar --}}
    {{-- Unverified visits scoped to the day currently being viewed --}}
    @php $unverifiedVisits = $visits->filter(fn($v) => $v->workOrder->confirmation_status !== \App\Models\WorkOrder::CONFIRMATION_CONFIRMED); @endphp
    <div style="width:270px;flex-shrink:0;display:flex;flex-direction:column;gap:.75rem;height:calc(100vh - 220px);min-height:400px;overflow-y:auto;">

        {{-- Unverified visits --}}
        @if($unverifiedVisits->isNotEmpty())
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:.85rem 1rem;flex-shrink:0;">
            <p style="font-size:.78rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .6rem;">
                ⚠ Visit Not Verified
            </p>
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($unverifiedVisits as $uv)
                @php
                    $order = $uv->workOrder;
                    $phones = collect();
                    if ($order->site_contact_phone)
                        $phones->push(['label' => $order->site_contact_name ?: 'Site Contact', 'number' => $order->site_contact_phone]);
                    if ($order->customer->phone)
                        $phones->push(['label' => $order->customer->name, 'number' => $order->customer->phone]);
                    foreach ($order->customer->companies as $co)
                        if ($co->phone) $phones->push(['label' => $co->name, 'number' => $co->phone]);
                    $phones = $phones->unique('number')->values();
                @endphp
                <div style="padding:.45rem .6rem;border-left:3px solid #f59e0b;border-radius:0 5px 5px 0;background:#fef3c7;">
                    <div style="font-size:.8rem;font-weight:700;color:#78350f;line-height:1.3;">
                        {{ $order->woLabel() }} &mdash; {{ $order->customer->name }}
                    </div>
                    <div style="font-size:.74rem;font-weight:600;color:#b45309;margin-top:.15rem;">
                        {{ $uv->scheduled_at->format('D, M j \a\t g:i A') }}
                    </div>
                    <div style="font-size:.7rem;color:#92400e;opacity:.85;margin-top:.1rem;font-style:italic;">
                        @if($order->confirmation_status === 'pending')
                            Awaiting customer reply
                        @elseif($order->confirmation_status === 'declined')
                            Customer declined — follow up needed
                        @else
                            Confirmation not collected
                        @endif
                    </div>
                    @if($order->site_street)
                    <div style="font-size:.74rem;color:#92400e;margin-top:.1rem;">{{ $order->site_street }}</div>
                    @endif
                    @if($phones->isNotEmpty())
                    <div style="margin-top:.4rem;display:flex;flex-direction:column;gap:.2rem;">
                        @foreach($phones as $phone)
                        <div style="display:flex;align-items:baseline;gap:.3rem;">
                            <span style="font-size:.68rem;color:#92400e;opacity:.75;white-space:nowrap;flex-shrink:0;">{{ $phone['label'] }}:</span>
                            <a href="tel:{{ preg_replace('/\D/', '', $phone['number']) }}"
                               style="font-size:.78rem;font-weight:600;color:#78350f;text-decoration:none;white-space:nowrap;">
                                {{ $phone['number'] }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    <button type="button"
                            onclick="openConfirmModal('{{ $order->id }}', '{{ addslashes($order->woLabel()) }}', '{{ addslashes($order->customer->name) }}')"
                            style="margin-top:.45rem;width:100%;padding:.3rem .5rem;background:#f59e0b;color:#fff;border:none;border-radius:5px;font-size:.74rem;font-weight:700;cursor:pointer;letter-spacing:.01em;">
                        ✓ Confirmed with Customer
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Equipment needed --}}
        <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1rem;flex:1;min-height:0;display:flex;flex-direction:column;">
            <p style="font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin:0 0 .75rem;flex-shrink:0;">Equipment Needed</p>
            @if($visits->isEmpty())
            <p style="font-size:.82rem;color:#bbb;margin:0;">No visits scheduled for today.</p>
            @else
            <div style="overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:.65rem;padding-right:.2rem;">
                @foreach($visits as $visit)
                @php $wo = $visit->workOrder; @endphp
                <div style="padding:.55rem .7rem;border-left:3px solid var(--accent);border-radius:0 5px 5px 0;background:#f8f9fa;">
                    <div style="font-size:.8rem;font-weight:700;color:var(--primary);line-height:1.3;">
                        {{ $wo->woLabel() }} &mdash; {{ $wo->customer->name }}
                        <span style="font-weight:400;color:var(--accent);font-size:.75rem;margin-left:.4rem;">{{ $visit->scheduled_at->format('g:i A') }}</span>
                    </div>
                    @if($wo->site_street)
                    <div style="font-size:.74rem;color:#6b7280;margin-top:.1rem;">{{ $wo->site_street }}</div>
                    @endif
                    @if(trim($wo->equipment_details ?? '') !== '')
                    <div style="font-size:.8rem;color:#374151;line-height:1.55;margin-top:.35rem;padding-left:.65rem;border-left:2px solid #d1d5db;white-space:pre-wrap;word-break:break-word;">{{ $wo->equipment_details }}</div>
                    @else
                    <div style="font-size:.78rem;color:#9ca3af;font-style:italic;margin-top:.35rem;padding-left:.65rem;border-left:2px solid #e5e7eb;">No Equipment documented.</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</div>

{{-- ══════════════════════════════════════════
     WEEK VIEW
══════════════════════════════════════════ --}}
@else

<div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:auto;">
    <div style="display:grid;grid-template-columns:repeat(7,minmax(130px,1fr));min-width:700px;">

        {{-- Day headers --}}
        @foreach($days as $day)
        @php
            $isToday = $day['date']->isToday();
        @endphp
        <div style="padding:.75rem .6rem;text-align:center;border-bottom:2px solid {{ $isToday ? 'var(--accent)' : '#e5e7eb' }};
                    background:{{ $isToday ? '#eff6ff' : '#f9fafb' }};
                    {{ !$loop->last ? 'border-right:1px solid #e5e7eb;' : '' }}">
            <div style="font-size:.75rem;font-weight:600;color:{{ $isToday ? 'var(--accent)' : '#6b7280' }};text-transform:uppercase;letter-spacing:.05em;">
                {{ $day['date']->format('D') }}
            </div>
            <div style="font-size:1.1rem;font-weight:700;color:{{ $isToday ? 'var(--accent)' : '#1e293b' }};line-height:1.2;">
                {{ $day['date']->format('j') }}
            </div>
            <a href="{{ $dayUrl($day['date']) }}" style="font-size:.7rem;color:{{ $isToday ? 'var(--accent)' : '#9ca3af' }};text-decoration:none;">
                Day view →
            </a>
        </div>
        @endforeach

        {{-- Day cells --}}
        @foreach($days as $day)
        <div style="padding:.6rem;min-height:200px;vertical-align:top;
                    border-top:none;{{ !$loop->last ? 'border-right:1px solid #e5e7eb;' : '' }}
                    background:{{ $day['date']->isToday() ? '#fafcff' : '#fff' }};">
            @forelse($day['visits'] as $visit)
            @php
                $wo           = $visit->workOrder;
                $color        = $urgencyColor($wo->urgency);
                $bg           = $urgencyBg($wo->urgency);
                $wkUnverified = $wo->confirmation_status !== \App\Models\WorkOrder::CONFIRMATION_CONFIRMED;
            @endphp
            <a href="{{ route('employee.work-orders.visits.show', [$wo, $visit]) }}"
               style="display:block;background:{{ $bg }};border-left:3px solid {{ $color }};border-radius:0 5px 5px 0;
                        padding:.45rem .55rem .45rem {{ $wkUnverified ? '1.45rem' : '.55rem' }};margin-bottom:.5rem;text-decoration:none;
                        position:relative;overflow:hidden;"
               title="{{ $wo->woLabel() }}{{ $wkUnverified ? ' — ⚠ Visit not verified' : '' }}">
                @if($wkUnverified)
                <div style="position:absolute;left:0;top:0;bottom:0;width:18px;background:#f59e0b;display:flex;align-items:center;justify-content:center;z-index:2;">
                    <span style="font-size:.7rem;font-weight:900;color:#fff;line-height:1;">!</span>
                </div>
                @endif
                <div style="font-size:.75rem;font-weight:700;color:{{ $color }};">
                    {{ $visit->scheduled_at->format('g:i A') }}
                </div>
                <div style="font-size:.68rem;color:{{ $color }};opacity:.8;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $statusLabel($wo->status) }}
                </div>
                <div style="font-size:.8rem;font-weight:600;color:#1e293b;margin-top:.1rem;line-height:1.25;">
                    {{ $wo->woLabel() }} {{ $wo->customer->name }}
                </div>
                @if($wo->site_street)
                <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $wo->site_street }}
                </div>
                @endif
                @if($wo->serviceTypes->count())
                <div style="font-size:.7rem;color:#9ca3af;margin-top:.1rem;">
                    {{ $wo->serviceTypes->pluck('name')->join(', ') }}
                </div>
                @endif
                @php $visitTechUsers = $visit->techs->map(fn($t) => $t->user)->filter(); @endphp
                @if($visitTechUsers->count() > 0)
                <div style="display:flex;gap:.12rem;margin-top:.15rem;flex-wrap:wrap;align-items:center;">
                    @foreach($visitTechUsers as $tech)
                    @php $ti=collect(explode(' ',$tech->name))->map(fn($w)=>strtoupper($w[0]??''))->take(2)->join(''); @endphp
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:15px;height:15px;border-radius:50%;background:{{ $color }};opacity:.7;color:#fff;font-size:.45rem;font-weight:700;flex-shrink:0;" title="{{ $tech->name }}">{{ $ti }}</span>
                    @endforeach
                </div>
                @endif
            </a>
            @empty
            <p style="color:#d1d5db;font-size:.8rem;text-align:center;margin-top:1rem;">—</p>
            @endforelse
        </div>
        @endforeach

    </div>
</div>

{{-- Equipment needed for focused day (week view) --}}
@if($todayVisits->isNotEmpty())
<div style="margin-top:1.25rem;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1rem 1.25rem;">
    <p style="font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.75rem;">
        Equipment Needed &mdash; {{ $focusDate->format('l, F j') }}
    </p>
    <div style="display:flex;flex-direction:column;gap:.65rem;">
        @foreach($todayVisits as $visit)
        @php $wo = $visit->workOrder; @endphp
        <div style="padding:.55rem .75rem;border-left:3px solid var(--accent);border-radius:0 5px 5px 0;background:#f8f9fa;">
            <div style="font-size:.85rem;font-weight:700;color:var(--primary);">
                {{ $wo->woLabel() }} &mdash; {{ $wo->customer->name }}
                <span style="font-weight:400;color:var(--accent);font-size:.8rem;margin-left:.35rem;">{{ $visit->scheduled_at->format('g:i A') }}</span>
                @if($wo->site_street) <span style="font-weight:400;color:#6b7280;font-size:.8rem;">· {{ $wo->site_street }}</span>@endif
            </div>
            @if(trim($wo->equipment_details ?? '') !== '')
            <div style="font-size:.85rem;color:#374151;line-height:1.55;margin-top:.35rem;padding-left:.75rem;border-left:2px solid #d1d5db;white-space:pre-wrap;word-break:break-word;">{{ $wo->equipment_details }}</div>
            @else
            <div style="font-size:.82rem;color:#9ca3af;font-style:italic;margin-top:.35rem;padding-left:.75rem;border-left:2px solid #e5e7eb;">No Equipment documented.</div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@endif

<script>
(function () {
    var tl = document.getElementById('cal-timeline');
    if (!tl) return;
    // Scroll to 8 AM on load (2 hours past the 6 AM start, 80px/hour)
    tl.scrollTop = 2 * {{ $pxPerHour }};
})();
</script>

{{-- ── Customer Confirmation Modal ── --}}
<div id="confirm-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;"
     onclick="if(event.target===this) closeConfirmModal()">
    <div style="background:#fff;border-radius:10px;padding:1.75rem;width:440px;max-width:94vw;box-shadow:0 8px 40px rgba(0,0,0,.22);">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.1rem;gap:1rem;">
            <div>
                <h2 style="font-size:1.05rem;font-weight:700;color:var(--primary);margin:0 0 .2rem;">Confirming on the Customer's Behalf</h2>
                <p id="confirm-modal-subtitle" style="font-size:.82rem;color:#6b7280;margin:0;"></p>
            </div>
            <button type="button" onclick="closeConfirmModal()"
                    style="background:none;border:none;font-size:1.4rem;line-height:1;cursor:pointer;color:#aaa;padding:0;flex-shrink:0;">&times;</button>
        </div>

        <p style="font-size:.85rem;color:#374151;margin:0 0 1rem;line-height:1.5;">
            You are recording that you have personally confirmed this scheduled visit with the customer.
            Please describe how you confirmed it (e.g. spoke by phone, text message, in-person).
        </p>

        <form id="confirm-modal-form" method="POST">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">
                    How did you confirm with the customer? <span style="color:#dc2626;">*</span>
                </label>
                <textarea name="confirmation_note" id="confirm-note" rows="3" required
                          placeholder="e.g. Called the customer at 10am and they confirmed the 3pm visit."
                          style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.88rem;resize:vertical;box-sizing:border-box;font-family:inherit;"></textarea>
                <p id="confirm-note-error" style="display:none;font-size:.78rem;color:#dc2626;margin:.3rem 0 0;">Please describe how you confirmed with the customer.</p>
            </div>

            <div style="display:flex;gap:.65rem;justify-content:flex-end;">
                <button type="button" onclick="closeConfirmModal()"
                        style="padding:.5rem 1.1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;font-size:.88rem;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:.5rem 1.25rem;background:#f59e0b;color:#fff;border:none;border-radius:6px;font-size:.88rem;font-weight:700;cursor:pointer;">
                    ✓ Mark as Confirmed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const modal    = document.getElementById('confirm-modal');
    const form     = document.getElementById('confirm-modal-form');
    const subtitle = document.getElementById('confirm-modal-subtitle');
    const noteEl   = document.getElementById('confirm-note');
    const noteErr  = document.getElementById('confirm-note-error');

    const baseUrl  = '{{ url("employee/work-orders") }}';

    window.openConfirmModal = function (id, label, customer) {
        form.action = baseUrl + '/' + id + '/confirm-customer';
        subtitle.textContent = label + ' — ' + customer;
        noteEl.value = '';
        noteErr.style.display = 'none';
        modal.style.display = 'flex';
        setTimeout(() => noteEl.focus(), 60);
    };

    window.closeConfirmModal = function () {
        modal.style.display = 'none';
    };

    form.addEventListener('submit', function (e) {
        if (!noteEl.value.trim() || noteEl.value.trim().length < 5) {
            e.preventDefault();
            noteErr.style.display = 'block';
            noteEl.focus();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeConfirmModal();
    });
})();
</script>

@endsection
