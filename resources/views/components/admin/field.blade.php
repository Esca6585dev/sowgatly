{{-- Text-like input with label and validation error. --}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'col' => 'col-6', 'addon' => null])
@php $id = $attributes->get('id', 'f-' . str_replace(['[', ']'], ['-', ''], $name)); $err = $errors->first(rtrim(str_replace(['[', ']'], ['.', ''], $name), '.')); @endphp
<div class="field {{ $col }}">
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    @if($addon)<div class="input-group">@endif
    <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $type === 'password' ? '' : old(rtrim(str_replace(['[', ']'], ['.', ''], $name), '.'), $value) }}"
        {{ $attributes->except('id')->merge(['class' => 'input' . ($err ? ' is-invalid' : '')]) }}>
    @if($addon)<span class="addon">{{ $addon }}</span></div>@endif
    @if($hint)<span class="hint">{{ $hint }}</span>@endif
    @if($err)<span class="err">{{ $err }}</span>@endif
</div>
