@extends('layouts.portal')
@section('title', 'Submit Work Order')

@section('content')
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
    <a href="{{ route('portal.dashboard') }}" style="color:var(--accent);text-decoration:none;font-size:.9rem;">← Dashboard</a>
    <h1 class="page-title" style="margin:0;">Submit a Work Order</h1>
</div>

<div style="max-width:720px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('portal.work-orders.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Description --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#444;margin-bottom:.4rem;">
                What do you need done? <span style="color:var(--danger);">*</span>
            </label>
            <textarea name="description" rows="4" required
                placeholder="Describe the work — e.g. 'Run Cat6 cabling to 4 new workstations in the east office'"
                style="width:100%;padding:.65rem .9rem;border:1px solid #ccc;border-radius:5px;font-size:.93rem;resize:vertical;">{{ old('description') }}</textarea>
        </div>

        {{-- Equipment details --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#444;margin-bottom:.4rem;">
                Equipment Details
                <span style="font-weight:400;color:#888;">(make, model, serial numbers, quantities, etc.)</span>
                <span style="font-weight:400;color:#9ca3af;font-size:.75rem;display:block;margin-top:.1rem;">Type <kbd style="font-size:.72rem;background:#f3f4f6;border:1px solid #d1d5db;border-radius:3px;padding:.05rem .3rem;font-family:monospace;">..</kbd> to search the device catalog</span>
            </label>
            <textarea name="equipment_details" id="equip-details-ta" rows="3"
                placeholder="e.g. 'Cisco SG350-28 switch × 2, existing Cat5e patch panels, 24-port Leviton keystone jacks'"
                style="width:100%;padding:.65rem .9rem;border:1px solid #ccc;border-radius:5px;font-size:.93rem;resize:vertical;">{{ old('equipment_details') }}</textarea>
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
                <input type="hidden" name="preferred_availability" id="avail-json"
                       value="{{ old('preferred_availability', json_encode($customerAvailDefaults ?: (object)[])) }}">

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

                {{-- Shown when selected availability differs from customer's saved defaults --}}
                <div id="avail-update-defaults-box" style="display:none;margin-top:.55rem;padding:.55rem .85rem;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;">
                    <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.82rem;color:#78350f;">
                        <input type="checkbox" name="update_customer_defaults" value="1" checked
                               style="accent-color:var(--accent);width:14px;height:14px;flex-shrink:0;">
                        <span>Also save as my default availability</span>
                    </label>
                </div>
            </div>

            {{-- Urgency + Preferred Date --}}
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

        {{-- Services --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#444;margin-bottom:.5rem;">Services Needed</label>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:.5rem;">
                @foreach($serviceTypes as $svc)
                <label style="display:flex;align-items:flex-start;gap:.5rem;cursor:pointer;background:#f8f9fa;padding:.5rem .75rem;border-radius:5px;border:1px solid #e5e7eb;">
                    <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                           {{ in_array($svc->id, old('service_ids', [])) ? 'checked' : '' }}
                           style="margin-top:.15rem;flex-shrink:0;">
                    <span style="font-size:.88rem;">{{ $svc->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Site details --}}
        <div style="background:#f0f6ff;border-radius:6px;padding:1.25rem;margin-bottom:1.25rem;">
            <p style="font-weight:600;font-size:.9rem;color:var(--primary);margin-bottom:.75rem;">
                Site Details <span style="font-weight:400;color:#888;">(optional)</span>
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.85rem;color:#555;margin-bottom:.3rem;">Site Address</label>
                    <div style="display:flex;gap:.5rem;align-items:stretch;">
                        <input type="text" name="site_street" id="site_street"
                               value="{{ old('site_street', $defaultSite?->formattedAddress() ?? $siteAccountAddress) }}"
                               placeholder="123 Main St, City, State"
                               style="flex:1;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                        @if($sites->isNotEmpty())
                        <button type="button" onclick="openSitePicker()"
                                style="padding:.5rem .85rem;border:1px solid var(--accent);border-radius:5px;
                                       background:#fff;color:var(--accent);font-size:.82rem;font-weight:600;
                                       cursor:pointer;white-space:nowrap;flex-shrink:0;">
                            📍 Pick Site
                        </button>
                        @else
                        <a href="{{ route('profile.edit') }}"
                           style="padding:.5rem .85rem;border:1px solid #d1d5db;border-radius:5px;
                                  background:#fff;color:#555;font-size:.82rem;font-weight:600;
                                  text-decoration:none;white-space:nowrap;flex-shrink:0;display:flex;align-items:center;">
                            + Add Sites
                        </a>
                        @endif
                    </div>
                    @if($defaultSite)
                    <p style="font-size:.78rem;color:#2563eb;margin-top:.3rem;">
                        📍 Pre-filled from your default site: <strong>{{ $defaultSite->label }}</strong>
                    </p>
                    @endif
                    @if(!$defaultSite && !$siteAccountAddress && count($sitePriorAddresses) > 0)
                    <div style="margin-top:.45rem;">
                        <span style="font-size:.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;">Prior Addresses:</span>
                        <div style="margin-top:.3rem;display:flex;flex-direction:column;gap:.2rem;">
                            @foreach($sitePriorAddresses as $addr)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:.28rem .65rem;background:#f9fafb;border:1px solid #e5e7eb;border-radius:5px;gap:.75rem;">
                                <span style="font-size:.82rem;color:#374151;">{{ $addr }}</span>
                                <button type="button"
                                        data-addr="{{ $addr }}"
                                        onclick="document.getElementById('site_street').value=this.dataset.addr"
                                        title="Use this address"
                                        style="flex-shrink:0;width:22px;height:22px;border-radius:50%;border:1.5px solid var(--accent);background:#fff;color:var(--accent);font-size:1rem;font-weight:700;cursor:pointer;line-height:1;padding:0;display:flex;align-items:center;justify-content:center;">+</button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;color:#555;margin-bottom:.3rem;">On-site Contact Name</label>
                    <input type="text" name="site_contact_name" value="{{ old('site_contact_name', $user->name) }}"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                </div>
                <div>
                    <label style="display:block;font-size:.85rem;color:#555;margin-bottom:.3rem;">On-site Contact Phone</label>
                    <input type="text" name="site_contact_phone" id="site_contact_phone"
                           value="{{ old('site_contact_phone', $user->phone) }}"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    @if(!$user->phone)
                    <div id="save-phone-prompt" style="display:none;margin-top:.5rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#374151;cursor:pointer;font-weight:400;">
                            <input type="checkbox" name="save_phone_as_default" value="1"
                                   {{ old('save_phone_as_default') ? 'checked' : '' }}
                                   style="width:auto;margin:0;flex-shrink:0;">
                            Save this as my account phone number
                        </label>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Photos --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#444;margin-bottom:.4rem;">
                Photos <span style="font-weight:400;color:#888;">(up to 3 — JPG, PNG, WEBP, max 10 MB each)</span>
            </label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
                @for($i = 0; $i < 3; $i++)
                <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;
                              border:2px dashed #cbd5e1;border-radius:6px;padding:1.25rem .5rem;cursor:pointer;
                              background:#fafafa;text-align:center;font-size:.82rem;color:#888;min-height:90px;"
                       id="photo-label-{{ $i }}">
                    <span style="font-size:1.5rem;">📷</span>
                    <span id="photo-name-{{ $i }}">Photo {{ $i + 1 }}</span>
                    <input type="file" name="photos[]" accept="image/*"
                           style="display:none;" onchange="updateLabel(this,'photo-name-{{ $i }}')">
                </label>
                @endfor
            </div>
        </div>

        {{-- Documents --}}
        <div style="margin-bottom:1.75rem;">
            <label style="display:block;font-weight:600;font-size:.88rem;color:#444;margin-bottom:.4rem;">
                Documents <span style="font-weight:400;color:#888;">(up to 3 — PDF, DOC, DOCX, XLS, XLSX, max 20 MB each)</span>
            </label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;">
                @for($i = 0; $i < 3; $i++)
                <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;
                              border:2px dashed #cbd5e1;border-radius:6px;padding:1.25rem .5rem;cursor:pointer;
                              background:#fafafa;text-align:center;font-size:.82rem;color:#888;min-height:90px;"
                       id="doc-label-{{ $i }}">
                    <span style="font-size:1.5rem;">📄</span>
                    <span id="doc-name-{{ $i }}">Document {{ $i + 1 }}</span>
                    <input type="file" name="documents[]"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.txt"
                           style="display:none;" onchange="updateLabel(this,'doc-name-{{ $i }}')">
                </label>
                @endfor
            </div>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-primary">Submit Work Order</button>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

{{-- ── Site Picker Modal ── --}}
@if($sites->isNotEmpty())
<div id="site-picker-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;width:100%;max-width:480px;margin:1rem;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.2);">
        <div style="background:var(--primary);padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;">
            <span style="color:#fff;font-weight:600;font-size:1rem;">Select a Site</span>
            <button type="button" onclick="closeSitePicker()"
                    style="background:none;border:none;color:rgba(255,255,255,.8);font-size:1.4rem;cursor:pointer;line-height:1;">×</button>
        </div>
        <div style="padding:1.25rem;max-height:60vh;overflow-y:auto;">

            {{-- Saved sites list --}}
            <div style="display:grid;gap:.6rem;margin-bottom:1.25rem;">
                @foreach($sites as $site)
                <label style="display:flex;align-items:flex-start;gap:.75rem;padding:.85rem 1rem;
                               border:2px solid #e5e7eb;border-radius:6px;cursor:pointer;
                               transition:border-color .15s;"
                       onclick="this.querySelector('input').checked=true;highlightSite(this);">
                    <input type="radio" name="_site_pick" value="{{ $site->formattedAddress() }}"
                           {{ $site->is_default ? 'checked' : '' }}
                           style="margin-top:.2rem;flex-shrink:0;">
                    <div>
                        <div style="font-weight:600;font-size:.9rem;color:var(--primary);">
                            {{ $site->label }}
                            @if($site->is_default)
                                <span style="font-size:.7rem;background:#dbeafe;color:#1e40af;padding:.1em .45em;border-radius:999px;margin-left:.35rem;">DEFAULT</span>
                            @endif
                        </div>
                        <div style="font-size:.83rem;color:#666;margin-top:.1rem;">{{ $site->formattedAddress() }}</div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Quick-add new site --}}
            <div style="border-top:1px solid #e5e7eb;padding-top:1rem;">
                <button type="button" onclick="toggleQuickAdd()"
                        style="font-size:.83rem;color:var(--accent);background:none;border:none;cursor:pointer;font-weight:600;padding:0;">
                    + Add a new site
                </button>
                <div id="quick-add-site" style="display:none;margin-top:.85rem;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.75rem;">
                        <div style="grid-column:1/-1;">
                            <label style="font-size:.8rem;color:#555;">Site Name *</label>
                            <input type="text" id="qa_label" placeholder="Main Office"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #ccc;border-radius:5px;font-size:.88rem;">
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:.8rem;color:#555;">Street Address *</label>
                            <input type="text" id="qa_street" placeholder="123 Main St"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #ccc;border-radius:5px;font-size:.88rem;">
                        </div>
                        <div>
                            <label style="font-size:.8rem;color:#555;">City *</label>
                            <input type="text" id="qa_city" placeholder="Dallas"
                                   style="width:100%;padding:.45rem .7rem;border:1px solid #ccc;border-radius:5px;font-size:.88rem;">
                        </div>
                        <div style="display:grid;grid-template-columns:70px 1fr;gap:.4rem;">
                            <div>
                                <label style="font-size:.8rem;color:#555;">State *</label>
                                <input type="text" id="qa_state" maxlength="2" placeholder="TX"
                                       style="width:100%;padding:.45rem .5rem;border:1px solid #ccc;border-radius:5px;font-size:.88rem;text-transform:uppercase;">
                            </div>
                            <div>
                                <label style="font-size:.8rem;color:#555;">ZIP *</label>
                                <input type="text" id="qa_zip" maxlength="10" placeholder="75201"
                                       style="width:100%;padding:.45rem .7rem;border:1px solid #ccc;border-radius:5px;font-size:.88rem;">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="saveQuickSite()"
                            class="btn btn-primary btn-sm">Save & Use This Site</button>
                    <span id="qa_error" style="display:none;font-size:.8rem;color:var(--danger);margin-left:.75rem;"></span>
                </div>
            </div>
        </div>
        <div style="padding:1rem 1.25rem;border-top:1px solid #e5e7eb;display:flex;justify-content:flex-end;gap:.75rem;">
            <button type="button" onclick="closeSitePicker()" class="btn btn-secondary">Cancel</button>
            <button type="button" onclick="useSite()" class="btn btn-primary">Use Selected Site</button>
        </div>
    </div>
</div>
@endif

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
    const DAY_TO_JS     = { monday:1, tuesday:2, wednesday:3, thursday:4, friday:5, saturday:6 };
    const DAY_NAMES     = { monday:'Monday', tuesday:'Tuesday', wednesday:'Wednesday', thursday:'Thursday', friday:'Friday', saturday:'Saturday' };
    const defaultAvail  = @json($customerAvailDefaults ?? (object)[]);
    const state         = {};
    const hasOldDate    = {{ old('preferred_date') ? 'true' : 'false' }};

    try {
        const initial = JSON.parse(document.getElementById('avail-json').value || '{}');
        Object.entries(initial).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    } catch (e) {}

    function checkDefaultsDiff() {
        const box  = document.getElementById('avail-update-defaults-box');
        if (!box) return;
        const DAYS  = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        const SLOTS = ['morning','lunch','afternoon'];
        function normalize(obj) {
            const r = {};
            DAYS.forEach(d => {
                const arr = obj[d];
                const items = arr instanceof Set ? [...arr] : (Array.isArray(arr) ? arr : []);
                const f = items.filter(x => SLOTS.includes(x)).sort();
                if (f.length) r[d] = f;
            });
            return JSON.stringify(r);
        }
        box.style.display = normalize(state) === normalize(defaultAvail || {}) ? 'none' : '';
    }

    // ── Smart date ──
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
                    hintText = `Next available ${DAY_NAMES[matched]} — based on your preferred availability`;
                    targetDate = d;
                    break;
                }
                d.setDate(d.getDate()+1);
            }
        }

        if (!targetDate) {
            // Next business day (Mon–Fri)
            const d = new Date(start);
            while (d.getDay() === 0 || d.getDay() === 6) d.setDate(d.getDate()+1);
            targetDate = d;
            hintText = '';
        }

        dateInput.value = toYMD(targetDate);
        hint.textContent = hintText;
        hint.style.display = hintText ? '' : 'none';
    }

    // ── Sync JSON ──
    function syncJson() {
        const out = {};
        Object.entries(state).forEach(([day, slots]) => { if (slots.size) out[day] = [...slots]; });
        document.getElementById('avail-json').value = JSON.stringify(out);
    }

    // ── Render ──
    function renderDayBtn(btn) {
        const active = !!state[btn.dataset.day];
        btn.style.background  = active ? 'var(--primary)' : '#fff';
        btn.style.color       = active ? '#fff' : '#6b7280';
        btn.style.borderColor = active ? 'var(--primary)' : '#d1d5db';
    }

    function renderSlotBtn(btn) {
        const active = state[btn.dataset.day]?.has(btn.dataset.slot);
        btn.style.background  = active ? '#3b82f6' : '#fff';
        btn.style.borderColor = active ? '#3b82f6' : '#93c5fd';
        const name = btn.querySelector('.sb-name');
        const time = btn.querySelector('.sb-time');
        if (name) name.style.color = active ? '#fff'                    : '#3b82f6';
        if (time) time.style.color = active ? 'rgba(255,255,255,.75)'   : '#93c5fd';
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
        checkDefaultsDiff();
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
            checkDefaultsDiff();
        });
    });

    // On init: set smart date only if no old() value was preserved
    applyState();
    if (hasOldDate) {
        // old() date already in the input; just show appropriate hint
        const hint = document.getElementById('date-hint');
        if (hint) hint.style.display = 'none';
    }
})();

