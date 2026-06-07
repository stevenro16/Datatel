@php
    $layout = match(auth()->user()->role ?? '') {
        'admin'    => 'layouts.admin',
        'employee' => 'layouts.employee',
        default    => 'layouts.portal',
    };
    $activeSites   = $sites->where('is_active', true);
    $inactiveSites = $sites->where('is_active', false);
    $sitesLabel    = $company ? 'Company Sites' : 'My Sites';
@endphp
@extends($layout)
@section('title', 'My Account')

@section('content')
<h1 class="page-title">My Account</h1>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.5rem;">{{ session('success') }}</div>
@endif
@if(session('status') === 'profile-updated')
    <div class="alert alert-success" style="margin-bottom:1.5rem;">Profile information saved.</div>
@endif
@if(session('status') === 'password-updated')
    <div class="alert alert-success" style="margin-bottom:1.5rem;">Password updated successfully.</div>
@endif

<div style="display:grid;gap:1.5rem;max-width:600px;">

    {{-- ── Profile Information ── --}}
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
            <h2 class="section-title" style="margin:0;">Profile Information</h2>
            <button type="button" onclick="openPasswordModal()"
                    style="padding:.35rem .9rem;border-radius:6px;border:1px solid #d1d5db;background:#f8f9fa;color:#555;font-size:.83rem;cursor:pointer;">
                🔒 Change Password
            </button>
        </div>

        @if($errors->has('name') || $errors->has('phone'))
            <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label>Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                </div>
                <div>
                    <label>Title <span style="font-weight:400;color:#888;">(job title)</span></label>
                    <input type="text" name="title" value="{{ old('title', $user->title) }}"
                           placeholder="e.g. Network Engineer"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <label>Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;"
                       placeholder="e.g. (555) 123-4567">
            </div>
            <div style="margin-bottom:1.25rem;">
                <label style="color:#888;">Email Address</label>
                <input type="text" value="{{ $user->email }}" disabled
                       style="width:100%;padding:.55rem .85rem;border:1px solid #e5e7eb;border-radius:5px;font-size:.9rem;background:#f9fafb;color:#888;">
                <p style="font-size:.78rem;color:#999;margin-top:.3rem;">Contact support to change your email address.</p>
            </div>
            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    {{-- ── Company ── --}}
    @if($company)
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <h2 class="section-title" style="margin-top:0;">Company</h2>
        <div style="display:grid;gap:.5rem;font-size:.9rem;color:#555;">
            <div style="display:flex;gap:.75rem;">
                <span style="color:#888;min-width:110px;">Company</span>
                <span style="font-weight:600;color:var(--primary);">{{ $company->name }}</span>
            </div>
            @if($company->owner_name)
            <div style="display:flex;gap:.75rem;">
                <span style="color:#888;min-width:110px;">Primary Contact</span>
                <span>{{ $company->owner_name }}</span>
            </div>
            @endif
            @if($company->phone)
            <div style="display:flex;gap:.75rem;">
                <span style="color:#888;min-width:110px;">Phone</span>
                <span>{{ $company->phone }}</span>
            </div>
            @endif
            @if($company->email)
            <div style="display:flex;gap:.75rem;">
                <span style="color:#888;min-width:110px;">Email</span>
                <span>{{ $company->email }}</span>
            </div>
            @endif
            @if($company->address_street)
            <div style="display:flex;gap:.75rem;">
                <span style="color:#888;min-width:110px;">Address</span>
                <span>{{ $company->address_street }}, {{ $company->address_city }}, {{ $company->address_state }} {{ $company->address_zip }}</span>
            </div>
            @endif
        </div>
        <p style="font-size:.78rem;color:#999;margin-top:1rem;margin-bottom:0;">
            Contact DataTel to update company information.
        </p>
    </div>
    @endif

    {{-- ── Default Preferred Availability (customers only) ── --}}
    @if(auth()->user()->role === 'customer')
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <h2 class="section-title" style="margin-top:0;">Default Preferred Availability</h2>
        <p style="font-size:.85rem;color:#666;margin-bottom:1.25rem;margin-top:0;">
            Set the days and times that work best for you. These will auto-fill whenever you submit a new work order — you can always override them per order.
        </p>

        @if(session('success') === 'Default availability saved.')
        <div class="alert alert-success" style="margin-bottom:1rem;">Default availability saved.</div>
        @endif

        <form method="POST" action="{{ route('profile.availability.update') }}">
            @csrf @method('PATCH')
            <input type="hidden" name="preferred_availability" id="profile-avail-json"
                   value="{{ json_encode($user->preferred_availability ?: (object)[]) }}">

            <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.5rem;">
                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                <button type="button" class="profile-avail-day-btn" data-day="{{ $day }}"
                        style="padding:.3rem .8rem;border-radius:999px;border:2px solid #d1d5db;
                               background:#fff;font-size:.8rem;font-weight:600;color:#6b7280;
                               cursor:pointer;transition:all .12s;line-height:1.3;">
                    {{ $dayLabel }}
                </button>
                @endforeach
            </div>

            <div id="profile-avail-panels" style="display:none;border:1px solid #bfdbfe;border-radius:6px;overflow:hidden;margin-bottom:1rem;">
                @foreach(['monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri','saturday'=>'Sat'] as $day => $dayLabel)
                <div class="profile-avail-day-panel" data-day="{{ $day }}"
                     style="display:none;align-items:center;justify-content:center;gap:.6rem;padding:.5rem .85rem;border-bottom:1px solid #dbeafe;background:#f0f7ff;flex-wrap:wrap;">
                    <span style="font-size:.78rem;font-weight:700;color:var(--primary);width:30px;flex-shrink:0;text-align:center;">{{ $dayLabel }}</span>
                    @foreach(['morning'=>['Morning','7am–11am'],'lunch'=>['Lunch','11am–2pm'],'afternoon'=>['Afternoon','2pm–6pm']] as $slot => $slotData)
                    <button type="button" class="profile-avail-slot-btn" data-day="{{ $day }}" data-slot="{{ $slot }}"
                            style="padding:.3rem .85rem;border-radius:8px;border:1.5px solid #93c5fd;
                                   background:#fff;cursor:pointer;transition:all .12s;text-align:center;min-width:108px;">
                        <div class="psb-name" style="font-size:.74rem;font-weight:700;color:#3b82f6;line-height:1.3;">{{ $slotData[0] }}</div>
                        <div class="psb-time" style="font-size:.62rem;color:#93c5fd;line-height:1.2;font-weight:500;">{{ $slotData[1] }}</div>
                    </button>
                    @endforeach
                </div>
                @endforeach
            </div>

            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary" style="font-size:.88rem;">Save Default Availability</button>
                @if($user->preferred_availability)
                <button type="button" onclick="clearProfileAvail()"
                        style="font-size:.82rem;color:#6b7280;background:none;border:none;cursor:pointer;text-decoration:underline;">
                    Clear defaults
                </button>
                @endif
            </div>
            @if(!$user->preferred_availability)
            <p style="font-size:.78rem;color:#9ca3af;margin-top:.65rem;margin-bottom:0;">
                No default set — new work orders start with no availability preference.
            </p>
            @endif
        </form>
    </div>
    @endif

    {{-- ── Sites (not shown to admins) ── --}}
    @if(auth()->user()->role !== 'admin')
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
            <h2 class="section-title" style="margin:0;">{{ $sitesLabel }}</h2>
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddSiteModal()">+ Add Site</button>
        </div>
        <p style="font-size:.85rem;color:#666;margin-bottom:1.25rem;">
            @if($company)
                These sites are shared across all members of <strong>{{ $company->name }}</strong>.
            @else
                Your saved locations for quickly filling in work order addresses.
            @endif
        </p>

        @if($errors->siteStore->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->siteStore->first() }}</div>
        @endif
        @if($errors->siteUpdate->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->siteUpdate->first() }}</div>
        @endif

        {{-- Active sites --}}
        @forelse($activeSites as $site)
        <div style="border:1px solid {{ $site->is_default ? 'var(--accent)' : '#e5e7eb' }};border-radius:6px;margin-bottom:.75rem;overflow:hidden;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;">
                <div style="min-width:0;">
                    <div style="font-weight:600;font-size:.95rem;color:var(--primary);display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                        {{ $site->label }}
                        @if($site->is_default)
                            <span style="font-size:.72rem;background:#dbeafe;color:#1e40af;padding:.15em .55em;border-radius:999px;font-weight:700;">DEFAULT</span>
                        @endif
                    </div>
                    <div style="font-size:.85rem;color:#666;margin-top:.2rem;">{{ $site->formattedAddress() }}</div>
                </div>
                <div style="display:flex;gap:.35rem;flex-shrink:0;flex-wrap:wrap;">
                    @if(!$site->is_default)
                    <form method="POST" action="{{ route('portal.sites.default', $site) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Set Default</button>
                    </form>
                    @endif
                    <button type="button" class="btn btn-secondary btn-sm"
                            onclick="openEditSiteModal({{ $site->id }}, '{{ addslashes($site->label) }}', '{{ addslashes($site->street) }}', '{{ addslashes($site->city) }}', '{{ $site->state }}', '{{ $site->zip }}')">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('portal.sites.deactivate', $site) }}"
                          onsubmit="return confirm('Deactivate \'{{ addslashes($site->label) }}\'? It will no longer appear in work orders.')">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm" style="color:#92400e;border-color:#d97706;">Deactivate</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;color:#999;padding:1.5rem;border:2px dashed #e5e7eb;border-radius:6px;font-size:.88rem;">
            No active sites yet. Add your first location using the button above.
        </div>
        @endforelse

        {{-- Inactive sites --}}
        @if($inactiveSites->isNotEmpty())
        <details style="margin-top:1rem;">
            <summary style="font-size:.85rem;color:#888;cursor:pointer;user-select:none;padding:.4rem 0;">
                {{ $inactiveSites->count() }} inactive site{{ $inactiveSites->count() === 1 ? '' : 's' }}
            </summary>
            <div style="margin-top:.75rem;display:grid;gap:.5rem;">
                @foreach($inactiveSites as $site)
                <div style="border:1px solid #e5e7eb;border-radius:6px;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.75rem;background:#fafafa;opacity:.8;">
                    <div>
                        <div style="font-weight:600;font-size:.88rem;color:#888;">{{ $site->label }}</div>
                        <div style="font-size:.82rem;color:#aaa;">{{ $site->formattedAddress() }}</div>
                    </div>
                    <form method="POST" action="{{ route('portal.sites.reactivate', $site) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Reactivate</button>
                    </form>
                </div>
                @endforeach
            </div>
        </details>
        @endif
    </div>
    @endif {{-- end non-admin sites section --}}

    {{-- ── Delete Account ── --}}
    @if(auth()->user()->role === 'customer')
    <div style="background:#fff;padding:2rem;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);">
        <h2 class="section-title" style="margin-top:0;color:var(--danger);">Delete Account</h2>
        <p style="font-size:.88rem;color:#666;margin-bottom:1.25rem;">
            Permanently removes your account and all associated data. This cannot be undone.
        </p>

        @if($errors->userDeletion->any())
            <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->userDeletion->first() }}</div>
        @endif

        <button type="button" id="delete-btn" class="btn btn-danger"
                onclick="document.getElementById('delete-section').style.display='block';this.style.display='none';">
            Delete My Account
        </button>

        <div id="delete-section"
             style="display:{{ $errors->userDeletion->any() ? 'block' : 'none' }};padding:1.25rem;border:1px solid #fca5a5;border-radius:6px;background:#fef2f2;">
            <p style="font-size:.88rem;color:#991b1b;font-weight:600;margin-bottom:.75rem;">Enter your password to confirm.</p>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf @method('DELETE')
                <div style="margin-bottom:1rem;">
                    <label>Password *</label>
                    <input type="password" name="password" required
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;">
                </div>
                <div style="display:flex;gap:.75rem;">
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you absolutely sure? This cannot be undone.')">
                        Permanently Delete Account
                    </button>
                    <button type="button" class="btn btn-secondary"
                            onclick="document.getElementById('delete-section').style.display='none';document.getElementById('delete-btn').style.display='';">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- ══ Change Password Modal ══ --}}
