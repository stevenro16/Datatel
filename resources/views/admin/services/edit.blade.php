@extends('layouts.admin')
@section('title', 'Edit Service')

@section('content')

<div style="max-width:620px;display:grid;gap:1.25rem;">

    {{-- Edit form --}}
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
    <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @php $currentIcon = old('icon', $service->icon ?? ''); @endphp

        <div style="display:grid;gap:1.25rem;">
            <div>
                <label>Service Name *</label>
                <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
            </div>

            {{-- Icon Picker --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#444;margin-bottom:.5rem;">Service Icon</label>
                <input type="hidden" name="icon" id="icon-value" value="{{ $currentIcon }}">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:.45rem;">
                    @foreach($icons as $key => $icon)
                    <button type="button" onclick="selectIcon('{{ $key }}')"
                            id="icon-btn-{{ $key }}"
                            style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3rem;padding:.6rem .35rem;border:2px solid {{ $currentIcon === $key ? 'var(--accent)' : '#e5e7eb' }};background:{{ $currentIcon === $key ? '#f0f6ff' : '#fff' }};border-radius:8px;cursor:pointer;transition:border-color .12s,background .12s;min-height:68px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="{{ $currentIcon === $key ? 'var(--accent)' : '#6b7280' }}" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" id="icon-svg-{{ $key }}">{!! $icon['paths'] !!}</svg>
                        <span id="icon-lbl-{{ $key }}" style="font-size:.6rem;color:{{ $currentIcon === $key ? 'var(--accent)' : '#9ca3af' }};text-align:center;line-height:1.25;font-weight:{{ $currentIcon === $key ? '700' : '500' }};">{{ $icon['label'] }}</span>
                    </button>
                    @endforeach
                </div>
                <p style="font-size:.75rem;color:#9ca3af;margin:.4rem 0 0;">Click an icon to assign it. Click again to remove.</p>
            </div>

            <div>
                <label>Description</label>
                <textarea name="description" rows="3"
                          style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;resize:vertical;">{{ old('description', $service->description) }}</textarea>
            </div>
            <div style="max-width:220px;">
                <label>Default Price per Unit</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#888;font-size:.9rem;">$</span>
                    <input type="number" name="default_unit_price"
                           value="{{ old('default_unit_price', $service->default_unit_price !== null ? number_format($service->default_unit_price, 2) : '') }}"
                           min="0" step="0.01" placeholder="0.00"
                           style="width:100%;padding:.55rem .85rem .55rem 1.75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                </div>
                <p style="font-size:.78rem;color:#888;margin:.3rem 0 0;">Pre-fills the unit price when added to an invoice.</p>
            </div>

            {{-- Service Image --}}
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:#444;margin-bottom:.5rem;">Service Image <span style="font-size:.75rem;font-weight:400;color:#9ca3af;">(optional — icon used if not set)</span></label>

                @if($service->imageUrl())
                <div style="margin-bottom:.85rem;display:flex;align-items:flex-start;gap:1rem;">
                    <img src="{{ $service->imageUrl() }}" alt="{{ $service->name }}"
                         style="width:160px;height:100px;object-fit:cover;border-radius:7px;border:1px solid #e5e7eb;flex-shrink:0;">
                    <div>
                        <p style="font-size:.82rem;color:#374151;margin:0 0 .4rem;font-weight:600;">Current image</p>
                        <p style="font-size:.78rem;color:#9ca3af;margin:0 0 .65rem;">{{ $service->image }}</p>
                        <label style="display:flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:400;color:#dc2626;cursor:pointer;">
                            <input type="checkbox" name="remove_image" value="1" style="width:auto;accent-color:#dc2626;">
                            Remove current image
                        </label>
                    </div>
                </div>
                <p style="font-size:.8rem;color:#6b7280;margin:0 0 .5rem;">Upload a new image to replace it:</p>
                @endif

                <div style="border:2px dashed #d1d5db;border-radius:8px;padding:1.1rem;text-align:center;background:#f9fafb;cursor:pointer;transition:border-color .15s;"
                     onclick="document.getElementById('edit-img').click()"
                     onmouseover="this.style.borderColor='#2E86C1'" onmouseout="this.style.borderColor='#d1d5db'">
                    <img id="img-preview" src="" alt="" style="display:none;max-height:120px;border-radius:6px;margin:0 auto .65rem;">
                    <svg id="img-icon" xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.5" style="margin:0 auto .4rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p id="img-text" style="color:#6b7280;font-size:.8rem;margin:0;">{{ $service->image ? 'Click to replace image' : 'Click to upload an image' }}<br><span style="font-size:.73rem;color:#9ca3af;">JPG, PNG, WebP · max 5 MB</span></p>
                    <input type="file" id="edit-img" name="image" accept="image/jpeg,image/png,image/gif,image/webp" style="display:none;"
                           onchange="previewImage(this)">
                </div>
                @error('image')<span style="color:#b91c1c;font-size:.8rem;margin-top:.25rem;display:block;">{{ $message }}</span>@enderror
            </div>
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
    </div>

    {{-- Activate / Inactivate --}}
    <div style="background:#fff;padding:1.25rem 2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);display:flex;align-items:center;justify-content:space-between;gap:1rem;">
        <div>
            <p style="font-weight:600;margin:0 0 .2rem;">
                {{ $service->is_active ? 'Inactivate this service' : 'Reactivate this service' }}
            </p>
            <p style="font-size:.85rem;color:#666;margin:0;">
                @if($service->is_active)
                    Inactive services are hidden from new work order forms but stay on existing records.
                @else
                    Reactivating will make this service available for selection on new work orders.
                @endif
            </p>
        </div>
        <form method="POST" action="{{ route('admin.services.toggle', $service) }}" style="flex-shrink:0;">
            @csrf
            <button type="submit" class="btn btn-sm"
                style="background:{{ $service->is_active ? '#fff7ed' : '#f0fdf4' }};color:{{ $service->is_active ? '#c2410c' : '#15803d' }};border:1px solid {{ $service->is_active ? '#fed7aa' : '#bbf7d0' }};padding:.45rem 1rem;">
                {{ $service->is_active ? 'Inactivate' : 'Activate' }}
            </button>
        </form>
    </div>

</div>

<script>
var currentIcon = '{{ $currentIcon }}';

function selectIcon(key) {
    var prev = currentIcon;
    currentIcon = (currentIcon === key) ? '' : key;
    document.getElementById('icon-value').value = currentIcon;

    [prev, key].filter(Boolean).forEach(function(k) {
        var btn = document.getElementById('icon-btn-' + k);
        var svg = document.getElementById('icon-svg-' + k);
        var lbl = document.getElementById('icon-lbl-' + k);
        if (!btn) return;
        var on = (k === currentIcon);
        btn.style.borderColor = on ? 'var(--accent)' : '#e5e7eb';
        btn.style.background  = on ? '#f0f6ff' : '#fff';
        if (svg) svg.setAttribute('stroke', on ? 'var(--accent)' : '#6b7280');
        if (lbl) { lbl.style.color = on ? 'var(--accent)' : '#9ca3af'; lbl.style.fontWeight = on ? '700' : '500'; }
    });
}

function previewImage(input) {
    const preview = document.getElementById('img-preview');
    const icon    = document.getElementById('img-icon');
    const text    = document.getElementById('img-text');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src           = e.target.result;
            preview.style.display = 'block';
            icon.style.display    = 'none';
            text.textContent      = input.files[0].name;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.services.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;">← Service Catalog</a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">Edit Service</h1>
    <span class="badge" style="background:{{ $service->is_active ? '#d1fae5' : '#fee2e2' }};color:{{ $service->is_active ? '#065f46' : '#991b1b' }}">
        {{ $service->is_active ? 'Active' : 'Inactive' }}
    </span>
</div>
@endpush
