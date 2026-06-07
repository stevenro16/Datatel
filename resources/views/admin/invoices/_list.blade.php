{{-- Sort helper --}}
@php
$mkSort = fn($col) => route('admin.invoices.index', array_filter(
    array_merge(request()->query(), ['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]),
    fn($v) => $v !== null && $v !== ''
));
$sortIcon = fn($col) => $sort === $col
    ? '<span style="color:var(--accent);font-size:.7rem;margin-left:.15rem;">'.($dir === 'asc' ? '↑' : '↓').'</span>'
    : '<span style="color:#d1d5db;font-size:.7rem;margin-left:.15rem;">↕</span>';
$thLink = 'text-decoration:none;color:inherit;display:inline-flex;align-items:center;';
@endphp

{{-- Customer filter badge --}}
@if($customerId)
@php $filteredCustomer = \App\Models\User::find($customerId); @endphp
@if($filteredCustomer)
<div style="display:inline-flex;align-items:center;gap:.5rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.4rem .85rem;margin-bottom:1rem;font-size:.82rem;color:#1e40af;">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
    Filtered to: <strong>{{ $filteredCustomer->name }}</strong>
    <a href="{{ route('admin.invoices.index', ['tab' => $tab]) }}" style="color:#6b7280;margin-left:.25rem;text-decoration:none;font-size:1rem;line-height:1;" title="Remove filter">×</a>
</div>
@endif
@endif

<style>
.inv-resize-handle{position:absolute;right:0;top:0;bottom:0;width:6px;cursor:col-resize;z-index:2;border-right:2px solid transparent;transition:border-color .12s;}
.inv-resize-handle:hover,.inv-resize-handle.dragging{border-color:rgba(255,255,255,.55);}
</style>

<table id="inv-col-table" class="data-table" style="table-layout:fixed;">
    <colgroup>
        <col data-col="inv_num"  style="width:130px">
        <col data-col="dates"    style="width:130px">
        <col data-col="customer" style="width:200px">
        <col>{{-- Work Order: fills remaining space --}}
        <col data-col="total"    style="width:100px">
    </colgroup>
    <thead>
        <tr>
            <th data-col="inv_num" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('id') }}" style="{{ $thLink }}"># {!! $sortIcon('id') !!}</a>
                <div style="font-size:.68rem;color:#9ca3af;font-weight:400;margin-top:.15rem;">Status</div>
                <div class="inv-resize-handle"></div>
            </th>
            <th data-col="dates" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('created_at') }}" style="{{ $thLink }}">Created {!! $sortIcon('created_at') !!}</a>
                <div style="font-size:.68rem;color:#9ca3af;font-weight:400;margin-top:.15rem;">
                    <a href="{{ $mkSort('due_date') }}" style="{{ $thLink }}">Due {!! $sortIcon('due_date') !!}</a>
                </div>
                <div class="inv-resize-handle"></div>
            </th>
            <th data-col="customer" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('customer_name') }}" style="{{ $thLink }}">Customer {!! $sortIcon('customer_name') !!}</a>
                <div class="inv-resize-handle"></div>
            </th>
            <th><a href="{{ $mkSort('work_order_id') }}" style="{{ $thLink }}">Work Order {!! $sortIcon('work_order_id') !!}</a></th>
            <th data-col="total" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('total') }}" style="{{ $thLink }}">Total {!! $sortIcon('total') !!}</a>
                <div class="inv-resize-handle"></div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $inv)
        @php
            $today     = now()->toDateString();
            $isPastDue = $inv->status === \App\Models\Invoice::STATUS_ISSUED
                      && $inv->due_date
                      && $inv->due_date->toDateString() < $today;

            $statusBg = match($inv->status) {
                'draft'            => '#fef3c7',
                'issued'           => '#dbeafe',
                'payment_received' => '#d1fae5',
                'completed'        => '#f0fdf4',
                'canceled'         => '#fee2e2',
                default            => '#f3f4f6',
            };
            $statusColor = match($inv->status) {
                'draft'            => '#92400e',
                'issued'           => '#1e40af',
                'payment_received' => '#065f46',
                'completed'        => '#14532d',
                'canceled'         => '#991b1b',
                default            => '#374151',
            };
            $statusLabel = match($inv->status) {
                'draft'            => 'New',
                'issued'           => 'Billed',
                'payment_received' => 'Payment Received',
                'completed'        => 'Completed',
                'canceled'         => 'Canceled',
                default            => ucfirst($inv->status),
            };
        @endphp
        <tr data-href="{{ route('admin.invoices.show', $inv) }}">

            {{-- Invoice # + Status --}}
            <td style="white-space:nowrap;vertical-align:top;">
                <div style="font-weight:700;color:var(--primary);">INV-{{ str_pad($inv->id, 4, '0', STR_PAD_LEFT) }}</div>
                <div style="margin-top:.3rem;">
                    <span class="badge" style="background:{{ $statusBg }};color:{{ $statusColor }};font-size:.68rem;">{{ $statusLabel }}</span>
                </div>
                @if($isPastDue)
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fee2e2;border:1px solid #fca5a5;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#991b1b;">⚠ Past Due</span>
                </div>
                @endif
            </td>

            {{-- Created + Due --}}
            <td style="font-size:.82rem;color:#666;white-space:nowrap;vertical-align:top;">
                <div>{{ $inv->created_at->format('M j, Y') }}</div>
                <div style="color:#9ca3af;font-size:.75rem;">{{ $inv->created_at->format('g:i A') }}</div>
                @if($inv->due_date)
                <div style="margin-top:.2rem;font-size:.75rem;color:{{ $isPastDue ? '#dc2626' : '#9ca3af' }};font-weight:{{ $isPastDue ? '600' : '400' }};">
                    Due {{ $inv->due_date->format('M j, Y') }}
                </div>
                @endif
            </td>

            {{-- Customer --}}
            <td style="vertical-align:top;">
                <div style="font-weight:500;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $inv->workOrder->customer->name ?? '—' }}</div>
                @if(!empty($inv->workOrder->customer->email))
                <div style="font-size:.75rem;color:#9ca3af;margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $inv->workOrder->customer->email }}</div>
                @endif
            </td>

            {{-- Work Order + covered visits --}}
            <td style="vertical-align:top;">
                @if($inv->work_order_id)
                @php
                    $coveredIds    = $inv->covered_visit_ids ?? [];
                    $wo            = $inv->workOrder;
                    $coveredVisits = ($wo && count($coveredIds))
                        ? $wo->visits->whereIn('id', $coveredIds)->sortBy('scheduled_at')->values()
                        : collect();
                @endphp
                <span style="display:inline-flex;align-items:center;gap:.3rem;flex-wrap:wrap;">
                    <a href="{{ route('admin.work-orders.show', $inv->work_order_id) }}"
                       onclick="event.stopPropagation()"
                       style="color:var(--accent);font-weight:700;font-size:.82rem;text-decoration:none;">
                        WO-{{ str_pad($inv->work_order_id, 5, '0', STR_PAD_LEFT) }}
                    </a>
                    @if($wo && $wo->visits->count() > 0)
                    @php $totalVisits = $wo->visits->count(); $billedVisits = $coveredVisits->count(); @endphp
                    <span style="font-size:.72rem;color:#6b7280;white-space:nowrap;">
                        ({{ $billedVisits }} of {{ $totalVisits }})
                        <span title="This invoice covers {{ $billedVisits }} {{ Str::plural('visit', $billedVisits) }} out of {{ $totalVisits }} total {{ Str::plural('visit', $totalVisits) }} on this work order"
                              style="display:inline-flex;align-items:center;justify-content:center;width:13px;height:13px;border-radius:50%;background:#f3f4f6;color:#c9cdd4;font-size:.6rem;font-weight:600;cursor:help;vertical-align:middle;line-height:1;flex-shrink:0;">i</span>
                    </span>
                    @endif
                </span>

                {{-- Visit cards: up to 2 per row --}}
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.35rem;margin-top:.4rem;">
                @foreach($coveredVisits as $cv)
                @php
                    $entries  = $cv->timeEntries;
                    $arrival  = $entries->whereNotNull('clocked_in_at')->min('clocked_in_at');
                    $depart   = $entries->whereNotNull('clocked_out_at')->max('clocked_out_at');
                    $durMins  = ($arrival && $depart)
                        ? (int) \Carbon\Carbon::parse($arrival)->diffInMinutes(\Carbon\Carbon::parse($depart))
                        : null;
                    $durFmt   = $durMins !== null
                        ? ($durMins >= 60
                            ? floor($durMins/60).'h'.($durMins % 60 ? ' '.($durMins % 60).'m' : '')
                            : $durMins.'m')
                        : null;
                    $cvSig    = $cv->signature;
                    $sigPath  = $cvSig ? storage_path('app/signatures/work-orders/'.$cvSig->signature_path) : null;
                    $sigOk    = $sigPath && file_exists($sigPath);
                    $lateMins = $arrival
                        ? (int) $cv->scheduled_at->diffInMinutes(\Carbon\Carbon::parse($arrival), false)
                        : null;
                @endphp
                <div style="border:1px solid #e5e7eb;border-radius:6px;padding:.45rem .6rem;background:#fafafa;">

                    {{-- Header: date + scheduled time left, tech avatars right --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.35rem;margin-bottom:.15rem;">
                        <div>
                            <span style="font-size:.78rem;font-weight:700;color:#1e293b;">{{ $cv->scheduled_at->format('M j, Y') }}</span>
                            <span style="font-size:.72rem;color:#6b7280;margin-left:.3rem;">{{ $cv->scheduled_at->format('g:i A') }}</span>
                        </div>
                        @if($cv->techUsers->isNotEmpty())
                        <div style="display:flex;gap:.18rem;flex-shrink:0;">
                            @foreach($cv->techUsers->take(3) as $tech)
                            @php $hasPhoto = $tech->profile_photo && file_exists(storage_path('app/profile-photos/'.$tech->profile_photo)); @endphp
                            @if($hasPhoto)
                            <img src="{{ route('users.photo', $tech) }}" alt="{{ $tech->name }}" title="{{ $tech->name }}"
                                 style="width:22px;height:22px;border-radius:50%;object-fit:cover;border:1.5px solid #bfdbfe;flex-shrink:0;">
                            @else
                            <div title="{{ $tech->name }}"
                                 style="width:22px;height:22px;border-radius:50%;background:var(--primary);border:1.5px solid #bfdbfe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:.58rem;font-weight:700;color:#fff;line-height:1;">{{ strtoupper(substr($tech->name,0,1)) }}</span>
                            </div>
                            @endif
                            @endforeach
                            @if($cv->techUsers->count() > 3)
                            <div title="{{ $cv->techUsers->skip(3)->pluck('name')->join(', ') }}"
                                 style="width:22px;height:22px;border-radius:50%;background:#e5e7eb;border:1.5px solid #d1d5db;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:.55rem;font-weight:700;color:#6b7280;line-height:1;">+{{ $cv->techUsers->count() - 3 }}</span>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Address --}}
                    @if($wo->site_street)
                    <div style="font-size:.68rem;color:#6b7280;margin-bottom:.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $wo->site_street }}">
                        📍 {{ $wo->site_street }}
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
                        @if($durFmt)
                        <span style="font-weight:700;color:#059669;white-space:nowrap;">{{ $durFmt }}</span>
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
                             style="height:26px;max-width:90px;object-fit:contain;background:#fff;border:1px solid #e2e8f0;border-radius:3px;padding:1px;flex-shrink:0;">
                        <span style="font-size:.68rem;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cvSig->signer_name }}</span>
                    </div>
                    @else
                    <div style="padding-top:.2rem;border-top:1px solid #f0f0f0;font-size:.68rem;color:#d1d5db;">— unsigned</div>
                    @endif

                </div>
                @endforeach
                </div>{{-- /visit grid --}}

                @if($coveredVisits->isEmpty() && $wo)
                <div style="font-size:.72rem;color:#d1d5db;margin-top:.3rem;">no visits linked</div>
                @endif

                @else
                <span style="font-size:.72rem;background:#fef3c7;color:#92400e;padding:.15rem .45rem;border-radius:999px;font-weight:700;">Standalone</span>
                @endif
            </td>

            {{-- Total --}}
            <td style="vertical-align:top;font-size:.95rem;font-weight:700;color:{{ $inv->total > 0 ? '#111' : '#9ca3af' }};">
                {{ $inv->total > 0 ? '$'.number_format($inv->total, 2) : '—' }}
            </td>

        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:3rem;font-size:.9rem;">No invoices found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $invoices->links('admin.partials.pagination') }}

<script>
(function () {
    var LS_KEY = 'adminInvColWidths';
    var COLS   = ['inv_num', 'dates', 'customer', 'total'];
    var table  = document.getElementById('inv-col-table');
    if (!table) return;

    var colEls = {}, thEls = {};
    table.querySelectorAll('colgroup col[data-col]').forEach(function (c) { colEls[c.dataset.col] = c; });
    table.querySelectorAll('thead th[data-col]').forEach(function (h) { thEls[h.dataset.col] = h; });

    // ── Restore saved widths ──────────────────────────────────────────────
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem(LS_KEY)); } catch (e) {}
    if (saved) {
        COLS.forEach(function (k) {
            if (saved[k] && colEls[k]) colEls[k].style.width = saved[k] + 'px';
        });
    }

    // ── Persist current rendered widths ───────────────────────────────────
    function saveWidths() {
        var data = {};
        COLS.forEach(function (k) {
            if (thEls[k]) data[k] = thEls[k].offsetWidth;
        });
        try { localStorage.setItem(LS_KEY, JSON.stringify(data)); } catch (e) {}
    }

    // ── Wire up resize handles ────────────────────────────────────────────
    COLS.forEach(function (key) {
        var th  = thEls[key];
        var col = colEls[key];
        if (!th || !col) return;

        var handle = th.querySelector('.inv-resize-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var startX     = e.clientX;
            var startWidth = th.offsetWidth;

            // Leave ≥80px for the auto Customer column
            var otherFixed = 0;
            COLS.forEach(function (k) { if (k !== key && thEls[k]) otherFixed += thEls[k].offsetWidth; });
            var maxWidth = Math.max(startWidth, table.offsetWidth - otherFixed - 80);

            handle.classList.add('dragging');
            document.body.style.cursor     = 'col-resize';
            document.body.style.userSelect = 'none';

            function onMove(e) {
                var w = Math.max(50, Math.min(maxWidth, startWidth + (e.clientX - startX)));
                col.style.width = w + 'px';
            }
            function onUp() {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                handle.classList.remove('dragging');
                document.body.style.cursor     = '';
                document.body.style.userSelect = '';
                saveWidths();
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
})();
</script>
