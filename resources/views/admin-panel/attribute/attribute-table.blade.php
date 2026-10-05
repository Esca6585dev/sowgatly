@php
    $l = app()->getLocale();
    $nameKey = 'name_' . (in_array($l, ['tm', 'en', 'ru'], true) ? $l : 'tm');
@endphp
@if($attrs->isEmpty())
    <x-admin.empty icon="tag" :text="__('No attributes found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Value') }}</th><th>{{ __('Category') }}</th><th>{{ __('Updated') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($attrs as $attr)
            <tr>
                <td><a href="{{ route('attribute.show', [$l, $attr->id]) }}" style="font-weight:600">{{ $attr->type }}</a></td>
                <td><x-admin.pill tone="info">{{ $attr->value }}</x-admin.pill></td>
                <td>
                    @if($attr->category)
                        <a class="text-brand" href="{{ route('category.show', [$l, $attr->category->category_id ? 'sub' : 'parent', $attr->category->id]) }}">{{ $attr->category->$nameKey }}</a>
                        @if($attr->category->parent)<div class="muted small">{{ $attr->category->parent->$nameKey }}</div>@endif
                    @else
                        <span class="muted">{{ __('All categories') }}</span>
                    @endif
                </td>
                <td class="muted nowrap num">{{ optional($attr->updated_at)->format('d.m.Y') }}</td>
                <td class="right"><x-admin.row-actions route="attribute" :model="$attr->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $attrs->links('layouts.pagination') }}
@endif
