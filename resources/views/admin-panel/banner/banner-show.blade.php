@extends('layouts.admin-page')

@php $l = app()->getLocale(); @endphp
@section('page-title'){{ $banner->title_tm }}@endsection
@section('breadcrumb')<a href="{{ route('banner.index', $l) }}">{{ __('Banners') }}</a><span class="sep">/</span><span>{{ $banner->title_tm }}</span>@endsection

@section('content')
<x-admin.page-header :title="$banner->title_tm" :subtitle="$banner->subtitle_tm">
    <x-slot:actions>
        <form method="post" action="{{ route('banner.destroy', [$l, $banner->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('banner.edit', [$l, $banner->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <div class="stack">
        <x-admin.card :title="__('Image')" subtitle="1125×441 px">
            @if($banner->image)
            <img src="{{ asset($banner->image) }}" alt="{{ $banner->title_tm }}" style="width:100%;aspect-ratio:1125/441;object-fit:cover;border-radius:14px;display:block;background:var(--surface-2)">
            @else
            <x-admin.empty icon="image" :text="__('No image')" />
            @endif
        </x-admin.card>

        <x-admin.card :title="__('Text')" flush>
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr><th>{{ __('Language') }}</th><th>{{ __('Title') }}</th><th>{{ __('Subtitle') }}</th></tr></thead>
                    <tbody>
                    @foreach(['tm' => 'Türkmençe', 'ru' => 'Русский', 'en' => 'English'] as $code => $label)
                        <tr>
                            <td class="muted nowrap">{{ $label }}</td>
                            <td style="font-weight:600">{{ $banner->{'title_' . $code} ?: '—' }}</td>
                            <td class="muted">{{ $banner->{'subtitle_' . $code} ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    </div>

    <x-admin.card :title="__('Details')">
        <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
            <dt>{{ __('Status') }}</dt><dd><x-admin.status :value="$banner->is_active ? '1' : '0'" /></dd>
            <dt>{{ __('Link') }}</dt>
            <dd>@if($banner->link_type === 'none')<span class="muted">—</span>@else<x-admin.pill tone="violet">{{ __(ucfirst($banner->link_type)) }}</x-admin.pill><div class="small" style="margin-top:4px">{{ $banner->link_value }}</div>@endif</dd>
            <dt>{{ __('Region') }}</dt><dd>{{ optional($banner->region)->name ?? __('All cities') }}</dd>
            <dt>{{ __('Position') }}</dt><dd class="num">{{ $banner->position }}</dd>
            <dt>{{ __('Starts at') }}</dt><dd class="num">{{ optional($banner->starts_at)->format('d.m.Y H:i') ?? '—' }}</dd>
            <dt>{{ __('Ends at') }}</dt><dd class="num">{{ optional($banner->ends_at)->format('d.m.Y H:i') ?? '—' }}</dd>
            <dt>{{ __('Updated') }}</dt><dd class="num">{{ optional($banner->updated_at)->format('d.m.Y H:i') ?? '—' }}</dd>
        </dl>
    </x-admin.card>
</section>
@endsection
