@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $money = fn ($v) => number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 2, '.', ' ') . ' TMT';
    $name = fn ($m) => $m ? ($m->{'name_' . $l} ?: $m->name_tm ?: $m->name_en ?: $m->name_ru) : null;
    $title = $name($product);
    $langs = ['tm' => __('Turkmen'), 'ru' => __('Russian'), 'en' => __('English')];
    $cover = $product->images->first();
    $avg = $product->reviews_avg !== null ? round((float) $product->reviews_avg, 1) : null;
@endphp
@section('page-title'){{ $title }}@endsection
@section('breadcrumb')<a href="{{ route('product.index', $l) }}">{{ __('Products') }}</a><span class="sep">/</span><span>{{ $title }}</span>@endsection

@section('content')
<x-admin.page-header :title="$title" :subtitle="'#' . $product->id . ($product->shop ? ' · ' . $product->shop->name : '') . ($product->category ? ' · ' . $name($product->category) : '')">
    <x-slot:actions>
        <form method="post" action="{{ route('product.destroy', [$l, $product->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('product.edit', [$l, $product->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<div class="split">
    <div class="stack">
        <x-admin.card :title="__('Images')" :subtitle="trans_choice(':count image|:count images', $product->images->count())">
            @if($cover)
                <a href="{{ asset($cover->url) }}" target="_blank" rel="noopener"><img src="{{ asset($cover->url) }}" alt="{{ $title }}" style="display:block;width:100%;max-height:420px;object-fit:cover;border-radius:14px;background:var(--surface-2)"></a>
                @if($product->images->count() > 1)
                <div class="previews" style="margin-top:12px">
                    @foreach($product->images->skip(1) as $image)
                    <a href="{{ asset($image->url) }}" target="_blank" rel="noopener"><img src="{{ asset($image->url) }}" alt="" loading="lazy"></a>
                    @endforeach
                </div>
                @endif
            @else
                <x-admin.empty icon="image" :text="__('No images yet')" />
            @endif
        </x-admin.card>

        <x-admin.card :title="__('Description')">
            <dl class="dl">
                @foreach($langs as $code => $language)
                <dt>{{ $language }}</dt>
                <dd><b>{{ $product->{'name_' . $code} }}</b><div class="muted" style="margin-top:4px;white-space:pre-line">{{ $product->{'description_' . $code} }}</div></dd>
                @endforeach
            </dl>
        </x-admin.card>

        @if($product->getRelation('attributes')->isNotEmpty() || $product->brands->isNotEmpty() || $product->compositions->isNotEmpty())
        <x-admin.card :title="__('Attributes')">
            <dl class="dl">
                @foreach($product->getRelation('attributes') as $attribute)
                @php $values = json_decode($attribute->attribute_value, true); @endphp
                <dt>{{ __(ucfirst(str_replace('-', ' ', $attribute->attribute_key))) }}</dt>
                <dd><div class="chips">@foreach((array) ($values ?? [$attribute->attribute_value]) as $v)<span class="chip">{{ is_scalar($v) ? $v : json_encode($v) }}</span>@endforeach</div></dd>
                @endforeach
                @if($product->brands->isNotEmpty())
                <dt>{{ __('Brands') }}</dt>
                <dd><div class="chips">@foreach($product->brands as $brand)<span class="chip">{{ $brand->name }}</span>@endforeach</div></dd>
                @endif
                @if($product->compositions->isNotEmpty())
                <dt>{{ __('Composition') }}</dt>
                <dd><div class="chips">@foreach($product->compositions as $c)<span class="chip">{{ $c->name }}@if($c->pivot->qty) · {{ $c->pivot->qty }} {{ $c->pivot->qty_type }}@endif</span>@endforeach</div></dd>
                @endif
            </dl>
        </x-admin.card>
        @endif
    </div>

    <div class="stack">
        <x-admin.card :title="__('Details')">
            <dl class="dl" style="grid-template-columns:130px minmax(0,1fr)">
                <dt>{{ __('Price') }}</dt>
                <dd class="num">
                    @if($product->discount > 0)
                        <b class="nowrap">{{ $money($product->getDiscountedPrice()) }}</b> <small class="muted nowrap"><s>{{ $money($product->price) }}</s></small>
                    @else
                        <b>{{ $money($product->price) }}</b>
                    @endif
                </dd>
                <dt>{{ __('Discount') }}</dt><dd>@if($product->discount > 0)<x-admin.pill tone="brand">−{{ $product->discount }}%</x-admin.pill>@else<span class="muted">—</span>@endif</dd>
                <dt>{{ __('Stock') }}</dt><dd class="num">{{ $product->stock ?? '—' }}</dd>
                <dt>{{ __('Production time') }}</dt><dd>{{ $product->production_time !== null ? __(':n min', ['n' => $product->production_time]) : '—' }}</dd>
                <dt>{{ __('Minimum order') }}</dt><dd>{{ $product->min_order ?? '—' }}</dd>
                <dt>{{ __('Status') }}</dt><dd><x-admin.status :value="$product->status" /></dd>
                <dt>{{ __('Shop status') }}</dt><dd>@if($product->seller_status)<x-admin.pill tone="ok" dot>{{ __('Visible') }}</x-admin.pill>@else<x-admin.pill tone="warn" dot>{{ __('Hidden by shop') }}</x-admin.pill>@endif</dd>
                <dt>{{ __('Created') }}</dt><dd class="small">{{ optional($product->created_at)->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</dd>
                <dt>{{ __('Updated') }}</dt><dd class="small">{{ optional($product->updated_at)->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</dd>
            </dl>
        </x-admin.card>

        <x-admin.card :title="__('Shop & category')" flush>
            <div class="list" style="padding-top:8px">
                @if($product->shop)
                <a class="list-item" href="{{ route('shop.show', [$l, $product->shop->id]) }}">
                    @if($product->shop->image)<img class="thumb" src="{{ asset($product->shop->image) }}" alt="">@else<span class="thumb"><x-admin.icon name="shop" /></span>@endif
                    <div class="body"><b>{{ $product->shop->name }}</b><p>{{ __('Shop') }}</p></div>
                    <x-admin.icon name="chevron" class="i-sm muted" />
                </a>
                @endif
                @if($product->category)
                <a class="list-item" href="{{ route('category.show', [$l, 'all', $product->category->id]) }}">
                    <span class="thumb"><x-admin.icon name="grid" /></span>
                    <div class="body"><b>{{ $name($product->category) }}</b><p>{{ $product->category->parent ? $name($product->category->parent) : __('Category') }}</p></div>
                    <x-admin.icon name="chevron" class="i-sm muted" />
                </a>
                @endif
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Activity')">
            <dl class="dl" style="grid-template-columns:130px minmax(0,1fr)">
                <dt>{{ __('Rating') }}</dt>
                <dd>@if($avg !== null)<x-admin.icon name="star" class="i-sm" style="color:var(--warn)" /> <b>{{ $avg }}</b> <span class="muted small">/ 5</span>@else<span class="muted">—</span>@endif</dd>
                <dt>{{ __('Reviews') }}</dt><dd class="num">{{ $product->reviews_count }}</dd>
                <dt>{{ __('Ordered') }}</dt><dd class="num">{{ trans_choice(':count time|:count times', $product->order_items_count) }}</dd>
            </dl>
        </x-admin.card>
    </div>
</div>
@endsection
