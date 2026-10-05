@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $money = fn ($v) => number_format((float) $v, 2, '.', ' ') . ' TMT';
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
    $itemsTotal = $order->items_total ?? $order->items->sum(fn ($i) => $i->price * $i->quantity);
    // Progress shown above the status form; pickup orders skip "delivering".
    $steps = $order->fulfillment === 'pickup' ? ['pending', 'processing', 'completed'] : ['pending', 'processing', 'delivering', 'completed'];
    $reached = array_search($order->status, $steps, true);
    $next = \App\Models\Order::TRANSITIONS[$order->status] ?? [];
    $productName = fn ($p) => $p ? ($p->{'name_' . $l} ?: $p->name_tm ?: $p->name_ru) : null;
@endphp
@section('page-title'){{ __('Order') }} № {{ $order->number }}@endsection
@section('breadcrumb')<a href="{{ route('order.index', $l) }}">{{ __('Orders') }}</a><span class="sep">/</span><span>№ {{ $order->number }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Order') . ' № ' . $order->number" :subtitle="$order->created_at->locale($faker)->isoFormat('D MMMM YYYY, HH:mm') . (optional($order->shop)->name ? ' · ' . $order->shop->name : '')">
    <x-slot:actions>
        @if($order->chatThread)
        <a class="btn" href="{{ route('chat.show', [$l, $order->chatThread->id]) }}"><x-admin.icon name="chat" class="i-sm" />{{ __('Open chat') }}</a>
        @endif
        <a class="btn btn-ghost" href="{{ route('order.index', $l) }}"><x-admin.icon name="left" class="i-sm" />{{ __('All orders') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    {{-- min-width:0 lets the items table scroll instead of widening the grid on small screens. --}}
    <div class="stack" style="min-width:0">
        <x-admin.card :title="__('Items')" :subtitle="__('Quantity') . ': ' . $order->items->sum('quantity')" flush>
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr><th>{{ __('Product') }}</th><th class="right">{{ __('Quantity') }}</th><th class="right">{{ __('Price') }}</th><th class="right">{{ __('Sum') }}</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                        @php $image = $item->product ? $item->product->images->first() : null; @endphp
                        <tr>
                            <td>
                                <div class="who">
                                    @if($image)
                                        <img class="thumb" src="{{ asset($image->url) }}" alt="">
                                    @else
                                        <span class="thumb"><x-admin.icon name="box" /></span>
                                    @endif
                                    <div>
                                        @if($item->product)
                                            <a href="{{ route('product.show', [$l, $item->product_id]) }}" style="font-weight:600">{{ $productName($item->product) }}</a>
                                        @else
                                            <span class="muted">{{ __('Deleted product') }} #{{ $item->product_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="right num">× {{ $item->quantity }}</td>
                            <td class="right num nowrap">{{ $money($item->price) }}</td>
                            <td class="right num nowrap" style="font-weight:600">{{ $money($item->price * $item->quantity) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:6px 20px 14px;border-top:1px solid var(--line)">
                <div class="kv"><span class="muted">{{ __('Items total') }}</span><span class="num">{{ $money($itemsTotal) }}</span></div>
                <div class="kv"><span class="muted">{{ __('Delivery fee') }}</span><span class="num">{{ $money($order->delivery_fee) }}</span></div>
                <div class="kv" style="font-weight:700;font-size:15px"><span>{{ __('Total') }}</span><span class="num">{{ $money($order->total_amount) }}</span></div>
            </div>
        </x-admin.card>

        <x-admin.card :title="$order->fulfillment === 'pickup' ? __('Pickup') : __('Delivery')">
            <dl class="dl">
                <dt>{{ __('Type') }}</dt>
                <dd>{{ __(ucfirst($order->fulfillment ?? 'delivery')) }} · {{ $order->delivery_type === 'scheduled' ? __('Scheduled') : __('As soon as possible') }}</dd>
                @if($order->scheduled_at)
                <dt>{{ __('Scheduled at') }}</dt><dd>{{ $order->scheduled_at->locale($faker)->isoFormat('D MMMM YYYY, HH:mm') }}</dd>
                @endif
                <dt>{{ __('Recipient') }}</dt>
                <dd>{{ $order->recipient_name ?: optional($order->user)->name }}@if($order->recipient_phone) · <a class="text-brand nowrap" href="tel:+993{{ $order->recipient_phone }}">+993 {{ $order->recipient_phone }}</a>@endif</dd>
                @if($order->delivery_address)
                <dt>{{ __('Address') }}</dt><dd>{{ $order->delivery_address }}</dd>
                @endif
                @if($order->note)
                <dt>{{ __('Comment') }}</dt><dd>{!! nl2br(e($order->note)) !!}</dd>
                @endif
            </dl>
        </x-admin.card>
    </div>

    <div class="stack">
        <x-admin.card :title="__('Status')">
            <x-slot:right><x-admin.status :value="$order->status" /></x-slot:right>
            <div class="steps" style="display:flex;flex-direction:column;gap:8px;margin-bottom:18px">
                @foreach($steps as $idx => $step)
                    @php $done = $order->status !== 'cancelled' && $reached !== false && $idx <= $reached; @endphp
                    <div style="display:flex;align-items:center;gap:10px;{{ $done ? '' : 'color:var(--muted)' }}">
                        <x-admin.icon :name="$done ? 'check-circle' : 'clock'" class="i-sm" :style="$done ? 'color:var(--ok)' : ''" />
                        <span style="{{ $order->status === $step ? 'font-weight:700' : '' }}">{{ __(ucfirst($step)) }}</span>
                    </div>
                @endforeach
                @if($order->status === 'cancelled')
                    <div style="display:flex;align-items:center;gap:10px;color:var(--bad);font-weight:700">
                        <x-admin.icon name="x" class="i-sm" /><span>{{ __('Cancelled') }}</span>
                    </div>
                @endif
            </div>

            <x-admin.form :action="route('order.update', [$l, $order->id])" method="put">
                <div class="form-grid">
                    <x-admin.select name="status" :label="__('Order status')" :value="$order->status" col="col-12"
                        :disabled="empty($next)"
                        :hint="empty($next) ? __('This order is closed; its status cannot change.') : null"
                        :options="collect([$order->status => __(ucfirst($order->status)) . ' (' . __('current') . ')'])->merge(collect($next)->mapWithKeys(fn ($s) => [$s => __(ucfirst($s))]))" />
                    <x-admin.select name="payment_status" :label="__('Payment status')" :value="$order->payment_status" col="col-12"
                        :options="collect(\App\Http\Controllers\AdminControllers\Order\OrderController::PAYMENT_STATUSES)->mapWithKeys(fn ($s) => [$s => __(ucfirst($s))])" />
                </div>
                <dl class="dl" style="grid-template-columns:110px minmax(0,1fr);margin:14px 0 16px">
                    <dt>{{ __('Payment') }}</dt>
                    <dd>{{ __(ucfirst($order->payment_method ?? 'cash')) }}@if($order->payment_bank) · {{ $banks[$order->payment_bank]['name'][$l] ?? $order->payment_bank }}@endif</dd>
                    @if($order->paid_at)
                    <dt>{{ __('Paid at') }}</dt><dd>{{ $order->paid_at->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</dd>
                    @endif
                </dl>
                <button class="btn btn-primary btn-block" type="submit"><x-admin.icon name="check" class="i-sm" />{{ __('Save') }}</button>
            </x-admin.form>
        </x-admin.card>

        <x-admin.card :title="__('Customer')">
            <div class="who" style="margin-bottom:14px">
                <span class="avatar lg">{{ $initials(optional($order->user)->name) }}</span>
                <div>
                    @if($order->user)
                        <a href="{{ route('user.show', [$l, $order->user_id]) }}" style="font-weight:600">{{ $order->user->name ?: __('No name') }}</a>
                        @if($order->user->phone_number)<small><a href="tel:+993{{ $order->user->phone_number }}">+993 {{ $order->user->phone_number }}</a></small>@endif
                    @else
                        <span class="muted">{{ __('Deleted user') }}</span>
                    @endif
                </div>
            </div>
            <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
                <dt>{{ __('Shop') }}</dt>
                <dd>@if($order->shop)<a class="text-brand" href="{{ route('shop.show', [$l, $order->shop_id]) }}">{{ $order->shop->name }}</a>@else — @endif</dd>
            </dl>
            @if($order->chatThread)
            <a class="btn btn-soft btn-block" style="margin-top:16px" href="{{ route('chat.show', [$l, $order->chatThread->id]) }}"><x-admin.icon name="chat" class="i-sm" />{{ __('Open chat') }}</a>
            @endif
        </x-admin.card>
    </div>
</section>
@endsection
