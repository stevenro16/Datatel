@extends('layouts.admin')
@section('title', 'Settings')

@section('content')

@php
$queueDefs = [
    'all'                  => ['label' => 'All Active',        'icon' => '📋', 'color' => '#1A3C5E'],
    'new'                  => ['label' => 'New',               'icon' => '✨', 'color' => '#7c3aed'],
    'pending_confirmation' => ['label' => 'Pending Confirm.',  'icon' => '⏳', 'color' => '#d97706'],
    'scheduled'            => ['label' => 'Scheduled',         'icon' => '📅', 'color' => '#0284c7'],
    'prepare_invoice'      => ['label' => 'Prepare Invoice',   'icon' => '📄', 'color' => '#059669'],
    'confirm_payment'      => ['label' => 'Confirm Payment',   'icon' => '💳', 'color' => '#dc2626'],
    'unread'               => ['label' => 'Unread Notes',      'icon' => '💬', 'color' => '#9a3412'],
];
$savedOrder = \App\Models\AdminSetting::get('work_queue_order');
if ($savedOrder) {
    $orderKeys = array_filter(array_map('trim', explode(',', $savedOrder)));
    $sorted = [];
    foreach ($orderKeys as $k) { if (isset($queueDefs[$k])) $sorted[$k] = $queueDefs[$k]; }
    foreach ($queueDefs as $k => $v) { if (!isset($sorted[$k])) $sorted[$k] = $v; }
    $queueDefs = $sorted;
}

