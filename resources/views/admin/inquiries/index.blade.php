@extends('layouts.admin')

@section('title', 'Inquiries')

@section('content')

    {{-- Status filter tabs --}}
    <div style="display:flex;gap:.5rem;margin-bottom:1.25rem;margin-top:.85rem;flex-wrap:wrap;">
        @foreach([
            ['all',         'All',         $counts['all']],
            ['new',         'New',         $counts['new']],
            ['in_progress', 'In Progress', $counts['in_progress']],
            ['closed',      'Closed',      $counts['closed']],
        ] as [$val, $label, $cnt])
        <a href="?status={{ $val }}"
           style="display:inline-flex;align-items:center;gap:.45rem;padding:.45rem 1rem;border-radius:6px;font-size:.83rem;font-weight:600;text-decoration:none;border:1.5px solid;transition:all .15s;
                  {{ $status === $val
                       ? 'background:#1A3C5E;color:#fff;border-color:#1A3C5E;'
                       : 'background:#fff;color:#374151;border-color:#d1d5db;' }}">
            {{ $label }}
            <span style="background:{{ $status === $val ? 'rgba(255,255,255,.25)' : '#e5e7eb' }};color:{{ $status === $val ? '#fff' : '#374151' }};padding:1px 7px;border-radius:999px;font-size:.75rem;">{{ $cnt }}</span>
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div style="background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.08);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1.5px solid #e5e7eb;">
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Date</th>
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Name</th>
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Email</th>
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Company</th>
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Services</th>
                    <th style="padding:.8rem 1rem;text-align:left;font-size:.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Status</th>
                    <th style="padding:.8rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($inquiries as $inq)
                <tr data-href="{{ route('admin.inquiries.show', $inq) }}"
                    style="border-bottom:1px solid #f3f4f6;cursor:pointer;transition:background .1s;"
                    onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="padding:.75rem 1rem;color:#6b7280;white-space:nowrap;">{{ $inq->created_at->format('M j, Y') }}<br><span style="font-size:.78rem;">{{ $inq->created_at->format('g:i A') }}</span></td>
                    <td style="padding:.75rem 1rem;font-weight:600;color:#111827;">{{ $inq->name }}</td>
                    <td style="padding:.75rem 1rem;color:#374151;">{{ $inq->email }}</td>
                    <td style="padding:.75rem 1rem;color:#6b7280;">{{ $inq->company ?: '—' }}</td>
                    <td style="padding:.75rem 1rem;">
                        @if(!empty($inq->services))
                            @php $svcNames = \App\Models\ServiceType::whereIn('id', $inq->services)->pluck('name') @endphp
                            <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
                                @foreach($svcNames as $sn)
                                <span style="background:#e0f2fe;color:#0369a1;font-size:.73rem;font-weight:600;padding:2px 8px;border-radius:999px;">{{ $sn }}</span>
                                @endforeach
                            </div>
                        @else
                            <span style="color:#9ca3af;font-size:.82rem;">None selected</span>
                        @endif
                    </td>
                    <td style="padding:.75rem 1rem;">
                        <span class="badge {{ $inq->statusClass() }}" style="font-size:.75rem;">{{ $inq->statusLabel() }}</span>
                    </td>
                    <td style="padding:.75rem 1rem;text-align:right;">
                        <a href="{{ route('admin.inquiries.show', $inq) }}"
                           onclick="event.stopPropagation()"
                           style="color:#2E86C1;font-size:.82rem;font-weight:600;text-decoration:none;white-space:nowrap;">View →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="padding:3rem;text-align:center;color:#9ca3af;">
                        No inquiries found{{ $status !== 'all' ? ' with status "'.ucfirst(str_replace('_',' ',$status)).'"' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($inquiries->hasPages())
        <div style="padding:1rem 1.25rem;border-top:1px solid #f3f4f6;">
            {{ $inquiries->links() }}
        </div>
        @endif
    </div>

@endsection

@push('topbar-title')
<div>
    <p style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--accent);margin:0 0 .1rem;">INQUIRIES</p>
    <h1 style="font-size:1.25rem;font-weight:800;color:var(--primary);margin:0;line-height:1.15;letter-spacing:-.2px;display:flex;align-items:center;gap:.4rem;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;opacity:.85;"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        Contact Inquiries
    </h1>
</div>
@endpush
