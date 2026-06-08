@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 16 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', auth()->user()->name)[0];
@endphp

{{-- ══════════════════════════════════════════════════════
     KPI CARDS
══════════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,220px));justify-content:center;gap:1rem;margin-bottom:2rem;margin-top:.85rem;">

    {{-- Open Invoices --}}
    <a href="{{ route('admin.invoices.index', ['tab' => 'all_active']) }}"
       style="text-decoration:none;display:block;background:#fff;border-left:4px solid var(--accent);border-radius:6px;padding:1.1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.07);transition:box-shadow .15s,transform .12s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.12)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.07)';this.style.transform='none'">
        <span style="display:block;font-size:2rem;font-weight:700;color:var(--primary);line-height:1.1;">{{ $openInvoicesCount }}</span>
        <span style="font-size:.8rem;color:#777;text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:.2rem;">Open Invoices</span>
        <span style="display:block;font-size:.82rem;font-weight:600;color:var(--accent);margin-top:.6rem;">${{ number_format($openInvoicesRevenue, 2) }} uncollected</span>
    </a>

    {{-- Past Due --}}
    <a href="{{ route('admin.invoices.index', ['tab' => 'past_due']) }}"
       style="text-decoration:none;display:block;background:#fff;border-left:4px solid #dc2626;border-radius:6px;padding:1.1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.07);transition:box-shadow .15s,transform .12s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.12)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.07)';this.style.transform='none'">
        <span style="display:block;font-size:2rem;font-weight:700;color:{{ $pastDueCount > 0 ? '#dc2626' : 'var(--primary)' }};line-height:1.1;">{{ $pastDueCount }}</span>
        <span style="font-size:.8rem;color:#777;text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:.2rem;">Past Due Invoices</span>
        <span style="display:block;font-size:.82rem;font-weight:600;color:#dc2626;margin-top:.6rem;">${{ number_format($pastDueRevenue, 2) }} overdue</span>
    </a>

    {{-- Unconfirmed Visits --}}
    <a href="{{ route('admin.work-orders.index', ['queue' => 'pending_confirmation']) }}"
       style="text-decoration:none;display:block;background:#fff;border-left:4px solid #d97706;border-radius:6px;padding:1.1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.07);transition:box-shadow .15s,transform .12s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.12)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.07)';this.style.transform='none'">
        <span style="display:block;font-size:2rem;font-weight:700;color:{{ $unconfirmedCount > 0 ? '#b45309' : 'var(--primary)' }};line-height:1.1;">{{ $unconfirmedCount }}</span>
        <span style="font-size:.8rem;color:#777;text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:.2rem;">Unconfirmed Visits</span>
        <span style="display:block;font-size:.82rem;color:#b45309;margin-top:.6rem;">Awaiting customer confirmation</span>
    </a>

    {{-- Open Work Orders --}}
    <a href="{{ route('admin.work-orders.index', ['queue' => 'all']) }}"
       style="text-decoration:none;display:block;background:#fff;border-left:4px solid var(--primary);border-radius:6px;padding:1.1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.07);transition:box-shadow .15s,transform .12s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.12)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.07)';this.style.transform='none'">
        <span style="display:block;font-size:2rem;font-weight:700;color:var(--primary);line-height:1.1;">{{ $openWoCount }}</span>
        <span style="font-size:.8rem;color:#777;text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:.2rem;">Open Work Orders</span>
        <span style="display:block;font-size:.82rem;color:#888;margin-top:.6rem;">All active orders</span>
    </a>

    {{-- Scheduled Today --}}
    <a href="{{ route('admin.calendar', ['view' => 'day', 'date' => today()->format('Y-m-d')]) }}"
       style="text-decoration:none;display:block;background:#fff;border-left:4px solid #059669;border-radius:6px;padding:1.1rem 1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.07);transition:box-shadow .15s,transform .12s;"
       onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.12)';this.style.transform='translateY(-1px)'"
       onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.07)';this.style.transform='none'">
        <span style="display:block;font-size:2rem;font-weight:700;color:{{ $scheduledToday > 0 ? '#065f46' : 'var(--primary)' }};line-height:1.1;">{{ $scheduledToday }}</span>
        <span style="font-size:.8rem;color:#777;text-transform:uppercase;letter-spacing:.05em;display:block;margin-top:.2rem;">Scheduled Today</span>
        <span style="display:block;font-size:.82rem;color:#888;margin-top:.6rem;">{{ today()->format('l, M j') }}</span>
    </a>

</div>

{{-- ══════════════════════════════════════════════════════
     CALENDAR SECTION CARD
══════════════════════════════════════════════════════ --}}
<div style="background:#fff;border-radius:10px;border:1px solid #d0d5dd;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1.25rem 1.5rem;margin-bottom:1.5rem;">

