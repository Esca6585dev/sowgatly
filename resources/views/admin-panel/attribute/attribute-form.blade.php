@extends('layouts.admin-page')

@php $editing = $attribute->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $attribute->type . ': ' . $attribute->value : __('New attribute') }}@endsection
@section('breadcrumb')<a href="{{ route('attribute.index', $l) }}">{{ __('Attributes') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit attribute') : __('New attribute')" :subtitle="$editing ? $attribute->type . ': ' . $attribute->value : null" />

<x-admin.form :action="$editing ? route('attribute.update', [$l, $attribute->id]) : route('attribute.store', $l)" :method="$editing ? 'put' : 'post'">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <x-admin.field name="type" :label="__('Type')" :value="$attribute->type" required autofocus col="col-6" list="attribute-types"
                :hint="__('For example: Colour, Size, Material')" />
            <datalist id="attribute-types">@foreach($types as $type)<option value="{{ $type }}">@endforeach</datalist>
            <x-admin.field name="value" :label="__('Value')" :value="$attribute->value" required col="col-6" :hint="__('For example: Red, 40 cm, Silk')" />
            <x-admin.select name="category_id" :label="__('Category')" :value="$attribute->category_id" :placeholder="__('— All categories —')" col="col-12"
                :hint="__('Leave empty if the attribute applies to every category.')" :options="$categories" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('attribute.show', [$l, $attribute->id]) : route('attribute.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
