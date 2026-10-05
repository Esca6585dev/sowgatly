@extends('layouts.admin-page')

@section('page-title'){{ __('Addresses') }}@endsection
@section('breadcrumb')<a href="{{ route('shop.index', app()->getLocale()) }}">{{ __('Shops') }}</a><span class="sep">/</span><span>{{ __('Addresses') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Addresses')" :subtitle="__('Where each shop is located (one address per shop)')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('address.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New address') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Shop, address or postal code')" :per-page="$pagination" />
    <div id="datatable">@include('admin-panel.address.address-table')</div>
</x-admin.card>
@endsection
