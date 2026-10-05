@extends('layouts.admin-page')

@php $editing = $permission->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $permission->name : __('New permission') }}@endsection
@section('breadcrumb')<a href="{{ route('permission.index', $l) }}">{{ __('Permissions') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit permission') : __('New permission')" :subtitle="$editing ? $permission->name : null" />

<x-admin.form :action="$editing ? route('permission.update', [$l, $permission->id]) : route('permission.store', $l)" :method="$editing ? 'put' : 'post'">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <x-admin.field name="name" :label="__('Name')" :value="$permission->name" required autofocus col="col-8" placeholder="banner-create"
                :hint="__('Section, a dash, then the action: banner-list, banner-create, banner-edit, banner-delete. Roles group permissions by the part before the last dash.')" />
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('permission.show', [$l, $permission->id]) : route('permission.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
