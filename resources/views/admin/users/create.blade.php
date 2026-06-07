@extends('layouts.admin')
@section('title', 'New User')

@section('content')

<div style="max-width:540px;background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div style="display:grid;gap:1rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label>Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            </div>
            <div>
                <label>Title <span style="font-weight:400;color:#888;">(job title)</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Network Engineer" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            </div>
        </div>
        <div>
            <label>Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
        <div>
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div>
                <label>Role *</label>
                <select name="role" id="role-select" required onchange="toggleCompanySection(this.value)" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    <option value="customer" {{ old('role', 'customer') === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="admin"    {{ old('role') === 'admin'    ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <label>Status *</label>
                <select name="status" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    <option value="active"   {{ old('status','active') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="pending"  {{ old('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        {{-- Company (customers only) --}}
        <div id="company-section" style="display:{{ old('role', 'customer') === 'customer' ? 'block' : 'none' }};padding-top:.25rem;border-top:1px solid #f0f0f0;">
            <label style="display:block;margin-bottom:.4rem;">Company</label>
            @php
                $initCoId   = old('company_id', '');
                $initCoName = $initCoId ? ($companies->firstWhere('id', $initCoId)?->name ?? 'Unknown') : null;
            @endphp
            <div id="company-display-box" onclick="openCompanyPicker()"
                 style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .85rem;
                        border:1px solid #ccc;border-radius:5px;cursor:pointer;background:#fff;
                        font-size:.9rem;user-select:none;">
                <span id="company-display-name" style="color:{{ $initCoName ? 'inherit' : '#aaa' }};">
                    {{ $initCoName ?? '— No company selected —' }}
                </span>
                <span style="color:#aaa;font-size:.78rem;flex-shrink:0;margin-left:.5rem;">Select ▾</span>
            </div>
            <input type="hidden" name="company_id" id="company-id-input" value="{{ $initCoId }}">
            <p style="font-size:.78rem;color:#888;margin-top:.3rem;">Optional — assign this customer to a company.</p>
        </div>

        <div>
            <label>Password *</label>
            <input type="password" name="password" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
        <div>
            <label>Confirm Password *</label>
            <input type="password" name="password_confirmation" required style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
</div>

{{-- ── Company Picker Modal ── --}}
<style>
.cp-item { padding:.55rem .9rem; cursor:pointer; font-size:.9rem; border-radius:4px; transition:background .1s; }
.cp-item:hover { background:#f0f7ff; }
.cp-item.cp-selected { background:#eff6ff; font-weight:600; color:var(--primary); }
</style>
<div id="company-picker-modal" onclick="if(event.target===this)closeCompanyPicker()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:460px;display:flex;flex-direction:column;max-height:80vh;">

        {{-- Search / select panel --}}
        <div id="cp-search-panel" style="display:flex;flex-direction:column;min-height:0;">
            <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
                <h3 style="margin:0;font-size:1rem;color:var(--primary);">Select Company</h3>
                <button type="button" onclick="closeCompanyPicker()"
                        style="background:none;border:none;font-size:1.2rem;color:#888;cursor:pointer;line-height:1;">×</button>
            </div>
            <div style="padding:.65rem 1rem;border-bottom:1px solid #f0f0f0;flex-shrink:0;">
                <input type="text" id="cp-search" placeholder="Search companies…"
                       oninput="filterCompanyList(this.value)"
                       style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div id="cp-list" style="overflow-y:auto;flex:1;padding:.4rem .6rem;min-height:120px;max-height:300px;"></div>
            <div style="padding:.7rem 1rem;border-top:1px solid #f0f0f0;flex-shrink:0;">
                <button type="button" onclick="showCreatePanel()"
                        style="width:100%;padding:.5rem;border:1px dashed var(--accent);border-radius:5px;
                               background:#f0f7ff;color:var(--accent);font-size:.88rem;cursor:pointer;font-weight:600;">
                    + Create New Company
                </button>
            </div>
        </div>

        {{-- Create panel --}}
        <div id="cp-create-panel" style="display:none;flex-direction:column;">
            <div style="padding:1.1rem 1.4rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:.65rem;flex-shrink:0;">
                <button type="button" onclick="showSearchPanel()"
                        style="background:none;border:none;color:var(--accent);font-size:.9rem;cursor:pointer;padding:0;line-height:1;">← Back</button>
                <h3 style="margin:0;font-size:1rem;color:var(--primary);">New Company</h3>
            </div>
            <div id="quick-company-error" style="display:none;padding:.55rem 1.4rem;background:#fee2e2;flex-shrink:0;">
                <p id="quick-company-error-msg" style="font-size:.84rem;color:#991b1b;margin:0;"></p>
            </div>
            <div style="padding:1.25rem 1.4rem;display:grid;gap:.85rem;overflow-y:auto;">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Company Name *</label>
                    <input type="text" id="qc-name"
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Primary Contact / Owner</label>
                    <input type="text" id="qc-owner"
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Phone</label>
                        <input type="text" id="qc-phone"
                               style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Email</label>
                        <input type="email" id="qc-email"
                               style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
                <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                    <button type="button" onclick="showSearchPanel()"
                            style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                        Cancel
                    </button>
                    <button type="button" id="qc-submit-btn" onclick="submitNewCompany()"
                            class="btn btn-primary btn-sm">Create &amp; Select</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const _companies = [
    {id: '', name: '— No Company —'},
    @foreach($companies as $co)
    {id: '{{ $co->id }}', name: @json($co->name)},
    @endforeach
];

function toggleCompanySection(role) {
    document.getElementById('company-section').style.display = role === 'customer' ? 'block' : 'none';
}

function openCompanyPicker() {
    document.getElementById('company-picker-modal').style.display = 'flex';
    document.getElementById('cp-search').value = '';
    filterCompanyList('');
    showSearchPanel();
    setTimeout(() => document.getElementById('cp-search').focus(), 50);
}

function closeCompanyPicker() {
    document.getElementById('company-picker-modal').style.display = 'none';
}

function filterCompanyList(term) {
    const t    = term.toLowerCase();
    const cur  = String(document.getElementById('company-id-input').value);
    const list = document.getElementById('cp-list');
    list.innerHTML = '';
    const matches = _companies.filter(c => !t || c.name.toLowerCase().includes(t));
    if (matches.length === 0) {
        const p = document.createElement('p');
        p.style.cssText = 'text-align:center;color:#aaa;padding:1.25rem;font-size:.88rem;margin:0;';
        p.textContent = 'No companies found.';
        list.appendChild(p);
        return;
    }
    matches.forEach(c => {
        const div = document.createElement('div');
        div.className = 'cp-item' + (cur === String(c.id) ? ' cp-selected' : '');
        div.textContent = c.name;
        div.onclick = () => selectCompany(c.id, c.name);
        list.appendChild(div);
    });
}

function selectCompany(id, name) {
    document.getElementById('company-id-input').value = id;
    const span = document.getElementById('company-display-name');
    span.textContent = name;
    span.style.color = id ? '' : '#aaa';
    closeCompanyPicker();
}

function showCreatePanel() {
    document.getElementById('cp-search-panel').style.display = 'none';
    document.getElementById('cp-create-panel').style.display = 'flex';
    document.getElementById('quick-company-error').style.display = 'none';
    setTimeout(() => document.getElementById('qc-name').focus(), 50);
}

function showSearchPanel() {
    document.getElementById('cp-create-panel').style.display = 'none';
    document.getElementById('cp-search-panel').style.display = 'flex';
}

function submitNewCompany() {
    const btn  = document.getElementById('qc-submit-btn');
    const name = document.getElementById('qc-name').value.trim();
    if (!name) {
        document.getElementById('quick-company-error-msg').textContent = 'Company name is required.';
        document.getElementById('quick-company-error').style.display = 'block';
        return;
    }
    btn.disabled = true;
    btn.textContent = 'Saving…';
    fetch('{{ route('admin.users.quick-company') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({
            name:       name,
            owner_name: document.getElementById('qc-owner').value.trim() || null,
            phone:      document.getElementById('qc-phone').value.trim() || null,
            email:      document.getElementById('qc-email').value.trim() || null,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            _companies.push({id: String(data.id), name: data.name});
            _companies.sort((a, b) => { if (!a.id) return -1; if (!b.id) return 1; return a.name.localeCompare(b.name); });
            selectCompany(String(data.id), data.name);
            ['qc-name','qc-owner','qc-phone','qc-email'].forEach(id => document.getElementById(id).value = '');
        } else {
            document.getElementById('quick-company-error-msg').textContent = data.message || 'Failed to create company.';
            document.getElementById('quick-company-error').style.display = 'block';
        }
    })
    .catch(() => {
        document.getElementById('quick-company-error-msg').textContent = 'An error occurred. Please try again.';
        document.getElementById('quick-company-error').style.display = 'block';
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Create & Select'; });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('company-picker-modal').style.display !== 'none') closeCompanyPicker();
});
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.users.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← Users</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        New User
    </h1>
</div>
@endpush
