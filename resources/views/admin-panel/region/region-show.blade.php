@extends('layouts.admin-page')

@php $l = app()->getLocale(); @endphp
@section('page-title'){{ $region->name }}@endsection
@section('breadcrumb')<a href="{{ route('region.index', $l) }}">{{ __('Regions') }}</a><span class="sep">/</span><span>{{ $region->name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$region->name" :subtitle="__(ucfirst($region->type)) . ($region->parent ? ' · ' . $region->parent->name : '')">
    <x-slot:actions>
        <form method="post" action="{{ route('region.destroy', [$l, $region->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('region.edit', [$l, $region->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<div class="grid grid-2">
    <x-admin.card :title="__('Subregions')" :subtitle="trans_choice(':count region|:count regions', $region->children->count())" flush>
        @if($region->children->isEmpty())
            <x-admin.empty icon="map" :text="__('No subregions')" />
        @else
        <div class="list">
            @foreach($region->children->sortBy('name') as $child)
            <a class="list-item" href="{{ route('region.show', [$l, $child->id]) }}">
                <span class="thumb"><x-admin.icon name="map" /></span>
                <div class="body"><b>{{ $child->name }}</b><p>{{ __(ucfirst($child->type)) }}</p></div>
                <x-admin.icon name="chevron" class="i-sm muted" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <x-admin.card :title="__('Shops')" :subtitle="trans_choice(':count shop|:count shops', $region->shops->count())" flush>
        @if($region->shops->isEmpty())
            <x-admin.empty icon="shop" :text="__('No shops in this region')" />
        @else
        <div class="list">
            @foreach($region->shops as $shop)
            <a class="list-item" href="{{ route('shop.show', [$l, $shop->id]) }}">
                <span class="thumb"><x-admin.icon name="shop" /></span>
                <div class="body"><b>{{ $shop->name }}</b></div>
                <x-admin.status :value="$shop->status ?? 'approved'" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>
@endsection
