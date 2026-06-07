@extends('layouts.admin')
@section('title', 'Work Orders')

@section('content')

{{-- Work queue pills + New WO button on same row --}}
@php
$pills = [
    ['key' => 'all',                  'label' => 'All Active',          'icon' => '📋', 'color' => '#1A3C5E'],
    ['key' => 'new',                  'label' => 'New',                 'icon' => '✨', 'color' => '#7c3aed'],
    ['key' => 'pending_confirmation', 'label' => 'Pending Confirm.',    'icon' => '⏳', 'color' => '#d97706'],
    ['key' => 'scheduled',            'label' => 'Scheduled',           'icon' => '📅', 'color' => '#0284c7'],
    ['key' => 'prepare_invoice',      'label' => 'Prepare Invoice',     'icon' => '📄', 'color' => '#059669'],
    ['key' => 'confirm_payment',      'label' => 'Confirm Payment',     'icon' => '💳', 'color' => '#dc2626'],
    ['key' => 'unread',               'label' => 'Unread Notes',        'icon' => '💬', 'color' => '#9a3412'],
];

// Apply saved queue order from settings
$_savedOrder = \App\Models\AdminSetting::get('work_queue_order');
if ($_savedOrder) {
    $_keys  = array_filter(array_map('trim', explode(',', $_savedOrder)));
    $_keyed = collect($pills)->keyBy('key');
    $_sorted = [];
    foreach ($_keys as $_k) {
        if ($_keyed->has($_k)) $_sorted[] = $_keyed[$_k];
    }
    // append any pills not covered by the saved order
    foreach ($pills as $_p) {
        if (!in_array($_p['key'], $_keys)) $_sorted[] = $_p;
    }
    $pills = $_sorted;
    unset($_savedOrder, $_keys, $_keyed, $_sorted, $_k, $_p);
}
$countMap = [
    'all'                  => 'all',
    'new'                  => 'new',
    'pending_confirmation' => 'pending_confirmation',
    'scheduled'            => 'scheduled',
    'prepare_invoice'      => 'prepare_invoice',
    'confirm_payment'      => 'confirm_payment',
    'unread'               => 'unread',
];
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem;margin-top:.85rem;">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
    @foreach($pills as $pill)
    @php
    $pKey     = $pill['key'];
    $isActive = $queue === $pKey;
    $cnt      = $queueCounts[$countMap[$pKey]] ?? 0;
    $qs       = array_filter(['queue' => $pKey, 'search' => request('search'), 'sort' => request('sort'), 'dir' => request('dir')], fn($v) => $v !== null && $v !== '');
    $href     = route('admin.work-orders.index', $qs);
    $c        = $pill['color'];
    @endphp
    <a href="{{ $href }}" style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .85rem .45rem .7rem;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;border:2px solid {{ $c }};background:{{ $isActive ? $c : '#fff' }};color:{{ $isActive ? '#fff' : $c }};white-space:nowrap;">
        <span>{{ $pill['icon'] }}</span>
        <span>{{ $pill['label'] }}</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:1.35rem;height:1.35rem;padding:0 .3rem;border-radius:999px;font-size:.72rem;font-weight:700;background:{{ $isActive ? 'rgba(255,255,255,.22)' : $c.'18' }};color:{{ $isActive ? '#fff' : $c }};">{{ $cnt }}</span>
    </a>
    @endforeach
    </div>
</div>