<div id="password-modal" onclick="if(event.target===this)closePasswordModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:420px;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1rem;color:var(--primary);">Change Password</h3>
            <button type="button" onclick="closePasswordModal()"
                    style="background:none;border:none;font-size:1.2rem;color:#888;cursor:pointer;line-height:1;">×</button>
        </div>
        @if($errors->updatePassword->any())
        <div style="padding:.75rem 1.5rem;background:#fee2e2;">
            <p style="font-size:.85rem;color:#991b1b;margin:0;">{{ $errors->updatePassword->first() }}</p>
        </div>
        @endif
        <form method="POST" action="{{ route('password.update') }}" style="padding:1.5rem;">
            @csrf @method('PUT')
            <div style="display:grid;gap:1rem;margin-bottom:1.25rem;">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Current Password *</label>
                    <input type="password" name="current_password" required autocomplete="current-password"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">New Password *</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button type="button" onclick="closePasswordModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()->role !== 'admin')
{{-- ══ Add Site Modal ══ --}}
<div id="add-site-modal" onclick="if(event.target===this)closeAddSiteModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:480px;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1rem;color:var(--primary);">Add Site</h3>
            <button type="button" onclick="closeAddSiteModal()"
                    style="background:none;border:none;font-size:1.2rem;color:#888;cursor:pointer;line-height:1;">×</button>
        </div>
        <form method="POST" action="{{ route('portal.sites.store') }}" style="padding:1.5rem;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">
                        Site Name * <span style="font-weight:400;color:#888;">(e.g. "Main Office")</span>
                    </label>
                    <input type="text" name="label" value="{{ old('label') }}" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Street Address *</label>
                    <input type="text" name="street" value="{{ old('street') }}" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">City *</label>
                    <input type="text" name="city" value="{{ old('city') }}" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:72px 1fr;gap:.5rem;">
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">State *</label>
                        <input type="text" name="state" maxlength="2" placeholder="TX"
                               value="{{ old('state') }}" required
                               style="width:100%;padding:.5rem .5rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;text-transform:uppercase;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">ZIP *</label>
                        <input type="text" name="zip" maxlength="10"
                               value="{{ old('zip') }}" required
                               style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button type="button" onclick="closeAddSiteModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Add Site</button>
            </div>
        </form>
    </div>
