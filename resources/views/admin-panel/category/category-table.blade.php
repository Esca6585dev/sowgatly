@php
    $l = app()->getLocale();
    $nameKey = 'name_' . (in_array($l, ['tm', 'en', 'ru'], true) ? $l : 'tm');
@endphp
@if($categories->isEmpty())
    <x-admin.empty icon="grid" :text="__('No categories found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th></th><th>{{ __('Name') }}</th><th>{{ __('Parent category') }}</th><th class="right">{{ __('Subcategories') }}</th><th class="right">{{ __('Products') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($categories as $category)
            @php $others = collect(['tm', 'en', 'ru'])->map(fn ($c) => 'name_' . $c)->reject(fn ($k) => $k === $nameKey)->map(fn ($k) => $category->$k)->filter()->implode(' · '); @endphp
            <tr>
                <td style="width:64px">
                    @if($category->image)<img class="thumb" src="{{ asset($category->image) }}" alt="" loading="lazy" style="max-width:none">
                    @else<span class="thumb"><x-admin.icon name="image" class="i-sm" /></span>@endif
                </td>
                <td>
                    <a href="{{ route('category.show', [$l, $categoryType, $category->id]) }}" style="font-weight:600">{{ $category->$nameKey }}</a>
                    @if($others !== '')<div class="muted small">{{ $others }}</div>@endif
                </td>
                <td>
                    @if($category->parent)
                        <a class="text-brand" href="{{ route('category.show', [$l, 'parent', $category->parent->id]) }}">{{ $category->parent->$nameKey }}</a>
                    @else
                        <x-admin.pill tone="brand">{{ __('Parent category') }}</x-admin.pill>
                    @endif
                </td>
                <td class="right num">{{ $category->categories_count }}</td>
                <td class="right num">{{ $category->products_count }}</td>
                <td class="right"><x-admin.row-actions route="category" :model="$category->id" :params="[$categoryType]" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $categories->links('layouts.pagination') }}
@endif
