@extends('layouts.admin-page')

@php $editing = $region->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $region->name : __('New region') }}@endsection
@section('breadcrumb')<a href="{{ route('region.index', $l) }}">{{ __('Regions') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit region') : __('New region')" :subtitle="$editing ? $region->name : null" />

<x-admin.form :action="$editing ? route('region.update', [$l, $region->id]) : route('region.store', $l)" :method="$editing ? 'put' : 'post'">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <x-admin.field name="name" :label="__('Name')" :value="$region->name" required autofocus col="col-6" />
            <x-admin.select name="type" :label="__('Type')" :value="$region->type" col="col-6"
                :options="collect(\App\Http\Controllers\AdminControllers\Region\RegionController::TYPES)->mapWithKeys(fn ($t) => [$t => __(ucfirst($t))])" />
            <x-admin.select name="parent_id" :label="__('Parent region')" :value="$region->parent_id" :placeholder="__('— None —')" col="col-12"
                :hint="__('A city belongs to a province, a village to a city.')"
                :options="$parents->mapWithKeys(fn ($p) => [$p->id => $p->name . ' (' . __(ucfirst($p->type)) . ')'])" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('region.show', [$l, $region->id]) : route('region.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
