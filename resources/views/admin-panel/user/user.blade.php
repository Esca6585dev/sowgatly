@extends('layouts.admin-page')

@section('page-title'){{ __('Users') }}@endsection
@section('breadcrumb')<span>{{ __('Users') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Users')" :subtitle="__('Customers of the app. They sign in with a one-time code sent to their phone.')">
    <x-slot:actions>
        <a class="btn" href="{{ route('export', array_merge([app()->getLocale()], request()->only('search', 'status', 'has_shop'))) }}"><x-admin.icon name="download" class="i-sm" />{{ __('Export to Excel') }}</a>
        <a class="btn btn-primary" href="{{ route('user.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New user') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Name, phone or email')" :per-page="$pagination">
        <select name="status" class="select" style="width:auto" aria-label="{{ __('Status') }}">
            <option value="">{{ __('All statuses') }}</option>
            <option value="1" @selected(request('status') === '1')>{{ __('Active') }}</option>
            <option value="0" @selected(request('status') === '0')>{{ __('Inactive') }}</option>
        </select>
        <select name="has_shop" class="select" style="width:auto" aria-label="{{ __('Shop') }}">
            <option value="">{{ __('With and without shop') }}</option>
            <option value="1" @selected(request('has_shop') === '1')>{{ __('Shop owners') }}</option>
            <option value="0" @selected(request('has_shop') === '0')>{{ __('Without a shop') }}</option>
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.user.user-table')</div>
</x-admin.card>
@endsection