{{-- Search --}}
<div style="display:flex;gap:.75rem;margin-bottom:1.25rem;align-items:center;">
    <div style="position:relative;flex:1;min-width:180px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);pointer-events:none;">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="wo-search-input" autocomplete="off" autofocus
               value="{{ request('search') }}"
               placeholder="Search by name, phone, or WO-#####"
               style="width:100%;padding:.5rem .85rem .5rem 2.25rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;padding-right:2rem;">
        <button type="button" id="wo-search-clear"
                onclick="woSearchClear()"
                style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.1rem;line-height:1;padding:0;display:{{ request('search') ? 'block' : 'none' }};"
                title="Clear search">&#215;</button>
    </div>
    <label title="When checked, also searches completed and canceled work orders"
           style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;color:#555;cursor:pointer;white-space:nowrap;user-select:none;">
        <input type="checkbox" id="wo-full-search" value="1"
               {{ request('full_search') ? 'checked' : '' }}
               style="width:15px;height:15px;cursor:pointer;">
        Include Completed&nbsp;/&nbsp;Canceled
    </label>
    <button type="button" onclick="openNewWOModal()"
            style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:.875rem;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(46,134,193,.3);letter-spacing:.01em;white-space:nowrap;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New
    </button>
</div>

<div id="wo-list">
@include('admin.work-orders._list')
</div>


{{-- ── New Work Order Modal ──────────────────────────────────────────── --}}
<div id="new-wo-modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:1rem;"
     onclick="if(event.target===this) closeNewWOModal()">

    <div style="background:#fff;border-radius:12px;width:640px;max-width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 12px 48px rgba(0,0,0,.25);overflow:hidden;">

        {{-- Header --}}
        <div style="background:var(--primary);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-radius:12px 12px 0 0;">
            <div>
                <div style="font-size:1rem;font-weight:700;color:#fff;margin:0 0 .1rem;">📋 New Work Order</div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.72);margin:0;">Search by customer name, company, or phone number</div>
            </div>
            <button type="button" onclick="closeNewWOModal()"
                    style="background:none;border:none;color:rgba(255,255,255,.8);font-size:1.5rem;cursor:pointer;line-height:1;padding:.2rem;"
                    onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.8)'">×</button>
        </div>

        {{-- Scrollable body --}}
        <div style="padding:1.5rem 1.75rem;overflow-y:auto;flex:1;">
        <form method="POST" action="{{ route('admin.work-orders.store') }}" id="new-wo-form">
            @csrf
            <input type="hidden" name="customer_id" id="modal-customer-id">
            <input type="hidden" name="urgency" value="routine">

            {{-- Search input --}}
            <div style="position:relative;margin-bottom:.75rem;">
                <span style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.95rem;pointer-events:none;">🔍</span>
                <input type="text" id="cust-search" placeholder="Search customers or companies…" autocomplete="off"
                       style="width:100%;padding:.7rem .85rem .7rem 2.4rem;border:1.5px solid #d1d5db;border-radius:8px;font-size:.92rem;box-sizing:border-box;outline:none;transition:border-color .15s;"
                       onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#d1d5db'">
            </div>

            {{-- Live search results --}}
            <div id="cust-list" style="border:1.5px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:1rem;min-height:60px;">
                <div id="cust-prompt" style="padding:2rem 1rem;text-align:center;color:#9ca3af;font-size:.85rem;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;">🔍</div>
                    Type at least 2 characters to search
                </div>
                <div id="cust-loading" style="display:none;padding:1.5rem 1rem;text-align:center;color:#6b7280;font-size:.85rem;">
                    <div style="display:inline-block;width:18px;height:18px;border:2px solid #e5e7eb;border-top-color:var(--accent);border-radius:50%;animation:wo-spin .6s linear infinite;margin-bottom:.35rem;"></div>
                    <div>Searching…</div>
                </div>
                <div id="cust-no-match" style="display:none;padding:2rem 1rem;text-align:center;color:#9ca3af;font-size:.85rem;">
                    <div style="font-size:1.4rem;margin-bottom:.35rem;">🤷</div>
                    No customers or companies found
                </div>
            </div>

            {{-- Selected customer display --}}
            <div id="selected-display"
                 style="display:none;background:#f0f7ff;border:1.5px solid #bcd6f7;border-radius:8px;padding:.85rem 1.1rem;margin-bottom:1rem;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.75rem;min-width:0;">
                    <div id="selected-avatar" style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.88rem;font-weight:700;color:#fff;flex-shrink:0;"></div>
                    <div style="min-width:0;">
                        <div id="selected-label" style="font-weight:700;color:var(--primary);font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                        <div id="selected-sub" style="font-size:.75rem;color:#6b7280;margin-top:.1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                    </div>
                </div>
                <button type="button" onclick="clearSelection()"
                        style="background:none;border:1px solid #93c5fd;border-radius:5px;color:#3b82f6;cursor:pointer;font-size:.75rem;font-weight:600;padding:.2rem .55rem;white-space:nowrap;flex-shrink:0;"
                        title="Change selection">✕ Change</button>
            </div>

            {{-- New customer link --}}
            <div style="font-size:.8rem;color:#9ca3af;margin-bottom:1.5rem;">
                Customer not listed?
                <a href="{{ route('admin.users.create') }}" target="_blank"
                   style="color:var(--accent);text-decoration:none;font-weight:600;">
                    Create a new customer ↗
                </a>
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:.75rem;justify-content:flex-end;">
                <button type="button" onclick="closeNewWOModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" id="create-wo-btn" class="btn btn-primary"
                        disabled style="opacity:.45;cursor:not-allowed;">
                    Create Work Order
                </button>
            </div>
        </form>
        </div>
    </div>
