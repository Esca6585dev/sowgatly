@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $money = fn ($v) => number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 2, '.', ' ') . ' TMT';
    $name = fn ($m) => $m ? ($m->{'name_' . $l} ?: $m->name_tm ?: $m->name_en ?: $m->name_ru) : null;
    $initials = fn ($n) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $n)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
    $total = $cart->items->sum(fn ($i) => $i->price * $i->quantity);
    $title = __('Cart') . ' #' . $cart->id;
@endphp
@section('page-title'){{ $title }}@endsection
@section('breadcrumb')<a href="{{ route('cart.index', $l) }}">{{ __('Carts') }}</a><span class="sep">/</span><span>#{{ $cart->id }}</span>@endsection

@section('content')
<x-admin.page-header :title="$title" :subtitle="optional($cart->user)->name">
    <x-slot:actions>
        <form method="post" action="{{ route('cart.destroy', [$l, $cart->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
    </x-slot:actions>
</x-admin.page-header>

<div class="split">
    <x-admin.card :title="__('Items')" :subtitle="trans_choice(':count item|:count items', $cart->items->count())" flush>
        @if($cart->items->isEmpty())
            <x-admin.empty icon="cart" :text="__('The cart is empty')" />
        @else
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>{{ __('Product') }}</th><th class="right">{{ __('Quantity') }}</th><th class="right">{{ __('Price') }}</th><th class="right">{{ __('Sum') }}</th></tr></thead>
                <tbody>
                @foreach($cart->items as $item)
                    @php $product = $item->product; $img = optional($product)->images?->first(); @endphp
                    <tr>
                        <td>
                            @if($product)
                            <a class="who" href="{{ route('product.show', [$l, $product->id]) }}">
                                @if($img)<img class="thumb" src="{{ asset($img->url) }}" alt="" loading="lazy">@else<span class="thumb"><x-admin.icon name="image" /></span>@endif
                                <div style="min-width:0"><span style="font-weight:600">{{ $name($product) }}</span><small>{{ optional($product->shop)->name }}</small></div>
                            </a>
                            @else
                            <div class="who"><span class="thumb"><x-admin.icon name="box" /></span><span class="muted">{{ __('Deleted product') }} #{{ $item->product_id }}</span></div>
                            @endif
                        </td>
                        <td class="right num">× {{ $item->quantity }}</td>
                        <td class="right num nowrap">
                            {{ $money($item->price) }}
                            @if($product && (float) $product->getDiscountedPrice() != (float) $item->price)
                            <small class="muted" style="display:block" title="{{ __('Current price of the product') }}">{{ __('now') }} {{ $money($product->getDiscountedPrice()) }}</small>
                            @endif
                        </td>
                        <td class="right num nowrap" style="font-weight:600">{{ $money($item->price * $item->quantity) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="3" class="right" style="font-weight:600">{{ __('Total') }}</td><td class="right num nowrap" style="font-weight:700;font-size:16px">{{ $money($total) }}</td></tr>
                </tfoot>
            </table>
        </div>
        @endif
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Customer')">
            @if($cart->user)
            <div class="who" style="margin-bottom:14px">
                <span class="avatar">{{ $initials($cart->user->name) }}</span>
                <div><b>{{ $cart->user->name ?: __('No name') }}</b><small>#{{ $cart->user->id }}</small></div>
            </div>
            <dl class="dl" style="grid-template-columns:90px minmax(0,1fr)">
                <dt>{{ __('Phone') }}</dt><dd>{{ $cart->user->phone_number ? '+993 ' . $cart->user->phone_number : '—' }}</dd>
                <dt>{{ __('Email') }}</dt><dd>{{ $cart->user->email ?: '—' }}</dd>
            </dl>
            @else
            <p class="muted">{{ __('Deleted user') }}</p>
            @endif
            @if($cart->user)
            <x-slot:footer><a class="btn btn-soft btn-sm" href="{{ route('user.show', [$l, $cart->user->id]) }}"><x-admin.icon name="user" class="i-sm" />{{ __('Open profile') }}</a></x-slot:footer>
            @endif
        </x-admin.card>

        <x-admin.card :title="__('Summary')">
            <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
                <dt>{{ __('Products') }}</dt><dd class="num">{{ $cart->items->count() }}</dd>
                <dt>{{ __('Pieces') }}</dt><dd class="num">{{ $cart->items->sum('quantity') }}</dd>
                <dt>{{ __('Total') }}</dt><dd class="num"><b>{{ $money($total) }}</b></dd>
                <dt>{{ __('Created') }}</dt><dd class="small">{{ optional($cart->created_at)->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</dd>
                <dt>{{ __('Updated') }}</dt><dd class="small">{{ optional(collect([$cart->updated_at, $cart->items->max('updated_at')])->filter()->max())->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</dd>
            </dl>
        </x-admin.card>
    </div>
</div>
@endsection
