{{-- Sortable column header helper --}}
@php
$mkSort = fn($col) => route('admin.work-orders.index', array_filter(
    array_merge(request()->query(), ['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]),
    fn($v) => $v !== null && $v !== ''
));
$sortIcon = fn($col) => $sort === $col
    ? '<span style="color:var(--accent);font-size:.7rem;margin-left:.15rem;">'.($dir === 'asc' ? '↑' : '↓').'</span>'
    : '<span style="color:#d1d5db;font-size:.7rem;margin-left:.15rem;">↕</span>';
@endphp

<style>
.wo-resize-handle{position:absolute;right:0;top:0;bottom:0;width:6px;cursor:col-resize;z-index:2;border-right:2px solid transparent;transition:border-color .12s;}
.wo-resize-handle:hover,.wo-resize-handle.dragging{border-color:rgba(255,255,255,.55);}
</style>

<table id="wo-col-table" class="data-table" style="table-layout:fixed;">
    <colgroup>
        <col data-col="status"   style="width:calc(9% + 50px)">
        <col data-col="dates"    style="width:11%">
        <col data-col="customer" style="width:17%">
        <col>{{-- Service Details: fills remaining space --}}
        <col data-col="visits"   style="width:15%">
        <col data-col="urgency"  style="width:8%">
    </colgroup>
    <thead>
        <tr>
            <th data-col="status" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('wo_number') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">#&nbsp;{!! $sortIcon('wo_number') !!}</a>
                <div style="font-size:.68rem;color:#9ca3af;font-weight:400;margin-top:.15rem;">Status</div>
                <div class="wo-resize-handle"></div>
            </th>
            <th data-col="dates" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('created_at') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">Created&nbsp;{!! $sortIcon('created_at') !!}</a>
                <div style="font-size:.68rem;color:#9ca3af;font-weight:400;margin-top:.15rem;">
                    <a href="{{ $mkSort('updated_at') }}" style="text-decoration:none;color:inherit;display:inline-flex;align-items:center;">Updated&nbsp;{!! $sortIcon('updated_at') !!}</a>
                </div>
                <div class="wo-resize-handle"></div>
            </th>
            <th data-col="customer" style="position:relative;padding-right:1rem;">
                Customer
                <div class="wo-resize-handle"></div>
            </th>
            <th>Service Details</th>
            <th data-col="visits" style="position:relative;padding-right:1rem;">
                Visits
                <div class="wo-resize-handle"></div>
            </th>
            <th data-col="urgency" style="position:relative;padding-right:1rem;">
                <a href="{{ $mkSort('urgency') }}" style="text-decoration:none;color:inherit;display:flex;align-items:center;">Urgency&nbsp;{!! $sortIcon('urgency') !!}</a>
                <div class="wo-resize-handle"></div>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr data-href="{{ route('admin.work-orders.show', $order) }}">

            {{-- WO ID + Status + Unread Note --}}
            <td style="white-space:nowrap;vertical-align:top;">
                <div>{{ $order->woLabel() }}</div>
                <div style="margin-top:.3rem;">
                    <span class="badge badge-{{ $order->status }}" style="font-size:.51rem;padding:.1rem .35rem;">{{ str_replace('_', ' ', $order->status) }}</span>
                </div>
                @if($order->needs_invoice)
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#d1fae5;border:1px solid #6ee7b7;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#065f46;">📄 Invoice Needed</span>
                </div>
                @endif
                @if(array_key_exists($order->id, $unreadWoIds))
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#92400e;">💬 Note</span>
                </div>
                @endif
                @if($order->confirmation_status === 'pending' || $order->visits->where('confirmation_status', \App\Models\WorkOrderVisit::CONFIRMATION_PENDING)->isNotEmpty())
                <div style="margin-top:.3rem;">
                    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fff7ed;border:1px solid #fdba74;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#9a3412;">⏳ Awaiting Confirmation</span>
                </div>
                @endif
            </td>

            {{-- Created + Updated footnote --}}
            <td style="font-size:.82rem;color:#666;white-space:nowrap;vertical-align:top;position:relative;{{ $order->updated_at->timestamp > $order->created_at->timestamp ? 'padding-bottom:2.6rem;' : '' }}">
                <div>{{ $order->created_at->format('M j, Y') }}</div>
                <div style="color:#9ca3af;font-size:.75rem;">{{ $order->created_at->format('g:i A') }}</div>
                @if($order->updated_at->timestamp > $order->created_at->timestamp)
                <div style="position:absolute;bottom:0;left:0;right:0;padding:.3rem 1rem .4rem;border-top:1px solid #f3f4f6;color:#c4c9d4;font-size:.7rem;">
                    <span style="color:#b0b7c3;font-weight:500;">Updated:</span> {{ $order->updated_at->format('M j, Y') }}
                    <span style="display:block;font-size:.68rem;padding-left:.85rem;">{{ $order->updated_at->format('g:i A') }}</span>
                </div>
                @endif
            </td>

            {{-- Customer --}}
            <td style="vertical-align:top;">
                <div style="font-size:.875rem;font-weight:600;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $order->customer->name }}</div>
                @php $custCompany = $order->customer->companies->first(); @endphp
                @if($custCompany)
                <div style="font-size:.75rem;color:#2E86C1;font-weight:500;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $custCompany->name }}</div>
                @endif
                @if($order->customer->phone)
                <div style="font-size:.73rem;color:#9ca3af;margin-top:.05rem;white-space:nowrap;">{{ $order->customer->phone }}</div>
                @endif
            </td>

            {{-- Service Details --}}
            <td style="vertical-align:top;">
                @if($order->serviceTypes->count())
                <div style="display:flex;flex-wrap:wrap;gap:.2rem;margin-bottom:.3rem;">
                    @foreach($order->serviceTypes as $svc)
                    <span style="background:#e0f2fe;color:#0369a1;padding:.05rem .4rem;border-radius:999px;font-size:.68rem;font-weight:600;white-space:nowrap;">{{ $svc->name }}</span>
                    @endforeach
                </div>
                @endif
                @if($order->description)
                <div style="font-size:.78rem;color:#374151;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">{{ $order->description }}</div>
                @endif
                @if($order->equipment_details)
                <div style="font-size:.74rem;color:#6b7280;margin-top:.2rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">
                    <span style="font-weight:600;color:#9ca3af;">Equip:</span> {{ $order->equipment_details }}
                </div>
                @endif
                @if($order->site_contact_name || $order->site_contact_phone)
                <div style="font-size:.74rem;color:#6b7280;margin-top:.2rem;">
                    @if($order->site_contact_name)<span style="font-weight:500;color:#4b5563;">{{ $order->site_contact_name }}</span>@endif
                    @if($order->site_contact_name && $order->site_contact_phone)<span style="color:#d1d5db;"> · </span>@endif
                    @if($order->site_contact_phone){{ $order->site_contact_phone }}@endif
                </div>
                @endif
                @if($order->site_street)
                <div style="font-size:.72rem;color:#9ca3af;margin-top:.1rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $order->site_street }}</div>
                @endif
                @if(!$order->serviceTypes->count() && !$order->description && !$order->equipment_details && !$order->site_contact_name && !$order->site_contact_phone && !$order->site_street)
                <span style="color:#d1d5db;">—</span>
                @endif
            </td>

            {{-- Visits --}}
            <td style="vertical-align:top;">
                @if($order->visits->isEmpty())
                    <span style="color:#bbb;font-size:.82rem;">—</span>
                @else
                <div style="display:flex;flex-direction:column;gap:.3rem;">
                    @foreach($order->visits->sortBy('scheduled_at') as $v)
                    @php $confirmed = $v->confirmation_status === \App\Models\WorkOrderVisit::CONFIRMATION_CONFIRMED; @endphp
                    <div style="display:flex;align-items:center;gap:.35rem;">
                        @if($confirmed)
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="6 12 10 16 18 8"/></svg>
                        @else
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                        @endif
                        <span style="font-size:.78rem;color:{{ $confirmed ? '#374151' : '#6b7280' }};white-space:nowrap;">{{ $v->scheduled_at->format('M j, Y') }}</span>
                        <span style="font-size:.7rem;color:#9ca3af;white-space:nowrap;">{{ $v->scheduled_at->format('g:i A') }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </td>

            {{-- Urgency --}}
            <td style="vertical-align:top;">
                <span class="badge" style="background:{{ $order->urgency === 'emergency' ? '#fee2e2' : ($order->urgency === 'urgent' ? '#fef3c7' : '#f3f4f6') }};color:{{ $order->urgency === 'emergency' ? '#991b1b' : ($order->urgency === 'urgent' ? '#92400e' : '#374151') }};">
                    {{ ucfirst($order->urgency) }}
                </span>
            </td>

        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;color:#999;padding:2rem;">No work orders found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $orders->links('admin.partials.pagination') }}

<script>
(function () {
    var LS_KEY  = 'adminWoColWidths';
    var COLS    = ['status', 'dates', 'customer', 'visits', 'urgency'];
    var table   = document.getElementById('wo-col-table');
    if (!table) return;

    // Index cols and header cells by data-col key
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

    // ── Persist current rendered widths to localStorage ───────────────────
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

        var handle = th.querySelector('.wo-resize-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation(); // don't trigger sort link

            var startX     = e.clientX;
            var startWidth = th.offsetWidth;

            // Max width: leave ≥80px for Service Details (the auto column)
            var otherFixed = 0;
            COLS.forEach(function (k) { if (k !== key && thEls[k]) otherFixed += thEls[k].offsetWidth; });
            var maxWidth = Math.max(startWidth, table.offsetWidth - otherFixed - 80);

            handle.classList.add('dragging');
            document.body.style.cursor    = 'col-resize';
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
