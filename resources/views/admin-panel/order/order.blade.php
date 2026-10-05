@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $current = request('status');
    $chipUrl = fn ($status) => route('order.index', array_merge([$l], request()->except(['status', 'page']), $status ? ['status' => $status] : []));
@endphp
@section('page-title'){{ __('Orders') }}@endsection
@section('breadcrumb')<span>{{ __('Orders') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Orders')" :subtitle="__('Orders placed by customers in every shop')" />

<x-admin.card flush>
    <div class="chips" style="padding:16px 20px 14px">
        <a class="chip {{ in_array($current, \App\Models\Order::STATUSES, true) ? '' : 'on' }}" href="{{ $chipUrl(null) }}">{{ __('All') }} <span class="num">{{ $statusCounts->sum() }}</span></a>
        @foreach(\App\Models\Order::STATUSES as $status)
        <a class="chip {{ $current === $status ? 'on' : '' }}" href="{{ $chipUrl($status) }}">{{ __(ucfirst($status)) }} <span class="num">{{ $statusCounts[$status] ?? 0 }}</span></a>
        @endforeach
        @if(request()->filled('shop_id'))
        <a class="chip on" href="{{ route('order.index', array_merge([$l], request()->except(['shop_id', 'page']))) }}" title="{{ __('Remove filter') }}" style="margin-left:auto;display:inline-flex;align-items:center;gap:6px">
            <x-admin.icon name="shop" class="i-sm" />{{ optional($shop)->name ?? '#' . (int) request('shop_id') }}<x-admin.icon name="x" class="i-sm" />
        </a>
        @endif
    </div>
    <x-admin.toolbar :placeholder="__('Order number or product')" :per-page="$pagination">
        @if(in_array($current, \App\Models\Order::STATUSES, true))<input type="hidden" name="status" value="{{ $current }}">@endif
        @if(request()->filled('shop_id'))<input type="hidden" name="shop_id" value="{{ (int) request('shop_id') }}">@endif
        <select name="payment_status" class="select" style="width:auto" aria-label="{{ __('Payment') }}">
            <option value="">{{ __('All payments') }}</option>
            @foreach(\App\Http\Controllers\AdminControllers\Order\OrderController::PAYMENT_STATUSES as $ps)
            <option value="{{ $ps }}" @selected(request('payment_status') === $ps)>{{ __(ucfirst($ps)) }}</option>
            @endforeach
        </select>
        <select name="fulfillment" class="select" style="width:auto" aria-label="{{ __('Delivery') }}">
            <option value="">{{ __('Delivery and pickup') }}</option>
            <option value="delivery" @selected(request('fulfillment') === 'delivery')>{{ __('Delivery') }}</option>
            <option value="pickup" @selected(request('fulfillment') === 'pickup')>{{ __('Pickup') }}</option>
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.order.order-table')</div>
</x-admin.card>
@endsection
