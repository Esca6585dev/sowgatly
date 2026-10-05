@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $shop = $address->shop;
    $logo = \App\Http\Controllers\AdminControllers\Shop\ShopController::imageUrl(optional($shop)->image);
    $title = $address->address_name ?: __('Address') . ' #' . $address->id;
@endphp
@section('page-title'){{ $title }}@endsection
@section('breadcrumb')<a href="{{ route('address.index', $l) }}">{{ __('Addresses') }}</a><span class="sep">/</span><span>#{{ $address->id }}</span>@endsection

@section('content')
<x-admin.page-header :title="$title" :subtitle="optional($shop)->name">
    <x-slot:actions>
        <form method="post" action="{{ route('address.destroy', [$l, $address->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('address.edit', [$l, $address->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card :title="__('Details')">
        <dl class="dl">
            <dt>{{ __('Address') }}</dt><dd>{{ $address->address_name ?: '—' }}</dd>
            <dt>{{ __('Postal code') }}</dt><dd class="num">{{ $address->postal_code ?: '—' }}</dd>
            <dt>{{ __('Created') }}</dt><dd>{{ optional($address->created_at)->format('d.m.Y H:i') }}</dd>
            <dt>{{ __('Updated') }}</dt><dd>{{ optional($address->updated_at)->format('d.m.Y H:i') }}</dd>
        </dl>
    </x-admin.card>

    <x-admin.card :title="__('Shop')" flush>
        @if($shop)
        <div class="list">
            <a class="list-item" href="{{ route('shop.show', [$l, $shop->id]) }}">
                @if($logo)<img class="thumb" src="{{ $logo }}" alt="">@else<span class="thumb"><x-admin.icon name="shop" /></span>@endif
                <div class="body"><b>{{ $shop->name }}</b><p>{{ collect([optional($shop->region)->name, optional($shop->user)->name])->filter()->implode(' · ') ?: '—' }}</p></div>
                <x-admin.status :value="$shop->status ?? 'approved'" />
            </a>
        </div>
        @else
            <x-admin.empty icon="shop" :text="__('No shop')" />
        @endif
    </x-admin.card>
</section>
@endsection
