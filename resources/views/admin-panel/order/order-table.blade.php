@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $banks = collect(config('payments.methods'))->keyBy('code');
    $money = fn ($v) => number_format((float) $v, 2, '.', ' ') . ' TMT';
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
@if($orders->isEmpty())
    <x-admin.empty icon="orders" :text="__('No orders found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr>
            <th>№</th><th>{{ __('Customer') }}</th><th>{{ __('Shop') }}</th>
            <th class="right">{{ __('Total') }}</th><th>{{ __('Delivery') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Status') }}</th><th></th>
        </tr></thead>
        <tbody>
        @foreach($orders as $i => $order)
            <tr>
                <td class="nowrap">
                    <a class="text-brand" style="font-weight:600" href="{{ route('order.show', [$l, $order->id]) }}">{{ $order->number }}</a>
                    <small class="muted" style="display:block;margin-top:2px">{{ $order->created_at->locale($faker)->isoFormat('D MMM, HH:mm') }}</small>
                </td>
                <td>
                    <div class="who">
                        <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials(optional($order->user)->name) }}</span>
                        <div>{{ optional($order->user)->name ?? '—' }}@if(optional($order->user)->phone_number)<small class="nowrap">+993 {{ $order->user->phone_number }}</small>@endif</div>
                    </div>
                </td>
                <td>{{ optional($order->shop)->name ?? '—' }}</td>
                <td class="right num nowrap">
                    <b>{{ $money($order->total_amount) }}</b>
                    <small class="muted" style="display:block;margin-top:2px">{{ __('Items') }}: {{ (int) $order->items_quantity }}</small>
                </td>
                <td class="nowrap">
                    <x-admin.pill :tone="$order->fulfillment === 'pickup' ? 'violet' : 'info'">{{ __(ucfirst($order->fulfillment ?? 'delivery')) }}</x-admin.pill>
                    @if($order->delivery_type === 'scheduled' && $order->scheduled_at)
                    <small class="muted" style="display:block;margin-top:4px">{{ $order->scheduled_at->locale($faker)->isoFormat('D MMM, HH:mm') }}</small>
                    @endif
                </td>
                <td class="nowrap">
                    <x-admin.status :value="$order->payment_status ?? 'unpaid'" />
                    <small class="muted" style="display:block;margin-top:4px">{{ __(ucfirst($order->payment_method ?? 'cash')) }}@if($order->payment_bank) · {{ $banks[$order->payment_bank]['name'][$l] ?? $order->payment_bank }}@endif</small>
                </td>
                <td><x-admin.status :value="$order->status" /></td>
                <td class="right"><x-admin.row-actions route="order" :model="$order->id" :edit="false" :delete="false" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $orders->links('layouts.pagination') }}
@endif
