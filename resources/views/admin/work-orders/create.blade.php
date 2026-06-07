@extends('layouts.admin')
@section('title', 'New Work Order')

@section('content')

<div style="max-width:700px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
<form method="POST" action="{{ route('admin.work-orders.store') }}">
    @csrf

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Customer --}}
    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.4rem;">
            Customer <span style="color:var(--danger);">*</span>
        </label>
        <select name="customer_id" required
                style="width:100%;padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;background:#fff;">
            <option value="">— Select customer —</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }} ({{ $c->email }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Description --}}
    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.4rem;">Description</label>
        <textarea name="description" rows="3"
                  style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;resize:vertical;">{{ old('description') }}</textarea>
    </div>

    {{-- Equipment Details --}}
    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.4rem;">Equipment Details</label>
        <textarea name="equipment_details" rows="2"
                  style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;resize:vertical;">{{ old('equipment_details') }}</textarea>
    </div>

    {{-- Scheduling Preferences --}}
    <div style="border:1px solid #e5e7eb;border-radius:8px;padding:1.25rem;margin-bottom:1.25rem;background:#fafbfc;">
        <p style="font-weight:700;font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;margin:0 0 1rem;">
            Scheduling Preferences
        </p>

        {{-- Preferred Availability --}}
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.25rem;">
                Preferred Days &amp; Times
                <span style="font-weight:400;color:#9ca3af;font-size:.78rem;">— leave blank if flexible</span>
            </label>
            <input type="hidden" name="preferred_availability" id="avail-json" value="{{ old('preferred_availability', '{}') }}">

            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                <button type="button" class="avail-day-btn" data-day="{{ $day }}"
                        style="padding:.3rem .8rem;border-radius:999px;border:2px solid #d1d5db;
                               background:#fff;font-size:.8rem;font-weight:600;color:#6b7280;
                               cursor:pointer;transition:all .12s;line-height:1.3;">
                    {{ $dayLabel }}
                </button>
                @endforeach
            </div>

            <div id="avail-time-panels" style="display:none;border:1px solid #bfdbfe;border-radius:6px;overflow:hidden;">
                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                <div class="avail-day-panel" data-day="{{ $day }}"
                     style="display:none;align-items:center;justify-content:center;gap:.6rem;padding:.5rem .85rem;border-bottom:1px solid #dbeafe;background:#f0f7ff;flex-wrap:wrap;">
                    <span style="font-size:.78rem;font-weight:700;color:var(--primary);width:30px;flex-shrink:0;text-align:center;">{{ $dayLabel }}</span>
                    @foreach(['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']] as $slot => $slotData)
                    <button type="button" class="avail-slot-btn" data-day="{{ $day }}" data-slot="{{ $slot }}"
                            style="padding:.3rem .85rem;border-radius:8px;border:1.5px solid #93c5fd;
                                   background:#fff;cursor:pointer;transition:all .12s;text-align:center;min-width:108px;">
                        <div class="sb-name" style="font-size:.74rem;font-weight:700;color:#3b82f6;line-height:1.3;">{{ $slotData[0] }}</div>
                        <div class="sb-time" style="font-size:.62rem;color:#93c5fd;line-height:1.2;font-weight:500;">{{ $slotData[1] }}</div>
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        {{-- Priority + Preferred Date --}}
        <div style="display:flex;flex-wrap:wrap;gap:1.5rem;align-items:flex-start;padding-top:1rem;border-top:1px solid #e5e7eb;">

            {{-- Urgency pills --}}
            <div>
                <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.45rem;">
                    Priority <span style="color:var(--danger);">*</span>
                </label>
                <input type="hidden" name="urgency" id="urgency-input" value="{{ old('urgency', 'routine') }}">
                <div style="display:flex;gap:.5rem;">
                    <button type="button" class="urgency-btn" data-value="routine"
                            style="padding:.45rem 1rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:86px;transition:all .15s;">
                        <div class="ub-label" style="font-size:.82rem;font-weight:700;color:#374151;line-height:1.2;">Routine</div>
                        <div class="ub-sub" style="font-size:.68rem;color:#9ca3af;margin-top:.1rem;">No rush</div>
                    </button>
                    <button type="button" class="urgency-btn" data-value="urgent"
                            style="padding:.45rem 1rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:86px;transition:all .15s;">
                        <div class="ub-label" style="font-size:.82rem;font-weight:700;color:#374151;line-height:1.2;">Urgent</div>
                        <div class="ub-sub" style="font-size:.68rem;color:#9ca3af;margin-top:.1rem;">Within days</div>
                    </button>
                    <button type="button" class="urgency-btn" data-value="emergency"
                            style="padding:.45rem 1rem;border-radius:7px;border:2px solid #d1d5db;background:#fff;cursor:pointer;text-align:center;min-width:86px;transition:all .15s;">
                        <div class="ub-label" style="font-size:.82rem;font-weight:700;color:#374151;line-height:1.2;">Emergency</div>
                        <div class="ub-sub" style="font-size:.68rem;color:#9ca3af;margin-top:.1rem;">ASAP</div>
                    </button>
                </div>
            </div>

            {{-- Preferred Date --}}
            <div style="flex:1;min-width:190px;">
                <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.45rem;">
                    Preferred Date
                </label>
                <input type="date" name="preferred_date" id="preferred-date"
                       value="{{ old('preferred_date') }}"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;background:#fff;">
                <p id="date-hint" style="font-size:.74rem;color:#2563eb;margin:.3rem 0 0;display:none;"></p>
            </div>
        </div>
    </div>

    {{-- Site Details --}}
    <div style="background:#f0f6ff;border-radius:6px;padding:1.25rem;margin-bottom:1.25rem;">
        <p style="font-weight:700;font-size:.75rem;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin:0 0 .85rem;">
            Site Details
        </p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Site Address</label>
                <input type="text" name="site_street" value="{{ old('site_street') }}"
                       placeholder="123 Main St, City, State"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #c7d7f5;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Contact Name</label>
                <input type="text" name="site_contact_name" value="{{ old('site_contact_name') }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #c7d7f5;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Contact Phone</label>
                <input type="text" name="site_contact_phone" value="{{ old('site_contact_phone') }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #c7d7f5;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
        </div>
    </div>

    {{-- Actual Schedule (admin only) --}}
    <div style="margin-bottom:1.25rem;">
        <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.45rem;">
            Schedule Visit
            <span style="font-weight:400;color:#9ca3af;font-size:.78rem;">— optional, set now if already confirmed</span>
        </label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label style="display:block;font-size:.8rem;color:#6b7280;margin-bottom:.25rem;">Date</label>
                <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.8rem;color:#6b7280;margin-bottom:.25rem;">Time</label>
                <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.9rem;box-sizing:border-box;">
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div style="margin-bottom:1.5rem;">
        <label style="display:block;font-weight:600;font-size:.88rem;color:#374151;margin-bottom:.5rem;">Services</label>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.5rem;">
            @foreach($serviceTypes as $svc)
            <label style="display:flex;align-items:center;gap:.4rem;font-weight:400;font-size:.88rem;cursor:pointer;
                          background:#f8f9fa;padding:.45rem .75rem;border-radius:5px;border:1px solid #e5e7eb;">
                <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                       {{ in_array($svc->id, old('service_ids', [])) ? 'checked' : '' }}
                       style="width:auto;margin:0;flex-shrink:0;">
                {{ $svc->name }}
            </label>
            @endforeach
        </div>
    </div>

    <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-primary">Create Work Order</button>
        <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>

