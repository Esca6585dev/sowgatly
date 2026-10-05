@extends('layouts.admin-page')

@section('page-title'){{ __('Regions') }}@endsection
@section('breadcrumb')<span>{{ __('Regions') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Regions')" :subtitle="__('Countries, provinces, cities and villages used for shops and delivery')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('region.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New region') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Region name')" :per-page="$pagination">
        <select name="type" class="select" style="width:auto">
            <option value="">{{ __('All types') }}</option>
            @foreach(\App\Http\Controllers\AdminControllers\Region\RegionController::TYPES as $type)
            <option value="{{ $type }}" @selected(request('type') === $type)>{{ __(ucfirst($type)) }}</option>
            @endforeach
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.region.region-table')</div>
</x-admin.card>
@endsection
