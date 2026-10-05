@extends('layouts.admin-page')

@section('page-title'){{ __('Dashboard') }}@endsection

@section('content')
@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $admin = auth('admin')->user();
    $money = fn ($v) => number_format((float) $v, 0, '.', ' ') . ' TMT';
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp

<x-admin.page-header :title="__('Hello, :name', ['name' => $admin->first_name ?? 'Admin'])" :subtitle="now()->locale($faker)->isoFormat('dddd, D MMMM')">
    <x-slot:actions>
        <a class="btn" href="{{ route('order.index', $l) }}"><x-admin.icon name="orders" class="i-sm" />{{ __('All orders') }}</a>
        <a class="btn btn-primary" href="{{ route('product.create', $l) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New product') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="grid grid-4">
    <x-admin.stat hero icon="orders" :label="__('Orders today')" :value="$stats['ordersToday']" :trend="$stats['ordersTrend']" :foot="$stats['ordersTrend'] !== null ? __('vs yesterday') : __(':n waiting', ['n' => $stats['pending']])" :href="route('order.index', $l)" />
    <x-admin.stat icon="wallet" :label="__('Revenue (7 days)')" :value="$money($stats['revenueWeek'])" :trend="$stats['revenueTrend']" :foot="$stats['revenueTrend'] === null ? __('Cancelled orders excluded') : null" />
    <x-admin.stat icon="truck" :label="__('On the way')" :value="$stats['delivering']" :foot="__(':n waiting to be accepted', ['n' => $stats['pending']])" :href="route('order.index', [$l, 'status' => 'delivering'])" />
    <x-admin.stat icon="inbox" :label="__('New shop applications')" :value="$stats['applicationsNew']" :foot="__(':n in progress', ['n' => $stats['applicationsContacted']])" :href="route('shop-application.index', [$l, 'status' => 'new'])" />
</section>

<section class="split">
    <x-admin.card :title="__('Latest orders')" :subtitle="__('Across all shops')" flush>
        <x-slot:right>
            <div class="chips">
                <a class="chip on" href="{{ route('order.index', $l) }}">{{ __('All') }}</a>
                <a class="chip" href="{{ route('order.index', [$l, 'status' => 'pending']) }}">{{ __('Pending') }}</a>
                <a class="chip" href="{{ route('order.index', [$l, 'status' => 'delivering']) }}">{{ __('Delivering') }}</a>
                <a class="chip" href="{{ route('order.index', [$l, 'status' => 'completed']) }}">{{ __('Completed') }}</a>
            </div>
        </x-slot:right>
        @if($recentOrders->isEmpty())
            <x-admin.empty icon="orders" :text="__('No orders yet')" />
        @else
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>№</th><th>{{ __('Customer') }}</th><th>{{ __('Shop') }}</th><th>{{ __('When') }}</th><th class="right">{{ __('Total') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                @foreach($recentOrders as $i => $order)
                    <tr>
                        <td><a class="text-brand" style="font-weight:600" href="{{ route('order.show', [$l, $order->id]) }}">{{ $order->number }}</a></td>
                        <td><div class="who"><span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials(optional($order->user)->name) }}</span><div>{{ optional($order->user)->name }}<small class="nowrap">+993 {{ optional($order->user)->phone_number }}</small></div></div></td>
                        <td>{{ optional($order->shop)->name }}</td>
                        <td class="nowrap">
                            @if($order->fulfillment === 'pickup'){{ __('Pickup') }}
                            @elseif($order->delivery_type === 'scheduled' && $order->scheduled_at){{ $order->scheduled_at->locale($faker)->isoFormat('D MMM, HH:mm') }}
                            @else{{ __('ASAP') }}@endif
                        </td>
                        <td class="right num nowrap" style="font-weight:600">{{ $money($order->total_amount) }}</td>
                        <td><x-admin.status :value="$order->status" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Revenue by day')" :subtitle="__('Last 7 days')">
            <div class="bars">
                @foreach($days as $d)
                <div class="{{ $loop->last ? 'hi' : '' }}" style="height:{{ max(3, round($d['value'] / $max * 100)) }}%" title="{{ $d['date'] }}: {{ $money($d['value']) }}">@if($d['value'] > 0 && $d['value'] == $max)<span>{{ number_format($d['value'] / 1000, 1) }}k</span>@endif</div>
                @endforeach
            </div>
            <div class="days">@foreach($days as $d)<span>{{ $d['label'] }}</span>@endforeach</div>
        </x-admin.card>

        <x-admin.card :title="__('Chats')" :subtitle="__(':n waiting for a reply', ['n' => $stats['chatsWaiting']])" flush>
            <x-slot:right><a class="btn btn-sm btn-ghost" href="{{ route('chat.index', $l) }}">{{ __('All') }}</a></x-slot:right>
            @if($threads->isEmpty())
                <x-admin.empty icon="chat" :text="__('No chats yet')" />
            @else
            <div class="list">
                @foreach($threads as $i => $t)
                <a class="list-item" href="{{ route('chat.show', [$l, $t->id]) }}">
                    <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials(optional($t->user)->name) }}</span>
                    <div class="body"><b>{{ optional($t->user)->name }}</b><p>{{ optional($t->lastMessage)->body }}</p><p class="small">{{ optional($t->shop)->name }}</p></div>
                    <div class="meta">
                        {{ optional($t->last_message_at)->locale($faker)->isoFormat('HH:mm') }}
                        @if($t->shop_unread > 0)<i class="badge-n">{{ $t->shop_unread }}</i>@endif
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </x-admin.card>
    </div>
</section>

<section class="grid grid-3">
    <x-admin.stat icon="users" :label="__('Users')" :value="number_format($stats['users'], 0, '.', ' ')" :href="route('user.index', $l)" />
    <x-admin.stat icon="shop" :label="__('Shops')" :value="$stats['shops']" :href="route('shop.index', $l)" />
    <x-admin.stat icon="box" :label="__('Products')" :value="number_format($stats['products'], 0, '.', ' ')" :href="route('product.index', $l)" />
</section>
@endsection