function updateLabel(input, nameId) {
    const label = document.getElementById(nameId);
    if (input.files && input.files[0]) {
        label.textContent = input.files[0].name;
        label.style.color = '#1A3C5E';
        label.style.fontWeight = '600';
    }
}

function openSitePicker() {
    const m = document.getElementById('site-picker-modal');
    if (m) { m.style.display = 'flex'; document.addEventListener('keydown', spKeyHandler); }
}
function closeSitePicker() {
    const m = document.getElementById('site-picker-modal');
    if (m) { m.style.display = 'none'; document.removeEventListener('keydown', spKeyHandler); }
}
function spKeyHandler(e) { if (e.key === 'Escape') closeSitePicker(); }

// Highlight selected card border
function highlightSite(lbl) {
    document.querySelectorAll('[name="_site_pick"]').forEach(r => {
        r.closest('label').style.borderColor = r.checked ? 'var(--accent)' : '#e5e7eb';
    });
}
// Init highlight on load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[name="_site_pick"]').forEach(r => {
        r.closest('label').style.borderColor = r.checked ? 'var(--accent)' : '#e5e7eb';
    });
});

function useSite() {
    const selected = document.querySelector('[name="_site_pick"]:checked');
    if (!selected) return;
    document.getElementById('site_street').value = selected.value;
    closeSitePicker();
}

