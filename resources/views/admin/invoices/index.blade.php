@extends('layouts.admin')
@section('title', 'Invoices')

@section('content')

@php
$pills = [
    ['key' => 'new',              'label' => 'New / Draft',      'icon' => '📝', 'color' => '#7c3aed'],
    ['key' => 'billed',           'label' => 'Billed',           'icon' => '📬', 'color' => '#0284c7'],
    ['key' => 'payment_received', 'label' => 'Payment Received', 'icon' => '💳', 'color' => '#059669'],
    ['key' => 'all_active',       'label' => 'All Active',       'icon' => '📋', 'color' => '#1A3C5E'],
    ['key' => 'past_due',         'label' => 'Past Due',         'icon' => '🔴', 'color' => '#dc2626'],
    ['key' => 'completed',        'label' => 'Completed',        'icon' => '✅', 'color' => '#6b7280'],
];

// Apply saved invoice queue priority order
$_invOrder = \App\Models\AdminSetting::get('invoice_queue_order');
if ($_invOrder) {
    $_keys   = array_filter(array_map('trim', explode(',', $_invOrder)));
    $_keyed  = collect($pills)->keyBy('key');
    $_sorted = [];
    foreach ($_keys as $_k) {
        if ($_keyed->has($_k)) $_sorted[] = $_keyed[$_k];
    }
    foreach ($pills as $_p) {
        if (!in_array($_p['key'], $_keys)) $_sorted[] = $_p;
    }
    $pills = $_sorted;
    unset($_invOrder, $_keys, $_keyed, $_sorted, $_k, $_p);
}
@endphp

{{-- Tab pills --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem;margin-top:.85rem;">
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
    @foreach($pills as $pill)
    @php
    $pKey     = $pill['key'];
    $isActive = $tab === $pKey;
    $cnt      = $tabCounts[$pKey] ?? 0;
    $c        = $pill['color'];
    $qs       = array_filter(['tab' => $pKey, 'sort' => request('sort'), 'dir' => request('dir')], fn($v) => $v !== null && $v !== '');
    @endphp
    <a href="{{ route('admin.invoices.index', $qs) }}"
       style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem .85rem .45rem .7rem;border-radius:8px;text-decoration:none;font-size:.82rem;font-weight:600;border:2px solid {{ $c }};background:{{ $isActive ? $c : '#fff' }};color:{{ $isActive ? '#fff' : $c }};white-space:nowrap;">
        @if(!empty($pill['icon']))<span>{{ $pill['icon'] }}</span>@endif
        <span>{{ $pill['label'] }}</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:1.35rem;height:1.35rem;padding:0 .3rem;border-radius:999px;font-size:.72rem;font-weight:700;background:{{ $isActive ? 'rgba(255,255,255,.22)' : $c.'18' }};color:{{ $isActive ? '#fff' : $c }};">{{ $cnt }}</span>
    </a>
    @endforeach
    </div>
</div>

{{-- Search bar --}}
<div style="display:flex;gap:.75rem;margin-bottom:1.25rem;align-items:center;">
    <div style="position:relative;flex:1;min-width:180px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);pointer-events:none;">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="inv-search-input" autocomplete="off" autofocus
               value="{{ $search }}"
               placeholder="Search by INV-#, WO-#, customer name, email, or phone…"
               style="width:100%;padding:.5rem .85rem .5rem 2.25rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;padding-right:2rem;">
        <button type="button" id="inv-search-clear"
                onclick="invSearchClear()"
                style="position:absolute;right:.5rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:1.1rem;line-height:1;padding:0;display:{{ $search ? 'block' : 'none' }};"
                title="Clear search">&#215;</button>
    </div>
    <label title="When checked, also searches completed and canceled invoices"
           style="display:flex;align-items:center;gap:.4rem;font-size:.85rem;color:#555;cursor:pointer;white-space:nowrap;user-select:none;">
        <input type="checkbox" id="inv-full-search" value="1"
               {{ $fullSearch ? 'checked' : '' }}
               style="width:15px;height:15px;cursor:pointer;">
        Include Completed&nbsp;/&nbsp;Canceled
    </label>
    <a href="{{ route('admin.invoices.create') }}"
       style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--accent);color:#fff;border:none;border-radius:6px;font-size:.875rem;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(46,134,193,.3);letter-spacing:.01em;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New
    </a>
</div>

<div id="inv-list">
@include('admin.invoices._list')
</div>

@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">INVOICING</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="13" x2="12" y2="17"/><line x1="10" y1="15" x2="14" y2="15"/></svg>
        Invoice Queues
    </h1>
</div>
@endpush

@push('scripts')
<script>
// ── Invoice list partial-refresh + auto-search ──────────────────────────────
(function () {
    var input    = document.getElementById('inv-search-input');
    var checkbox = document.getElementById('inv-full-search');
    var clearBtn = document.getElementById('inv-search-clear');
    if (!input) return;

    var LS_KEY    = 'adminInvFullSearch';
    var MIN_CHARS = 2;
    var timer;
    var listBusy  = false;
    var urlParams = new URLSearchParams(window.location.search);

    // Restore full_search from localStorage when not in the URL
    if (!urlParams.has('full_search') && localStorage.getItem(LS_KEY) === '1') {
        checkbox.checked = true;
    }

    // ── fetchList: swap only #inv-list ──────────────────────────────────────
    function fetchList(url) {
        var container = document.getElementById('inv-list');
        if (!container || listBusy) return;
        listBusy = true;
        container.style.transition = 'opacity .1s';
        container.style.opacity    = '.4';

        fetch(url, { headers: { 'X-List-Request': '1', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw r; return r.text(); })
            .then(function (html) {
                container.innerHTML     = html;
                container.style.opacity = '1';
                history.pushState(null, '', url);
                urlParams = new URLSearchParams(url.split('?')[1] || '');
                listBusy  = false;
                refocusSearch();
            })
            .catch(function () { container.style.opacity = '1'; listBusy = false; });
    }

    function refocusSearch() {
        var si = document.getElementById('inv-search-input');
        if (si) { si.focus(); var l = si.value.length; si.setSelectionRange(l, l); }
    }

    // ── Intercept sort/pagination clicks inside #inv-list (capture phase) ──
    if (window._invListClickHandler) {
        document.removeEventListener('click', window._invListClickHandler, true);
    }
    window._invListClickHandler = function (e) {
        var container = document.getElementById('inv-list');
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
    document.addEventListener('click', window._invListClickHandler, true);

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

    window.invSearchClear = function () {
        input.value = '';
        clearBtn.style.display = 'none';
        doSearch();
        input.focus();
    };

    function doSearch() {
        var q          = input.value.trim();
        var tab        = urlParams.get('tab') || 'new';
        var sort       = urlParams.get('sort') || '';
        var dir        = urlParams.get('dir') || '';
        var customerId = urlParams.get('customer_id') || '';

        var params = new URLSearchParams();
        params.set('tab', tab);
        if (sort)       params.set('sort', sort);
        if (dir)        params.set('dir', dir);
        if (q)          params.set('search', q);
        if (customerId) params.set('customer_id', customerId);
        if (checkbox.checked) params.set('full_search', '1');

        fetchList(window.location.pathname + '?' + params.toString());
    }

    // Keep cursor ready in search field
    input.focus();
    var len = input.value.length;
    input.setSelectionRange(len, len);
})();
</script>
@endpush
