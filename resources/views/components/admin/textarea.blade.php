@props(['name', 'label' => null, 'value' => null, 'hint' => null, 'col' => 'col-12'])
@php $id = $attributes->get('id', 'f-' . $name); $err = $errors->first($name); @endphp
<div class="field {{ $col }}">
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->merge(['class' => 'textarea' . ($err ? ' is-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>
    @if($hint)<span class="hint">{{ $hint }}</span>@endif
    @if($err)<span class="err">{{ $err }}</span>@endif
</div>
