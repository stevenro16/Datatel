@extends('layouts.admin')
@section('title', 'Device Catalog')

@section('content')

<div style="margin-top:.85rem;max-width:900px;">

    {{-- Add new device row --}}
    <div style="background:#fff;border:1px solid #d1d5db;border-radius:8px;padding:1rem 1.25rem;margin-bottom:1rem;display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:2;min-width:180px;">
            <label style="display:block;font-size:.78rem;font-weight:600;color:#555;margin-bottom:.3rem;">Device Label <span style="color:#dc2626;">*</span></label>
            <input id="new-label" type="text" placeholder="e.g. Cisco CP-8851 IP Phone"
                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div style="flex:1;min-width:130px;">
            <label style="display:block;font-size:.78rem;font-weight:600;color:#555;margin-bottom:.3rem;">Type <span style="color:#dc2626;">*</span></label>
            <input id="new-type" type="text" list="type-list" placeholder="Phone / Switch …"
                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
            <datalist id="type-list">
                @foreach($devices->pluck('type')->unique()->sort() as $t)
                <option value="{{ $t }}">
                @endforeach
                <option value="Phone"><option value="Router"><option value="Switch"><option value="Firewall">
                <option value="Access Point"><option value="Modem"><option value="Cable"><option value="Connector">
                <option value="Patch Panel"><option value="Camera"><option value="NVR"><option value="UPS"><option value="Rack">
            </datalist>
        </div>
        <div style="flex:2;min-width:180px;">
            <label style="display:block;font-size:.78rem;font-weight:600;color:#555;margin-bottom:.3rem;">Search Keywords</label>
            <input id="new-keywords" type="text" placeholder="cisco 8851 voip sip desk"
                   style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:5px;font-size:.88rem;box-sizing:border-box;">
        </div>
        <div>
            <button id="add-btn" onclick="addDevice()"
                    style="padding:.47rem 1.1rem;background:var(--accent);color:#fff;border:none;border-radius:5px;font-size:.88rem;font-weight:700;cursor:pointer;white-space:nowrap;">
                + Add Device
            </button>
        </div>
    </div>

    <div id="flash-msg" style="display:none;font-size:.83rem;color:#16a34a;margin-bottom:.6rem;font-weight:600;"></div>

    {{-- Filter / search --}}
    <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.75rem;flex-wrap:wrap;">
        <input id="filter-input" type="text" placeholder="Filter devices…"
               oninput="filterTable()"
               style="padding:.4rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;min-width:200px;">
        <select id="filter-type" onchange="filterTable()"
                style="padding:.4rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.85rem;background:#fff;">
            <option value="">All Types</option>
            @foreach($devices->pluck('type')->unique()->sort() as $t)
            <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:.35rem;font-size:.83rem;color:#555;cursor:pointer;margin-left:.25rem;">
            <input type="checkbox" id="filter-inactive" onchange="filterTable()"> Show inactive
        </label>
        <span id="row-count" style="font-size:.8rem;color:#9ca3af;margin-left:auto;"></span>
    </div>

    <table class="data-table" id="catalog-table">
        <thead>
            <tr>
                <th style="width:36%;">Device Label</th>
                <th style="width:13%;">Type</th>
                <th>Search Keywords</th>
                <th style="width:70px;text-align:center;">Active</th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($devices as $dev)
        <tr data-id="{{ $dev->id }}" data-type="{{ $dev->type }}" data-active="{{ $dev->is_active ? '1' : '0' }}"
            style="{{ $dev->is_active ? '' : 'opacity:.55;' }}">
            <td>
                <span class="view-mode">{{ $dev->label }}</span>
                <input class="edit-mode" type="text" value="{{ $dev->label }}"
                       style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;">
            </td>
            <td>
                <span class="view-mode">{{ $dev->type }}</span>
                <input class="edit-mode" type="text" list="type-list" value="{{ $dev->type }}"
                       style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;">
            </td>
            <td style="font-size:.82rem;color:#555;">
                <span class="view-mode">{{ $dev->keywords ?? '' }}</span>
                <input class="edit-mode" type="text" value="{{ $dev->keywords ?? '' }}"
                       style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;">
            </td>
            <td style="text-align:center;">
                <input type="checkbox" class="active-toggle" {{ $dev->is_active ? 'checked' : '' }}
                       title="Toggle active" onchange="toggleActive(this)"
                       style="width:16px;height:16px;cursor:pointer;">
            </td>
            <td style="text-align:right;white-space:nowrap;">
                <button class="edit-btn" onclick="startEdit(this)"
                        style="padding:.25rem .6rem;font-size:.78rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:4px;cursor:pointer;">Edit</button>
                <button class="save-btn" onclick="saveEdit(this)" style="display:none;
                        padding:.25rem .6rem;font-size:.78rem;background:#16a34a;color:#fff;border:none;border-radius:4px;cursor:pointer;">Save</button>
                <button class="cancel-btn" onclick="cancelEdit(this)" style="display:none;
                        padding:.25rem .6rem;font-size:.78rem;background:#f3f4f6;color:#555;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">✕</button>
                <button class="delete-btn" onclick="deleteDevice(this)"
                        style="padding:.25rem .5rem;font-size:.78rem;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:4px;cursor:pointer;">Del</button>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem;">No devices yet. Add one above.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">Device Catalog</h1>
    <span style="font-size:.78rem;color:#9ca3af;font-weight:400;">Equipment autocomplete source</span>