</div>

{{-- ══ Edit Site Modal ══ --}}
<div id="edit-site-modal" onclick="if(event.target===this)closeEditSiteModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9000;align-items:center;justify-content:center;padding:1rem;">
    <div style="background:#fff;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.18);width:100%;max-width:480px;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1rem;color:var(--primary);">Edit Site</h3>
            <button type="button" onclick="closeEditSiteModal()"
                    style="background:none;border:none;font-size:1.2rem;color:#888;cursor:pointer;line-height:1;">×</button>
        </div>
        <form id="edit-site-form" method="POST" action="" style="padding:1.5rem;">
            @csrf @method('PATCH')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Site Name *</label>
                    <input type="text" id="edit-label" name="label" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="grid-column:1/-1;">
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">Street Address *</label>
                    <input type="text" id="edit-street" name="street" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">City *</label>
                    <input type="text" id="edit-city" name="city" required
                           style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:72px 1fr;gap:.5rem;">
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">State *</label>
                        <input type="text" id="edit-state" name="state" maxlength="2"
                               style="width:100%;padding:.5rem .5rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;text-transform:uppercase;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#444;margin-bottom:.3rem;">ZIP *</label>
                        <input type="text" id="edit-zip" name="zip" maxlength="10"
                               style="width:100%;padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button type="button" onclick="closeEditSiteModal()"
                        style="padding:.45rem 1rem;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:.88rem;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endif {{-- end non-admin site modals --}}

