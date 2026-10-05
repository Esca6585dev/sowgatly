@props(['icon', 'label', 'value', 'hero' => false, 'trend' => null, 'foot' => null, 'href' => null])
@php $tag = $href ? 'a' : 'div'; @endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif class="card stat {{ $hero ? 'hero' : '' }}">
    <span class="ico"><x-admin.icon :name="$icon" /></span>
    <span class="lbl">{{ $label }}</span>
    <span class="val">{{ $value }}</span>
    @if($trend !== null)
        <span class="trend {{ $trend >= 0 ? 'up' : 'down' }}"><x-admin.icon :name="$trend >= 0 ? 'up' : 'down'" class="i-xs" />{{ abs($trend) }}%@if($foot) <span style="font-weight:500">{{ $foot }}</span>@endif</span>
    @elseif($foot)
        <span class="foot">{{ $foot }}</span>
    @endif
</{{ $tag }}>
