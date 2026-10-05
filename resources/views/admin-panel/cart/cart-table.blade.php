@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $money = fn ($v) => number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 2, '.', ' ') . ' TMT';
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
@if($carts->isEmpty())
    <x-admin.empty icon="cart" :text="__('No carts found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>№</th><th>{{ __('Customer') }}</th><th class="right">{{ __('Items') }}</th><th class="right">{{ __('Total') }}</th><th>{{ __('Updated') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($carts as $i => $cart)
            @php $touched = collect([$cart->updated_at, $cart->items_updated_at ? \Carbon\Carbon::parse($cart->items_updated_at) : null])->filter()->max(); @endphp
            <tr>
                <td><a class="text-brand" style="font-weight:600" href="{{ route('cart.show', [$l, $cart->id]) }}">#{{ $cart->id }}</a></td>
                <td>
                    @if($cart->user)
                    <a class="who" href="{{ route('user.show', [$l, $cart->user->id]) }}"><span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials($cart->user->name) }}</span><div>{{ $cart->user->name ?: __('No name') }}<small class="nowrap">@if($cart->user->phone_number)+993 {{ $cart->user->phone_number }}@else{{ $cart->user->email }}@endif</small></div></a>
                    @else
                    <span class="muted">{{ __('Deleted user') }}</span>
                    @endif
                </td>
                <td class="right num">
                    @if($cart->items_count)
                        {{ $cart->items_count }}@if($cart->items_quantity > $cart->items_count) <small class="muted">· {{ trans_choice(':count pc|:count pcs', $cart->items_quantity) }}</small>@endif
                    @else
                        <x-admin.pill>{{ __('Empty') }}</x-admin.pill>
                    @endif
                </td>
                <td class="right num nowrap" style="font-weight:600">{{ $cart->items_count ? $money($cart->items_total) : '—' }}</td>
                <td class="nowrap muted" title="{{ optional($touched)->format('d.m.Y H:i') }}">{{ optional($touched)->locale($faker)->diffForHumans() }}</td>
                <td class="right"><x-admin.row-actions route="cart" :model="$cart->id" :edit="false" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $carts->links('layouts.pagination') }}
@endif
