@extends('layouts.admin-page')

@php $editing = $address->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? __('Edit address') : __('New address') }}@endsection
@section('breadcrumb')<a href="{{ route('address.index', $l) }}">{{ __('Addresses') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit address') : __('New address')" :subtitle="$editing ? optional($address->shop)->name : __('Each shop has one address')" />

<x-admin.form :action="$editing ? route('address.update', [$l, $address->id]) : route('address.store', $l)" :method="$editing ? 'put' : 'post'">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <x-admin.select name="shop_id" :label="__('Shop')" :value="$address->shop_id" :placeholder="__('— Choose a shop —')" col="col-12" required
                :hint="$shops->isEmpty() ? __('Every shop already has an address.') : null"
                :options="$shops->mapWithKeys(fn ($s) => [$s->id => $s->name])" />
            <x-admin.field name="address_name" :label="__('Address')" :value="$address->address_name" col="col-8" required :placeholder="__('Street, building, apartment')" />
            <x-admin.field name="postal_code" :label="__('Postal code')" :value="$address->postal_code" col="col-4" required placeholder="744000" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('address.show', [$l, $address->id]) : route('address.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