{{-- Section header --}}
<div style="background:var(--primary);margin:-1.25rem -1.5rem 1.25rem;padding:.8rem 1.25rem;border-radius:9px 9px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
    <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
        <svg width="16" height="16" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <div>
            <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Schedule</div>
            <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Appointments · Time tracking</div>
        </div>
    </div>
    {{-- Week / Day toggle --}}
    <div style="display:flex;gap:.2rem;background:rgba(255,255,255,.12);border-radius:7px;padding:.2rem;">
        <a href="{{ route('admin.dashboard', array_merge(request()->only('week','sort','dir'), ['cal_view' => 'week'])) }}"
           style="padding:.28rem .85rem;border-radius:5px;font-size:.78rem;font-weight:600;text-decoration:none;transition:background .12s,color .12s;white-space:nowrap;
                  background:{{ $calView === 'week' ? 'rgba(255,255,255,.9)' : 'transparent' }};
                  color:{{ $calView === 'week' ? 'var(--primary)' : 'rgba(255,255,255,.75)' }};">Week</a>
        <a href="{{ route('admin.dashboard', array_merge(request()->only('day','sort','dir'), ['cal_view' => 'day'])) }}"
           style="padding:.28rem .85rem;border-radius:5px;font-size:.78rem;font-weight:600;text-decoration:none;transition:background .12s,color .12s;white-space:nowrap;
                  background:{{ $calView === 'day' ? 'rgba(255,255,255,.9)' : 'transparent' }};
                  color:{{ $calView === 'day' ? 'var(--primary)' : 'rgba(255,255,255,.75)' }};">Day</a>
    </div>
</div>

