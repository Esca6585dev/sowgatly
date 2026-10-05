@extends('layouts.admin-page')

@php
    $editing = $product->exists;
    $l = app()->getLocale();
    $title = $editing ? ($product->{'name_' . $l} ?: $product->name_tm) : __('New product');
    $langs = ['tm' => __('Turkmen'), 'ru' => __('Russian'), 'en' => __('English')];
    $categoryOptions = [];
    foreach ($parentCategories as $parent) {
        $categoryOptions[$parent->id] = $parent->{'name_' . $l};
        foreach ($parent->categories as $child) {
            $categoryOptions[$child->id] = '— ' . $child->{'name_' . $l};
        }
    }
@endphp
@section('page-title'){{ $title }}@endsection
@section('breadcrumb')<a href="{{ route('product.index', $l) }}">{{ __('Products') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit product') : __('New product')" :subtitle="$editing ? $title . ' · #' . $product->id : null">
    <x-slot:actions>
        <a class="btn btn-ghost" href="{{ $editing ? route('product.show', [$l, $product->id]) : route('product.index', $l) }}">{{ __('Cancel') }}</a>
        <button class="btn btn-primary" type="submit" form="product-form"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.form id="product-form" :action="$editing ? route('product.update', [$l, $product->id]) : route('product.store', $l)" :method="$editing ? 'put' : 'post'" files>
<div class="split">
    <div class="stack">
        <x-admin.card :title="__('Main')" :subtitle="__('Name and description in every language')">
            <div class="form-grid">
                @foreach($langs as $code => $language)
                <x-admin.field :name="'name_' . $code" :label="__('Name') . ' (' . $language . ')'" :value="$product->{'name_' . $code}" required maxlength="255" col="col-4" />
                @endforeach
                @foreach($langs as $code => $language)
                <x-admin.textarea :name="'description_' . $code" :label="__('Description') . ' (' . $language . ')'" :value="$product->{'description_' . $code}" rows="4" required />
                @endforeach
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Price & stock')">
            <div class="form-grid">
                <x-admin.field name="price" type="number" step="0.01" min="0" :label="__('Price')" :value="$product->price" addon="TMT" required col="col-4" />
                <x-admin.field name="discount" type="number" min="0" max="100" :label="__('Discount')" :value="$product->discount" addon="%" col="col-4" />
                <x-admin.field name="stock" type="number" min="0" :label="__('Stock')" :value="$product->stock" :hint="__('Leave empty if not tracked')" col="col-4" />
                <x-admin.field name="production_time" type="number" min="0" :label="__('Production time')" :value="$product->production_time" :addon="__('min')" :hint="__('Up to 180 minutes counts as same-day delivery')" col="col-6" />
                <x-admin.field name="min_order" type="number" min="1" :label="__('Minimum order')" :value="$product->min_order" :addon="__('pcs')" col="col-6" />
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Images')" :subtitle="$editing ? trans_choice(':count image|:count images', $product->images->count()) : null">
            <div class="form-grid">
                <x-admin.file name="images[]" multiple :current="$editing ? $product->images->map(fn ($i) => asset($i->url))->all() : []"
                    :hint="$editing && $product->images->isNotEmpty() ? __('JPG, PNG, WEBP up to 10 MB. New images replace the current ones.') : __('JPG, PNG, WEBP up to 10 MB. The first image is the cover.')" />
            </div>
        </x-admin.card>
    </div>

    <div class="stack">
        <x-admin.card :title="__('Shop & category')">
            <div class="form-grid">
                <x-admin.select name="shop_id" :label="__('Shop')" :options="$shops" :value="$product->shop_id" :placeholder="__('— Select —')" required col="col-12" />
                <x-admin.select name="category_id" :label="__('Category')" :options="$categoryOptions" :value="$product->category_id" :placeholder="__('— Select —')" required col="col-12" />
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Visibility')" :subtitle="__('Customers see a product only when both are on')">
            <div class="stack" style="gap:14px">
                <div class="field">
                    <x-admin.checkbox name="status" :label="__('Approved by admin')" :checked="(bool) $product->status" switch />
                    <span class="hint" style="padding-left:48px">{{ __('Moderation status set by Sowgatly') }}</span>
                    @error('status')<span class="err">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <x-admin.checkbox name="seller_status" :label="__('Switched on by the shop')" :checked="(bool) $product->seller_status" switch />
                    <span class="hint" style="padding-left:48px">{{ __('The seller can hide a product, e.g. when out of season') }}</span>
                    @error('seller_status')<span class="err">{{ $message }}</span>@enderror
                </div>
            </div>
            <x-slot:footer>
                <a class="btn btn-ghost" href="{{ $editing ? route('product.show', [$l, $product->id]) : route('product.index', $l) }}">{{ __('Cancel') }}</a>
                <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
            </x-slot:footer>
        </x-admin.card>
    </div>
</div>
</x-admin.form>
@endsection