$invoiceQueueDefs = [
    'new'              => ['label' => 'New / Draft',        'icon' => '📝', 'color' => '#7c3aed'],
    'billed'           => ['label' => 'Billed',             'icon' => '📬', 'color' => '#0284c7'],
    'payment_received' => ['label' => 'Payment Received',   'icon' => '💳', 'color' => '#059669'],
    'all_active'       => ['label' => 'All Active',         'icon' => '📋', 'color' => '#1A3C5E'],
    'past_due'         => ['label' => 'Past Due',           'icon' => '🔴', 'color' => '#dc2626'],
    'completed'        => ['label' => 'Completed',          'icon' => '✅', 'color' => '#6b7280'],
];
$savedInvOrder = \App\Models\AdminSetting::get('invoice_queue_order');
if ($savedInvOrder) {
    $invOrderKeys = array_filter(array_map('trim', explode(',', $savedInvOrder)));
    $invSorted = [];
    foreach ($invOrderKeys as $k) { if (isset($invoiceQueueDefs[$k])) $invSorted[$k] = $invoiceQueueDefs[$k]; }
    foreach ($invoiceQueueDefs as $k => $v) { if (!isset($invSorted[$k])) $invSorted[$k] = $v; }
    $invoiceQueueDefs = $invSorted;
}
@endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;margin-top:.85rem;">

    {{-- Company & Invoice settings --}}
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company &amp; Invoice</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing · Tax · Payment Terms</div>
            </div>
        </div>
        <div style="padding:1.5rem;">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @if(session('success'))<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>@endif

                <div style="display:grid;gap:1rem;">
                    <div>
                        <label>Company Name</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']->value ?? '') }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    </div>
                    <div>
                        <label>Company Phone</label>
                        <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']->value ?? '') }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    </div>
                    <div>
                        <label>Company Email</label>
                        <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email']->value ?? '') }}" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    </div>
                    <div>
                        <label>Default Tax Rate (%)</label>
                        <input type="number" name="default_tax_rate" value="{{ old('default_tax_rate', $settings['default_tax_rate']->value ?? '0') }}" min="0" max="100" step="0.01" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    </div>
                    <div>
                        <label>Invoice Payment Due (days)</label>
                        <input type="number" name="invoice_due_days" value="{{ old('invoice_due_days', $settings['invoice_due_days']->value ?? '30') }}" min="0" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                    </div>
                    <div>
                        <label>Invoice Terms / Footer</label>
                        <textarea name="invoice_terms" rows="3" style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;">{{ old('invoice_terms', $settings['invoice_terms']->value ?? '') }}</textarea>
                    </div>
                </div>

                <div style="margin-top:1.25rem;">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Right column: Service Catalog + Work Queue Priority --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

        {{-- Service catalog --}}
        @php $activeCount = $serviceTypes->where('is_active', true)->count(); @endphp
        <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                <div style="display:flex;align-items:center;gap:.6rem;min-width:0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
                    <div>
                        <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Service Catalog</div>
                        <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">{{ $activeCount }} active {{ Str::plural('service', $activeCount) }} · Work order options</div>
                    </div>
                </div>
                <a href="{{ route('admin.services.index') }}"
                   style="display:flex;align-items:center;gap:.3rem;padding:.28rem .75rem;border-radius:5px;border:1px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1);font-size:.75rem;font-weight:700;color:#fff;white-space:nowrap;text-decoration:none;flex-shrink:0;">
                    Manage →
                </a>
            </div>
            <div style="padding:1.5rem;">
                <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                    @foreach($serviceTypes->where('is_active', true)->sortBy('sort_order') as $svc)
                    <span style="background:#f0f6ff;color:var(--primary);padding:.25rem .7rem;border-radius:999px;font-size:.82rem;border:1px solid #d0e4f7;">{{ $svc->name }}</span>
                    @endforeach
                </div>
                @if($serviceTypes->where('is_active', false)->count())
                <p style="font-size:.8rem;color:#999;margin:.75rem 0 0;">
                    {{ $serviceTypes->where('is_active', false)->count() }} inactive.
                    <a href="{{ route('admin.services.index', ['filter' => 'inactive']) }}" style="color:var(--accent);">View</a>
                </p>
                @endif
            </div>
        </div>

        {{-- Work Queue Priority --}}
        <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Work Queue Tab Priority</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Drag to reorder · First tab loads by default</div>
                </div>
            </div>
            <div style="padding:1.5rem;">
                @if(session('queue_success'))
                <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('queue_success') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.settings.queue-order') }}" id="queue-order-form">
                    @csrf
                    <input type="hidden" name="queue_order" id="queue-order-value"
                           value="{{ collect(array_keys($queueDefs))->implode(',') }}">

                    <div id="queue-sortable" style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.25rem;">
                        @foreach($queueDefs as $key => $def)
                        <div class="queue-item" data-key="{{ $key }}"
                             draggable="true"
                             style="display:flex;align-items:center;gap:.85rem;padding:.7rem 1rem;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:8px;cursor:grab;user-select:none;transition:box-shadow .15s,border-color .15s;">
                            <span style="color:#9ca3af;font-size:1.1rem;cursor:grab;flex-shrink:0;">⠿</span>
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $def['color'] }};flex-shrink:0;display:inline-block;"></span>
                            <span style="font-size:1rem;flex-shrink:0;">{{ $def['icon'] }}</span>
                            <span style="font-weight:600;font-size:.9rem;color:#111;flex:1;">{{ $def['label'] }}</span>
                            @if($loop->first)
                            <span style="font-size:.7rem;font-weight:700;color:#0284c7;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:999px;padding:.1rem .5rem;white-space:nowrap;">Default</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary">Save Queue Order</button>
                </form>
            </div>
        </div>

        {{-- Invoice Queue Priority --}}
        <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Invoice Queue Tab Priority</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Drag to reorder · Highest priority with items loads first</div>
                </div>
            </div>
            <div style="padding:1.5rem;">
                @if(session('invoice_queue_success'))
                <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('invoice_queue_success') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.settings.invoice-queue-order') }}" id="invoice-queue-order-form">
                    @csrf
                    <input type="hidden" name="invoice_queue_order" id="invoice-queue-order-value"
                           value="{{ collect(array_keys($invoiceQueueDefs))->implode(',') }}">

                    <div id="invoice-queue-sortable" style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.25rem;">
                        @foreach($invoiceQueueDefs as $key => $def)
                        <div class="invoice-queue-item" data-key="{{ $key }}"
                             draggable="true"
                             style="display:flex;align-items:center;gap:.85rem;padding:.7rem 1rem;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:8px;cursor:grab;user-select:none;transition:box-shadow .15s,border-color .15s;">
                            <span style="color:#9ca3af;font-size:1.1rem;cursor:grab;flex-shrink:0;">⠿</span>
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $def['color'] }};flex-shrink:0;display:inline-block;"></span>
                            <span style="font-size:1rem;flex-shrink:0;">{{ $def['icon'] }}</span>
                            <span style="font-weight:600;font-size:.9rem;color:#111;flex:1;">{{ $def['label'] }}</span>
                            @if($loop->first)
                            <span class="inv-default-badge" style="font-size:.7rem;font-weight:700;color:#0284c7;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:999px;padding:.1rem .5rem;white-space:nowrap;">Default</span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-primary">Save Invoice Queue Order</button>
                </form>
            </div>
        </div>

        {{-- Appearance --}}
        <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);overflow:hidden;">
            <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                <div>
                    <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Appearance</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Interface theme · Saved in this browser</div>
                </div>
            </div>
            <div style="padding:1.5rem;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1.5rem;">
                    <div>
                        <div style="font-weight:600;font-size:.9rem;color:#111;">Dark Mode</div>
                        <div style="font-size:.78rem;color:#6b7280;margin-top:.2rem;">Applies to this browser only</div>
                    </div>
                    <label for="dark-mode-toggle" style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;flex-shrink:0;" title="Toggle dark mode">
                        <input type="checkbox" id="dark-mode-toggle" style="position:absolute;opacity:0;width:0;height:0;">
                        <span id="dm-track" style="position:absolute;inset:0;border-radius:999px;background:#d1d5db;transition:background .2s;"></span>
                        <span id="dm-thumb" style="position:absolute;width:18px;height:18px;top:3px;left:3px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .2s;"></span>
                    </label>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    const list    = document.getElementById('queue-sortable');
    const hidden  = document.getElementById('queue-order-value');
    if (!list) return;

    let dragSrc = null;

    function updateHidden() {
        hidden.value = [...list.querySelectorAll('.queue-item')]
            .map(el => el.dataset.key).join(',');
    }

    function updateDefaultBadge() {
        list.querySelectorAll('.queue-item').forEach((el, i) => {
            let badge = el.querySelector('.default-badge');
            if (i === 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'default-badge';
                    badge.style.cssText = 'font-size:.7rem;font-weight:700;color:#0284c7;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:999px;padding:.1rem .5rem;white-space:nowrap;';
                    badge.textContent = 'Default';
                    el.appendChild(badge);
                }
            } else if (badge) {
                badge.remove();
            }
        });
    }

    list.querySelectorAll('.queue-item').forEach(item => {
        item.addEventListener('dragstart', e => {
            dragSrc = item;
            item.style.opacity = '.4';
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            item.style.opacity = '1';
            list.querySelectorAll('.queue-item').forEach(el => {
                el.style.borderColor = '#e5e7eb';
                el.style.boxShadow   = '';
            });
            updateDefaultBadge();
        });

        item.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (item !== dragSrc) {
                item.style.borderColor = 'var(--accent)';
                item.style.boxShadow   = '0 0 0 3px rgba(46,134,193,.15)';
            }
        });

        item.addEventListener('dragleave', () => {
            item.style.borderColor = '#e5e7eb';
            item.style.boxShadow   = '';
        });

        item.addEventListener('drop', e => {
            e.preventDefault();
            if (dragSrc && dragSrc !== item) {
                const items   = [...list.querySelectorAll('.queue-item')];
                const srcIdx  = items.indexOf(dragSrc);
                const tgtIdx  = items.indexOf(item);
                if (srcIdx < tgtIdx) {
                    item.after(dragSrc);
                } else {
                    item.before(dragSrc);
                }
                updateHidden();
            }
            item.style.borderColor = '#e5e7eb';
            item.style.boxShadow   = '';
        });
    });
})();

