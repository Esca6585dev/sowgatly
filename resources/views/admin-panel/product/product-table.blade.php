@php
    $l = app()->getLocale();
    $money = fn ($v) => number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 2, '.', ' ') . ' TMT';
    $name = fn ($m) => $m ? ($m->{'name_' . $l} ?: $m->name_tm ?: $m->name_en ?: $m->name_ru) : null;
@endphp
@if($products->isEmpty())
    <x-admin.empty icon="box" :text="__('No products found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Shop') }}</th><th>{{ __('Category') }}</th><th class="right">{{ __('Price') }}</th><th class="right">{{ __('Stock') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($products as $product)
            @php $img = $product->images->first(); @endphp
            <tr>
                <td style="min-width:220px">
                    <a class="who" href="{{ route('product.show', [$l, $product->id]) }}">
                        @if($img)<img class="thumb" src="{{ asset($img->url) }}" alt="" loading="lazy">@else<span class="thumb"><x-admin.icon name="image" /></span>@endif
                        <div style="min-width:0"><span style="font-weight:600">{{ $name($product) }}</span><small class="muted">#{{ $product->id }}</small></div>
                    </a>
                </td>
                <td style="min-width:120px">@if($product->shop)<a href="{{ route('shop.show', [$l, $product->shop_id]) }}">{{ $product->shop->name }}</a>@else<span class="muted">—</span>@endif</td>
                <td class="muted">{{ $name($product->category) ?? '—' }}</td>
                <td class="right num nowrap">
                    @if($product->discount > 0)
                        <b>{{ $money($product->getDiscountedPrice()) }}</b>
                        <small class="muted" style="display:block"><s>{{ $money($product->price) }}</s> −{{ $product->discount }}%</small>
                    @else
                        <b>{{ $money($product->price) }}</b>
                    @endif
                </td>
                <td class="right num">
                    @if($product->stock === null)<span class="muted">—</span>
                    @elseif($product->stock === 0)<x-admin.pill tone="bad">0</x-admin.pill>
                    @else{{ $product->stock }}@endif
                </td>
                <td class="nowrap">
                    <x-admin.status :value="$product->status" />
                    @unless($product->seller_status)<x-admin.pill tone="warn" title="{{ __('Hidden by shop') }}">{{ __('Hidden') }}</x-admin.pill>@endunless
                </td>
                <td class="right"><x-admin.row-actions route="product" :model="$product->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $products->links('layouts.pagination') }}
@endif
