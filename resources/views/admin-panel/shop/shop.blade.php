@extends('layouts.admin-page')

@section('page-title'){{ __('Shops') }}@endsection
@section('breadcrumb')<span>{{ __('Shops') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Shops')" :subtitle="__('Sellers, their opening hours and delivery settings')">
    <x-slot:actions>
        <a class="btn" href="{{ route('address.index', app()->getLocale()) }}"><x-admin.icon name="pin" class="i-sm" />{{ __('Addresses') }}</a>
        <a class="btn btn-primary" href="{{ route('shop.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New shop') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Shop, owner or phone')" :per-page="$pagination">
        <select name="status" class="select" style="width:auto" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach(\App\Models\Shop::STATUSES as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>
            @endforeach
        </select>
        <select name="region_id" class="select" style="width:auto;max-width:220px" aria-label="{{ __('Region') }}">
            <option value="">{{ __('All regions') }}</option>
            @foreach($regions as $region)
            <option value="{{ $region->id }}" @selected((int) request('region_id') === $region->id)>{{ $region->name }}</option>
            @endforeach
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.shop.shop-table')</div>
</x-admin.card>
@endsection
