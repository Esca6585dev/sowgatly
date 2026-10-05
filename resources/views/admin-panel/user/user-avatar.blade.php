{{-- Avatar of a customer: their photo when the file exists, otherwise initials. Pass $u, optional $size ('lg') and $i (colour index). --}}
@php
    $img = $u->image && file_exists(public_path($u->image)) ? asset($u->image) : null;
    $ini = mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $u->name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
<span class="avatar {{ $size ?? '' }} {{ ['', 'av-2', 'av-3', 'av-4'][($i ?? $u->id) % 4] }}">@if($img)<img src="{{ $img }}" alt="" loading="lazy">@else{{ $ini }}@endif</span>