<script>
// ── Urgency pill picker ────────────────────────────────────────────────────
(function () {
    const COLORS = {
        routine:   { bg: '#1A3C5E', border: '#1A3C5E', labelColor: '#fff', subColor: 'rgba(255,255,255,.7)' },
        urgent:    { bg: '#b45309', border: '#b45309', labelColor: '#fff', subColor: 'rgba(255,255,255,.7)' },
        emergency: { bg: '#b91c1c', border: '#b91c1c', labelColor: '#fff', subColor: 'rgba(255,255,255,.7)' },
    };
    const input = document.getElementById('urgency-input');

    function applyUrgency(val) {
        input.value = val;
        document.querySelectorAll('.urgency-btn').forEach(btn => {
            const active = btn.dataset.value === val;
            const c = COLORS[btn.dataset.value] || {};
            btn.style.background  = active ? c.bg     : '#fff';
            btn.style.borderColor = active ? c.border : '#d1d5db';
            btn.querySelector('.ub-label').style.color = active ? c.labelColor : '#374151';
            btn.querySelector('.ub-sub').style.color   = active ? c.subColor   : '#9ca3af';
        });
    }

    document.querySelectorAll('.urgency-btn').forEach(btn => {
        btn.addEventListener('click', () => applyUrgency(btn.dataset.value));
    });

    applyUrgency(input.value || 'routine');
})();