function toggleQuickAdd() {
    const el = document.getElementById('quick-add-site');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function saveQuickSite() {
    const label  = document.getElementById('qa_label').value.trim();
    const street = document.getElementById('qa_street').value.trim();
    const city   = document.getElementById('qa_city').value.trim();
    const state  = document.getElementById('qa_state').value.trim().toUpperCase();
    const zip    = document.getElementById('qa_zip').value.trim();
    const errEl  = document.getElementById('qa_error');

    if (!label || !street || !city || !state || !zip) {
        errEl.textContent = 'All fields are required.';
        errEl.style.display = 'inline';
        return;
    }
    errEl.style.display = 'none';

    const address = `${street}, ${city}, ${state} ${zip}`;

    // POST the new site via fetch, then fill the field and close
    fetch('{{ route("portal.sites.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ label, street, city, state, zip }),
    }).then(r => {
        // Even on validation error from server, use the address in the field
        document.getElementById('site_street').value = address;
        closeSitePicker();
    });
}

// Close picker when clicking backdrop
document.getElementById('site-picker-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeSitePicker();
});

// Show "save as account phone" prompt when no account phone is set
const phoneInput   = document.getElementById('site_contact_phone');
const phonePrompt  = document.getElementById('save-phone-prompt');
if (phoneInput && phonePrompt) {
    function updatePhonePrompt() {
        phonePrompt.style.display = phoneInput.value.trim() ? 'block' : 'none';
    }
    phoneInput.addEventListener('input', updatePhonePrompt);
    updatePhonePrompt(); // run on load in case old() restored a value
}
</script>
{{-- ── Equipment Autocomplete ── --}}
<div id="equip-ac" style="display:none;position:fixed;z-index:9999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.14);width:420px;max-height:340px;overflow:hidden;">
    <div style="padding:.5rem .85rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input id="equip-ac-search" type="text" placeholder="Search equipment…"
               autocomplete="off" spellcheck="false"
               style="flex:1;border:none;outline:none;font-size:.85rem;background:transparent;color:#111;">
        <span style="font-size:.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0;">esc to cancel</span>
    </div>
    <div id="equip-ac-results" style="overflow-y:auto;max-height:285px;"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
