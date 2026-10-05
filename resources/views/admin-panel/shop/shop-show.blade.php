@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $img = fn ($p) => \App\Http\Controllers\AdminControllers\Shop\ShopController::imageUrl($p);
    $logo = $img($shop->image);
    $money = fn ($v) => number_format((float) $v, 2, '.', ' ') . ' TMT';
    $hm = fn ($v) => $v ? substr($v, 0, 5) : '—';
    $status = $shop->status ?? 'approved';
    $description = $shop->{'description_' . $l} ?: ($shop->description_tm ?: ($shop->description_en ?: $shop->description_ru));
@endphp
@section('page-title'){{ $shop->name }}@endsection
@section('breadcrumb')<a href="{{ route('shop.index', $l) }}">{{ __('Shops') }}</a><span class="sep">/</span><span>{{ $shop->name }}</span>@endsection

@section('content')
<div class="page-head">
    <div class="who" style="gap:14px">
        @if($logo)<img class="thumb lg" src="{{ $logo }}" alt="">@else<span class="thumb lg"><x-admin.icon name="shop" /></span>@endif
        <div style="min-width:0">
            <h1>{{ $shop->name }}</h1>
            <p style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <x-admin.status :value="$status" />
                <span>{{ collect([optional($shop->region)->name, $shop->created_at ? __('since :date', ['date' => $shop->created_at->locale($faker)->isoFormat('D MMM YYYY')]) : null])->filter()->implode(' · ') }}</span>
            </p>
        </div>
    </div>
    <div class="actions">
        @if($status !== 'approved')
        <form method="post" action="{{ route('shop.update', [$l, $shop->id]) }}">
            @csrf @method('put')
            <input type="hidden" name="status" value="approved">
            <button class="btn btn-soft" type="submit"><x-admin.icon name="check" class="i-sm" />{{ __('Approve') }}</button>
        </form>
        @endif
        @if($status !== 'rejected')
        <form method="post" action="{{ route('shop.update', [$l, $shop->id]) }}" data-confirm="{{ __('Reject this shop? It will be hidden in the app.') }}">
            @csrf @method('put')
            <input type="hidden" name="status" value="rejected">
            <button class="btn" type="submit"><x-admin.icon name="x" class="i-sm" />{{ __('Reject') }}</button>
        </form>
        @endif
        <form method="post" action="{{ route('shop.destroy', [$l, $shop->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('shop.edit', [$l, $shop->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </div>
</div>

<section class="grid grid-3">
    <x-admin.stat icon="box" :label="__('Products')" :value="$shop->products_count" :href="route('product.index', [$l, 'shop_id' => $shop->id])" />
    <x-admin.stat icon="orders" :label="__('Orders')" :value="$shop->orders_count" :href="route('order.index', [$l, 'shop_id' => $shop->id])" />
    <x-admin.stat icon="wallet" :label="__('Revenue')" :value="number_format((float) $revenue, 0, '.', ' ') . ' TMT'" :foot="__('Cancelled orders excluded')" />
</section>

