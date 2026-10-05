@extends('layouts.admin-page')

@php $l = app()->getLocale(); @endphp
@section('page-title'){{ $permission->name }}@endsection
@section('breadcrumb')<a href="{{ route('permission.index', $l) }}">{{ __('Permissions') }}</a><span class="sep">/</span><span>{{ $permission->name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$permission->name" :subtitle="__('Section') . ': ' . \App\Http\Controllers\AdminControllers\Role\RoleController::groupOf($permission->name)">
    <x-slot:actions>
        <form method="post" action="{{ route('permission.destroy', [$l, $permission->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('permission.edit', [$l, $permission->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<div class="grid grid-2">
    <x-admin.card :title="__('Roles')" :subtitle="__('Roles that include this permission')" flush>
        @if($permission->roles->isEmpty())
            <x-admin.empty icon="shield" :text="__('No role includes this permission yet')" />
        @else
        <div class="list" style="padding-top:4px">
            @foreach($permission->roles as $role)
            <a class="list-item" href="{{ route('role.show', [$l, $role->id]) }}">
                <span class="thumb"><x-admin.icon name="shield" /></span>
                <div class="body"><b>{{ $role->name }}</b><p>{{ trans_choice(':count permission|:count permissions', $role->permissions_count) }}</p></div>
                <x-admin.icon name="chevron" class="i-sm muted" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <x-admin.card :title="__('Admins')" :subtitle="__('Admins who have it through a role or directly')" flush>
        @if($admins->isEmpty())
            <x-admin.empty icon="user" :text="__('No admins have this permission')" />
        @else
        <div class="list" style="padding-top:4px">
            @foreach($admins as $admin)
            <a class="list-item" href="{{ route('admin.show', [$l, $admin->id]) }}">
                <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$admin->id % 4] }}">{{ mb_strtoupper(mb_substr($admin->first_name, 0, 1) . mb_substr($admin->last_name, 0, 1)) }}</span>
                <div class="body"><b>{{ $admin->first_name }} {{ $admin->last_name }}</b><p>{{ '@' . $admin->username }}</p></div>
                <x-admin.icon name="chevron" class="i-sm muted" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>
@endsection
