@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $name = $admin->first_name . ' ' . $admin->last_name;
    $isMe = $admin->is(auth('admin')->user());
@endphp
@section('page-title'){{ $name }}@endsection
@section('breadcrumb')<a href="{{ route('admin.index', $l) }}">{{ __('Admins') }}</a><span class="sep">/</span><span>{{ $name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$name" :subtitle="'@' . $admin->username">
    <x-slot:actions>
        @unless($isMe)
        <form method="post" action="{{ route('admin.destroy', [$l, $admin->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        @endunless
        <a class="btn btn-primary" href="{{ route('admin.edit', [$l, $admin->id]) }}"><x-admin.icon name="edit" class="i-sm" />{{ __('Edit') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card :title="__('Permissions')" :subtitle="__('Granted through the roles of this admin')">
        @if($groups->isEmpty())
            <x-admin.empty icon="key" :text="__('No permissions')" />
        @else
        <div class="stack" style="gap:14px">
            @foreach($groups as $group => $perms)
            <div>
                <div class="small muted" style="font-weight:600;margin-bottom:6px">{{ $group }}</div>
                <div class="chips">@foreach($perms as $p)<span class="chip">{{ $p->name }}</span>@endforeach</div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Profile')">
            <div class="who" style="margin-bottom:16px">
                <span class="avatar lg {{ ['', 'av-2', 'av-3', 'av-4'][$admin->id % 4] }}">{{ mb_strtoupper(mb_substr($admin->first_name, 0, 1) . mb_substr($admin->last_name, 0, 1)) }}</span>
                <div><b style="font-size:16px">{{ $name }}</b>@if($isMe)<small>{{ __('This is you') }}</small>@endif</div>
            </div>
            <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
                <dt>{{ __('Username') }}</dt><dd>{{ $admin->username }}</dd>
                <dt>{{ __('Email') }}</dt><dd><a href="mailto:{{ $admin->email }}">{{ $admin->email }}</a></dd>
                <dt>{{ __('Added') }}</dt><dd>{{ optional($admin->created_at)->format('d.m.Y') }}</dd>
            </dl>
        </x-admin.card>

        <x-admin.card :title="__('Roles')" flush>
            @if($admin->roles->isEmpty())
                <x-admin.empty icon="shield" :text="__('No roles assigned')" />
            @else
            <div class="list" style="padding-top:4px">
                @foreach($admin->roles as $role)
                <a class="list-item" href="{{ route('role.show', [$l, $role->id]) }}">
                    <span class="thumb"><x-admin.icon name="shield" /></span>
                    <div class="body"><b>{{ $role->name }}</b><p>{{ trans_choice(':count permission|:count permissions', $role->permissions->count()) }}</p></div>
                    <x-admin.icon name="chevron" class="i-sm muted" />
                </a>
                @endforeach
            </div>
            @endif
        </x-admin.card>
    </div>
</section>
@endsection