<section class="split">
    <x-admin.card :title="__('Details')">
        <dl class="dl">
            <dt>{{ __('Owner') }}</dt>
            <dd>
                @if($shop->user)
                    <a class="text-brand" href="{{ route('user.show', [$l, $shop->user->id]) }}">{{ $shop->user->name }}</a>
                    <span class="muted nowrap"> · +993 {{ $shop->user->phone_number }}</span>
                @else<span class="muted">—</span>@endif
            </dd>
            <dt>{{ __('Phone number') }}</dt>
            <dd>@if($shop->phone)<a href="tel:+993{{ $shop->phone }}">+993 {{ $shop->phone }}</a>@else<span class="muted">—</span>@endif</dd>
            <dt>{{ __('Email') }}</dt>
            <dd>@if($shop->email)<a href="mailto:{{ $shop->email }}">{{ $shop->email }}</a>@else<span class="muted">—</span>@endif</dd>
            <dt>{{ __('Region') }}</dt>
            <dd>@if($shop->region)<a href="{{ route('region.show', [$l, $shop->region->id]) }}">{{ $shop->region->name }}</a>@if($shop->region->parent)<span class="muted"> · {{ $shop->region->parent->name }}</span>@endif @else<span class="muted">—</span>@endif</dd>
            <dt>{{ __('Address') }}</dt>
            <dd>
                @if($shop->address)
                    {{ $shop->address->address_name ?: '—' }}@if($shop->address->postal_code)<span class="muted"> · {{ $shop->address->postal_code }}</span>@endif
                    <a class="text-brand small nowrap" style="margin-left:8px" href="{{ route('address.edit', [$l, $shop->address->id]) }}">{{ __('Edit') }}</a>
                @else
                    <a class="btn btn-sm btn-soft" href="{{ route('address.create', [$l, 'shop_id' => $shop->id]) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('Add address') }}</a>
                @endif
            </dd>
            <dt>{{ __('Description') }}</dt>
            <dd>@if($description){!! nl2br(e($description)) !!}@else<span class="muted">—</span>@endif</dd>
        </dl>
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Opening hours')">
            <div class="kv"><span class="muted">{{ __('Monday – Friday') }}</span><b class="num">{{ $hm($shop->mon_fri_open) }} – {{ $hm($shop->mon_fri_close) }}</b></div>
            <div class="kv"><span class="muted">{{ __('Saturday – Sunday') }}</span><b class="num">{{ $hm($shop->sat_sun_open) }} – {{ $hm($shop->sat_sun_close) }}</b></div>
        </x-admin.card>
        <x-admin.card :title="__('Delivery')">
            <div class="kv"><span class="muted">{{ __('Delivery fee') }}</span><b class="num">{{ $money($shop->delivery_fee ?? 0) }}</b></div>
            <div class="kv"><span class="muted">{{ __('Minimum order') }}</span><b class="num">{{ $shop->min_order_amount !== null ? $money($shop->min_order_amount) : __('No minimum') }}</b></div>
            <div class="kv"><span class="muted">{{ __('Pickup') }}</span>@if($shop->pickup_available)<x-admin.pill tone="ok" dot>{{ __('Available') }}</x-admin.pill>@else<x-admin.pill dot>{{ __('Not available') }}</x-admin.pill>@endif</div>
        </x-admin.card>
    </div>
</section>

<section class="grid grid-2">
    <x-admin.card :title="__('Latest products')" :subtitle="trans_choice(':count product|:count products', $shop->products_count)" flush>
        <x-slot:right><a class="btn btn-sm btn-ghost" href="{{ route('product.index', [$l, 'shop_id' => $shop->id]) }}">{{ __('All') }}</a></x-slot:right>
        @if($products->isEmpty())
            <x-admin.empty icon="box" :text="__('No products yet')" />
        @else
        <div class="list">
            @foreach($products as $product)
            @php $thumb = $img(optional($product->images->first())->url); @endphp
            <a class="list-item" href="{{ route('product.show', [$l, $product->id]) }}">
                @if($thumb)<img class="thumb" src="{{ $thumb }}" alt="" loading="lazy">@else<span class="thumb"><x-admin.icon name="box" /></span>@endif
                <div class="body"><b>{{ $product->{'name_' . $l} ?: $product->name_tm }}</b><p class="num">{{ $money($product->price) }}@if($product->discount) · −{{ $product->discount }}%@endif</p></div>
                <x-admin.status :value="(bool) $product->status" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <x-admin.card :title="__('Latest orders')" :subtitle="trans_choice(':count order|:count orders', $shop->orders_count)" flush>
        <x-slot:right><a class="btn btn-sm btn-ghost" href="{{ route('order.index', [$l, 'shop_id' => $shop->id]) }}">{{ __('All') }}</a></x-slot:right>
        @if($orders->isEmpty())
            <x-admin.empty icon="orders" :text="__('No orders yet')" />
        @else
        <div class="list">
            @foreach($orders as $order)
            <a class="list-item" href="{{ route('order.show', [$l, $order->id]) }}">
                <span class="thumb"><x-admin.icon name="orders" /></span>
                <div class="body"><b>{{ $order->number }} · {{ optional($order->user)->name ?? __('Guest') }}</b><p>{{ optional($order->created_at)->locale($faker)->isoFormat('D MMM, HH:mm') }}</p></div>
                <div class="meta"><b class="num" style="color:var(--text)">{{ $money($order->total_amount) }}</b><x-admin.status :value="$order->status" /></div>
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</section>
@endsection