<script>
// ── Password modal ────────────────────────────────────────────────────────────
function openPasswordModal()  { document.getElementById('password-modal').style.display = 'flex'; document.addEventListener('keydown', _pwKey); }
function closePasswordModal() { document.getElementById('password-modal').style.display = 'none'; document.removeEventListener('keydown', _pwKey); }
function _pwKey(e) { if (e.key === 'Escape') closePasswordModal(); }

@if(auth()->user()->role !== 'admin')
// ── Add site modal ────────────────────────────────────────────────────────────
function openAddSiteModal()  { document.getElementById('add-site-modal').style.display = 'flex'; document.addEventListener('keydown', _asKey); }
function closeAddSiteModal() { document.getElementById('add-site-modal').style.display = 'none'; document.removeEventListener('keydown', _asKey); }
function _asKey(e) { if (e.key === 'Escape') closeAddSiteModal(); }

// ── Edit site modal ───────────────────────────────────────────────────────────
function openEditSiteModal(id, label, street, city, state, zip) {
    document.getElementById('edit-site-form').action = '/portal/sites/' + id;
    document.getElementById('edit-label').value  = label;
    document.getElementById('edit-street').value = street;
    document.getElementById('edit-city').value   = city;
    document.getElementById('edit-state').value  = state;
    document.getElementById('edit-zip').value    = zip;
    document.getElementById('edit-site-modal').style.display = 'flex';
    document.addEventListener('keydown', _esKey);
}
function closeEditSiteModal() { document.getElementById('edit-site-modal').style.display = 'none'; document.removeEventListener('keydown', _esKey); }
function _esKey(e) { if (e.key === 'Escape') closeEditSiteModal(); }

