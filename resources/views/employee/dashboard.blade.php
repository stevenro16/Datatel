@extends('layouts.employee')

@section('title', 'My Dashboard')

@section('content')
<h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>

@if($openEntry)
<div class="alert alert-warn">
    You are currently clocked in since {{ $openEntry->clocked_in_at->format('g:i A') }}.
    <a href="{{ route('employee.dashboard') }}">Clock Out</a>
</div>
@endif

<h2 class="section-title">My Assigned Work Orders</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>#</th><th>Customer</th><th>Services</th><th>Status</th><th>Scheduled</th>
        </tr>
    </thead>
    <tbody>
        @forelse($assignedOrders as $order)
        <tr>
            <td>{{ $order->woLabel() }}</td>
            <td>{{ $order->customer->name }}</td>
            <td>{{ $order->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
            <td><span class="badge badge-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>
            <td>{{ $order->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center; color:#999;">No assigned work orders.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
