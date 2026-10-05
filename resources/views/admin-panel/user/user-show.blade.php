@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $money = fn ($v) => number_format((float) $v, 0, '.', ' ') . ' TMT';
@endphp
@section('page-title'){{ $user->name }}@endsection
@section('breadcrumb')<a href="{{ route('user.index', $l) }}">{{ __('Users') }}</a><span class="sep">/</span><span>{{ $user->name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$user->name" :subtitle="'+993 ' . $user->phone_number">
    <x-slot:actions>
        <form method="post" action="{{ route('user.destroy', [$l, $user->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('user.edit', [$l, $user->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="grid grid-3">
    <x-admin.stat hero icon="orders" :label="__('Orders')" :value="$user->orders_count" />
    <x-admin.stat icon="wallet" :label="__('Total spent')" :value="$money($totalSpent)" :foot="__('Cancelled orders excluded')" />
    <x-admin.stat icon="star" :label="__('Favorites')" :value="$user->favorites_count" />
</section>

<section class="split">
    <div class="stack">
        <x-admin.card :title="__('Latest orders')" flush>
            @if($user->orders->isEmpty())
                <x-admin.empty icon="orders" :text="__('No orders yet')" />
            @else
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr><th>№</th><th>{{ __('Shop') }}</th><th>{{ __('Date') }}</th><th class="right">{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                    @foreach($user->orders as $order)
                        <tr>
                            <td><a class="text-brand" style="font-weight:600" href="{{ route('order.show', [$l, $order->id]) }}">{{ $order->number }}</a></td>
                            <td>{{ optional($order->shop)->name ?? '—' }}</td>
                            <td class="muted nowrap">{{ optional($order->created_at)->format('d.m.Y H:i') }}</td>
                            <td class="right num nowrap" style="font-weight:600">{{ $money($order->total_amount) }}</td>
                            <td><x-admin.status :value="$order->status" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </x-admin.card>

        <x-admin.card :title="__('Delivery addresses')" :subtitle="trans_choice(':count address|:count addresses', $user->deliveryAddresses->count())" flush>
            @if($user->deliveryAddresses->isEmpty())
                <x-admin.empty icon="map" :text="__('No saved addresses')" />
            @else
            <div class="list">
                @foreach($user->deliveryAddresses as $address)
                <div class="list-item">
                    <span class="thumb"><x-admin.icon name="pin" /></span>
                    <div class="body"><b>{{ $address->title ?: __('Address') }}</b><p>{{ $address->address }}</p></div>
                    @if($address->is_default)<x-admin.pill tone="ok">{{ __('Default') }}</x-admin.pill>@endif
                </div>
                @endforeach
            </div>
            @endif
        </x-admin.card>
    </div>

    <div class="stack">
        <x-admin.card :title="__('Profile')">
            <div class="who" style="margin-bottom:16px">
                @include('admin-panel.user.user-avatar', ['u' => $user, 'size' => 'lg'])
                <div><b style="font-size:16px">{{ $user->name }}</b><small>{{ __('Customer since :date', ['date' => optional($user->created_at)->format('d.m.Y')]) }}</small></div>
            </div>
            <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
                <dt>{{ __('Status') }}</dt><dd><x-admin.status :value="(bool) $user->status" /></dd>
                <dt>{{ __('Phone') }}</dt><dd class="num"><a href="tel:+993{{ $user->phone_number }}">+993 {{ $user->phone_number }}</a></dd>
                <dt>{{ __('Email') }}</dt><dd>@if($user->email)<a href="mailto:{{ $user->email }}">{{ $user->email }}</a>@else<span class="muted">—</span>@endif</dd>
                <dt>{{ __('Birth date') }}</dt><dd>{{ optional($user->birth_date)->format('d.m.Y') ?? '—' }}</dd>
            </dl>
        </x-admin.card>

        <x-admin.card :title="__('Shop')" flush>
            @if($user->shop)
            <div class="list" style="padding-top:4px">
                <a class="list-item" href="{{ route('shop.show', [$l, $user->shop->id]) }}">
                    <span class="thumb"><x-admin.icon name="shop" /></span>
                    <div class="body"><b>{{ $user->shop->name }}</b><p>{{ __('Owner') }}</p></div>
                    <x-admin.status :value="$user->shop->status ?? 'approved'" />
                </a>
            </div>
            @else
                <x-admin.empty icon="shop" :text="__('This user does not own a shop')" />
            @endif
        </x-admin.card>
    </div>
</section>
@endsection
