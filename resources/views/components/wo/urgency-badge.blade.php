@props(['workOrder'])
@php $c = $workOrder->urgencyColors(); @endphp
<span style="padding:.25rem .75rem;border-radius:999px;font-size:.78rem;font-weight:700;background:{{ $c['bg'] }};color:{{ $c['text'] }};">{{ $workOrder->urgencyLabel() }}</span>
