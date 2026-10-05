{{-- Sends 0 when unchecked (hidden input) unless :plain="true". --}}
@props(['name', 'label', 'checked' => false, 'value' => 1, 'switch' => false, 'plain' => false])
@php $isOn = old($name, $checked) ? true : false; @endphp
<label class="check {{ $switch ? 'switch' : '' }}">
    @unless($plain)<input type="hidden" name="{{ $name }}" value="0">@endunless
    <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked($isOn) {{ $attributes }}>
    <span>{{ $label }}</span>
</label>
