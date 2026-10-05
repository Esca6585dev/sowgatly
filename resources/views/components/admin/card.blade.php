@props(['title' => null, 'subtitle' => null, 'flush' => false])
<section {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($right))
    <div class="card-h">
        <div>
            @if($title)<h3>{{ $title }}</h3>@endif
            @if($subtitle)<span class="sub">{{ $subtitle }}</span>@endif
        </div>
        @isset($right)<div class="right">{{ $right }}</div>@endisset
    </div>
    @endif
    @if($flush)
        {{ $slot }}
    @else
        <div class="card-b">{{ $slot }}</div>
    @endif
    @isset($footer)<div class="card-f">{{ $footer }}</div>@endisset
</section>
