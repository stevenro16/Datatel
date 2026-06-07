{{-- Shared dashboard table row — included by admin/dashboard.blade.php --}}

{{-- # --}}
<td style="white-space:nowrap;">
    {{ $order->woLabel() }}
    @if(isset($unreadWoIds) && array_key_exists($order->id, $unreadWoIds))
    <span style="display:inline-flex;align-items:center;gap:.2rem;background:#fef3c7;border:1px solid #fcd34d;border-radius:999px;padding:.05rem .4rem;font-size:.68rem;font-weight:700;color:#92400e;vertical-align:middle;margin-left:.2rem;">💬 Note</span>
    @endif
</td>

{{-- Customer --}}
<td>{{ $order->customer->name }}</td>

{{-- Services --}}
<td style="font-size:.82rem;color:#666;">{{ $order->serviceTypes->pluck('name')->join(', ') ?: '—' }}</td>

{{-- Techs --}}
<td>
    <div style="display:flex;">
        @forelse($order->assignments as $assignment)
        @php $emp = $assignment->employee; @endphp
        <div title="{{ $emp->name }}"
             style="width:30px;height:30px;border-radius:50%;border:2px solid #fff;overflow:hidden;margin-right:-6px;flex-shrink:0;background:var(--primary);display:flex;align-items:center;justify-content:center;">
            @if($emp->profile_photo)
                <img src="{{ route('users.photo', $emp) }}" alt="{{ $emp->name }}" style="width:100%;height:100%;object-fit:cover;">
            @else
                <span style="color:#fff;font-size:.68rem;font-weight:600;line-height:1;">{{ strtoupper(substr($emp->name,0,1)) }}</span>
            @endif
        </div>
        @empty
        <span style="color:#bbb;font-size:.82rem;">—</span>
        @endforelse
    </div>
</td>

{{-- Site / Contact --}}
<td style="font-size:.82rem;">
    @if($order->site_contact_name || $order->site_contact_phone || $order->site_street)
        @if($order->site_contact_name)<div style="color:#333;font-weight:500;">{{ $order->site_contact_name }}</div>@endif
        @if($order->site_contact_phone)<div style="color:#666;">{{ $order->site_contact_phone }}</div>@endif
        @if($order->site_street)<div style="color:#666;">{{ $order->site_street }}</div>@endif
    @else
        <span style="color:#bbb;">—</span>
    @endif
</td>

{{-- Urgency --}}
<td>
    <span class="badge" style="background:{{ $order->urgency === 'emergency' ? '#fee2e2' : ($order->urgency === 'urgent' ? '#fef3c7' : '#f3f4f6') }};color:{{ $order->urgency === 'emergency' ? '#991b1b' : ($order->urgency === 'urgent' ? '#92400e' : '#374151') }};">
        {{ ucfirst($order->urgency) }}
    </span>
</td>

{{-- Status --}}
<td><span class="badge badge-{{ $order->status }}">{{ str_replace('_', ' ', $order->status) }}</span></td>

{{-- Created --}}
<td style="font-size:.82rem;color:#666;white-space:nowrap;">
    <div>{{ $order->created_at->format('M j, Y') }}</div>
    <div style="color:#9ca3af;font-size:.75rem;">{{ $order->created_at->format('g:i A') }}</div>
</td>

{{-- Updated --}}
<td style="font-size:.82rem;color:#666;white-space:nowrap;">
    <div>{{ $order->updated_at->format('M j, Y') }}</div>
    <div style="color:#9ca3af;font-size:.75rem;">{{ $order->updated_at->format('g:i A') }}</div>
</td>
