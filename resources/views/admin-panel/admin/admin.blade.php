@extends('layouts.admin-page')

@section('page-title'){{ __('Admins') }}@endsection
@section('breadcrumb')<span>{{ __('Admins') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Admins')" :subtitle="__('People who can sign in to this admin panel')">
    <x-slot:actions>
        <a class="btn" href="{{ route('role.index', app()->getLocale()) }}"><x-admin.icon name="shield" class="i-sm" />{{ __('Roles') }}</a>
        <a class="btn btn-primary" href="{{ route('admin.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New admin') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Name, username or email')" :per-page="$pagination">
        @if($roles->isNotEmpty())
        <select name="role" class="select" style="width:auto" aria-label="{{ __('Role') }}">
            <option value="">{{ __('All roles') }}</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        @endif
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.admin.admin-table')</div>
</x-admin.card>
@endsection