// Auto-open add site modal if there were store errors
@if($errors->siteStore->any())
openAddSiteModal();
@endif
@endif

// Auto-open password modal if there were validation errors
@if($errors->updatePassword->any())
openPasswordModal();
@endif

// ── Profile Default Availability Picker ──────────────────────────────────────
(function () {
    const inp = document.getElementById('profile-avail-json');
    if (!inp) return;

    const state = {};
    try {
        const initial = JSON.parse(inp.value || '{}');
        Object.entries(initial).forEach(([day, slots]) => {
            if (Array.isArray(slots) && slots.length) state[day] = new Set(slots);
        });
    } catch(e) {}

    function syncJson() {
        const out = {};
        Object.entries(state).forEach(([day, slots]) => { if (slots.size) out[day] = [...slots]; });
        inp.value = JSON.stringify(out);
    }

    function renderDayBtn(btn) {
        const active = !!state[btn.dataset.day];
        btn.style.background  = active ? 'var(--primary)' : '#fff';
        btn.style.color       = active ? '#fff'           : '#6b7280';
        btn.style.borderColor = active ? 'var(--primary)' : '#d1d5db';
    }

    function renderSlotBtn(btn) {
        const active = state[btn.dataset.day]?.has(btn.dataset.slot);
        btn.style.background  = active ? '#3b82f6' : '#fff';
        btn.style.borderColor = active ? '#3b82f6' : '#93c5fd';
        const nm = btn.querySelector('.psb-name');
        const tm = btn.querySelector('.psb-time');
        if (nm) nm.style.color = active ? '#fff'                  : '#3b82f6';
        if (tm) tm.style.color = active ? 'rgba(255,255,255,.75)' : '#93c5fd';
    }

    function applyState() {
        const panels    = document.querySelectorAll('.profile-avail-day-panel');
        const container = document.getElementById('profile-avail-panels');
        let anyVisible = false;
        document.querySelectorAll('.profile-avail-day-btn').forEach(renderDayBtn);
        panels.forEach(p => {
            const show = !!state[p.dataset.day];
            p.style.display = show ? 'flex' : 'none';
            if (show) anyVisible = true;
        });
        let lastVisible = null;
        panels.forEach(p => { if (p.style.display !== 'none') lastVisible = p; });
        panels.forEach(p => { p.style.borderBottom = p === lastVisible ? 'none' : '1px solid #dbeafe'; });
        if (container) container.style.display = anyVisible ? '' : 'none';
        document.querySelectorAll('.profile-avail-slot-btn').forEach(renderSlotBtn);
        syncJson();
    }

    document.querySelectorAll('.profile-avail-day-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const day = btn.dataset.day;
            if (state[day]) delete state[day]; else state[day] = new Set();
            applyState();
        });
    });

    document.querySelectorAll('.profile-avail-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const { day, slot } = btn.dataset;
            if (!state[day]) state[day] = new Set();
            if (state[day].has(slot)) state[day].delete(slot); else state[day].add(slot);
            renderSlotBtn(btn);
            syncJson();
        });
    });

    applyState();
})();

function clearProfileAvail() {
    const inp  = document.getElementById('profile-avail-json');
    const form = inp ? inp.closest('form') : null;
    if (!inp || !form) return;
    inp.value = '{}';
    form.submit();
}
</script>
@endsection
