{{-- options: [value => label]; or pass <option>s in the slot. --}}
@props(['name', 'label' => null, 'options' => null, 'value' => null, 'placeholder' => null, 'hint' => null, 'col' => 'col-6'])
@php $key = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.'); $id = $attributes->get('id', 'f-' . $key); $err = $errors->first($key); $current = old($key, $value); @endphp
<div class="field {{ $col }}">
    @if($label)<label for="{{ $id }}">{{ $label }}</label>@endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('id')->merge(['class' => 'select' . ($err ? ' is-invalid' : '')]) }}>
        @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @if($options !== null)
            @foreach($options as $v => $text)
            <option value="{{ $v }}" @selected(is_array($current) ? in_array((string) $v, array_map('strval', $current), true) : (string) $current === (string) $v)>{{ $text }}</option>
            @endforeach
        @endif
        {{ $slot }}
    </select>
    @if($hint)<span class="hint">{{ $hint }}</span>@endif
    @if($err)<span class="err">{{ $err }}</span>@endif
</div>