</div>
<style>
@keyframes wo-spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    const modal      = document.getElementById('new-wo-modal');
    const search     = document.getElementById('cust-search');
    const list       = document.getElementById('cust-list');
    const prompt     = document.getElementById('cust-prompt');
    const loading    = document.getElementById('cust-loading');
    const noMatch    = document.getElementById('cust-no-match');
    const hiddenId   = document.getElementById('modal-customer-id');
    const selDisplay = document.getElementById('selected-display');
    const selLabel   = document.getElementById('selected-label');
    const selSub     = document.getElementById('selected-sub');
    const selAvatar  = document.getElementById('selected-avatar');
    const createBtn  = document.getElementById('create-wo-btn');
    const searchUrl  = '{{ route('admin.analytics.customers.search') }}';

    const AVATAR_COLORS = ['#2E86C1','#16a34a','#9333ea','#dc2626','#d97706','#0891b2','#be185d'];

    function avatarColor(name) {
        return AVATAR_COLORS[(name.charCodeAt(0) || 0) % AVATAR_COLORS.length];
    }
    function initials(name) {
        const parts = name.trim().split(/\s+/);
        return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
    }

    let debounceTimer = null;

    window.openNewWOModal = function () {
        clearSelection();
        search.value = '';
        showPrompt();
        modal.style.display = 'flex';
        setTimeout(() => search.focus(), 60);
    };

    window.closeNewWOModal = function () {
        modal.style.display = 'none';
    };

    window.clearSelection = function () {
        hiddenId.value = '';
        selDisplay.style.display = 'none';
        list.style.display = 'block';
        search.style.display = 'block';
        search.value = '';
        showPrompt();
        createBtn.disabled = true;
        createBtn.style.opacity = '.45';
        createBtn.style.cursor = 'not-allowed';
        setTimeout(() => search.focus(), 60);
    };

    function showPrompt() {
        prompt.style.display = 'block';
        loading.style.display = 'none';
        noMatch.style.display = 'none';
        list.querySelectorAll('.cust-opt').forEach(r => r.remove());
    }

    search.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (q.length < 2) { showPrompt(); return; }
        prompt.style.display = 'none';
        noMatch.style.display = 'none';
        loading.style.display = 'block';
        list.querySelectorAll('.cust-opt').forEach(r => r.remove());
        debounceTimer = setTimeout(() => fetchResults(q), 280);
    });

    function fetchResults(q) {
        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            list.querySelectorAll('.cust-opt').forEach(r => r.remove());
            if (!data.length) { noMatch.style.display = 'block'; return; }

            // Group: separate customers with a company from those without
            const withCompany = data.filter(c => c.company);
            const solo        = data.filter(c => !c.company);

            // Render grouped results: company customers first, then solo
            const groups = [];
            if (withCompany.length) groups.push({ label: null, items: withCompany });
            if (solo.length)        groups.push({ label: withCompany.length ? 'Individual Customers' : null, items: solo });

            groups.forEach(group => {
                if (group.label) {
                    const sep = document.createElement('div');
                    sep.style.cssText = 'padding:.35rem 1rem .2rem;font-size:.68rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em;background:#f9fafb;border-bottom:1px solid #f3f4f6;';
                    sep.textContent = group.label;
                    list.insertBefore(sep, loading);
                }
                group.items.forEach(c => {
                    const row = document.createElement('div');
                    row.className = 'cust-opt';
                    row.dataset.id      = c.id;
                    row.dataset.name    = c.name;
                    row.dataset.company = c.company || '';
                    row.dataset.email   = c.email || '';
                    row.dataset.phone   = c.phone || '';
                    row.style.cssText   = 'padding:.75rem 1.1rem;cursor:pointer;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:.85rem;transition:background .12s;';

                    const color = avatarColor(c.name);
                    const inits = initials(c.name);

                    let meta = [];
                    if (c.email) meta.push(esc(c.email));
                    if (c.phone) meta.push(esc(c.phone));

                    row.innerHTML = `
                        <div style="width:40px;height:40px;border-radius:50%;background:${color};display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff;flex-shrink:0;">${esc(inits)}</div>
                        <div style="min-width:0;flex:1;">
                            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                <span style="font-weight:700;font-size:.92rem;color:#111;">${esc(c.name)}</span>
                                ${c.company ? `<span style="font-size:.72rem;font-weight:600;color:#1d4ed8;background:#dbeafe;border:1px solid #93c5fd;border-radius:999px;padding:.1rem .5rem;">${esc(c.company)}</span>` : ''}
                            </div>
                            ${meta.length ? `<div style="font-size:.76rem;color:#6b7280;margin-top:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${meta.join('&nbsp;&nbsp;·&nbsp;&nbsp;')}</div>` : ''}
                        </div>
                        <button class="wo-quick-btn" type="button" title="Create work order for ${esc(c.name)}"
                                style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:var(--accent);border:none;color:#fff;font-size:1.1rem;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center;font-weight:700;transition:background .15s,transform .1s;">+</button>`;

                    const quickBtn = row.querySelector('.wo-quick-btn');
                    quickBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hiddenId.value = c.id;
                        document.getElementById('new-wo-form').submit();
                    });
                    quickBtn.addEventListener('mouseover', () => { quickBtn.style.background = '#1a6fa8'; quickBtn.style.transform = 'scale(1.12)'; });
                    quickBtn.addEventListener('mouseout',  () => { quickBtn.style.background = 'var(--accent)'; quickBtn.style.transform = ''; });

                    row.addEventListener('mouseover', () => row.style.background = '#f0f7ff');
                    row.addEventListener('mouseout',  () => row.style.background = '');
                    row.addEventListener('click', selectCustomer);
                    list.insertBefore(row, loading);
                });
            });
        })
        .catch(() => {
            loading.style.display = 'none';
            noMatch.style.display = 'block';
        });
    }

    function selectCustomer(e) {
        const row   = e.currentTarget;
        const name  = row.dataset.name;
        const comp  = row.dataset.company;
        const email = row.dataset.email;
        const phone = row.dataset.phone;

        hiddenId.value = row.dataset.id;

        // Avatar
        selAvatar.style.background = avatarColor(name);
        selAvatar.textContent = initials(name);

        // Label + sub
        selLabel.textContent = name + (comp ? ' — ' + comp : '');
        let sub = [];
        if (email) sub.push(email);
        if (phone) sub.push(phone);
        selSub.textContent = sub.join('  ·  ');

        selDisplay.style.display = 'flex';
        list.style.display = 'none';
        search.style.display = 'none';
        createBtn.disabled = false;
        createBtn.style.opacity = '1';
        createBtn.style.cursor = 'pointer';
    }

    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeNewWOModal();
    });

    document.getElementById('new-wo-form').addEventListener('submit', function (e) {
        if (!hiddenId.value) { e.preventDefault(); }
    });
})();
</script>

