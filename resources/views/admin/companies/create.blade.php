@extends('layouts.admin')
@section('title', 'New Company')

@section('content')

<form method="POST" action="{{ route('admin.companies.store') }}" style="max-width:760px;margin-top:.85rem;">
    @csrf

    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1rem;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company Info</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Name · Contact · Tax · Status</div>
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:.85rem 1.25rem;">
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Company Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                @error('name')<div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Owner / Primary Contact</label>
                <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Tax ID / EIN</label>
                <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                @error('email')<div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Website</label>
                <input type="url" name="website" value="{{ old('website') }}" placeholder="https://"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                @error('website')<div style="color:#dc2626;font-size:.8rem;margin-top:.25rem;">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Status <span style="color:#dc2626;">*</span></label>
                <select name="status" style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;background:#fff;">
                    @foreach(['active' => 'Active', 'pending' => 'Pending', 'inactive' => 'Inactive'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('status', 'active') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">
                    Custom Tax Rate %
                    <span style="font-weight:400;color:#888;">(default: {{ number_format($defaultTaxPct, 2) }}%)</span>
                </label>
                <input type="number" name="tax_rate_pct" value="{{ old('tax_rate_pct') }}" step="0.01" min="0" max="100"
                       placeholder="{{ number_format($defaultTaxPct, 2) }}"
                       style="width:140px;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;">
            </div>
        </div>
    </div>

    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;margin-bottom:1.25rem;">
        <div style="background:var(--primary);padding:.8rem 1.25rem;border-radius:7px 7px 0 0;display:flex;align-items:center;gap:.6rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <div>
                <div style="font-size:.92rem;font-weight:700;color:#fff;line-height:1.2;">Company Address</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.6);margin-top:.08rem;">Billing address for invoices</div>
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:.85rem 1.25rem;">
            <div style="grid-column:1/-1;">
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Street</label>
                <input type="text" name="address_street" value="{{ old('address_street') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">City</label>
                <input type="text" name="address_city" value="{{ old('address_city') }}"
                       style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
            </div>
            <div style="display:grid;grid-template-columns:80px 1fr;gap:.5rem;">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">State</label>
                    <input type="text" name="address_state" value="{{ old('address_state') }}" maxlength="2" placeholder="CA"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;text-transform:uppercase;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">ZIP</label>
                    <input type="text" name="address_zip" value="{{ old('address_zip') }}" maxlength="10"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-primary">Create Company</button>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('topbar-title')
<div style="display:flex;align-items:center;gap:.75rem;">
    <a href="{{ route('admin.companies.index') }}" style="font-size:.78rem;color:var(--accent);text-decoration:none;font-weight:600;white-space:nowrap;display:inline-flex;align-items:center;gap:.3rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
        Companies
    </a>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;">New Company</h1>
</div>
@endpush
