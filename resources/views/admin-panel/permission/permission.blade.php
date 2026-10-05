@extends('layouts.admin-page')

@section('page-title'){{ __('Permissions') }}@endsection
@section('breadcrumb')<span>{{ __('Permissions') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Permissions')" :subtitle="__('Single actions an admin may perform, named section-action (e.g. banner-create)')">
    <x-slot:actions>
        <a class="btn" href="{{ route('role.index', app()->getLocale()) }}"><x-admin.icon name="shield" class="i-sm" />{{ __('Roles') }}</a>
        <a class="btn btn-primary" href="{{ route('permission.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New permission') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Permission name')" :per-page="$pagination">
        @if($groups->count() > 1)
        <select name="group" class="select" style="width:auto" aria-label="{{ __('Section') }}">
            <option value="">{{ __('All sections') }}</option>
            @foreach($groups as $group)
            <option value="{{ $group }}" @selected(request('group') === $group)>{{ $group }}</option>
            @endforeach
        </select>
        @endif
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.permission.permission-table')</div>
</x-admin.card>
@endsection
