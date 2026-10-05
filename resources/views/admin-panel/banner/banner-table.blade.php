@php $l = app()->getLocale(); @endphp
@if($banners->isEmpty())
    <x-admin.empty icon="image" :text="__('No banners yet')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Image') }}</th><th>{{ __('Title') }}</th><th>{{ __('Link') }}</th><th>{{ __('Region') }}</th><th class="right">{{ __('Position') }}</th><th>{{ __('Status') }}</th><th>{{ __('Period') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($banners as $banner)
            <tr>
                <td style="width:120px">
                    @if($banner->image)<img class="thumb" src="{{ asset($banner->image) }}" alt="" loading="lazy" style="width:104px;height:41px;max-width:none">
                    @else<span class="thumb" style="width:104px;height:41px"><x-admin.icon name="image" class="i-sm" /></span>@endif
                </td>
                <td style="min-width:200px">
                    <a href="{{ route('banner.show', [$l, $banner->id]) }}" style="font-weight:600">{{ $banner->title_tm }}</a>
                    @if($banner->subtitle_tm)<div class="muted small">{{ $banner->subtitle_tm }}</div>@endif
                </td>
                <td>
                    @if($banner->link_type === 'none')<span class="muted">—</span>
                    @else<x-admin.pill tone="violet">{{ __(ucfirst($banner->link_type)) }}</x-admin.pill> <span class="small muted">{{ \Illuminate\Support\Str::limit($banner->link_value, 32) }}</span>@endif
                </td>
                <td class="nowrap {{ $banner->region ? '' : 'muted' }}">{{ optional($banner->region)->name ?? __('All cities') }}</td>
                <td class="right num">{{ $banner->position }}</td>
                <td><x-admin.status :value="$banner->is_active ? '1' : '0'" /></td>
                <td class="small muted nowrap num">{{ optional($banner->starts_at)->format('d.m.Y') ?? '…' }} – {{ optional($banner->ends_at)->format('d.m.Y') ?? '…' }}</td>
                <td class="right"><x-admin.row-actions route="banner" :model="$banner->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $banners->links('layouts.pagination') }}
@endif
