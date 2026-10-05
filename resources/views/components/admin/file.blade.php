{{-- Image upload with instant previews. Pass current image URL(s) via :current. --}}
@props(['name', 'label' => null, 'multiple' => false, 'current' => [], 'hint' => null, 'col' => 'col-12', 'accept' => 'image/*'])
@php $id = 'f-' . str_replace(['[', ']'], '', $name); $key = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.'); $err = $errors->first($key) ?: $errors->first($key . '.*'); $current = array_filter((array) $current); @endphp
<div class="field {{ $col }}">
    @if($label)<span class="label">{{ $label }}</span>@endif
    <label class="dropzone {{ $err ? 'is-invalid' : '' }}" for="{{ $id }}">
        <span class="thumb"><x-admin.icon name="upload" /></span>
        <span><b data-file-label>{{ $multiple ? __('Choose images') : __('Choose an image') }}</b><br><span class="muted small">{{ $hint ?? 'JPG, PNG, WEBP' }}</span></span>
        <input id="{{ $id }}" type="file" name="{{ $name }}" accept="{{ $accept }}" @if($multiple) multiple @endif data-preview="#{{ $id }}-preview" {{ $attributes }}>
    </label>
    <div class="previews" id="{{ $id }}-preview">
        @foreach($current as $src)<img src="{{ $src }}" alt="">@endforeach
    </div>
    @if($err)<span class="err">{{ $err }}</span>@endif
</div>