<script>
// ── Work order list partial-refresh + auto-search ───────────────────────────
(function () {
    var input    = document.getElementById('wo-search-input');
    var checkbox = document.getElementById('wo-full-search');
    var clearBtn = document.getElementById('wo-search-clear');
    if (!input) return;

    var LS_KEY    = 'adminWoFullSearch';
    var MIN_CHARS = 2;
    var timer;
    var listBusy  = false;
    var urlParams = new URLSearchParams(window.location.search);

    // Restore full_search from localStorage when not in the URL
    if (!urlParams.has('full_search') && localStorage.getItem(LS_KEY) === '1') {
        checkbox.checked = true;
    }

    // ── fetchList: swap only #wo-list, no full page reload ─────────────────
    function fetchList(url) {
        var container = document.getElementById('wo-list');
        if (!container || listBusy) return;
        listBusy = true;
        container.style.transition = 'opacity .1s';
        container.style.opacity    = '.4';

        fetch(url, { headers: { 'X-List-Request': '1', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw r; return r.text(); })
            .then(function (html) {
                container.innerHTML    = html;
                container.style.opacity = '1';
                history.pushState(null, '', url);
                urlParams = new URLSearchParams(url.split('?')[1] || '');
                listBusy  = false;
                refocusSearch();
            })
            .catch(function () { container.style.opacity = '1'; listBusy = false; });
    }

    function refocusSearch() {
        var si = document.getElementById('wo-search-input');
        if (si) { si.focus(); var l = si.value.length; si.setSelectionRange(l, l); }
    }

    // ── Intercept sort/pagination clicks inside #wo-list (capture phase) ───
    // Fires before the layout's bubble-phase navigate handler so we can
    // replace only the list instead of doing a full content-body swap.
    if (window._woListClickHandler) {
        document.removeEventListener('click', window._woListClickHandler, true);
    }
    window._woListClickHandler = function (e) {
        var container = document.getElementById('wo-list');
        if (!container) return;
        var link = e.target.closest('a[href]');
        if (!link || !container.contains(link)) return;
        var href = link.getAttribute('href');
        if (!href || href[0] === '#' || /^(https?:|mailto:|tel:)/.test(href)
                  || link.target || link.hasAttribute('download')) return;
        e.stopImmediatePropagation();
        e.preventDefault();
        fetchList(href);
    };
    document.addEventListener('click', window._woListClickHandler, true);

    // ── Auto-search ─────────────────────────────────────────────────────────
    checkbox.addEventListener('change', function () {
        localStorage.setItem(LS_KEY, this.checked ? '1' : '0');
        doSearch();
    });

    input.addEventListener('input', function () {
        clearTimeout(timer);
        clearBtn.style.display = this.value.length ? 'block' : 'none';
        if (this.value.length === 0) {
            doSearch();
        } else if (this.value.length >= MIN_CHARS) {
            timer = setTimeout(doSearch, 350);
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { clearTimeout(timer); doSearch(); }
    });

    window.woSearchClear = function () {
        input.value = '';
        clearBtn.style.display = 'none';
        doSearch();
        input.focus();
    };

    function doSearch() {
        var q     = input.value.trim();
        var queue = urlParams.get('queue') || 'all';
        var sort  = urlParams.get('sort')  || '';
        var dir   = urlParams.get('dir')   || '';

        var params = new URLSearchParams();
        params.set('queue', queue);
        if (sort) params.set('sort', sort);
        if (dir)  params.set('dir',  dir);
        if (q)    params.set('search', q);
        if (checkbox.checked) params.set('full_search', '1');

        fetchList(window.location.pathname + '?' + params.toString());
    }

    // Keep cursor ready in search field
    input.focus();
    var len = input.value.length;
    input.setSelectionRange(len, len);
})();
</script>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">WORK ORDERS</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
        Work Queues
    </h1>
</div>
@endpush
