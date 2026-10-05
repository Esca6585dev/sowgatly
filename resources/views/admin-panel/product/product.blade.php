@extends('layouts.admin-page')

@php $l = app()->getLocale(); @endphp
@section('page-title'){{ __('Products') }}@endsection
@section('breadcrumb')<span>{{ __('Products') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Products')" :subtitle="__('Everything the shops sell, with prices, stock and visibility')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('product.create', $l) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New product') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Name, description or ID')" :per-page="$pagination">
        <select name="shop_id" class="select" style="width:auto;max-width:200px" aria-label="{{ __('Shop') }}">
            <option value="">{{ __('All shops') }}</option>
            @foreach($shops as $id => $name)
            <option value="{{ $id }}" @selected((string) request('shop_id') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="category_id" class="select" style="width:auto;max-width:220px" aria-label="{{ __('Category') }}">
            <option value="">{{ __('All categories') }}</option>
            @foreach($parentCategories as $parent)
            <option value="{{ $parent->id }}" @selected((string) request('category_id') === (string) $parent->id)>{{ $parent->{'name_' . $l} }}</option>
            @foreach($parent->categories as $child)
            <option value="{{ $child->id }}" @selected((string) request('category_id') === (string) $child->id)>&nbsp;&nbsp;– {{ $child->{'name_' . $l} }}</option>
            @endforeach
            @endforeach
        </select>
        <select name="status" class="select" style="width:auto" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
            <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
            <option value="hidden" @selected(request('status') === 'hidden')>{{ __('Hidden by shop') }}</option>
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.product.product-table')</div>
</x-admin.card>
@endsection