</div>
@endpush

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function flash(msg, err) {
    const el = document.getElementById('flash-msg');
    el.textContent = msg;
    el.style.color = err ? '#dc2626' : '#16a34a';
    el.style.display = 'block';
    setTimeout(() => el.style.display = 'none', 3000);
}

// ── Add new device ─────────────────────────────────────────────────────────
function addDevice() {
    const label    = document.getElementById('new-label').value.trim();
    const type     = document.getElementById('new-type').value.trim();
    const keywords = document.getElementById('new-keywords').value.trim();
    if (!label || !type) { flash('Label and Type are required.', true); return; }

    fetch('{{ route("admin.device-catalog.store") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        body: JSON.stringify({label, type, keywords}),
    })
    .then(r => r.json())
    .then(d => {
        if (!d.ok) { flash('Error saving.', true); return; }
        flash('Device added.');
        document.getElementById('new-label').value    = '';
        document.getElementById('new-type').value     = '';
        document.getElementById('new-keywords').value = '';
        appendRow(d.device);
        updateCount();
    });
}

function appendRow(dev) {
    const tbody = document.querySelector('#catalog-table tbody');
    const first = tbody.querySelector('td[colspan]');
    if (first) first.closest('tr').remove();

    const tr = document.createElement('tr');
    tr.dataset.id     = dev.id;
    tr.dataset.type   = dev.type;
    tr.dataset.active = '1';
    tr.innerHTML = `
        <td><span class="view-mode">${esc(dev.label)}</span>
            <input class="edit-mode" type="text" value="${esc(dev.label)}" style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;"></td>
        <td><span class="view-mode">${esc(dev.type)}</span>
            <input class="edit-mode" type="text" list="type-list" value="${esc(dev.type)}" style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;"></td>
        <td style="font-size:.82rem;color:#555;"><span class="view-mode">${esc(dev.keywords||'')}</span>
            <input class="edit-mode" type="text" value="${esc(dev.keywords||'')}" style="display:none;width:100%;padding:.3rem .5rem;border:1px solid #93c5fd;border-radius:4px;font-size:.88rem;box-sizing:border-box;"></td>
        <td style="text-align:center;"><input type="checkbox" class="active-toggle" checked title="Toggle active" onchange="toggleActive(this)" style="width:16px;height:16px;cursor:pointer;"></td>
        <td style="text-align:right;white-space:nowrap;">
            <button class="edit-btn" onclick="startEdit(this)" style="padding:.25rem .6rem;font-size:.78rem;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:4px;cursor:pointer;">Edit</button>
            <button class="save-btn" onclick="saveEdit(this)" style="display:none;padding:.25rem .6rem;font-size:.78rem;background:#16a34a;color:#fff;border:none;border-radius:4px;cursor:pointer;">Save</button>
            <button class="cancel-btn" onclick="cancelEdit(this)" style="display:none;padding:.25rem .6rem;font-size:.78rem;background:#f3f4f6;color:#555;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;">✕</button>
            <button class="delete-btn" onclick="deleteDevice(this)" style="padding:.25rem .5rem;font-size:.78rem;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:4px;cursor:pointer;">Del</button>
        </td>`;
    tbody.appendChild(tr);
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Inline edit ───────────────────────────────────────────────────────────
function startEdit(btn) {
    const tr = btn.closest('tr');
    tr.querySelectorAll('.view-mode').forEach(el => el.style.display = 'none');
    tr.querySelectorAll('.edit-mode').forEach(el => el.style.display = '');
    tr.querySelector('.edit-btn').style.display    = 'none';
    tr.querySelector('.delete-btn').style.display  = 'none';
    tr.querySelector('.save-btn').style.display    = '';
    tr.querySelector('.cancel-btn').style.display  = '';
    tr.querySelectorAll('.edit-mode')[0].focus();
}

function cancelEdit(btn) {
    const tr = btn.closest('tr');
    const inputs = tr.querySelectorAll('.edit-mode');
    const spans  = tr.querySelectorAll('.view-mode');
    inputs.forEach((inp, i) => { inp.value = spans[i].textContent; inp.style.display = 'none'; });
    spans.forEach(el => el.style.display = '');
    tr.querySelector('.edit-btn').style.display   = '';
    tr.querySelector('.delete-btn').style.display = '';
    tr.querySelector('.save-btn').style.display   = 'none';
    tr.querySelector('.cancel-btn').style.display = 'none';
}

function saveEdit(btn) {
    const tr      = btn.closest('tr');
    const id      = tr.dataset.id;
    const inputs  = tr.querySelectorAll('.edit-mode');
    const label   = inputs[0].value.trim();
    const type    = inputs[1].value.trim();
    const keywords= inputs[2].value.trim();
    if (!label || !type) { flash('Label and Type are required.', true); return; }

    fetch(`/admin/device-catalog/${id}`, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        body: JSON.stringify({label, type, keywords, is_active: tr.dataset.active === '1'}),
    })
    .then(r => r.json())
    .then(d => {
        if (!d.ok) { flash('Save failed.', true); return; }
        const spans = tr.querySelectorAll('.view-mode');
        spans[0].textContent = label;
        spans[1].textContent = type;
        spans[2].textContent = keywords;
        tr.dataset.type = type;
        cancelEdit(btn);
        flash('Saved.');
    });
}

