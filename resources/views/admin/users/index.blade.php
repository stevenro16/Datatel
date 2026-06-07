@extends('layouts.admin')
@section('title', 'Users')

@section('content')

@php
    $activeRole   = request('role', 'employee');
    $searchQuery  = request('search', '');
    $roleUrl = fn($role) => route('admin.users.index', array_filter(['role' => $role, 'search' => $searchQuery]));
    $pillBase = 'padding:.3rem .85rem;border-radius:999px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid;cursor:pointer;';
    $pillOn   = $pillBase . 'background:var(--primary);color:#fff;border-color:var(--primary);';
    $pillOff  = $pillBase . 'background:#fff;color:#555;border-color:#d1d5db;';
@endphp

<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;margin-top:.85rem;flex-wrap:wrap;">
    <a href="{{ $roleUrl('employee') }}" style="{{ $activeRole === 'employee' ? $pillOn : $pillOff }}">Employees</a>
    <a href="{{ $roleUrl('customer') }}" style="{{ $activeRole === 'customer' ? $pillOn : $pillOff }}">Customers</a>
    <a href="{{ $roleUrl('admin') }}"    style="{{ $activeRole === 'admin'    ? $pillOn : $pillOff }}">Admins</a>
    <a href="{{ $roleUrl('all') }}"      style="{{ $activeRole === 'all'      ? $pillOn : $pillOff }}">All</a>
</div>

<form method="GET" style="display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    @if($activeRole)<input type="hidden" name="role" value="{{ $activeRole }}">@endif
    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Search name or email"
           style="padding:.5rem .85rem;border:1px solid #ccc;border-radius:5px;font-size:.9rem;flex:1;min-width:180px;">
    <button type="submit" class="btn btn-secondary">Search</button>
    @if($searchQuery || $activeRole)
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
    @endif
    <a href="{{ route('admin.users.create') }}"
       style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:var(--accent);color:#fff;border-radius:6px;font-size:.875rem;font-weight:700;box-shadow:0 2px 6px rgba(46,134,193,.3);letter-spacing:.01em;text-decoration:none;white-space:nowrap;flex-shrink:0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New
    </a>
</form>

<table class="data-table">
    <thead>
        <tr><th style="width:48px;"></th><th>Name</th><th>Email</th><th>Role</th><th>Company</th><th>Status</th><th>Joined</th><th></th></tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr data-href="{{ route('admin.users.edit', $user) }}" style="{{ $user->trashed() ? 'opacity:.5;' : '' }}">
            <td style="padding:.4rem .6rem;">
                @if($user->profile_photo)
                <img src="{{ route('users.photo', $user) }}" alt="{{ $user->name }}"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb;display:block;">
                @endif
            </td>
            <td>{{ $user->name }} @if($user->is_super_admin)<span style="font-size:.75rem;background:#fef3c7;color:#92400e;padding:.1em .4em;border-radius:3px;margin-left:.3rem;">Super Admin</span>@endif</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge">{{ ucfirst($user->role) }}</span></td>
            <td style="font-size:.85rem;">
                @if($user->role === 'customer')
                    @php $co = $user->companyMemberships->first()?->company; @endphp
                    @if($co)
                        <span style="color:var(--primary);font-weight:600;">{{ $co->name }}</span>
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                @else
                    <span style="color:#bbb;">—</span>
                @endif
            </td>
            @php
                $sBg  = match($user->status) { 'active' => '#d1fae5', 'pending' => '#fef3c7', default => '#fee2e2' };
                $sClr = match($user->status) { 'active' => '#065f46', 'pending' => '#92400e', default => '#991b1b' };
                $sLbl = match($user->status) { 'active' => 'Active',  'pending' => 'Pending', default => 'Inactive' };
            @endphp
            <td><span class="badge" style="background:{{ $sBg }};color:{{ $sClr }};">{{ $sLbl }}</span></td>
            <td style="font-size:.82rem;color:#666;">{{ $user->created_at->format('M j, Y') }}</td>
            <td style="display:flex;gap:.4rem;">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">Edit</a>
                @if(!$user->is_super_admin && !$user->trashed())
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Deactivate this user?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#999;padding:2rem;">No users found.</td></tr>
        @endforelse
    </tbody>
</table>
<div style="margin-top:1rem;">{{ $users->links() }}</div>
@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">USER MANAGEMENT</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Users
    </h1>
</div>
@endpush
