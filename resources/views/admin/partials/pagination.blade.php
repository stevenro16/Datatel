@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;padding-top:.85rem;border-top:1px solid #e5e7eb;margin-top:.5rem;">
    <p style="font-size:.8rem;color:#6b7280;margin:0;">
        Showing
        <span style="font-weight:600;color:#374151;">{{ $paginator->firstItem() }}</span>
        to
        <span style="font-weight:600;color:#374151;">{{ $paginator->lastItem() }}</span>
        of
        <span style="font-weight:600;color:#374151;">{{ $paginator->total() }}</span>
        results
    </p>
    <div style="display:flex;align-items:center;gap:.25rem;flex-wrap:wrap;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="padding:.3rem .75rem;border:1px solid #e5e7eb;border-radius:5px;font-size:.8rem;color:#d1d5db;cursor:default;user-select:none;">&#8592; Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               style="padding:.3rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;color:#374151;text-decoration:none;transition:background .12s,border-color .12s;"
               onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''">&#8592; Prev</a>
        @endif

        {{-- Page number windows --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:.3rem .4rem;font-size:.8rem;color:#9ca3af;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="padding:.3rem .65rem;border:1px solid var(--accent, #2E86C1);border-radius:5px;font-size:.8rem;font-weight:700;background:var(--accent, #2E86C1);color:#fff;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           style="padding:.3rem .65rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;color:#374151;text-decoration:none;transition:background .12s,border-color .12s;"
                           onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               style="padding:.3rem .75rem;border:1px solid #d1d5db;border-radius:5px;font-size:.8rem;color:#374151;text-decoration:none;transition:background .12s,border-color .12s;"
               onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''">Next &#8594;</a>
        @else
            <span style="padding:.3rem .75rem;border:1px solid #e5e7eb;border-radius:5px;font-size:.8rem;color:#d1d5db;cursor:default;user-select:none;">Next &#8594;</span>
        @endif

    </div>
</nav>
@endif
