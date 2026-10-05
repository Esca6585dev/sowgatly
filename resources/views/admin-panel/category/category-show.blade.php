@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $nameKey = 'name_' . (in_array($l, ['tm', 'en', 'ru'], true) ? $l : 'tm');
    $titles = ['all' => __('Categories'), 'parent' => __('Parent categories'), 'sub' => __('Subcategories')];
    $name = $category->$nameKey;
@endphp
@section('page-title'){{ $name }}@endsection
@section('breadcrumb')<a href="{{ route('category.index', [$l, $categoryType]) }}">{{ $titles[$categoryType] }}</a>@if($category->parent)<span class="sep">/</span><a href="{{ route('category.show', [$l, 'parent', $category->parent->id]) }}">{{ $category->parent->$nameKey }}</a>@endif<span class="sep">/</span><span>{{ $name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$name" :subtitle="$category->parent ? __('Subcategory of :name', ['name' => $category->parent->$nameKey]) : __('Parent category')">
    <x-slot:actions>
        <form method="post" action="{{ route('category.destroy', [$l, $categoryType, $category->id]) }}" data-confirm="{{ $category->categories_count ? __('The category and its :n subcategories will be deleted. Continue?', ['n' => $category->categories_count]) : __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        @unless($category->parent)
        <a class="btn" href="{{ route('category.create', [$l, 'sub', 'parent' => $category->id]) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('Add subcategory') }}</a>
        @endunless
        <a class="btn btn-primary" href="{{ route('category.edit', [$l, $categoryType, $category->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <div class="stack">
        @unless($category->parent)
        <x-admin.card :title="__('Subcategories')" :subtitle="__('Total: :n', ['n' => $subcategories->count()])" flush>
            @if($subcategories->isEmpty())
                <x-admin.empty icon="grid" :text="__('No subcategories yet')" />
            @else
            <div class="list">
                @foreach($subcategories as $child)
                <a class="list-item" href="{{ route('category.show', [$l, 'sub', $child->id]) }}">
                    @if($child->image)<img class="thumb" src="{{ asset($child->image) }}" alt="" loading="lazy">
                    @else<span class="thumb"><x-admin.icon name="grid" class="i-sm" /></span>@endif
                    <div class="body"><b>{{ $child->$nameKey }}</b><p>{{ collect([$child->name_tm, $child->name_ru, $child->name_en])->filter()->reject(fn ($n) => $n === $child->$nameKey)->implode(' · ') }}</p></div>
                    <span class="pill" title="{{ __('Products') }}"><x-admin.icon name="box" class="i-xs" />{{ $child->products_count }}</span>
                    <x-admin.icon name="chevron" class="i-sm muted" />
                </a>
                @endforeach
            </div>
            @endif
        </x-admin.card>
        @endunless

        <x-admin.card :title="__('Products')" :subtitle="__('Total: :n', ['n' => $productsCount])" flush>
            @if($productsCount > $products->count())
            <x-slot:right><a class="btn btn-sm btn-ghost" href="{{ route('product.index', [$l, 'category_id' => $category->id]) }}">{{ __('All') }}</a></x-slot:right>
            @endif
            @if($products->isEmpty())
                <x-admin.empty icon="box" :text="__('No products in this category')" />
            @else
            <div class="list">
                @foreach($products as $product)
                @php $img = optional($product->images->first())->url; @endphp
                <a class="list-item" href="{{ route('product.show', [$l, $product->id]) }}">
                    @if($img)<img class="thumb" src="{{ asset($img) }}" alt="" loading="lazy">
                    @else<span class="thumb"><x-admin.icon name="box" class="i-sm" /></span>@endif
                    <div class="body"><b>{{ $product->$nameKey ?: $product->name_tm }}</b><p class="num">{{ number_format((float) $product->price, 2, '.', ' ') }} TMT</p></div>
                    <x-admin.status :value="$product->status ? '1' : '0'" />
                </a>
                @endforeach
            </div>
            @endif
        </x-admin.card>
    </div>

    <x-admin.card :title="__('Details')">
        <div class="stack">
            @if($category->image)
            <img src="{{ asset($category->image) }}" alt="{{ $name }}" style="width:100%;height:180px;object-fit:contain;border-radius:14px;background:var(--surface-2);padding:16px">
            @else
            <div class="empty" style="background:var(--surface-2);border-radius:14px"><x-admin.icon name="image" /><div>{{ __('No image') }}</div></div>
            @endif
            <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
                <dt>Türkmençe</dt><dd>{{ $category->name_tm }}</dd>
                <dt>Русский</dt><dd>{{ $category->name_ru }}</dd>
                <dt>English</dt><dd>{{ $category->name_en }}</dd>
                <dt>{{ __('Parent category') }}</dt>
                <dd>@if($category->parent)<a class="text-brand" href="{{ route('category.show', [$l, 'parent', $category->parent->id]) }}">{{ $category->parent->$nameKey }}</a>@else — @endif</dd>
                <dt>{{ __('Created') }}</dt><dd class="num">{{ optional($category->created_at)->format('d.m.Y H:i') ?? '—' }}</dd>
                <dt>{{ __('Updated') }}</dt><dd class="num">{{ optional($category->updated_at)->format('d.m.Y H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </x-admin.card>
</section>
@endsection
