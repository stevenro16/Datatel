@extends('layouts.admin')
@section('title', 'Calendar & Dispatch')

@section('content')
@php
    use Carbon\Carbon;
    $today     = Carbon::today();
    $wosByDate = $scheduled->groupBy(fn($visit) => $visit->scheduled_at->format('Y-m-d'));
    $calUrl    = function($d, $v = null) use ($view, $wkDays) {
        $params = ['date' => $d, 'view' => $v ?? $view];
        if (($v ?? $view) === 'week') $params['days'] = $wkDays;
        return route('admin.calendar', $params);
    };
    $woColor = function($visit) use ($techColors) {
        $firstUser = $visit->techUsers->first()
            ?? $visit->workOrder->assignments->first()?->employee;
        return $firstUser && isset($techColors[$firstUser->id]) ? $techColors[$firstUser->id] : '#94a3b8';
    };
    $woTechIds = function($visit) {
        $ids = $visit->techUsers->pluck('id')->filter();
        if ($ids->isEmpty()) {
            $ids = $visit->workOrder->assignments->pluck('employee.id')->filter();
        }
        return $ids->join(' ');
    };
    $visitTechs = function($visit) {
        $users = $visit->techUsers;
        if ($users->isEmpty()) {
            $users = $visit->workOrder->assignments->map(fn($a) => $a->employee)->filter()->values();
        }
        return $users;
    };
    $hrStart   = 6; $hrEnd = 20; // 6 AM – 8 PM
@endphp