{{-- CALENDAR HEADER (toggle + navigation) --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;flex-wrap:wrap;gap:.5rem;">

    {{-- Left: label + prev/next arrows --}}
    <div style="display:flex;align-items:center;gap:.6rem;">
        @if($calView === 'week')
            <h2 class="section-title" style="margin:0;">Week of {{ $weekLabel }}</h2>
            <a href="{{ route('admin.dashboard', ['week' => $prevWeek, 'cal_view' => 'week']) }}"
               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;text-decoration:none;font-size:.9rem;line-height:1;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'"
               title="Previous week">‹</a>
            <a href="{{ route('admin.dashboard', ['week' => $nextWeek, 'cal_view' => 'week']) }}"
               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;text-decoration:none;font-size:.9rem;line-height:1;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'"
               title="Next week">›</a>
        @else
            <h2 class="section-title" style="margin:0;">{{ $dayLabel }}</h2>
            <a href="{{ route('admin.dashboard', ['day' => $prevDay, 'cal_view' => 'day']) }}"
               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;text-decoration:none;font-size:.9rem;line-height:1;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'"
               title="Previous day">‹</a>
            <a href="{{ route('admin.dashboard', ['day' => $nextDay, 'cal_view' => 'day']) }}"
               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;text-decoration:none;font-size:.9rem;line-height:1;transition:background .12s;"
               onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'"
               title="Next day">›</a>
            <a href="{{ route('admin.dashboard', ['day' => today()->format('Y-m-d'), 'cal_view' => 'day']) }}"
               style="font-size:.75rem;color:var(--accent);text-decoration:none;font-weight:600;padding:.2rem .55rem;border:1px solid var(--accent);border-radius:5px;"
               title="Jump to today">Today</a>
        @endif
    </div>


</div>

{{-- Tech filter buttons --}}
@if($employees->isNotEmpty())
<div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.75rem;align-items:center;">
    <span style="font-size:.75rem;color:#888;font-weight:600;white-space:nowrap;margin-right:.1rem;">Techs:</span>
    @foreach($employees as $emp)
    @php $tColor = $employeeTechColors[$emp->id] ?? '#888'; @endphp
    <button type="button"
            class="tech-filter-btn"
            data-tech-id="{{ $emp->id }}"
            data-active="1"
            onclick="toggleTech({{ $emp->id }}, this)"
            style="display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .55rem;border-radius:999px;border:2px solid {{ $tColor }};background:{{ $tColor }};color:#fff;font-size:.74rem;font-weight:600;cursor:pointer;line-height:1.3;transition:background .12s,color .12s;">
        <div style="width:15px;height:15px;border-radius:50%;overflow:hidden;background:rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            @if($emp->profile_photo && file_exists(storage_path('app/profile-photos/'.$emp->profile_photo)))
                <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            @else
                <span style="color:#fff;font-size:.48rem;font-weight:700;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
            @endif
        </div>
        {{ explode(' ', trim($emp->name))[0] }}
    </button>
    @endforeach
    <button type="button" onclick="selectAllTechs()"
            style="font-size:.72rem;color:#6b7280;background:none;border:1px solid #d1d5db;border-radius:999px;padding:.22rem .55rem;cursor:pointer;line-height:1.3;">All</button>
    <button type="button" onclick="clearAllTechs()"
            style="font-size:.72rem;color:#6b7280;background:none;border:1px solid #d1d5db;border-radius:999px;padding:.22rem .55rem;cursor:pointer;line-height:1.3;">None</button>
</div>
@endif

@php
    $calPxHr    = 56;
    $calStart   = 7;
    $calEnd     = 20;
    $calHours   = $calEnd - $calStart;
    $calHeight  = $calHours * $calPxHr;
    $calEvHt    = 56;
    // Helper: hex color → rgba string for card backgrounds
    $hexTint = function (string $hex, float $alpha) {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = sscanf($hex, '%02x%02x%02x');
        return "rgba($r,$g,$b,$alpha)";
    };
@endphp

@if($calView === 'week')
{{-- ── WEEK VIEW ─────────────────────────────────────────── --}}
{{-- Single scrollable container — headers are sticky inside so they always align with the columns --}}
<div style="max-height:580px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;background:#fff;margin-bottom:2rem;">

    {{-- Sticky day headers --}}
    <div style="position:sticky;top:0;z-index:5;display:grid;grid-template-columns:44px repeat(5,1fr);background:#f8f9fa;border-bottom:2px solid #e5e7eb;">
        <div style="border-right:1px solid #e5e7eb;"></div>
        @for ($d = 0; $d < 5; $d++)
        @php
            $hDay   = $weekStart->copy()->addDays($d);
            $hToday = $hDay->isToday();
        @endphp
        <a href="{{ route('admin.dashboard', ['cal_view' => 'day', 'day' => $hDay->format('Y-m-d'), 'week' => $weekStart->format('Y-m-d')]) }}"
           style="padding:.5rem .6rem;border-left:1px solid #e5e7eb;background:{{ $hToday ? 'var(--accent)' : 'transparent' }};text-decoration:none;display:block;transition:filter .15s;"
           onmouseover="if(!{{ $hToday ? 'true' : 'false' }})this.style.background='#eff6ff'"
           onmouseout="if(!{{ $hToday ? 'true' : 'false' }})this.style.background='transparent'"
           title="View {{ $hDay->format('l, F j') }}">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $hToday ? 'rgba(255,255,255,.8)' : '#9ca3af' }};">{{ $hDay->format('D') }}</div>
            <div style="font-size:1.15rem;font-weight:700;line-height:1.1;color:{{ $hToday ? '#fff' : 'var(--primary)' }};">{{ $hDay->format('j') }}</div>
        </a>
        @endfor
    </div>

    {{-- Time grid --}}
    <div style="display:grid;grid-template-columns:44px repeat(5,1fr);height:{{ $calHeight }}px;">

        {{-- Time gutter --}}
        <div style="position:relative;border-right:1px solid #e5e7eb;background:#fafafa;">
            @for ($h = 0; $h <= $calHours; $h++)
            @php $hr = $calStart + $h; @endphp
            <div style="position:absolute;top:{{ $h * $calPxHr }}px;right:5px;transform:translateY(-50%);font-size:.63rem;color:#9ca3af;white-space:nowrap;line-height:1;user-select:none;">
                {{ $hr === 12 ? '12pm' : ($hr < 12 ? $hr.'am' : ($hr - 12).'pm') }}
            </div>
            @endfor
        </div>

        {{-- 5 day columns --}}
        @for ($d = 0; $d < 5; $d++)
        @php
            $day      = $weekStart->copy()->addDays($d);
            $dayKey   = $day->format('Y-m-d');
            $isToday  = $day->isToday();
            $dayOrders = $weekByDay[$dayKey] ?? collect();
        @endphp
        <div class="cal-day-col" style="position:relative;border-right:{{ $d < 4 ? '1px solid #e5e7eb' : 'none' }};background:{{ $isToday ? '#f5faff' : '#fff' }};">

            {{-- Hour grid lines --}}
            @for ($h = 0; $h <= $calHours; $h++)
            <div style="position:absolute;top:{{ $h * $calPxHr }}px;left:0;right:0;border-top:1px solid {{ $h % 2 === 0 ? '#e9ecef' : '#f3f4f6' }};pointer-events:none;"></div>
            @endfor

            {{-- Events --}}
            @foreach($dayOrders as $visit)
            @php
                $wo     = $visit->workOrder;
                $evH    = $visit->scheduled_at->hour;
                $evM    = $visit->scheduled_at->minute;
                $mfs    = ($evH - $calStart) * 60 + $evM;
                $topPx  = max(0, round($mfs / 60 * $calPxHr));
                $ht     = $calEvHt;

                $chipCompany = $wo->customer->companies->firstWhere('pivot.is_primary', true)
                            ?? $wo->customer->companies->first();

                // Visit-level techs; fall back to WO assignments only if none set
                $chipTechs = $visit->techUsers->isNotEmpty()
                    ? $visit->techUsers->take(3)
                    : $wo->assignments->map(fn($a) => $a->employee)->filter()->values()->take(3);
                $chipTechIds = $visit->techUsers->isNotEmpty()
                    ? $visit->techUsers->pluck('id')->toArray()
                    : $wo->assignments->pluck('user_id')->toArray();

                // Card color — keyed by primary tech
                $primaryTechId    = $chipTechIds[0] ?? null;
                $primaryTechColor = ($primaryTechId && isset($employeeTechColors[$primaryTechId]))
                                    ? $employeeTechColors[$primaryTechId] : '#64748b';
                $chipBg     = $primaryTechColor;
                $chipBorder = $primaryTechColor;
                $chipText   = '#fff';
                $urgencyBadgeColor = match($wo->urgency) {
                    'emergency' => '#ef4444', 'urgent' => '#f59e0b', default => '#93c5fd',
                };
            @endphp
            <a href="{{ route('admin.work-orders.show', [$wo, 'from' => 'dashboard']) }}"
               class="cal-event"
               data-orig-ht="{{ $ht }}"
               data-techs="{{ json_encode($chipTechIds) }}"
               style="position:absolute;top:{{ $topPx }}px;height:{{ $ht }}px;left:2px;right:2px;
                      background:{{ $chipBg }};border-left:3px solid {{ $chipBorder }};border-radius:6px;
                      padding:.28rem .35rem;text-decoration:none;overflow:hidden;z-index:2;
                      display:flex;align-items:flex-start;gap:.28rem;
                      box-shadow:0 1px 3px rgba(0,0,0,.08);
                      transition:height .18s ease,min-width .18s ease,box-shadow .15s,z-index 0s;">

                <div style="flex:1;min-width:0;">
                    {{-- Line 1: WO# @ Time  CustomerName --}}
                    <div style="font-size:.72rem;font-weight:700;color:#fff;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $wo->woLabel() }}&nbsp;@&nbsp;{{ $visit->scheduled_at->format('g:i A') }}&nbsp;&nbsp;{{ $wo->customer->name }}</div>
                    @if($chipCompany)
                    <div class="cal-summary-line" style="font-size:.63rem;color:rgba(255,255,255,.78);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">{{ $chipCompany->name }}</div>
                    @endif
                    @if($wo->site_street)
                    <div class="cal-summary-line" style="font-size:.62rem;color:rgba(255,255,255,.65);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">{{ $wo->site_street }}</div>
                    @endif

                    {{-- Expanded content — hidden until hover --}}
                    <div class="cal-extra" style="display:none;margin-top:.35rem;padding-top:.3rem;border-top:1px solid rgba(255,255,255,.25);">
                        @if($wo->description)
                        <div style="font-size:.67rem;color:rgba(255,255,255,.85);line-height:1.35;margin-bottom:.2rem;">
                            <span style="font-weight:600;color:#fff;">Description:</span> {{ Str::limit($wo->description, 120) }}
                        </div>
                        @endif
                        @if($wo->site_contact_name || $wo->site_contact_phone)
                        <div style="font-size:.67rem;color:rgba(255,255,255,.85);line-height:1.35;margin-bottom:.2rem;">
                            <span style="font-weight:600;color:#fff;">Contact:</span>
                            {{ $wo->site_contact_name }}{{ $wo->site_contact_name && $wo->site_contact_phone ? ' · ' : '' }}{{ $wo->site_contact_phone }}
                        </div>
                        @endif
                        @if($chipTechs->isNotEmpty())
                        <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-top:.25rem;">
                            @foreach($chipTechs as $emp)
                            @php $empColor = $employeeTechColors[$emp->id] ?? '#64748b'; @endphp
                            <div style="display:flex;align-items:center;gap:.25rem;">
                                <div style="width:18px;height:18px;border-radius:50%;overflow:hidden;background:{{ $empColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    @if($emp->profile_photo && file_exists(storage_path('app/profile-photos/'.$emp->profile_photo)))
                                        <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span style="color:#fff;font-size:.48rem;font-weight:700;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
                                    @endif
                                </div>
                                <span style="font-size:.67rem;color:rgba(255,255,255,.85);white-space:nowrap;">{{ $emp->name }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        <div style="margin-top:.3rem;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                            <span style="font-size:.63rem;font-weight:600;padding:.1rem .4rem;border-radius:999px;background:rgba(255,255,255,.2);color:#fff;">{{ str_replace('_',' ',ucfirst($wo->status)) }}</span>
                            <span style="font-size:.6rem;font-weight:700;color:{{ $urgencyBadgeColor }};background:#fff;border:1px solid {{ $urgencyBadgeColor }};border-radius:3px;padding:.02rem .25rem;line-height:1.3;white-space:nowrap;flex-shrink:0;">{{ ucfirst($wo->urgency) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tech avatars — top right, always visible --}}
                @if($chipTechs->isNotEmpty())
                <div class="cal-avatars" style="flex-shrink:0;display:flex;flex-direction:column;gap:1px;align-items:center;padding-top:.05rem;">
                    @foreach($chipTechs as $emp)
                    @php $empColor = $employeeTechColors[$emp->id] ?? '#64748b'; @endphp
                    <div title="{{ $emp->name }}"
                         style="width:20px;height:20px;border-radius:50%;border:1.5px solid #fff;overflow:hidden;background:{{ $empColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        @if($emp->profile_photo && file_exists(storage_path('app/profile-photos/'.$emp->profile_photo)))
                            <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span style="color:#fff;font-size:.52rem;font-weight:700;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </a>
            @endforeach

        </div>
        @endfor

    </div>
</div>

@else
{{-- ── DAY VIEW ──────────────────────────────────────────── --}}
<div style="max-height:580px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;background:#fff;margin-bottom:2rem;">

    {{-- Sticky day header --}}
    <div style="position:sticky;top:0;z-index:5;display:grid;grid-template-columns:44px 1fr;background:#f8f9fa;border-bottom:2px solid #e5e7eb;">
        <div style="border-right:1px solid #e5e7eb;"></div>
        <div style="padding:.5rem .75rem;background:{{ $dayAnchor->isToday() ? 'var(--accent)' : 'transparent' }};">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $dayAnchor->isToday() ? 'rgba(255,255,255,.8)' : '#9ca3af' }};">{{ $dayAnchor->format('D') }}</div>
            <div style="font-size:1.1rem;font-weight:700;line-height:1.1;color:{{ $dayAnchor->isToday() ? '#fff' : 'var(--primary)' }};">{{ $dayAnchor->format('F j, Y') }}</div>
        </div>
    </div>

    {{-- Time grid --}}
    <div style="display:grid;grid-template-columns:44px 1fr;height:{{ $calHeight }}px;">

        {{-- Time gutter --}}
        <div style="position:relative;border-right:1px solid #e5e7eb;background:#fafafa;">
            @for ($h = 0; $h <= $calHours; $h++)
            @php $hr = $calStart + $h; @endphp
            <div style="position:absolute;top:{{ $h * $calPxHr }}px;right:5px;transform:translateY(-50%);font-size:.63rem;color:#9ca3af;white-space:nowrap;line-height:1;user-select:none;">
                {{ $hr === 12 ? '12pm' : ($hr < 12 ? $hr.'am' : ($hr - 12).'pm') }}
            </div>
            @endfor
        </div>

        {{-- Single day column --}}
        <div class="cal-day-col" style="position:relative;background:{{ $dayAnchor->isToday() ? '#f5faff' : '#fff' }};">
            @for ($h = 0; $h <= $calHours; $h++)
            <div style="position:absolute;top:{{ $h * $calPxHr }}px;left:0;right:0;border-top:1px solid {{ $h % 2 === 0 ? '#e9ecef' : '#f3f4f6' }};pointer-events:none;"></div>
            @endfor

            @foreach($dayVisits as $visit)
            @php
                $wo     = $visit->workOrder;
                $evH    = $visit->scheduled_at->hour;
                $evM    = $visit->scheduled_at->minute;
                $mfs    = ($evH - $calStart) * 60 + $evM;
                $topPx  = max(0, round($mfs / 60 * $calPxHr));
                $ht     = $calEvHt;
                $chipCompany = $wo->customer->companies->firstWhere('pivot.is_primary', true)
                            ?? $wo->customer->companies->first();

                $chipTechs = $visit->techUsers->isNotEmpty()
                    ? $visit->techUsers->take(3)
                    : $wo->assignments->map(fn($a) => $a->employee)->filter()->values()->take(3);
                $chipTechIds = $visit->techUsers->isNotEmpty()
                    ? $visit->techUsers->pluck('id')->toArray()
                    : $wo->assignments->pluck('user_id')->toArray();

                // Card color — keyed by primary tech
                $primaryTechId    = $chipTechIds[0] ?? null;
                $primaryTechColor = ($primaryTechId && isset($employeeTechColors[$primaryTechId]))
                                    ? $employeeTechColors[$primaryTechId] : '#64748b';
                $chipBg     = $primaryTechColor;
                $chipBorder = $primaryTechColor;
                $chipText   = '#fff';
                $urgencyBadgeColor = match($wo->urgency) {
                    'emergency' => '#ef4444', 'urgent' => '#f59e0b', default => '#93c5fd',
                };
            @endphp
            <a href="{{ route('admin.work-orders.show', [$wo, 'from' => 'dashboard']) }}"
               class="cal-event"
               data-orig-ht="{{ $ht }}"
               data-techs="{{ json_encode($chipTechIds) }}"
               style="position:absolute;top:{{ $topPx }}px;height:{{ $ht }}px;left:2px;right:2px;
                      background:{{ $chipBg }};border-left:3px solid {{ $chipBorder }};border-radius:6px;
                      padding:.28rem .35rem;text-decoration:none;overflow:hidden;z-index:2;
                      display:flex;align-items:flex-start;gap:.28rem;
                      box-shadow:0 1px 3px rgba(0,0,0,.08);
                      transition:height .18s ease,min-width .18s ease,box-shadow .15s,z-index 0s;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.72rem;font-weight:700;color:#fff;line-height:1.25;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $wo->woLabel() }}&nbsp;@&nbsp;{{ $visit->scheduled_at->format('g:i A') }}&nbsp;&nbsp;{{ $wo->customer->name }}</div>
                    @if($chipCompany)
                    <div class="cal-summary-line" style="font-size:.63rem;color:rgba(255,255,255,.78);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">{{ $chipCompany->name }}</div>
                    @endif
                    @if($wo->site_street)
                    <div class="cal-summary-line" style="font-size:.62rem;color:rgba(255,255,255,.65);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.2;">{{ $wo->site_street }}</div>
                    @endif
                    <div class="cal-extra" style="display:none;margin-top:.35rem;padding-top:.3rem;border-top:1px solid rgba(255,255,255,.25);">
                        @if($wo->description)
                        <div style="font-size:.67rem;color:rgba(255,255,255,.85);line-height:1.35;margin-bottom:.2rem;">
                            <span style="font-weight:600;color:#fff;">Description:</span> {{ Str::limit($wo->description, 120) }}
                        </div>
                        @endif
                        @if($wo->site_contact_name || $wo->site_contact_phone)
                        <div style="font-size:.67rem;color:rgba(255,255,255,.85);line-height:1.35;margin-bottom:.2rem;">
                            <span style="font-weight:600;color:#fff;">Contact:</span>
                            {{ $wo->site_contact_name }}{{ $wo->site_contact_name && $wo->site_contact_phone ? ' · ' : '' }}{{ $wo->site_contact_phone }}
                        </div>
                        @endif
                        @if($chipTechs->isNotEmpty())
                        <div style="display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;margin-top:.25rem;">
                            @foreach($chipTechs as $emp)
                            @php $empColor = $employeeTechColors[$emp->id] ?? '#64748b'; @endphp
                            <div style="display:flex;align-items:center;gap:.25rem;">
                                <div style="width:18px;height:18px;border-radius:50%;overflow:hidden;background:{{ $empColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    @if($emp->profile_photo && file_exists(storage_path('app/profile-photos/'.$emp->profile_photo)))
                                        <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <span style="color:#fff;font-size:.48rem;font-weight:700;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
                                    @endif
                                </div>
                                <span style="font-size:.67rem;color:rgba(255,255,255,.85);white-space:nowrap;">{{ $emp->name }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                        <div style="margin-top:.3rem;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
                            <span style="font-size:.63rem;font-weight:600;padding:.1rem .4rem;border-radius:999px;background:rgba(255,255,255,.2);color:#fff;">{{ str_replace('_',' ',ucfirst($wo->status)) }}</span>
                            <span style="font-size:.6rem;font-weight:700;color:{{ $urgencyBadgeColor }};background:#fff;border:1px solid {{ $urgencyBadgeColor }};border-radius:3px;padding:.02rem .25rem;line-height:1.3;white-space:nowrap;flex-shrink:0;">{{ ucfirst($wo->urgency) }}</span>
                        </div>
                    </div>
                </div>
                @if($chipTechs->isNotEmpty())
                <div class="cal-avatars" style="flex-shrink:0;display:flex;flex-direction:column;gap:1px;align-items:center;padding-top:.05rem;">
                    @foreach($chipTechs as $emp)
                    @php $empColor = $employeeTechColors[$emp->id] ?? '#64748b'; @endphp
                    <div title="{{ $emp->name }}"
                         style="width:20px;height:20px;border-radius:50%;border:1.5px solid #fff;overflow:hidden;background:{{ $empColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        @if($emp->profile_photo && file_exists(storage_path('app/profile-photos/'.$emp->profile_photo)))
                            <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span style="color:#fff;font-size:.52rem;font-weight:700;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </a>
            @endforeach
        </div>

    </div>
</div>
@endif {{-- end cal_view --}}

<script>
(function () {
    /* ── 0. Tech filter state ─────────────────────────────── */
    var techColors  = @json($employeeTechColors);
    var allTechIds  = @json($employees->pluck('id')->toArray());
    var activeTechs = new Set(allTechIds);

    /* ── 1. Overlap column layout (callable) ──────────────── */
    function layoutColumn(col) {
        var allEls = Array.from(col.querySelectorAll('.cal-event'));

        // Reset every event to full-width before recalculating
        allEls.forEach(function (el) {
            el.style.left  = '2px';
            el.style.right = '2px';
            el.style.width = '';
            el.dataset.origLeft  = '2px';
            el.dataset.origWidth = '';
        });

        var els = allEls.filter(function (el) { return el.style.display !== 'none'; });
        if (els.length < 2) return;

        var events = els.map(function (el) {
            var top = parseFloat(el.style.top) || 0;
            var ht  = parseFloat(el.dataset.origHt || el.style.height) || 56;
            return { el: el, top: top, bot: top + ht, col: 0, numCols: 1 };
        }).sort(function (a, b) { return a.top - b.top; });

        var colBots = [];
        events.forEach(function (ev) {
            var placed = false;
            for (var c = 0; c < colBots.length; c++) {
                if (ev.top >= colBots[c] - 1) {
                    colBots[c] = ev.bot;
                    ev.col = c;
                    placed = true;
                    break;
                }
            }
            if (!placed) { ev.col = colBots.length; colBots.push(ev.bot); }
        });

        events.forEach(function (ev) {
            var maxCol = ev.col;
            events.forEach(function (other) {
                if (other === ev) return;
                if (other.top < ev.bot - 1 && other.bot > ev.top + 1 && other.col > maxCol)
                    maxCol = other.col;
            });
            ev.numCols = maxCol + 1;
        });

        var GAP = 3;
        events.forEach(function (ev) {
            var pct  = 100 / ev.numCols;
            var left = (ev.col * pct) + '%';
            var w    = 'calc(' + pct + '% - ' + GAP + 'px)';
            ev.el.style.left  = left;
            ev.el.style.right = 'auto';
            ev.el.style.width = w;
            ev.el.dataset.origLeft  = left;
            ev.el.dataset.origWidth = w;
        });
    }

    function layoutAll() {
        document.querySelectorAll('.cal-day-col').forEach(layoutColumn);
    }

    /* ── 2. Tech filtering ────────────────────────────────── */
    function filterEvents() {
        // No employees configured → nothing to filter, show everything
        if (allTechIds.length === 0) return;

        document.querySelectorAll('.cal-event').forEach(function (el) {
            var techs = JSON.parse(el.dataset.techs || '[]');
            // Only consider techs that are in the filterable set (active employees)
            var filterableTechs = techs.filter(function (id) { return allTechIds.indexOf(id) !== -1; });
            var show = activeTechs.size === 0
                ? false
                : (filterableTechs.length === 0 || filterableTechs.some(function (id) { return activeTechs.has(id); }));
            el.style.display = show ? 'flex' : 'none';
        });
    }

    function filterTable() {
        var visible = 0;
        document.querySelectorAll('.week-schedule-row').forEach(function (row) {
            var show = true;
            if (allTechIds.length > 0) {
                var techs = JSON.parse(row.dataset.techs || '[]');
                var filterableTechs = techs.filter(function (id) { return allTechIds.indexOf(id) !== -1; });
                show = activeTechs.size === 0
                    ? false
                    : (filterableTechs.length === 0 || filterableTechs.some(function (id) { return activeTechs.has(id); }));
            }
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        var badge = document.getElementById('schedule-count');
        if (badge) badge.textContent = visible + (visible === 1 ? ' order' : ' orders');
    }

    function filterAndLayout() { filterEvents(); filterTable(); layoutAll(); }

    /* ── 3. Button actions (global so onclick="" works) ───── */
    window.toggleTech = function (id, btn) {
        var color = techColors[id] || '#888';
        if (activeTechs.has(id)) {
            activeTechs.delete(id);
            btn.style.background = 'transparent';
            btn.style.color      = color;
        } else {
            activeTechs.add(id);
            btn.style.background = color;
            btn.style.color      = '#fff';
        }
        filterAndLayout();
    };

    window.selectAllTechs = function () {
        document.querySelectorAll('.tech-filter-btn').forEach(function (btn) {
            var id    = parseInt(btn.dataset.techId, 10);
            var color = techColors[id] || '#888';
            activeTechs.add(id);
            btn.style.background = color;
            btn.style.color      = '#fff';
        });
        filterAndLayout();
    };

    window.clearAllTechs = function () {
        activeTechs.clear();
        document.querySelectorAll('.tech-filter-btn').forEach(function (btn) {
            var id    = parseInt(btn.dataset.techId, 10);
            var color = techColors[id] || '#888';
            btn.style.background = 'transparent';
            btn.style.color      = color;
        });
        filterAndLayout();
    };

    /* ── 4. Initial render ────────────────────────────────── */
    filterAndLayout();

    /* ── 5. Hover expand / collapse ───────────────────────── */
    document.querySelectorAll('.cal-event').forEach(function (el) {
        var origHt = parseFloat(el.dataset.origHt) || 56;
        var collapseTimer;

        el.addEventListener('mouseenter', function () {
            clearTimeout(collapseTimer);
            el.style.zIndex       = '50';
            el.style.overflow     = 'visible';
            el.style.height       = 'auto';
            el.style.minHeight    = origHt + 'px';
            el.style.minWidth     = '210px';
            el.style.boxShadow    = '0 6px 24px rgba(0,0,0,.22)';
            el.style.borderRadius = '7px';
            el.querySelectorAll('.cal-summary-line').forEach(function (ln) {
                ln.style.whiteSpace = 'normal';
                ln.style.overflow   = 'visible';
            });
            el.querySelectorAll('.cal-extra').forEach(function (x)  { x.style.display = 'block'; });
            el.querySelectorAll('.cal-avatars').forEach(function (a) { a.style.display = 'none'; });
        });

        el.addEventListener('mouseleave', function () {
            collapseTimer = setTimeout(function () {
                el.style.zIndex       = '2';
                el.style.overflow     = 'hidden';
                el.style.height       = origHt + 'px';
                el.style.minHeight    = '';
                el.style.minWidth     = '';
                el.style.boxShadow    = '0 1px 3px rgba(0,0,0,.08)';
                el.style.borderRadius = '6px';
                el.querySelectorAll('.cal-summary-line').forEach(function (ln) {
                    ln.style.whiteSpace = 'nowrap';
                    ln.style.overflow   = 'hidden';
                });
                el.querySelectorAll('.cal-extra').forEach(function (x)  { x.style.display = 'none'; });
                el.querySelectorAll('.cal-avatars').forEach(function (a) { a.style.display = 'flex'; });
            }, 80);
        });
    });
}());
</script>

{{-- ══════════════════════════════════════════════════════
     WEEK SCHEDULE LIST
══════════════════════════════════════════════════════ --}}
@php
    $scheduleVisits = $calView === 'day' ? $dayVisits : $weekVisits;
    $listDurFmt = fn($m) => $m >= 60 ? floor($m/60).'h'.($m%60 ? ' '.($m%60).'m' : '') : $m.'m';
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem;margin-top:1.25rem;">
    <span style="font-size:.85rem;color:#6b7280;font-weight:500;">{{ $calView === 'day' ? $dayLabel : $weekLabel }}</span>
    <span id="schedule-count" style="display:inline-flex;align-items:center;justify-content:center;padding:.2rem .65rem;background:var(--primary);color:#fff;border-radius:999px;font-size:.75rem;font-weight:700;">{{ $scheduleVisits->count() }} visit{{ $scheduleVisits->count() === 1 ? '' : 's' }}</span>
</div>

@php
$mkSort = fn($col) => route('admin.dashboard', array_filter([
    'week'     => request('week'),
    'day'      => request('day'),
    'cal_view' => $calView,
    'sort'     => $col,
    'dir'      => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc',
], fn($v) => $v !== null && $v !== ''));
$sortIcon = fn($col) => $sort === $col
    ? '<span style="color:var(--accent);font-size:.7rem;margin-left:.15rem;">'.($dir === 'asc' ? '↑' : '↓').'</span>'
    : '<span style="color:#d1d5db;font-size:.7rem;margin-left:.15rem;">↕</span>';
@endphp

<table class="data-table">
    <thead>
        <tr>
            <th>
                <a href="{{ $mkSort('wo_number') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">#&nbsp;{!! $sortIcon('wo_number') !!}</a>
                <div style="font-size:.68rem;color:#9ca3af;font-weight:400;margin-top:.15rem;">Status</div>
            </th>
            <th>
                <a href="{{ $mkSort('scheduled_at') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">Visit&nbsp;{!! $sortIcon('scheduled_at') !!}</a>
            </th>
            <th>Customer</th>
            <th>Service Details</th>
            <th>Techs</th>
            <th><a href="{{ $mkSort('urgency') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">Urgency&nbsp;{!! $sortIcon('urgency') !!}</a></th>
        </tr>
    </thead>
    <tbody>
        @forelse($scheduleVisits as $visit)
        @php
            $wo = $visit->workOrder;
            $listTechs = $visit->techUsers->isNotEmpty()
                ? $visit->techUsers
                : $wo->assignments->map(fn($a) => $a->employee)->filter()->values();
            $listTechIds = $visit->techUsers->isNotEmpty()
                ? $visit->techUsers->pluck('id')->toArray()
                : $wo->assignments->pluck('user_id')->toArray();
        @endphp
        <tr class="week-schedule-row"
            data-href="{{ route('admin.work-orders.show', [$wo, 'from' => 'dashboard']) }}"
            data-techs="{{ json_encode($listTechIds) }}">

            {{-- WO # + Status + badges --}}
            <td style="white-space:nowrap;vertical-align:top;">
                <div>{{ $wo->woLabel() }}</div>
                <div style="margin-top:.3rem;">
                    <span class="badge badge-{{ $wo->status }}" style="font-size:.68rem;">{{ str_replace('_', ' ', $wo->status) }}</span>
                </div>
                @if($wo->needs_invoice)
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#d1fae5;border:1px solid #6ee7b7;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#065f46;">📄 Invoice Needed</span>
                </div>
                @endif
            </td>

            {{-- Visit date/time + duration --}}
            <td style="font-size:.82rem;white-space:nowrap;vertical-align:top;">
                <div style="font-weight:600;color:{{ $visit->scheduled_at->isToday() ? 'var(--accent)' : 'var(--primary)' }};">{{ $visit->scheduled_at->format('D, M j') }}</div>
                <div style="color:#9ca3af;font-size:.75rem;">{{ $visit->scheduled_at->format('g:i A') }}</div>
                @if($visit->duration_estimate_minutes)
                <div style="font-size:.7rem;color:#bbb;margin-top:.1rem;">{{ $listDurFmt($visit->duration_estimate_minutes) }} est.</div>
                @endif
            </td>

            {{-- Customer --}}
            <td style="vertical-align:top;">{{ $wo->customer->name }}</td>

            {{-- Service Details --}}
            <td style="vertical-align:top;max-width:300px;">
                @if($wo->serviceTypes->count())
                <div style="display:flex;flex-wrap:wrap;gap:.2rem;margin-bottom:.3rem;">
                    @foreach($wo->serviceTypes as $svc)
                    <span style="background:#e0f2fe;color:#0369a1;padding:.05rem .4rem;border-radius:999px;font-size:.68rem;font-weight:600;white-space:nowrap;">{{ $svc->name }}</span>
                    @endforeach
                </div>
                @endif
                @if($wo->description)
                <div style="font-size:.78rem;color:#374151;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $wo->description }}</div>
                @endif
                @if($wo->site_contact_name || $wo->site_contact_phone)
                <div style="font-size:.74rem;color:#6b7280;margin-top:.2rem;">
                    @if($wo->site_contact_name)<span style="font-weight:500;color:#4b5563;">{{ $wo->site_contact_name }}</span>@endif
                    @if($wo->site_contact_name && $wo->site_contact_phone)<span style="color:#d1d5db;"> · </span>@endif
                    @if($wo->site_contact_phone){{ $wo->site_contact_phone }}@endif
                </div>
                @endif
                @if($wo->site_street)
                <div style="font-size:.72rem;color:#9ca3af;margin-top:.1rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $wo->site_street }}</div>
                @endif
                @if(!$wo->serviceTypes->count() && !$wo->description && !$wo->site_contact_name && !$wo->site_contact_phone && !$wo->site_street)
                <span style="color:#d1d5db;">—</span>
                @endif
            </td>

            {{-- Techs (visit-level, fallback to WO assignments) --}}
            <td style="vertical-align:top;">
                <div style="display:flex;">
                    @forelse($listTechs as $emp)
                    <div title="{{ $emp->name }}"
                         style="width:30px;height:30px;border-radius:50%;border:2px solid #fff;overflow:hidden;margin-right:-6px;flex-shrink:0;background:{{ $employeeTechColors[$emp->id] ?? 'var(--primary)' }};display:flex;align-items:center;justify-content:center;">
                        @if($emp->profile_photo)
                            <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <span style="color:#fff;font-size:.68rem;font-weight:600;line-height:1;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
                        @endif
                    </div>
                    @empty
                    <span style="color:#bbb;font-size:.82rem;">—</span>
                    @endforelse
                </div>
            </td>

            {{-- Urgency --}}
            <td style="vertical-align:top;">
                <span class="badge" style="background:{{ $wo->urgency === 'emergency' ? '#fee2e2' : ($wo->urgency === 'urgent' ? '#fef3c7' : '#f3f4f6') }};color:{{ $wo->urgency === 'emergency' ? '#991b1b' : ($wo->urgency === 'urgent' ? '#92400e' : '#374151') }};">
                    {{ ucfirst($wo->urgency) }}
                </span>
            </td>

        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center;color:#999;padding:2.5rem;">No visits scheduled {{ $calView === 'day' ? 'today' : 'this week' }}.</td>
        </tr>
        @endforelse
    </tbody>
</table>

</div>{{-- /calendar section card --}}

@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">ADMIN DASHBOARD</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        {{ $greeting }}, {{ $firstName }}
    </h1>
</div>
@endpush
