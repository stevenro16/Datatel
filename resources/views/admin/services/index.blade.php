@extends('layouts.admin')
@section('title', 'Service Catalog')

@section('content')

@php
    $pillBase = 'padding:.3rem .85rem;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid;';
    $pillOn   = $pillBase . 'background:var(--primary);color:#fff;border-color:var(--primary);';
    $pillOff  = $pillBase . 'background:#fff;color:#555;border-color:#d1d5db;';
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:1.25rem;margin-top:.85rem;flex-wrap:wrap;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
        <a href="{{ route('admin.services.index', ['filter' => 'active']) }}"   style="{{ $filter === 'active'   ? $pillOn : $pillOff }}">Active</a>
        <a href="{{ route('admin.services.index', ['filter' => 'inactive']) }}" style="{{ $filter === 'inactive' ? $pillOn : $pillOff }}">Inactive</a>
        <a href="{{ route('admin.services.index', ['filter' => 'all']) }}"      style="{{ $filter === 'all'      ? $pillOn : $pillOff }}">All</a>
    </div>
    <a href="{{ route('admin.services.create') }}"
       style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--accent);color:#fff;border-radius:6px;font-size:.875rem;font-weight:700;box-shadow:0 2px 6px rgba(46,134,193,.3);letter-spacing:.01em;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New
    </a>
</div>

<div id="save-indicator" style="display:none;font-size:.82rem;color:#16a34a;margin-bottom:.75rem;">✓ Order saved</div>

<table class="data-table" id="services-table">
    <thead>
        <tr>
            <th style="width:32px;"></th>
            <th>Service Name</th>
            <th>Description</th>
            <th style="width:120px;text-align:right;">Default Price</th>
            <th style="width:90px;text-align:center;">Status</th>
            <th style="width:160px;"></th>
        </tr>
    </thead>
    <tbody id="sortable-body">
        @forelse($services as $svc)
        <tr draggable="true" data-id="{{ $svc->id }}" style="cursor:grab;">
            <td style="color:#ccc;font-size:1.15rem;text-align:center;padding-right:0;user-select:none;" title="Drag to reorder">⠿</td>
            <td style="font-weight:600;">{{ $svc->name }}</td>
            <td style="font-size:.88rem;color:#555;">{{ $svc->description ?? '—' }}</td>
            <td style="text-align:right;font-size:.9rem;">
                @if($svc->default_unit_price !== null)
                    ${{ number_format($svc->default_unit_price, 2) }}
                @else
                    <span style="color:#ccc;">—</span>
                @endif
            </td>
            <td style="text-align:center;">
                <span class="badge" style="background:{{ $svc->is_active ? '#d1fae5' : '#fee2e2' }};color:{{ $svc->is_active ? '#065f46' : '#991b1b' }}">
                    {{ $svc->is_active ? 'Active' : 'Inactive' }}
                </span>
            </td>
            <td>
                <div style="display:flex;gap:.4rem;justify-content:flex-end;">
                    <a href="{{ route('admin.services.edit', $svc) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.services.toggle', $svc) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                            style="background:{{ $svc->is_active ? '#fff7ed' : '#f0fdf4' }};color:{{ $svc->is_active ? '#c2410c' : '#15803d' }};border:1px solid {{ $svc->is_active ? '#fed7aa' : '#bbf7d0' }};">
                            {{ $svc->is_active ? 'Inactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center;color:#999;padding:2.5rem;">
                No {{ $filter !== 'all' ? $filter : '' }} services found.
                @if($filter === 'inactive')
                    <a href="{{ route('admin.services.index') }}" style="color:var(--accent);">View active</a>
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<p style="font-size:.8rem;color:#999;margin-top:.75rem;">
    Drag rows to reorder. Services marked inactive are hidden from new work order forms but remain on existing records.
</p>

<script>
(function () {
    const tbody      = document.getElementById('sortable-body');
    const reorderUrl = @json(route('admin.services.reorder'));
    const csrfToken  = @json(csrf_token());
    let dragSrc = null;

    function rows() {
        return [...tbody.querySelectorAll('tr[draggable]')];
    }

    function saveOrder() {
        const ids = rows().map(r => parseInt(r.dataset.id));
        fetch(reorderUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ ids }),
        }).then(() => {
            const ind = document.getElementById('save-indicator');
            ind.style.display = 'block';
            clearTimeout(ind._t);
            ind._t = setTimeout(() => ind.style.display = 'none', 2000);
        });
    }

    rows().forEach(row => {
        row.addEventListener('dragstart', e => {
            dragSrc = row;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => row.style.opacity = '0.4', 0);
        });

        row.addEventListener('dragend', () => {
            row.style.opacity = '';
            rows().forEach(r => r.style.boxShadow = '');
            saveOrder();
        });

        row.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            rows().forEach(r => r.style.boxShadow = '');
            if (row !== dragSrc) {
                row.style.boxShadow = 'inset 0 -2px 0 var(--accent)';
            }
        });

        row.addEventListener('dragleave', () => {
            row.style.boxShadow = '';
        });

        row.addEventListener('drop', e => {
            e.preventDefault();
            if (dragSrc && row !== dragSrc) {
                const allRows = rows();
                const srcIdx  = allRows.indexOf(dragSrc);
                const dstIdx  = allRows.indexOf(row);
                if (srcIdx < dstIdx) {
                    row.after(dragSrc);
                } else {
                    row.before(dragSrc);
                }
            }
        });
    });
})();
</script>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">SERVICES</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        Service Catalog
    </h1>
</div>
@endpush
