@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $nameKey = 'name_' . (in_array($l, ['tm', 'en', 'ru'], true) ? $l : 'tm');
    $category = $attribute->category;
@endphp
@section('page-title'){{ $attribute->type }}: {{ $attribute->value }}@endsection
@section('breadcrumb')<a href="{{ route('attribute.index', $l) }}">{{ __('Attributes') }}</a><span class="sep">/</span><span>{{ $attribute->type }}: {{ $attribute->value }}</span>@endsection

@section('content')
<x-admin.page-header :title="$attribute->type . ': ' . $attribute->value" :subtitle="$category ? $category->$nameKey : __('All categories')">
    <x-slot:actions>
        <form method="post" action="{{ route('attribute.destroy', [$l, $attribute->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('attribute.edit', [$l, $attribute->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<div class="grid grid-2">
    <x-admin.card :title="__('Details')">
        <dl class="dl">
            <dt>{{ __('Type') }}</dt><dd style="font-weight:600">{{ $attribute->type }}</dd>
            <dt>{{ __('Value') }}</dt><dd><x-admin.pill tone="info">{{ $attribute->value }}</x-admin.pill></dd>
            <dt>{{ __('Category') }}</dt>
            <dd>
                @if($category)
                    @if($category->parent)<span class="muted">{{ $category->parent->$nameKey }} ›</span>@endif
                    <a class="text-brand" href="{{ route('category.show', [$l, $category->category_id ? 'sub' : 'parent', $category->id]) }}">{{ $category->$nameKey }}</a>
                @else
                    <span class="muted">{{ __('All categories') }}</span>
                @endif
            </dd>
            <dt>{{ __('Created') }}</dt><dd class="num">{{ optional($attribute->created_at)->format('d.m.Y H:i') ?? '—' }}</dd>
            <dt>{{ __('Updated') }}</dt><dd class="num">{{ optional($attribute->updated_at)->format('d.m.Y H:i') ?? '—' }}</dd>
        </dl>
    </x-admin.card>

    <x-admin.card :title="__('Other values of this type')" :subtitle="$attribute->type" flush>
        @if($siblings->isEmpty())
            <x-admin.empty icon="tag" :text="__('No other values yet')" />
        @else
        <div class="list">
            @foreach($siblings as $sibling)
            <a class="list-item" href="{{ route('attribute.show', [$l, $sibling->id]) }}">
                <span class="thumb"><x-admin.icon name="tag" class="i-sm" /></span>
                <div class="body"><b>{{ $sibling->value }}</b><p>{{ $sibling->category ? $sibling->category->$nameKey : __('All categories') }}</p></div>
                <x-admin.icon name="chevron" class="i-sm muted" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>
@endsection
