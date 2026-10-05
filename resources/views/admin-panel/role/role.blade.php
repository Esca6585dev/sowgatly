@extends('layouts.admin-page')

@section('page-title'){{ __('Roles') }}@endsection
@section('breadcrumb')<span>{{ __('Roles') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Roles')" :subtitle="__('A role is a named set of permissions given to admins')">
    <x-slot:actions>
        <a class="btn" href="{{ route('permission.index', app()->getLocale()) }}"><x-admin.icon name="key" class="i-sm" />{{ __('Permissions') }}</a>
        <a class="btn btn-primary" href="{{ route('role.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New role') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Role name')" :per-page="$pagination" />
    <div id="datatable">@include('admin-panel.role.role-table')</div>
</x-admin.card>
@endsection
