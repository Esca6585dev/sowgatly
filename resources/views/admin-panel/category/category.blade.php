@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $titles = ['all' => __('Categories'), 'parent' => __('Parent categories'), 'sub' => __('Subcategories')];
@endphp
@section('page-title'){{ $titles[$categoryType] }}@endsection
@section('breadcrumb')
@if($categoryType === 'all')<span>{{ __('Categories') }}</span>
@else<a href="{{ route('category.index', [$l, 'all']) }}">{{ __('Categories') }}</a><span class="sep">/</span><span>{{ $titles[$categoryType] }}</span>@endif
@endsection

@section('content')
<x-admin.page-header :title="$titles[$categoryType]" :subtitle="__('Catalogue sections shown in the app and on the website')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('category.create', [$l, $categoryType]) }}"><x-admin.icon name="plus" class="i-sm" />{{ $categoryType === 'sub' ? __('New subcategory') : __('New category') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div class="card-h">
        <div class="chips">
            @foreach($titles as $type => $title)
            <a class="chip {{ $type === $categoryType ? 'on' : '' }}" href="{{ route('category.index', [$l, $type]) }}">{{ $title }}</a>
            @endforeach
        </div>
    </div>
    <x-admin.toolbar :placeholder="__('Category name')" :per-page="$pagination" />
    <div id="datatable">@include('admin-panel.category.category-table')</div>
</x-admin.card>
@endsection