// ── Toggle active ─────────────────────────────────────────────────────────
function toggleActive(cb) {
    const tr = cb.closest('tr');
    const id = tr.dataset.id;
    const isActive = cb.checked;

    fetch(`/admin/device-catalog/${id}`, {
        method: 'PATCH',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
        body: JSON.stringify({
            label:    tr.querySelectorAll('.view-mode')[0].textContent,
            type:     tr.dataset.type,
            is_active: isActive,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (!d.ok) { cb.checked = !isActive; flash('Toggle failed.', true); return; }
        tr.dataset.active = isActive ? '1' : '0';
        tr.style.opacity  = isActive ? '' : '.55';
    });
}

// ── Delete ────────────────────────────────────────────────────────────────
function deleteDevice(btn) {
    const tr    = btn.closest('tr');
    const label = tr.querySelectorAll('.view-mode')[0].textContent;
    if (!confirm(`Delete "${label}"?`)) return;

    fetch(`/admin/device-catalog/${tr.dataset.id}`, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json'},
    })
    .then(r => r.json())
    .then(d => {
        if (!d.ok) { flash('Delete failed.', true); return; }
        tr.remove();
        updateCount();
        flash('Deleted.');
    });
}

// ── Filter ────────────────────────────────────────────────────────────────
function filterTable() {
    const q       = document.getElementById('filter-input').value.toLowerCase();
    const type    = document.getElementById('filter-type').value;
    const showInactive = document.getElementById('filter-inactive').checked;
    let visible = 0;

    document.querySelectorAll('#catalog-table tbody tr[data-id]').forEach(tr => {
        const label    = tr.querySelectorAll('.view-mode')[0].textContent.toLowerCase();
        const kw       = tr.querySelectorAll('.view-mode')[2].textContent.toLowerCase();
        const rowType  = tr.dataset.type;
        const isActive = tr.dataset.active === '1';

        const matchQ    = !q    || label.includes(q) || kw.includes(q);
        const matchType = !type || rowType === type;
        const matchAct  = showInactive || isActive;

        const show = matchQ && matchType && matchAct;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('row-count').textContent = visible + ' device' + (visible === 1 ? '' : 's');
}

document.addEventListener('DOMContentLoaded', function () {
    updateCount();
    // Enter key on add form
    ['new-label','new-type','new-keywords'].forEach(id => {
        document.getElementById(id).addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); addDevice(); }
        });
    });
});

function updateCount() {
    const total = document.querySelectorAll('#catalog-table tbody tr[data-id]').length;
    document.getElementById('row-count').textContent = total + ' device' + (total === 1 ? '' : 's');
}
</script>
@endpush