<style>
    .cal-toolbar { display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;background:#fff;border:1px solid #d0d5dd;border-radius:10px;padding:.7rem 1rem;box-shadow:0 1px 4px rgba(0,0,0,.05); }
    .cal-nav-btn { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:1px solid #d0d5dd;border-radius:6px;background:#fff;cursor:pointer;font-size:1.1rem;color:#374151;text-decoration:none;transition:background .15s; }
    .cal-nav-btn:hover { background:#f3f4f6; }
    .cal-period { font-size:.95rem;font-weight:700;color:var(--primary);min-width:200px;text-align:center; }
    .cal-today-btn { padding:.3rem .75rem;border:1px solid #d0d5dd;border-radius:6px;background:#fff;font-size:.82rem;font-weight:600;color:#374151;text-decoration:none;transition:background .15s;white-space:nowrap; }
    .cal-today-btn:hover { background:#f3f4f6; }
    .view-toggle { display:flex;border:1px solid #d0d5dd;border-radius:6px;overflow:hidden; }
    .view-toggle a { padding:.3rem .85rem;font-size:.82rem;font-weight:600;text-decoration:none;color:#4b5563;background:#fff;transition:background .15s,color .15s; }
    .view-toggle a + a { border-left:1px solid #d0d5dd; }
    .view-toggle a.active { background:var(--primary);color:#fff; }

    .tech-chips { display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;padding:.5rem .85rem;background:#fff;border:1px solid #e5e7eb;border-radius:8px; }
    .tech-chip { display:inline-flex;align-items:center;gap:.32rem;padding:.28rem .65rem;border-radius:999px;font-size:.78rem;font-weight:600;cursor:pointer;border:2px solid transparent;transition:opacity .2s,filter .2s;user-select:none; }
    .tech-chip.off { opacity:.3;filter:grayscale(.8); }
    .tech-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }

    /* Shared */
    .wo-card { display:block;text-decoration:none;cursor:pointer;transition:filter .15s; }
    .wo-card:hover { filter:brightness(.92); }
    .tech-avatar { width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:700;color:#fff;flex-shrink:0; }

    /* ── Week timeline (Outlook-style) ── */
    .wk-tl-wrap { background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06); }

    /* Sticky header */
    .wk-tl-hdr { display:grid;border-bottom:2px solid #d0d5dd;background:#f9fafb;position:sticky;top:0;z-index:20; }
    .wk-tl-hdr-gutter { border-right:1px solid #d0d5dd;background:#f9fafb; }
    .wk-tl-day-hdr { padding:.55rem .4rem;text-align:center;border-right:1px solid #e5e7eb; }
    .wk-tl-day-hdr:last-child { border-right:none; }
    .wk-tl-day-hdr.today { background:#dbeafe; }
    .wk-tl-day-name { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280; }
    .wk-tl-day-num  { font-size:1.1rem;font-weight:800;color:var(--primary);line-height:1.1; }
    .wk-tl-day-hdr.today .wk-tl-day-num { color:#1d4ed8; }
    .wk-tl-month-label { font-size:.62rem;color:#9ca3af;margin-top:.1rem; }

    /* Scrollable body */
    .wk-tl-body { overflow-y:auto;max-height:660px; }
    .wk-tl-body-grid { display:grid;position:relative; }

    /* Time gutter */
    .wk-tl-gutter { border-right:1px solid #d0d5dd;background:#f9fafb;position:relative; }
    .wk-tl-gutter-row { height:60px;border-top:1px solid #e5e7eb;position:relative; }
    .wk-tl-gutter-row:first-child { border-top:none; }
    .wk-tl-hr-label { position:absolute;top:-9px;right:8px;font-size:.67rem;color:#9ca3af;font-weight:500;white-space:nowrap;background:#f9fafb;padding:0 2px; }

    /* Day columns */
    .wk-tl-col { position:relative;border-right:1px solid #e5e7eb; }
    .wk-tl-col:last-child { border-right:none; }
    .wk-tl-col.today { background:#f0f7ff; }
    .wk-tl-col-row { height:60px;border-top:1px solid #f0f0f0;position:relative; }
    .wk-tl-col-row:first-child { border-top:none; }
    .wk-tl-col-row::after { content:'';display:block;position:absolute;left:0;right:0;top:50%;border-top:1px dashed #ebebeb;pointer-events:none; }

    /* Events */
    .wk-tl-event { position:absolute;left:2px;right:2px;border-radius:5px;padding:.22rem .45rem;border-left:3px solid;overflow:hidden;z-index:5;box-shadow:0 1px 3px rgba(0,0,0,.08); }
    .wk-tl-event:hover { filter:brightness(.92);z-index:6; }
    .wk-evt-time { font-size:.67rem;font-weight:700;line-height:1.2; }
    .wk-evt-name { font-size:.76rem;font-weight:600;color:#111;line-height:1.2;overflow:hidden;white-space:nowrap;text-overflow:ellipsis; }
    .wk-evt-sub  { font-size:.67rem;color:#555;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;line-height:1.2; }

    /* Current time indicator */
    .now-line { position:absolute;left:0;right:0;height:2px;background:#ef4444;z-index:10;pointer-events:none; }
    .now-line::before { content:'';position:absolute;left:-1px;top:-4px;width:10px;height:10px;border-radius:50%;background:#ef4444; }

    /* Day timeline */
    .day-wrap { background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05); }
    .tl-body { position:relative;margin-left:56px;min-height:840px; }
    .tl-hour { position:relative;height:60px;border-top:1px solid #f0f0f0; }
    .tl-label { position:absolute;left:-56px;top:-9px;width:52px;text-align:right;font-size:.68rem;color:#9ca3af;font-weight:500;padding-right:6px; }
    .tl-half { position:absolute;top:50%;left:0;right:0;border-top:1px dashed #f5f5f5; }
    .tl-event { position:absolute;left:4px;right:4px;border-radius:5px;padding:.25rem .5rem;border-left:4px solid;overflow:hidden; }

    /* Month */
    .month-wrap { background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05); }
    .mo-dow-row { display:grid;grid-template-columns:repeat(7,1fr);background:#f9fafb;border-bottom:2px solid #e5e7eb; }
    .mo-dow-row div { padding:.4rem;text-align:center;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280; }
    .mo-weeks { display:grid;grid-template-columns:repeat(7,1fr); }
    .mo-cell { border-right:1px solid #f0f0f0;border-bottom:1px solid #f0f0f0;min-height:90px;padding:.3rem; }
    .mo-cell:nth-child(7n) { border-right:none; }
    .mo-cell.outside { background:#fafafa; }
    .mo-cell.today   { background:#eff6ff; }
    .mo-num { font-size:.8rem;font-weight:700;color:#374151;display:block;margin-bottom:.2rem; }
    .mo-cell.today .mo-num { color:#1d4ed8; }
    .mo-cell.outside .mo-num { color:#d1d5db; }
    .mo-pill { display:block;border-radius:3px;padding:.1rem .3rem;margin-bottom:.18rem;font-size:.68rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;border-left:3px solid; }
</style>

{{-- Toolbar --}}
<div class="cal-toolbar">
    <a href="{{ $calUrl($prev) }}" class="cal-nav-btn">&#8592;</a>
    <span class="cal-period">{{ $label }}</span>
    <a href="{{ $calUrl($next) }}" class="cal-nav-btn">&#8594;</a>
    <a href="{{ $calUrl($today->format('Y-m-d')) }}" class="cal-today-btn">Today</a>

    @if($view === 'week')
    <div class="view-toggle">
        <a href="{{ route('admin.calendar', ['date' => $anchor->format('Y-m-d'), 'view' => 'week', 'days' => 5]) }}"
           class="{{ $wkDays === 5 ? 'active' : '' }}">5 Day</a>
        <a href="{{ route('admin.calendar', ['date' => $anchor->format('Y-m-d'), 'view' => 'week', 'days' => 7]) }}"
           class="{{ $wkDays === 7 ? 'active' : '' }}">7 Day</a>
    </div>
    @endif

    <div class="view-toggle" style="margin-left:auto;">
        <a href="{{ route('admin.calendar', ['date' => $anchor->format('Y-m-d'), 'view' => 'day']) }}"   class="{{ $view==='day'   ? 'active':'' }}">Day</a>
        <a href="{{ route('admin.calendar', ['date' => $anchor->format('Y-m-d'), 'view' => 'week', 'days' => $wkDays]) }}" class="{{ $view==='week'  ? 'active':'' }}">Week</a>
        <a href="{{ route('admin.calendar', ['date' => $anchor->format('Y-m-d'), 'view' => 'month']) }}" class="{{ $view==='month' ? 'active':'' }}">Month</a>
    </div>
</div>

{{-- Tech filter --}}
@if($employees->isNotEmpty())
<div class="tech-chips">
    <span style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-right:.2rem;">Technicians:</span>
    @foreach($employees as $emp)
    @php $clr = $techColors[$emp->id]; @endphp
    <div class="tech-chip" id="chip-{{ $emp->id }}" data-tech-id="{{ $emp->id }}"
         style="background:{{ $clr }}16;border-color:{{ $clr }};" onclick="toggleTech({{ $emp->id }})">
        <span class="tech-dot" style="background:{{ $clr }};"></span>
        <span style="color:{{ $clr }};">{{ $emp->name }}</span>
    </div>
    @endforeach
    <span style="font-size:.73rem;color:#9ca3af;margin-left:.4rem;cursor:pointer;" onclick="resetTechs()">Show all</span>
</div>
@endif

{{-- ═══════════ WEEK VIEW (Outlook-style time grid) ═══════════ --}}
@if($view === 'week')
@php
    $days = collect();
    $d = $start->copy();
    while ($d->lte($end)) { $days->push($d->copy()); $d->addDay(); }
    $numCols = $days->count();
    $gridCols = "56px repeat({$numCols}, 1fr)";
    $gridH    = ($hrEnd - $hrStart + 1) * 60; // total px height
@endphp

<div class="wk-tl-wrap">
    {{-- Single scrollable container — header lives inside so widths always match --}}
    <div class="wk-tl-body" id="wkBody">

        {{-- Sticky header row (inside scroll = same width as body, no scrollbar offset) --}}
        <div style="position:sticky;top:0;z-index:20;display:grid;grid-template-columns:{{ $gridCols }};border-bottom:2px solid #d0d5dd;background:#f9fafb;">
            <div class="wk-tl-hdr-gutter" style="height:52px;"></div>
            @foreach($days as $day)
            @php $isToday = $day->isSameDay($today); @endphp
            <div class="wk-tl-day-hdr {{ $isToday ? 'today' : '' }}">
                <div class="wk-tl-day-name">{{ $day->format('D') }}</div>
                <div class="wk-tl-day-num">{{ $day->format('j') }}</div>
                @if(!$day->isSameMonth($anchor))<div class="wk-tl-month-label">{{ $day->format('M') }}</div>@endif
            </div>
            @endforeach
        </div>

        {{-- Timeline grid --}}
        <div class="wk-tl-body-grid" style="grid-template-columns:{{ $gridCols }};min-height:{{ $gridH }}px;">

            {{-- Time gutter --}}
            <div class="wk-tl-gutter">
                @for($h = $hrStart; $h <= $hrEnd; $h++)
                <div class="wk-tl-gutter-row">
                    @if($h > $hrStart)
                    <span class="wk-tl-hr-label">
                        {{ $h > 12 ? ($h-12).' PM' : ($h == 12 ? '12 PM' : $h.' AM') }}
                    </span>
                    @endif
                </div>
                @endfor
            </div>

            {{-- Day columns --}}
            @foreach($days as $day)
            @php
                $dk     = $day->format('Y-m-d');
                $isTdy  = $day->isSameDay($today);
                $dayWOs = $wosByDate->get($dk, collect());
            @endphp
            <div class="wk-tl-col {{ $isTdy ? 'today' : '' }}" data-date="{{ $dk }}">
                {{-- Hour grid rows (provide the grid lines) --}}
                @for($h = $hrStart; $h <= $hrEnd; $h++)
                <div class="wk-tl-col-row"></div>
                @endfor

                {{-- Current time indicator --}}
                @if($isTdy)
                <div class="now-line" id="nowLine" style="display:none;"></div>
                @endif

                {{-- Events --}}
                @foreach($dayWOs as $visit)
                @php
                    $wo     = $visit->workOrder;
                    $top    = max(0, ($visit->scheduled_at->hour - $hrStart) * 60 + $visit->scheduled_at->minute);
                    $ht     = max(44, $visit->duration_estimate_minutes ?? 60);
                    $clr    = $woColor($visit);
                    $tids   = $woTechIds($visit);
                    $techs  = $visitTechs($visit);
                    $isDone = $visit->signature !== null;
                    $bgClr  = $isDone ? '#f1f5f9' : $clr.'1a';
                    $bdClr  = $isDone ? '#94a3b8' : $clr;
                @endphp
                <a href="{{ route('admin.work-orders.show', [$wo, 'from' => 'calendar']) }}"
                   class="wo-card wk-tl-event" data-techs="{{ $tids }}"
                   style="top:{{ $top }}px;height:{{ $ht }}px;border-left-color:{{ $bdClr }};background:{{ $bgClr }};{{ $isDone ? 'opacity:.72;' : '' }}">
                    <div class="wk-evt-time" style="color:{{ $bdClr }};">{{ $visit->scheduled_at->format('g:i A') }}</div>
                    <div class="wk-evt-name">{{ $wo->customer->name }}{{ $isDone ? ' ✓' : '' }}</div>
                    @if($ht >= 56)
                    <div class="wk-evt-sub">{{ $wo->woLabel() }} · {{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</div>
                    @endif
                    @if($ht >= 80 && $techs->isNotEmpty())
                    <div style="display:flex;gap:.15rem;margin-top:.2rem;flex-wrap:wrap;">
                        @foreach($techs as $tech)
                        @php $ti=collect(explode(' ',$tech->name))->map(fn($w)=>strtoupper($w[0]??''))->take(2)->join(''); @endphp
                        <span class="tech-avatar" style="background:{{ $techColors[$tech->id]??'#94a3b8' }};width:16px;height:16px;font-size:.5rem;">{{ $ti }}</span>
                        @endforeach
                    </div>
                    @endif
                </a>
                @endforeach
            </div>
            @endforeach

        </div>
    </div>
</div>
@if($scheduled->isEmpty())
<p style="text-align:center;color:#9ca3af;font-size:.875rem;margin-top:1rem;">No scheduled work orders this week.</p>
@endif

{{-- ═══════════ DAY VIEW ═══════════ --}}

@elseif($view === 'day')
<div class="day-wrap">
    <div style="padding:.7rem 1rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.9rem;font-weight:700;color:var(--primary);">{{ $anchor->format('l, F j, Y') }}</span>
        <span style="font-size:.8rem;color:#9ca3af;">{{ $scheduled->count() }} {{ Str::plural('event', $scheduled->count()) }}</span>
    </div>
    <div style="padding:1rem 1rem 1rem 0;overflow-x:auto;">
        <div class="tl-body">
            @for($h = 6; $h <= 20; $h++)
            <div class="tl-hour">
                <span class="tl-label">{{ $h > 12 ? ($h-12).':00 PM' : ($h == 12 ? '12:00 PM' : $h.':00 AM') }}</span>
                <div class="tl-half"></div>
            </div>
            @endfor
            @foreach($scheduled as $visit)
            @php
                $wo     = $visit->workOrder;
                $top    = max(0, ($visit->scheduled_at->hour - 6) * 60 + $visit->scheduled_at->minute);
                $ht     = max(50, $visit->duration_estimate_minutes ?? 60);
                $clr    = $woColor($visit);
                $tids   = $woTechIds($visit);
                $techs  = $visitTechs($visit);
                $isDone = $visit->signature !== null;
                $bgClr  = $isDone ? '#f1f5f9' : $clr.'16';
                $bdClr  = $isDone ? '#94a3b8' : $clr;
            @endphp
            <a href="{{ route('admin.work-orders.show', [$wo, 'from' => 'calendar']) }}"
               class="wo-card tl-event" data-techs="{{ $tids }}"
               style="top:{{ $top }}px;height:{{ $ht }}px;border-left-color:{{ $bdClr }};background:{{ $bgClr }};{{ $isDone ? 'opacity:.72;' : '' }}">
                <div style="font-size:.7rem;font-weight:700;color:{{ $bdClr }};">{{ $visit->scheduled_at->format('g:i A') }}</div>
                <div style="font-size:.82rem;font-weight:600;color:#111;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $wo->customer->name }}{{ $isDone ? ' ✓' : '' }}</div>
                <div style="font-size:.7rem;color:#6b7280;">{{ $wo->woLabel() }} · {{ $wo->serviceTypes->pluck('name')->join(', ') ?: '—' }}</div>
                @if($techs->isNotEmpty())
                <div style="display:flex;gap:.18rem;margin-top:.2rem;">
                    @foreach($techs as $tech)
                    @php $ti=collect(explode(' ',$tech->name))->map(fn($w)=>strtoupper($w[0]??''))->take(2)->join(''); @endphp
                    <span class="tech-avatar" style="background:{{ $techColors[$tech->id]??'#94a3b8' }};">{{ $ti }}</span>
                    @endforeach
                </div>
                @endif
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════ MONTH VIEW ═══════════ --}}
@else
@php
    $gridStart = $start->copy()->startOfWeek(Carbon::SUNDAY);
    $gridEnd   = $end->copy()->endOfWeek(Carbon::SATURDAY);
    $monthWOs = WorkOrderVisit::whereHas('workOrder', fn($q) => $q->whereIn('status', [
            WorkOrder::STATUS_TRIAGED, WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK, WorkOrder::STATUS_SERVICES_PERFORMED,
        ]))
        ->with(['workOrder.customer', 'workOrder.assignments.employee', 'techUsers', 'signature'])
        ->whereNotNull('scheduled_at')
        ->whereBetween('scheduled_at', [$gridStart, $gridEnd->copy()->endOfDay()])
        ->orderBy('scheduled_at')
        ->get()
        ->groupBy(fn($v) => $v->scheduled_at->format('Y-m-d'));
@endphp
<div class="month-wrap">
    <div class="mo-dow-row">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dw)<div>{{ $dw }}</div>@endforeach
    </div>
    <div class="mo-weeks">
        @php $cur = $gridStart->copy(); @endphp
        @while($cur->lte($gridEnd))
        @php
            $dk      = $cur->format('Y-m-d');
            $isTdy   = $cur->isSameDay($today);
            $isOut   = !$cur->between($start, $end);
            $dayWOs  = $monthWOs->get($dk, collect());
        @endphp
        <div class="mo-cell {{ $isTdy ? 'today' : ($isOut ? 'outside' : '') }}">
            <a href="{{ $calUrl($dk, 'day') }}" style="text-decoration:none;">
                <span class="mo-num">{{ $cur->format('j') }}</span>
            </a>
            @foreach($dayWOs->take(3) as $visit)
            @php
                $clr    = $woColor($visit);
                $tids   = $woTechIds($visit);
                $wo     = $visit->workOrder;
                $isDone = $visit->signature !== null;
                $bgClr  = $isDone ? '#f1f5f9' : $clr.'13';
                $bdClr  = $isDone ? '#94a3b8' : $clr;
            @endphp
            <a href="{{ route('admin.work-orders.show', [$wo, 'from' => 'calendar']) }}"
               class="wo-card mo-pill" data-techs="{{ $tids }}"
               style="border-left-color:{{ $bdClr }};background:{{ $bgClr }};color:{{ $isDone ? '#6b7280' : '#111' }};{{ $isDone ? 'opacity:.8;' : '' }}">
                {{ $visit->scheduled_at->format('g:i') }} {{ $wo->customer->name }}{{ $isDone ? ' ✓' : '' }}
            </a>
            @endforeach
            @if($dayWOs->count() > 3)
            <a href="{{ $calUrl($dk, 'day') }}" style="font-size:.66rem;color:var(--accent);text-decoration:none;display:block;">+{{ $dayWOs->count()-3 }} more</a>
            @endif
        </div>
        @php $cur->addDay(); @endwhile
    </div>
</div>
@endif

<script>
const hiddenTechs = new Set();
function toggleTech(id) {
    const chip = document.getElementById('chip-' + id);
    if (hiddenTechs.has(id)) { hiddenTechs.delete(id); chip.classList.remove('off'); }
    else { hiddenTechs.add(id); chip.classList.add('off'); }
    applyVis();
}
function resetTechs() {
    hiddenTechs.clear();
    document.querySelectorAll('.tech-chip').forEach(c => c.classList.remove('off'));
    applyVis();
}
function applyVis() {
    document.querySelectorAll('.wo-card').forEach(card => {
        const ids = (card.dataset.techs||'').split(' ').map(Number).filter(Boolean);
        card.style.display = ids.length === 0 || ids.some(id => !hiddenTechs.has(id)) ? '' : 'none';
    });
}

// Day-view overlap fix
function fixDayOverlaps() {
    const els = Array.from(document.querySelectorAll('.tl-event'));
    if (els.length < 2) return;
    const evs = els.map(el => ({
        el, top: parseFloat(el.style.top),
        end: parseFloat(el.style.top) + parseFloat(el.style.height),
        col: 0, cols: 1,
    })).sort((a, b) => a.top - b.top);
    for (let i = 0; i < evs.length; i++) {
        const used = new Set(evs.slice(0, i).filter(e => e.end > evs[i].top).map(e => e.col));
        let c = 0; while (used.has(c)) c++;
        evs[i].col = c;
    }
    for (let i = 0; i < evs.length; i++) {
        let maxCol = evs[i].col;
        for (let j = 0; j < evs.length; j++) {
            if (i !== j && evs[j].top < evs[i].end && evs[j].end > evs[i].top)
                maxCol = Math.max(maxCol, evs[j].col);
        }
        evs[i].cols = maxCol + 1;
    }
    evs.forEach(({ el, col, cols }) => {
        const pct = 100 / cols;
        el.style.left  = `calc(${col * pct}% + 2px)`;
        el.style.right = 'auto';
        el.style.width = `calc(${pct}% - 6px)`;
    });
}

// Week-view overlap fix — runs per day column
function fixWeekOverlaps() {
    document.querySelectorAll('.wk-tl-col').forEach(col => {
        const els = Array.from(col.querySelectorAll('.wk-tl-event'));
        if (els.length < 2) return;
        const evs = els.map(el => ({
            el, top: parseFloat(el.style.top),
            end: parseFloat(el.style.top) + parseFloat(el.style.height),
            col: 0, cols: 1,
        })).sort((a, b) => a.top - b.top);
        for (let i = 0; i < evs.length; i++) {
            const used = new Set(evs.slice(0, i).filter(e => e.end > evs[i].top).map(e => e.col));
            let c = 0; while (used.has(c)) c++;
            evs[i].col = c;
        }
        for (let i = 0; i < evs.length; i++) {
            let maxCol = evs[i].col;
            for (let j = 0; j < evs.length; j++) {
                if (i !== j && evs[j].top < evs[i].end && evs[j].end > evs[i].top)
                    maxCol = Math.max(maxCol, evs[j].col);
            }
            evs[i].cols = maxCol + 1;
        }
        evs.forEach(({ el, col, cols }) => {
            const pct = 100 / cols;
            el.style.left  = `calc(${col * pct}% + 2px)`;
            el.style.right = 'auto';
            el.style.width = `calc(${pct}% - 6px)`;
        });
    });
}

// Current-time red line
function updateNowLine() {
    const now = new Date();
    const hrStart = {{ $hrStart }};
    const top = (now.getHours() - hrStart) * 60 + now.getMinutes();
    document.querySelectorAll('.now-line').forEach(el => {
        if (top >= 0 && top <= ({{ $hrEnd }} - hrStart) * 60) {
            el.style.top     = top + 'px';
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    fixDayOverlaps();
    fixWeekOverlaps();
    updateNowLine();
    setInterval(updateNowLine, 60000);

    // Scroll week/day body to 7 AM (1 hour × 60px from the top)
    const wkBody = document.getElementById('wkBody');
    if (wkBody) wkBody.scrollTop = 60;
});
</script>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">DISPATCH</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Calendar & Dispatch
    </h1>
</div>
@endpush
