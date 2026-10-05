@extends('layouts.admin-page')

@php $l = app()->getLocale(); @endphp
@section('page-title'){{ $role->name }}@endsection
@section('breadcrumb')<a href="{{ route('role.index', $l) }}">{{ __('Roles') }}</a><span class="sep">/</span><span>{{ $role->name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$role->name" :subtitle="trans_choice(':count permission|:count permissions', $role->permissions->count()) . ' · ' . trans_choice(':count admin|:count admins', $admins->count())">
    <x-slot:actions>
        <form method="post" action="{{ route('role.destroy', [$l, $role->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        <a class="btn btn-primary" href="{{ route('role.edit', [$l, $role->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card :title="__('Permissions')">
        @if($groups->isEmpty())
            <x-admin.empty icon="key" :text="__('This role has no permissions')" />
        @else
        <div class="stack" style="gap:14px">
            @foreach($groups as $group => $perms)
            <div>
                <div class="small muted" style="font-weight:600;margin-bottom:6px">{{ $group }}</div>
                <div class="chips">@foreach($perms as $p)<a class="chip" href="{{ route('permission.show', [$l, $p->id]) }}">{{ $p->name }}</a>@endforeach</div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <x-admin.card :title="__('Admins')" :subtitle="__('Who has this role')" flush>
        @if($admins->isEmpty())
            <x-admin.empty icon="user" :text="__('No admins have this role')" />
        @else
        <div class="list" style="padding-top:4px">
            @foreach($admins as $admin)
            <a class="list-item" href="{{ route('admin.show', [$l, $admin->id]) }}">
                <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$admin->id % 4] }}">{{ mb_strtoupper(mb_substr($admin->first_name, 0, 1) . mb_substr($admin->last_name, 0, 1)) }}</span>
                <div class="body"><b>{{ $admin->first_name }} {{ $admin->last_name }}</b><p>{{ $admin->email }}</p></div>
                <x-admin.icon name="chevron" class="i-sm muted" />
            </a>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</section>
@endsection
