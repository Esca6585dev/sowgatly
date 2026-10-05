@if($regions->isEmpty())
    <x-admin.empty icon="map" :text="__('No regions found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('Parent region') }}</th><th class="right">{{ __('Subregions') }}</th><th class="right">{{ __('Shops') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($regions as $region)
            <tr>
                <td><a href="{{ route('region.show', [app()->getLocale(), $region->id]) }}" style="font-weight:600">{{ $region->name }}</a></td>
                <td><x-admin.pill :tone="['country' => 'brand', 'province' => 'violet', 'city' => 'info', 'village' => null][$region->type] ?? null">{{ __(ucfirst($region->type)) }}</x-admin.pill></td>
                <td class="muted">{{ $region->parent->name ?? '—' }}</td>
                <td class="right num">{{ $region->children_count }}</td>
                <td class="right num">{{ $region->shops_count }}</td>
                <td class="right"><x-admin.row-actions route="region" :model="$region->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $regions->links('layouts.pagination') }}
@endif
