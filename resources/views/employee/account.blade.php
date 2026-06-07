@extends('layouts.employee')
@section('title', 'My Account')

@section('content')
<h1 class="page-title">My Account</h1>

<div style="max-width:560px;display:grid;gap:1.25rem;">

    {{-- Read-only profile summary --}}
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1.5rem;">
        <h2 style="font-size:.95rem;font-weight:700;color:var(--primary);margin:0 0 1rem;">Profile</h2>
        @php
            $photoPath = $user->profile_photo ? storage_path('app/profile-photos/'.$user->profile_photo) : null;
        @endphp
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
            @if($photoPath && file_exists($photoPath))
                <img src="{{ route('users.photo', $user) }}" alt="{{ $user->name }}"
                     style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;flex-shrink:0;">
            @else
                <div style="width:60px;height:60px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#fff;font-size:1.3rem;font-weight:700;">{{ strtoupper(substr($user->name,0,1)) }}</span>
                </div>
            @endif
            <div>
                <div style="font-size:1rem;font-weight:700;color:#1e293b;">{{ $user->name }}</div>
                @if($user->title)
                <div style="font-size:.85rem;color:#64748b;">{{ $user->title }}</div>
                @endif
                <div style="font-size:.83rem;color:#64748b;">{{ $user->email }}</div>
                @if($user->phone)
                <div style="font-size:.83rem;color:#64748b;">{{ $user->phone }}</div>
                @endif
            </div>
        </div>
        <p style="font-size:.8rem;color:#9ca3af;margin:0;">Contact your administrator to update your name, email, phone, or photo.</p>
    </div>

    {{-- Home Address form --}}
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.07);padding:1.5rem;">
        <h2 style="font-size:.95rem;font-weight:700;color:var(--primary);margin:0 0 1rem;">Home Address</h2>
        <p style="font-size:.83rem;color:#64748b;margin:0 0 1.1rem;">Used internally for scheduling and dispatch — not shared with customers.</p>

        <form method="POST" action="{{ route('employee.account.update') }}">
            @csrf @method('PATCH')

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
            @endif

            <div style="display:grid;gap:.85rem;">
                <div>
                    <label style="display:block;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:.3rem;">Street Address</label>
                    <input type="text" name="home_street" value="{{ old('home_street', $user->home_street) }}"
                           placeholder="123 Main St"
                           style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:.65rem;">
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:.3rem;">City</label>
                        <input type="text" name="home_city" value="{{ old('home_city', $user->home_city) }}"
                               style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:.3rem;">State</label>
                        <input type="text" name="home_state" value="{{ old('home_state', $user->home_state) }}"
                               maxlength="50"
                               style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.83rem;font-weight:600;color:#374151;margin-bottom:.3rem;">ZIP</label>
                        <input type="text" name="home_zip" value="{{ old('home_zip', $user->home_zip) }}"
                               maxlength="20"
                               style="width:100%;padding:.55rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <div style="margin-top:1.25rem;">
                <button type="submit" class="btn btn-primary">Save Address</button>
            </div>
        </form>
    </div>

</div>
@endsection
