@extends('layouts.admin-page')

@section('page-title'){{ __('Banners') }}@endsection
@section('breadcrumb')<span>{{ __('Banners') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Banners')" :subtitle="__('Promo slides on the home screen of the app')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('banner.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New banner') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Title')" :per-page="$pagination" />
    <div id="datatable">@include('admin-panel.banner.banner-table')</div>
</x-admin.card>
@endsection
