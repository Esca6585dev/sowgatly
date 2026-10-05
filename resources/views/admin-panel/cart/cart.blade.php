@extends('layouts.admin-page')

@section('page-title'){{ __('Carts') }}@endsection
@section('breadcrumb')<span>{{ __('Carts') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Carts')" :subtitle="__('What customers have put in their carts but not ordered yet')" />

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Customer name, phone or cart ID')" :per-page="$pagination">
        <select name="state" class="select" style="width:auto" aria-label="{{ __('Items') }}">
            <option value="">{{ __('All carts') }}</option>
            <option value="filled" @selected(request('state') === 'filled')>{{ __('With items') }}</option>
            <option value="empty" @selected(request('state') === 'empty')>{{ __('Empty') }}</option>
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.cart.cart-table')</div>
</x-admin.card>
@endsection