// ── Preferred Availability + smart date ───────────────────────────────────
(function () {
    const DAY_TO_JS = { monday:1, tuesday:2, wednesday:3, thursday:4, friday:5, saturday:6 };
    const DAY_NAMES = { monday:'Monday', tuesday:'Tuesday', wednesday:'Wednesday', thursday:'Thursday', friday:'Friday', saturday:'Saturday' };
    const state = {};
    const hasOldDate = {{ old('preferred_date') ? 'true' : 'false' }};

    try {
        const initial = JSON.parse(document.getElementById('avail-json').value || '{}');
        Object.entries(initial).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    } catch (e) {}

    function toYMD(d) {
        const mm = String(d.getMonth()+1).padStart(2,'0');
        const dd = String(d.getDate()).padStart(2,'0');
        return `${d.getFullYear()}-${mm}-${dd}`;
    }

    function updateSmartDate() {
        const dateInput = document.getElementById('preferred-date');
        const hint      = document.getElementById('date-hint');
        if (!dateInput || !hint) return;

        const selectedDays = Object.keys(state);
        const today = new Date(); today.setHours(0,0,0,0);
        const start = new Date(today); start.setDate(start.getDate()+1);

        let targetDate = null, hintText = '';

        if (selectedDays.length > 0) {
            const targetNums = selectedDays.map(d => DAY_TO_JS[d]).filter(Boolean);
            const d = new Date(start);
            for (let i = 0; i < 14; i++) {
                if (targetNums.includes(d.getDay())) {
                    const matched = Object.entries(DAY_TO_JS).find(([,n]) => n === d.getDay())?.[0];
                    hintText = `Next available ${DAY_NAMES[matched]} — based on preferred availability`;
                    targetDate = d;
                    break;
                }
                d.setDate(d.getDate()+1);
            }
        }

        if (!targetDate) {
            const d = new Date(start);
            while (d.getDay() === 0 || d.getDay() === 6) d.setDate(d.getDate()+1);
            targetDate = d;
            hintText = '';
        }

        dateInput.value = toYMD(targetDate);
        hint.textContent = hintText;
        hint.style.display = hintText ? '' : 'none';
    }

    function syncJson() {
        const out = {};
        Object.entries(state).forEach(([day, slots]) => { if (slots.size) out[day] = [...slots]; });
        document.getElementById('avail-json').value = JSON.stringify(out);
    }

    function renderDayBtn(btn) {
        const active = !!state[btn.dataset.day];
        btn.style.background  = active ? 'var(--primary)' : '#fff';
        btn.style.color       = active ? '#fff'           : '#6b7280';
        btn.style.borderColor = active ? 'var(--primary)' : '#d1d5db';
    }

    function renderSlotBtn(btn) {
        const active = state[btn.dataset.day]?.has(btn.dataset.slot);
        btn.style.background  = active ? '#3b82f6' : '#fff';
        btn.style.borderColor = active ? '#3b82f6' : '#93c5fd';
        const name = btn.querySelector('.sb-name');
        const time = btn.querySelector('.sb-time');
        if (name) name.style.color = active ? '#fff'                  : '#3b82f6';
        if (time) time.style.color = active ? 'rgba(255,255,255,.75)' : '#93c5fd';
    }

    function applyState() {
        const panels    = document.querySelectorAll('.avail-day-panel');
        const container = document.getElementById('avail-time-panels');
        let anyVisible  = false;

        document.querySelectorAll('.avail-day-btn').forEach(renderDayBtn);

        panels.forEach(panel => {
            const show = !!state[panel.dataset.day];
            panel.style.display = show ? 'flex' : 'none';
            if (show) anyVisible = true;
        });

        let lastVisible = null;
        panels.forEach(p => { if (p.style.display !== 'none') lastVisible = p; });
        panels.forEach(p => { p.style.borderBottom = p === lastVisible ? 'none' : '1px solid #dbeafe'; });

        container.style.display = anyVisible ? '' : 'none';
        document.querySelectorAll('.avail-slot-btn').forEach(renderSlotBtn);
        syncJson();
        updateSmartDate();
    }

    document.querySelectorAll('.avail-day-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            if (state[day]) delete state[day]; else state[day] = new Set();
            applyState();
        });
    });

    document.querySelectorAll('.avail-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const { day, slot } = btn.dataset;
            if (!state[day]) state[day] = new Set();
            if (state[day].has(slot)) state[day].delete(slot); else state[day].add(slot);
            renderSlotBtn(btn);
            syncJson();
        });
    });

    applyState();
    if (hasOldDate) document.getElementById('date-hint').style.display = 'none';
})();
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.work-orders.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← Work Orders</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        New Work Order
    </h1>
</div>
@endpush
