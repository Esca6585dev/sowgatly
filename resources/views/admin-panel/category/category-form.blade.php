@extends('layouts.admin-page')

@php
    $editing = $category->exists;
    $l = app()->getLocale();
    $nameKey = 'name_' . (in_array($l, ['tm', 'en', 'ru'], true) ? $l : 'tm');
    $titles = ['all' => __('Categories'), 'parent' => __('Parent categories'), 'sub' => __('Subcategories')];
    // The parent select is shown for subcategories and in the "all" list; the "parent" list only makes parents.
    $withParent = $categoryType !== 'parent' || $category->category_id;
    $newTitle = $categoryType === 'sub' ? __('New subcategory') : __('New category');
@endphp
@section('page-title'){{ $editing ? $category->$nameKey : $newTitle }}@endsection
@section('breadcrumb')<a href="{{ route('category.index', [$l, $categoryType]) }}">{{ $titles[$categoryType] }}</a>@if($editing)<span class="sep">/</span><a href="{{ route('category.show', [$l, $categoryType, $category->id]) }}">{{ $category->$nameKey }}</a>@endif<span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit category') : $newTitle" :subtitle="$editing ? $category->$nameKey : null" />

<x-admin.form :action="$editing ? route('category.update', [$l, $categoryType, $category->id]) : route('category.store', [$l, $categoryType])" :method="$editing ? 'put' : 'post'" files>
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <div class="form-section">{{ __('Name') }}</div>
            <x-admin.field name="name_tm" :label="__('Name') . ' (Türkmençe)'" :value="$category->name_tm" required autofocus col="col-4" />
            <x-admin.field name="name_ru" :label="__('Name') . ' (Русский)'" :value="$category->name_ru" required col="col-4" />
            <x-admin.field name="name_en" :label="__('Name') . ' (English)'" :value="$category->name_en" required col="col-4" />

            @if($withParent)
            <div class="form-section">{{ __('Place in the catalogue') }}</div>
            <x-admin.select name="category_id" :label="__('Parent category')" :value="$category->category_id" col="col-6"
                :placeholder="$categoryType === 'sub' ? __('— Choose a parent category —') : __('— None (parent category) —')"
                :hint="$categoryType === 'sub' ? __('A subcategory belongs to one parent category.') : __('Leave empty to make it a parent category.')"
                :options="$parents->mapWithKeys(fn ($p) => [$p->id => $p->$nameKey])" />
            @endif

            <div class="form-section">{{ __('Image') }}</div>
            <x-admin.file name="image" col="col-12" :current="$category->image ? [asset($category->image)] : []" :hint="__('Square image, PNG or SVG with a transparent background works best')" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('category.show', [$l, $categoryType, $category->id]) : route('category.index', [$l, $categoryType]) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
