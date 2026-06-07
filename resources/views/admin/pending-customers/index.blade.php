@extends('layouts.admin')
@section('title', 'Pending Account Approvals')

@section('content')
@if($pending->count() > 0)
<div style="margin-bottom:.85rem;margin-top:.85rem;">
    <span style="display:inline-flex;align-items:center;gap:.4rem;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:8px;padding:.45rem .85rem;font-size:.82rem;font-weight:700;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        {{ $pending->count() }} awaiting review
    </span>
</div>
@endif

@if($pending->isEmpty())
<div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:3rem;text-align:center;color:#999;margin-top:.85rem;">
    <div style="font-size:2rem;margin-bottom:.75rem;">✓</div>
    <div style="font-size:1rem;font-weight:600;color:#555;">No pending accounts</div>
    <div style="font-size:.88rem;margin-top:.4rem;">All customer registrations have been reviewed.</div>
</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Registered</th>
            <th style="text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pending as $user)
        <tr>
            <td style="font-weight:600;">{{ $user->name }}</td>
            <td style="color:#555;">{{ $user->email }}</td>
            <td>
                @if($user->requestedCompany)
                    <span style="font-size:.88rem;color:#1A3C5E;font-weight:600;">{{ $user->requestedCompany->name }}</span>
                @elseif($user->requested_company_name)
                    <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                        <span style="font-size:.82rem;background:#fef3c7;color:#92400e;padding:.2rem .6rem;border-radius:4px;font-weight:600;">
                            Other: {{ $user->requested_company_name }}
                        </span>
                        <button type="button"
                                onclick="openCreateCompanyModal({{ $user->id }}, '{{ addslashes($user->requested_company_name) }}')"
                                style="font-size:.78rem;padding:.2rem .65rem;border-radius:5px;border:1px solid #2E86C1;color:#2E86C1;background:#fff;cursor:pointer;white-space:nowrap;">
                            + Create Company
                        </button>
                    </div>
                @else
                    <span style="color:#bbb;font-size:.85rem;">—</span>
                @endif
            </td>
            <td style="font-size:.85rem;color:#888;">{{ $user->created_at->format('M j, Y g:i A') }}</td>
            <td style="text-align:center;">
                <div style="display:flex;gap:.5rem;justify-content:center;">
                    <form method="POST" action="{{ route('admin.pending-customers.approve', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                    </form>
                    <form method="POST" action="{{ route('admin.pending-customers.reject', $user) }}"
                          onsubmit="return confirm('Reject and permanently delete {{ addslashes($user->name) }}\'s account request?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                    </form>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Create Company modal --}}
<div id="create-company-modal" onclick="if(event.target===this)closeCreateCompanyModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:560px;max-height:90vh;overflow-y:auto;">
        <div style="padding:1.5rem 1.75rem;border-bottom:1px solid #e5e7eb;">
            <h3 style="font-size:1rem;color:var(--primary);margin:0;">Create New Company</h3>
        </div>

        <form id="create-company-form" method="POST" action="" style="padding:1.5rem 1.75rem;">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.85rem 1.25rem;margin-bottom:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Company Name *</label>
                    <input type="text" name="name" id="cc-name" required
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Owner / Primary Contact</label>
                    <input type="text" name="owner_name"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Phone</label>
                    <input type="text" name="phone"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Email</label>
                    <input type="email" name="email"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>

                <div style="grid-column:1/-1;padding-top:.5rem;border-top:1px solid #f0f0f0;">
                    <div style="font-size:.8rem;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.6rem;">Default Address</div>
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Street</label>
                    <input type="text" name="address_street"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">City</label>
                    <input type="text" name="address_city"
                           style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:80px 1fr;gap:.5rem;">
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">State</label>
                        <input type="text" name="address_state" maxlength="2" placeholder="TX"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;text-transform:uppercase;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Zip</label>
                        <input type="text" name="address_zip" maxlength="10"
                               style="width:100%;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>

                <div style="grid-column:1/-1;padding-top:.5rem;border-top:1px solid #f0f0f0;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">
                        Custom Tax Rate %
                        <span style="font-weight:400;color:#888;">(leave blank to use system default of {{ number_format($defaultTaxPct, 2) }}%)</span>
                    </label>
                    <input type="number" name="tax_rate_pct" step="0.01" min="0" max="100"
                           style="width:140px;padding:.5rem .75rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;"
                           placeholder="{{ number_format($defaultTaxPct, 2) }}">
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:.6rem;padding-top:.75rem;border-top:1px solid #e5e7eb;">
                <button type="button" onclick="closeCreateCompanyModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Create Company</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateCompanyModal(userId, companyName) {
    document.getElementById('cc-name').value = companyName;
    document.getElementById('create-company-form').action =
        '/admin/pending-customers/' + userId + '/create-company';
    document.getElementById('create-company-modal').style.display = 'flex';
    document.addEventListener('keydown', _ccKey);
}
function closeCreateCompanyModal() {
    document.getElementById('create-company-modal').style.display = 'none';
    document.removeEventListener('keydown', _ccKey);
}
function _ccKey(e) { if (e.key === 'Escape') closeCreateCompanyModal(); }
</script>

@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">ACCOUNT MANAGEMENT</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><circle cx="18" cy="8" r="3"/><line x1="18" y1="6" x2="18" y2="8.5"/><line x1="18" y1="8.5" x2="19.5" y2="8.5"/></svg>
        Pending Approvals
    </h1>
</div>
@endpush
