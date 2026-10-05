@extends('layouts.admin-page')

@php
    $editing = $shop->exists;
    $l = app()->getLocale();
    $hm = fn ($v) => $v ? substr($v, 0, 5) : null;
    // Every 30 minutes; a stored value outside the list (e.g. 08:15) stays selectable.
    $times = fn ($v) => collect(config('times'))->push($hm($v))->filter()->unique()->sort()->values()->mapWithKeys(fn ($t) => [$t => $t]);
    $logo = \App\Http\Controllers\AdminControllers\Shop\ShopController::imageUrl($shop->image);
@endphp
@section('page-title'){{ $editing ? $shop->name : __('New shop') }}@endsection
@section('breadcrumb')<a href="{{ route('shop.index', $l) }}">{{ __('Shops') }}</a>@if($editing)<span class="sep">/</span><a href="{{ route('shop.show', [$l, $shop->id]) }}">{{ $shop->name }}</a>@endif<span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit shop') : __('New shop')" :subtitle="$editing ? $shop->name : __('A shop sells products and receives orders')" />

<x-admin.form :action="$editing ? route('shop.update', [$l, $shop->id]) : route('shop.store', $l)" :method="$editing ? 'put' : 'post'" files>
<div class="split">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <div class="form-section">{{ __('Profile') }}</div>
            <x-admin.field name="name" :label="__('Shop name')" :value="$shop->name" required autofocus col="col-6" />
            <x-admin.field name="email" type="email" :label="__('Email')" :value="$shop->email" col="col-6" placeholder="shop@example.com" />
            <x-admin.field name="phone" :label="__('Phone number')" :value="$shop->phone" col="col-6" placeholder="65656565" :hint="__('8 digits, without +993')" inputmode="tel" />
            <x-admin.select name="region_id" :label="__('Region')" :value="$shop->region_id" :placeholder="__('— None —')" col="col-6"
                :options="$regions->mapWithKeys(fn ($r) => [$r->id => $r->name])" />

            <div class="form-section">{{ __('Opening hours') }}</div>
            <x-admin.select name="mon_fri_open" :label="__('Mon–Fri opens')" :value="$hm($shop->mon_fri_open)" :options="$times($shop->mon_fri_open)" col="col-3" />
            <x-admin.select name="mon_fri_close" :label="__('Mon–Fri closes')" :value="$hm($shop->mon_fri_close)" :options="$times($shop->mon_fri_close)" col="col-3" />
            <x-admin.select name="sat_sun_open" :label="__('Sat–Sun opens')" :value="$hm($shop->sat_sun_open)" :options="$times($shop->sat_sun_open)" col="col-3" />
            <x-admin.select name="sat_sun_close" :label="__('Sat–Sun closes')" :value="$hm($shop->sat_sun_close)" :options="$times($shop->sat_sun_close)" col="col-3" />

            <div class="form-section">{{ __('Delivery') }}</div>
            <x-admin.field name="delivery_fee" type="number" step="0.01" min="0" :label="__('Delivery fee')" :value="$shop->delivery_fee ?? 20" addon="TMT" col="col-4" />
            <x-admin.field name="min_order_amount" type="number" step="0.01" min="0" :label="__('Minimum order')" :value="$shop->min_order_amount" addon="TMT" col="col-4" :placeholder="__('No minimum')" />
            <div class="field col-4">
                <span class="label">{{ __('Pickup') }}</span>
                <div style="min-height:44px;display:flex;align-items:center">
                    <x-admin.checkbox name="pickup_available" :label="__('Customers can pick up orders')" :checked="$shop->pickup_available" switch />
                </div>
            </div>

            <div class="form-section">{{ __('Description') }}</div>
            <x-admin.textarea name="description_tm" label="Türkmençe" :value="$shop->description_tm" col="col-4" rows="4" />
            <x-admin.textarea name="description_ru" label="Русский" :value="$shop->description_ru" col="col-4" rows="4" />
            <x-admin.textarea name="description_en" label="English" :value="$shop->description_en" col="col-4" rows="4" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('shop.show', [$l, $shop->id]) : route('shop.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Status and owner')">
            <div class="form-grid">
                <x-admin.select name="status" :label="__('Status')" :value="$shop->status ?? 'approved'" col="col-12"
                    :hint="__('Only approved shops are visible in the app.')"
                    :options="collect(\App\Models\Shop::STATUSES)->mapWithKeys(fn ($s) => [$s => __(ucfirst($s))])" />
                <x-admin.select name="user_id" :label="__('Owner')" :value="$shop->user_id" :placeholder="__('— None —')" col="col-12"
                    :hint="__('A user can own one shop.')"
                    :options="$sellers->mapWithKeys(fn ($u) => [$u->id => $u->name . ' · +993 ' . $u->phone_number])" />
            </div>
        </x-admin.card>
        <x-admin.card :title="__('Logo')">
            <div class="form-grid">
                <x-admin.file name="image" :current="$logo ? [$logo] : []" hint="JPG, PNG, WEBP · 2 MB" />
            </div>
        </x-admin.card>
    </div>
</div>
</x-admin.form>
@endsection
