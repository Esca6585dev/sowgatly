@extends('layouts.admin-page')

@php $editing = $banner->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $banner->title_tm : __('New banner') }}@endsection
@section('breadcrumb')<a href="{{ route('banner.index', $l) }}">{{ __('Banners') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit banner') : __('New banner')" :subtitle="$editing ? $banner->title_tm : null" />

<x-admin.form :action="$editing ? route('banner.update', [$l, $banner->id]) : route('banner.store', $l)" :method="$editing ? 'put' : 'post'" files>
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <div class="form-section">{{ __('Text') }}</div>
            @foreach(['tm' => 'Türkmençe', 'ru' => 'Русский', 'en' => 'English'] as $code => $label)
            <x-admin.field :name="'title_' . $code" :label="__('Title') . ' (' . $label . ')'" :value="$banner->{'title_' . $code}" col="col-4" :required="$code === 'tm'" :autofocus="$code === 'tm'" />
            @endforeach
            @foreach(['tm' => 'Türkmençe', 'ru' => 'Русский', 'en' => 'English'] as $code => $label)
            <x-admin.field :name="'subtitle_' . $code" :label="__('Subtitle') . ' (' . $label . ')'" :value="$banner->{'subtitle_' . $code}" col="col-4" />
            @endforeach

            <div class="form-section">{{ __('Image') }} <span class="muted small" style="font-weight:500">1125×441 px</span></div>
            <x-admin.file name="image" :current="$banner->image ? [asset($banner->image)] : []" hint="JPG, PNG, WEBP · 4 MB" />

            <div class="form-section">{{ __('Link') }}</div>
            <x-admin.select name="link_type" :label="__('Link type')" :value="$banner->link_type" col="col-4"
                :options="collect(\App\Models\Banner::LINK_TYPES)->mapWithKeys(fn ($t) => [$t => __(ucfirst($t))])" />
            <x-admin.field name="link_value" :label="__('Link value')" :value="$banner->link_value" col="col-8" :hint="__('Category, product or shop id, or a full URL')" />

            <div class="form-section">{{ __('Display') }}</div>
            <x-admin.select name="region_id" :label="__('Region')" :value="$banner->region_id" :placeholder="__('All cities')" col="col-4"
                :options="$regions->pluck('name', 'id')" />
            <x-admin.field name="position" type="number" min="0" :label="__('Position')" :value="$banner->position ?? 0" col="col-4" :hint="__('Smaller numbers are shown first')" />
            <div class="field col-4" style="justify-content:flex-end;padding-bottom:10px">
                <x-admin.checkbox name="is_active" :label="__('Active')" :checked="$banner->is_active" switch />
            </div>
            <x-admin.field name="starts_at" type="datetime-local" :label="__('Starts at')" :value="optional($banner->starts_at)->format('Y-m-d\TH:i')" col="col-6" />
            <x-admin.field name="ends_at" type="datetime-local" :label="__('Ends at')" :value="optional($banner->ends_at)->format('Y-m-d\TH:i')" col="col-6" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('banner.show', [$l, $banner->id]) : route('banner.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
