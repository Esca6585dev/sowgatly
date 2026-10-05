{{-- tone: warn | violet | info | ok | bad | brand | (empty = neutral) --}}
@props(['tone' => null, 'dot' => false])
<span {{ $attributes->merge(['class' => 'pill' . ($tone ? ' p-' . $tone : '') . ($dot ? ' dot' : '')]) }}>{{ $slot }}</span>