// Dark mode toggle
(function () {
    const toggle = document.getElementById('dark-mode-toggle');
    const track  = document.getElementById('dm-track');
    const thumb  = document.getElementById('dm-thumb');
    const KEY    = 'adminDarkMode';
    if (!toggle) return;

    function applyDark(dark) {
        document.documentElement.classList.toggle('dark', dark);
        toggle.checked = dark;
        if (track) track.style.background = dark ? 'var(--accent)' : '#d1d5db';
        if (thumb) thumb.style.transform  = dark ? 'translateX(20px)' : '';
    }

    applyDark(localStorage.getItem(KEY) === '1');

    toggle.addEventListener('change', function () {
        const isDark = this.checked;
        localStorage.setItem(KEY, isDark ? '1' : '0');
        applyDark(isDark);
    });
})();

// Invoice queue drag-and-drop
(function () {
    const list   = document.getElementById('invoice-queue-sortable');
    const hidden = document.getElementById('invoice-queue-order-value');
    if (!list) return;

    let dragSrc = null;

    function updateHidden() {
        hidden.value = [...list.querySelectorAll('.invoice-queue-item')]
            .map(el => el.dataset.key).join(',');
    }

    function updateDefaultBadge() {
        list.querySelectorAll('.invoice-queue-item').forEach((el, i) => {
            let badge = el.querySelector('.inv-default-badge');
            if (i === 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'inv-default-badge';
                    badge.style.cssText = 'font-size:.7rem;font-weight:700;color:#0284c7;background:#e0f2fe;border:1px solid #7dd3fc;border-radius:999px;padding:.1rem .5rem;white-space:nowrap;';
                    badge.textContent = 'Default';
                    el.appendChild(badge);
                }
            } else if (badge) {
                badge.remove();
            }
        });
    }

    list.querySelectorAll('.invoice-queue-item').forEach(item => {
        item.addEventListener('dragstart', e => {
            dragSrc = item;
            item.style.opacity = '.4';
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            item.style.opacity = '1';
            list.querySelectorAll('.invoice-queue-item').forEach(el => {
                el.style.borderColor = '#e5e7eb';
                el.style.boxShadow   = '';
            });
            updateDefaultBadge();
        });

        item.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (item !== dragSrc) {
                item.style.borderColor = 'var(--accent)';
                item.style.boxShadow   = '0 0 0 3px rgba(46,134,193,.15)';
            }
        });

        item.addEventListener('dragleave', () => {
            item.style.borderColor = '#e5e7eb';
            item.style.boxShadow   = '';
        });

        item.addEventListener('drop', e => {
            e.preventDefault();
            if (dragSrc && dragSrc !== item) {
                const items  = [...list.querySelectorAll('.invoice-queue-item')];
                const srcIdx = items.indexOf(dragSrc);
                const tgtIdx = items.indexOf(item);
                if (srcIdx < tgtIdx) {
                    item.after(dragSrc);
                } else {
                    item.before(dragSrc);
                }
                updateHidden();
            }
            item.style.borderColor = '#e5e7eb';
            item.style.boxShadow   = '';
        });
    });
})();
</script>
@endpush

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">CONFIGURATION</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        System Settings
    </h1>
</div>
@endpush