(function () {
    let EQUIP = [];
    fetch('/device-catalog/data').then(r => r.json()).then(d => { EQUIP = d; });

    const ta      = document.getElementById('equip-details-ta');
    const panel   = document.getElementById('equip-ac');
    const search  = document.getElementById('equip-ac-search');
    const results = document.getElementById('equip-ac-results');
    if (!ta || !panel) return;

    let active     = false;
    let triggerPos = -1;
    let hiIdx      = -1;

    function checkTrigger() {
        const pos = ta.selectionStart;
        const val = ta.value;
        if (!active && pos >= 2 && val.slice(pos - 2, pos) === '..') {
            triggerPos = pos - 2;
            active = true;
            openPanel();
        } else if (active && pos <= triggerPos) {
            closePanel(false);
        }
    }
    ta.addEventListener('input',  checkTrigger);
    ta.addEventListener('keyup',  checkTrigger);

    function openPanel() {
        const rect   = ta.getBoundingClientRect();
        const panelW = 420;
        let left = rect.left;
        if (left + panelW > window.innerWidth - 8) left = window.innerWidth - panelW - 8;
        panel.style.top     = (rect.bottom + 4) + 'px';
        panel.style.left    = Math.max(8, left) + 'px';
        panel.style.display = 'block';
        search.value = '';
        renderResults(EQUIP.slice(0, 10));
        search.focus();
    }

    function closePanel(removeToken) {
        panel.style.display = 'none';
        active  = false;
        hiIdx   = -1;
        if (removeToken && triggerPos >= 0) {
            const val = ta.value;
            ta.value  = val.slice(0, triggerPos) + val.slice(triggerPos + 2);
            ta.selectionStart = ta.selectionEnd = triggerPos;
        }
        triggerPos = -1;
        ta.focus();
    }

    search.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        const filtered = q
            ? EQUIP.filter(e => (e.label + ' ' + e.q).toLowerCase().includes(q)).slice(0, 10)
            : EQUIP.slice(0, 10);
        renderResults(filtered);
    });

    function renderResults(items) {
        hiIdx = -1;
        if (!items.length) {
            results.innerHTML = '<div style="padding:.75rem 1rem;color:#9ca3af;font-size:.85rem;">No matches — keep typing</div>';
            return;
        }
        results.innerHTML = items.map((e, i) => {
            const lbl = e.label.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
            return `<div class="eq-row" data-idx="${i}"
                        style="padding:.52rem 1rem;cursor:pointer;font-size:.875rem;color:#111;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;gap:.75rem;line-height:1.35;">
                        <span>${lbl}</span>
                        <span style="font-size:.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0;background:#f3f4f6;border-radius:4px;padding:.1rem .4rem;">${e.type}</span>
                    </div>`;
        }).join('');

        const rows = results.querySelectorAll('.eq-row');
        rows.forEach((row, i) => {
            row.addEventListener('mouseenter', () => setHi(i));
            row.addEventListener('click',      () => selectItem(items[i].label));
        });
    }

    function setHi(idx) {
        const rows = [...results.querySelectorAll('.eq-row')];
        rows.forEach((r, i) => r.style.background = i === idx ? '#eff6ff' : '');
        hiIdx = idx;
        if (rows[idx]) rows[idx].scrollIntoView({ block: 'nearest' });
    }

    function navKeyHandler(e) {
        if (!active) return;
        const rows = [...results.querySelectorAll('.eq-row')];
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHi(Math.min(hiIdx + 1, rows.length - 1));
            if (document.activeElement !== search) search.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHi(Math.max(hiIdx - 1, 0));
            if (document.activeElement !== search) search.focus();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (rows[hiIdx]) selectItem(rows[hiIdx].dataset.label);
            else if (rows.length === 1) selectItem(rows[0].dataset.label);
        } else if (e.key === 'Escape') {
            closePanel(true);
        }
    }

    search.addEventListener('keydown', navKeyHandler);
    ta.addEventListener('keydown', navKeyHandler);

    function selectItem(label) {
        const val = ta.value;
        ta.value  = val.slice(0, triggerPos) + label + val.slice(triggerPos + 2);
        ta.selectionStart = ta.selectionEnd = triggerPos + label.length;
        closePanel(false);
    }

    document.addEventListener('mousedown', function (e) {
        if (active && !panel.contains(e.target) && e.target !== ta) closePanel(true);
    });
})();
}); // DOMContentLoaded
</script>

@endsection
