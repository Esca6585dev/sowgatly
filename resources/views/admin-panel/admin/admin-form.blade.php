@extends('layouts.admin-page')

@php $editing = $admin->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $admin->first_name . ' ' . $admin->last_name : __('New admin') }}@endsection
@section('breadcrumb')<a href="{{ route('admin.index', $l) }}">{{ __('Admins') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit admin') : __('New admin')" :subtitle="$editing ? $admin->first_name . ' ' . $admin->last_name : null" />

<x-admin.form :action="$editing ? route('admin.update', [$l, $admin->id]) : route('admin.store', $l)" :method="$editing ? 'put' : 'post'" autocomplete="off">
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <div class="form-section">{{ __('Profile') }}</div>
            <x-admin.field name="first_name" :label="__('First name')" :value="$admin->first_name" required autofocus col="col-6" />
            <x-admin.field name="last_name" :label="__('Last name')" :value="$admin->last_name" required col="col-6" />
            <x-admin.field name="username" :label="__('Username')" :value="$admin->username" required col="col-6" :hint="__('Used to sign in. Letters, digits, - and _')" autocomplete="off" />
            <x-admin.field name="email" type="email" :label="__('Email')" :value="$admin->email" required col="col-6" autocomplete="off" />

            <div class="form-section">{{ __('Password') }}</div>
            <x-admin.field name="password" type="password" :label="$editing ? __('New password') : __('Password')" col="col-6" :required="! $editing"
                :hint="$editing ? __('Leave empty to keep the current password') : __('At least 8 characters')" autocomplete="new-password" />
            <x-admin.field name="password_confirmation" type="password" :label="__('Repeat password')" col="col-6" :required="! $editing" autocomplete="new-password" />

            <div class="form-section">{{ __('Roles') }}</div>
            <div class="field col-12">
                @if($roles->isEmpty())
                    <div class="alert info"><x-admin.icon name="info" class="i-sm" /><div>{{ __('No roles yet.') }} <a class="text-brand" href="{{ route('role.create', $l) }}" style="font-weight:600">{{ __('Create a role') }}</a></div></div>
                @else
                    @php $checked = old('roles', $selected); @endphp
                    <div class="checks">
                        @foreach($roles as $role)
                        <label class="check">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, (array) $checked, true))>
                            <span>{{ $role->name }}<small class="muted" style="display:block;font-size:12px">{{ trans_choice(':count permission|:count permissions', $role->permissions_count) }}</small></span>
                        </label>
                        @endforeach
                    </div>
                    @error('roles')<span class="err">{{ $message }}</span>@enderror
                    @error('roles.*')<span class="err">{{ $message }}</span>@enderror
                @endif
            </div>
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('admin.show', [$l, $admin->id]) : route('admin.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
