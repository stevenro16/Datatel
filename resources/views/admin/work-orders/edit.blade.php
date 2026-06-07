@extends('layouts.admin')
@section('title', 'Edit '.$workOrder->woLabel())

@section('content')

<div style="max-width:700px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
<form method="POST" action="{{ route('admin.work-orders.update', $workOrder) }}">
    @csrf @method('PATCH')

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    @php
        $scheduledAndBeyond = [
            \App\Models\WorkOrder::STATUS_SCHEDULED,
            \App\Models\WorkOrder::STATUS_AWAITING_FEEDBACK,
            \App\Models\WorkOrder::STATUS_SERVICES_PERFORMED,
            \App\Models\WorkOrder::STATUS_INVOICE_PREPARED,
            \App\Models\WorkOrder::STATUS_BILLED,
            \App\Models\WorkOrder::STATUS_COMPLETED,
        ];
        $unverifiedVisits = in_array($workOrder->status, $scheduledAndBeyond)
            ? $workOrder->visits->whereNotIn('confirmation_status', [\App\Models\WorkOrderVisit::CONFIRMATION_CONFIRMED])
            : collect();
    @endphp

    @foreach($unverifiedVisits as $unverifiedVisit)
    <div style="background:#fff7ed;border:1px solid #fb923c;border-radius:8px;padding:1rem 1.15rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:flex-start;">
        <span style="font-size:1.2rem;flex-shrink:0;">⚠️</span>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:700;color:#9a3412;font-size:.9rem;margin-bottom:.15rem;">
                Visit Not Verified — {{ $unverifiedVisit->scheduled_at->format('M j, Y') }}
            </div>
            <div style="font-size:.82rem;color:#c2410c;margin-bottom:.65rem;">
                @if($unverifiedVisit->confirmation_status === \App\Models\WorkOrderVisit::CONFIRMATION_PENDING)
                    A confirmation request was sent but the customer has not yet responded.
                @else
                    Customer confirmation was never collected for this visit.
                @endif
            </div>
            <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.work-orders.visits.request-confirm', [$workOrder, $unverifiedVisit]) }}" style="margin:0;">
                    @csrf
                    <button type="submit" style="padding:.32rem .75rem;border:1px solid #fb923c;border-radius:5px;background:#fff7ed;color:#9a3412;font-size:.8rem;font-weight:600;cursor:pointer;">
                        📧 Request Confirmation
                    </button>
                </form>
                <a href="{{ route('admin.work-orders.show', $workOrder) }}"
                   style="padding:.32rem .75rem;border:1px solid #d1d5db;border-radius:5px;background:#f9fafb;color:#374151;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;">
                    ✓ Manage Verification →
                </a>
            </div>
        </div>
    </div>
    @endforeach

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div style="grid-column:1/-1;">
            <label>Description</label>
            <textarea name="description" rows="3" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;">{{ old('description', $workOrder->description) }}</textarea>
        </div>

        <div style="grid-column:1/-1;">
            <label>Equipment Details</label>
            <textarea name="equipment_details" id="equipment-details" rows="2" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;overflow:hidden;">{{ old('equipment_details', $workOrder->equipment_details) }}</textarea>
        </div>

        <div>
            <label>Status *</label>
            <select name="status" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                @foreach(['new','triaged','scheduled','awaiting_feedback','services_performed','invoice_prepared','billed','completed','canceled'] as $s)
                <option value="{{ $s }}" {{ old('status', $workOrder->status) === $s ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($s)) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Urgency *</label>
            <select name="urgency" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                <option value="routine"   {{ old('urgency', $workOrder->urgency) === 'routine'   ? 'selected' : '' }}>Routine</option>
                <option value="urgent"    {{ old('urgency', $workOrder->urgency) === 'urgent'    ? 'selected' : '' }}>Urgent</option>
                <option value="emergency" {{ old('urgency', $workOrder->urgency) === 'emergency' ? 'selected' : '' }}>Emergency</option>
            </select>
        </div>

        @php
            $hasCompanySites = $customerCompanies->contains(fn($co) =>
                $co->address_street || $co->sites->isNotEmpty()
            );
        @endphp
        @if($hasCompanySites)
        <div style="grid-column:1/-1;">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin-bottom:.5rem;">
                Company Sites — click to fill address
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                @foreach($customerCompanies as $co)
                    @if($co->address_street)
                    @php
                        $mainAddr = collect([$co->address_street, $co->address_city, trim(($co->address_state ?? '').' '.($co->address_zip ?? ''))])->filter()->join(', ');
                    @endphp
                    <button type="button"
                            onclick='fillSiteAddress(@json(["street"=>$mainAddr,"contact_name"=>null,"contact_phone"=>null]))'
                            style="text-align:left;background:#fff;border:1px solid #d1d5db;border-radius:7px;padding:.45rem .75rem;cursor:pointer;transition:border-color .15s,background .15s;max-width:220px;"
                            onmouseover="this.style.borderColor='var(--accent)';this.style.background='#f0f7ff';"
                            onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fff';">
                        <div style="font-size:.7rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">{{ $co->name }} · Main</div>
                        <div style="font-size:.78rem;color:#374151;line-height:1.3;">{{ $co->address_street }}</div>
                        @if($co->address_city)
                        <div style="font-size:.72rem;color:#9ca3af;">{{ trim($co->address_city.', '.($co->address_state ?? '')) }}</div>
                        @endif
                    </button>
                    @endif
                    @foreach($co->sites as $site)
                    @if($site->street)
                    @php
                        $siteAddr = collect([$site->street, $site->city, trim(($site->state ?? '').' '.($site->zip ?? ''))])->filter()->join(', ');
                    @endphp
                    <button type="button"
                            onclick='fillSiteAddress(@json(["street"=>$siteAddr,"contact_name"=>null,"contact_phone"=>null]))'
                            style="text-align:left;background:#fff;border:1px solid #d1d5db;border-radius:7px;padding:.45rem .75rem;cursor:pointer;transition:border-color .15s,background .15s;max-width:220px;"
                            onmouseover="this.style.borderColor='var(--accent)';this.style.background='#f0f7ff';"
                            onmouseout="this.style.borderColor='#d1d5db';this.style.background='#fff';">
                        <div style="font-size:.7rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">{{ $co->name }}{{ $site->label ? ' · '.$site->label : '' }}{{ $site->is_default ? ' ★' : '' }}</div>
                        <div style="font-size:.78rem;color:#374151;line-height:1.3;">{{ $site->street }}</div>
                        @if($site->city)
                        <div style="font-size:.72rem;color:#9ca3af;">{{ trim($site->city.', '.($site->state ?? '')) }}</div>
                        @endif
                    </button>
                    @endif
                    @endforeach
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <label>Site Address</label>
            <input type="text" id="site-street-input" name="site_street"
                   value="{{ old('site_street', $workOrder->site_street) }}"
                   oninput="toggleSiteSuggestions()"
                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            @if($siteSuggestions->isNotEmpty())
            <div id="site-suggestions"
                 style="display:{{ old('site_street', $workOrder->site_street) ? 'none' : 'block' }};margin-top:.5rem;border:1px solid #e5e7eb;border-radius:7px;overflow:hidden;">
                <div style="padding:.35rem .75rem;background:#f8f9fa;border-bottom:1px solid #e5e7eb;font-size:.7rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;">
                    Previously used addresses — click <span style="color:var(--accent);">+</span> to fill
                </div>
                @foreach($siteSuggestions as $s)
                <div style="display:flex;align-items:center;gap:.6rem;padding:.45rem .75rem;border-bottom:1px solid #f3f4f6;background:#fff;transition:background .1s;"
                     onmouseover="this.style.background='#f0f7ff'" onmouseout="this.style.background='#fff'">
                    <button type="button"
                            onclick='fillSiteAddress(@json($s))'
                            title="Use this address"
                            style="width:24px;height:24px;border-radius:50%;background:var(--accent);color:#fff;border:none;cursor:pointer;font-size:1.05rem;font-weight:700;line-height:1;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:background .12s;"
                            onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='var(--accent)'">+</button>
                    <div style="min-width:0;flex:1;">
                        <div style="font-size:.84rem;color:#111;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $s['street'] }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;margin-top:.05rem;display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
                            <span style="background:#f1f5f9;border-radius:4px;padding:.05rem .35rem;font-weight:600;">{{ $s['label'] }}</span>
                            @if(!empty($s['contact_name']))<span>· {{ $s['contact_name'] }}</span>@endif
                            @if(!empty($s['contact_phone']))<span>· {{ $s['contact_phone'] }}</span>@endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <label>Site Contact Name</label>
            <input type="text" name="site_contact_name" value="{{ old('site_contact_name', $workOrder->site_contact_name) }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>

        <div>
            <label>Site Contact Phone</label>
            <input type="text" name="site_contact_phone" value="{{ old('site_contact_phone', $workOrder->site_contact_phone) }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>

        <div>
            <label>Scheduled Date</label>
            <input type="date" name="scheduled_date"
                   value="{{ old('scheduled_date', $workOrder->scheduled_at ? $workOrder->scheduled_at->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>

        <div>
            <label>Scheduled Time</label>
            <input type="time" name="scheduled_time"
                   value="{{ old('scheduled_time', $workOrder->scheduled_at ? $workOrder->scheduled_at->format('H:i') : '') }}"
                   style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>

        <div style="grid-column:1/-1;">
            <label>Services</label>
            <div style="display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.4rem;">
                @foreach($serviceTypes as $svc)
                @php $checked = old('service_ids') !== null ? in_array($svc->id, (array) old('service_ids')) : $workOrder->serviceTypes->contains($svc->id); @endphp
                <label class="svc-pill" data-checked="{{ $checked ? '1' : '0' }}"
                       style="display:inline-flex;align-items:center;gap:.4rem;padding:.38rem .85rem;border-radius:999px;border:2px solid {{ $checked ? 'var(--accent)' : '#e5e7eb' }};background:{{ $checked ? '#f0f6ff' : '#f9fafb' }};cursor:pointer;font-size:.84rem;font-weight:600;color:{{ $checked ? 'var(--accent)' : '#6b7280' }};transition:border-color .12s,background .12s,color .12s;user-select:none;">
                    <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                           {{ $checked ? 'checked' : '' }}
                           style="display:none;">
                    @if($svc->icon)
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="{{ $checked ? 'var(--accent)' : '#9ca3af' }}" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" class="svc-icon">{!! \App\Models\ServiceType::iconSet()[$svc->icon]['paths'] ?? '' !!}</svg>
                    @endif
                    {{ $svc->name }}
                </label>
                @endforeach
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>

<script>
(function () {
    var ta = document.getElementById('equipment-details');
    if (!ta) return;
    function fit() { ta.style.height = '1px'; ta.style.height = ta.scrollHeight + 'px'; }
    ta.addEventListener('input', fit);
    requestAnimationFrame(fit);
})();

function toggleSiteSuggestions() {
    var input = document.getElementById('site-street-input');
    var panel = document.getElementById('site-suggestions');
    if (!panel) return;
    panel.style.display = (input.value.trim() === '') ? 'block' : 'none';
}

function fillSiteAddress(s) {
    var input = document.getElementById('site-street-input');
    if (input) input.value = s.street;
    var nameInput  = document.querySelector('input[name="site_contact_name"]');
    var phoneInput = document.querySelector('input[name="site_contact_phone"]');
    if (nameInput  && s.contact_name)  nameInput.value  = s.contact_name;
    if (phoneInput && s.contact_phone) phoneInput.value = s.contact_phone;
    var panel = document.getElementById('site-suggestions');
    if (panel) panel.style.display = 'none';
}

// Service pill toggles
document.querySelectorAll('.svc-pill').forEach(function(pill) {
    var inp = pill.querySelector('input[type="checkbox"]');
    var ico = pill.querySelector('.svc-icon');
    function update() {
        if (inp.checked) {
            pill.style.borderColor = 'var(--accent)';
            pill.style.background  = '#f0f6ff';
            pill.style.color       = 'var(--accent)';
            if (ico) ico.setAttribute('stroke', 'var(--accent)');
        } else {
            pill.style.borderColor = '#e5e7eb';
            pill.style.background  = '#f9fafb';
            pill.style.color       = '#6b7280';
            if (ico) ico.setAttribute('stroke', '#9ca3af');
        }
    }
    pill.addEventListener('click', function(e) {
        e.preventDefault();
        inp.checked = !inp.checked;
        update();
    });
    update();
});
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.work-orders.show', $workOrder) }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← {{ $workOrder->woLabel() }}</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">
        Edit {{ $workOrder->woLabel() }}
    </h1>
</div>
@endpush
