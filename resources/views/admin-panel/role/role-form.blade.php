@extends('layouts.admin-page')

@php $editing = $role->exists; $l = app()->getLocale(); $checked = (array) old('permissions', $selected); @endphp
@section('page-title'){{ $editing ? $role->name : __('New role') }}@endsection
@section('breadcrumb')<a href="{{ route('role.index', $l) }}">{{ __('Roles') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit role') : __('New role')" :subtitle="$editing ? $role->name : null" />

<x-admin.form :action="$editing ? route('role.update', [$l, $role->id]) : route('role.store', $l)" :method="$editing ? 'put' : 'post'">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <x-admin.field name="name" :label="__('Name')" :value="$role->name" required autofocus col="col-6" :placeholder="__('e.g. content-manager')" />

            <div class="form-section">{{ __('Permissions') }}</div>
            <div class="field col-12">
                @if($groups->isEmpty())
                    <div class="alert info"><x-admin.icon name="info" class="i-sm" /><div>{{ __('No permissions yet.') }} <a class="text-brand" href="{{ route('permission.create', $l) }}" style="font-weight:600">{{ __('Create a permission') }}</a></div></div>
                @else
                    <div class="stack" style="gap:18px">
                        @foreach($groups as $group => $perms)
                        <div>
                            <div class="small muted" style="font-weight:600;margin-bottom:8px">{{ $group }}</div>
                            <div class="checks">
                                @foreach($perms as $p)
                                <label class="check">
                                    <input type="checkbox" name="permissions[]" value="{{ $p->name }}" @checked(in_array($p->name, $checked, true))>
                                    <span>{{ $p->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('permissions')<span class="err">{{ $message }}</span>@enderror
                    @error('permissions.*')<span class="err">{{ $message }}</span>@enderror
                @endif
            </div>
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('role.show', [$l, $role->id]) : route('role.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
