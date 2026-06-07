@extends('layouts.portal')
@section('title', 'My Work Orders')

@section('content')
<div style="margin-bottom:1.5rem;">
    <h1 style="margin:0 0 .2rem;font-size:1.75rem;font-weight:700;color:var(--primary);">Welcome, {{ auth()->user()->name }}</h1>
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <p style="margin:0;font-size:.85rem;color:#6b7280;font-weight:500;">My Work Orders</p>
        <a href="{{ route('portal.work-orders.create') }}" class="btn btn-primary">+ Submit New Work Order</a>
    </div>
</div>

@php
    $cards = [
        ['key' => 'open',      'label' => 'Active Orders',      'value' => $stats['open'],      'color' => '#2E86C1'],
        ['key' => 'feedback',  'label' => 'Awaiting Feedback',  'value' => $stats['feedback'],  'color' => '#d97706'],
        ['key' => 'to_sign',   'label' => 'Invoice Ready',       'value' => $stats['to_sign'],   'color' => '#7c3aed'],
        ['key' => 'completed', 'label' => 'Completed This Month','value' => $stats['completed'],'color' => '#059669'],
    ];
@endphp

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach($cards as $card)
    <a href="{{ route('portal.work-orders.index', ['filter' => $card['key']]) }}"
       style="display:block;background:#fff;border-radius:8px;padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.07);text-decoration:none;border:2px solid {{ $filter === $card['key'] ? $card['color'] : 'transparent' }};transition:border-color .15s;">
        <div style="font-size:1.85rem;font-weight:700;color:{{ $card['color'] }};line-height:1;">{{ $card['value'] }}</div>
        <div style="font-size:.82rem;color:#555;margin-top:.3rem;">{{ $card['label'] }}</div>
    </a>
    @endforeach
</div>

<table class="data-table">
    <thead>
        <tr><th>#</th><th>Services</th><th>Site / Contact</th><th>Status</th><th>Urgency</th><th>Submitted</th><th>Updated</th></tr>
    </thead>
    <tbody>
        @forelse($orders as $order)
        <tr data-href="{{ route('portal.work-orders.show', $order) }}">
            <td style="white-space:nowrap;">
                {{ $order->woLabel() }}
                @if(array_key_exists($order->id, $unreadWoIds))
                <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#dc2626;vertical-align:middle;margin-left:.2rem;">💬 New</span>
                @endif
            </td>
            <td style="font-size:.85rem;color:#666;">{{ $order->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>
            <td style="font-size:.85rem;">
                @if($order->site_contact_name || $order->site_contact_phone || $order->site_street)
                    @if($order->site_contact_name)<div style="color:#333;font-weight:500;">{{ $order->site_contact_name }}</div>@endif
                    @if($order->site_contact_phone)<div style="color:#666;">{{ $order->site_contact_phone }}</div>@endif
                    @if($order->site_street)<div style="color:#666;">{{ $order->site_street }}</div>@endif
                @else
                    <span style="color:#bbb;">—</span>
                @endif
            </td>
            <td><span class="badge badge-{{ $order->status }}">{{ str_replace('_',' ',$order->status) }}</span></td>
            <td>{{ ucfirst($order->urgency) }}</td>
            <td style="font-size:.85rem;color:#666;">{{ $order->created_at->format('M j, Y') }}</td>
            <td style="font-size:.85rem;color:#666;">{{ $order->updated_at->format('M j, Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;color:#999;padding:2.5rem;">
                No active work orders.
                <a href="{{ route('portal.work-orders.create') }}" style="color:var(--accent);">Submit one now.</a>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<div style="margin-top:1rem;">{{ $orders->links() }}</div>
@endsection
