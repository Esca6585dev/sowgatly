@extends('layouts.admin-page')

@php $editing = $user->exists; $l = app()->getLocale(); @endphp
@section('page-title'){{ $editing ? $user->name : __('New user') }}@endsection
@section('breadcrumb')<a href="{{ route('user.index', $l) }}">{{ __('Users') }}</a><span class="sep">/</span><span>{{ $editing ? __('Edit') : __('New') }}</span>@endsection

@section('content')
<x-admin.page-header :title="$editing ? __('Edit user') : __('New user')" :subtitle="$editing ? $user->name : __('The customer signs in with a one-time code sent to this phone number.')" />

<x-admin.form :action="$editing ? route('user.update', [$l, $user->id]) : route('user.store', $l)" :method="$editing ? 'put' : 'post'" files>
    <x-admin.card>
        <div class="form-grid" style="padding-top:16px">
            <div class="form-section">{{ __('Profile') }}</div>
            <x-admin.field name="name" :label="__('Name')" :value="$user->name" required autofocus col="col-6" />
            {{-- Phone with a leading +993 addon (x-admin.field only puts the addon after the input). --}}
            <div class="field col-6">
                <label for="f-phone_number">{{ __('Phone') }}</label>
                <div class="input-group">
                    <span class="addon" style="border-left:1px solid var(--line-strong);border-right:0;border-radius:12px 0 0 12px">+993</span>
                    <input id="f-phone_number" type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required inputmode="numeric" maxlength="16" placeholder="65123456"
                        class="input @error('phone_number') is-invalid @enderror" style="border-radius:0 12px 12px 0">
                </div>
                <span class="hint">{{ __('8 digits, without +993') }}</span>
                @error('phone_number')<span class="err">{{ $message }}</span>@enderror
            </div>
            <x-admin.field name="email" type="email" :label="__('Email')" :value="$user->email" col="col-6" :hint="__('Optional')" />
            <x-admin.field name="birth_date" type="date" :label="__('Birth date')" :value="optional($user->birth_date)->format('Y-m-d')" col="col-6" :max="now()->subDay()->format('Y-m-d')" />

            <div class="form-section">{{ __('Photo') }}</div>
            <x-admin.file name="image" :label="__('Avatar')" col="col-12"
                :current="$user->image && file_exists(public_path($user->image)) ? [asset($user->image)] : []" hint="JPG, PNG, WEBP · 4 MB" />

            <div class="form-section">{{ __('Access') }}</div>
            <div class="field col-12">
                <x-admin.checkbox name="status" :label="__('Active — the user can sign in and place orders')" :checked="(bool) $user->status" switch />
            </div>
        </div>
        <x-slot:footer>
            <a class="btn btn-ghost" href="{{ $editing ? route('user.show', [$l, $user->id]) : route('user.index', $l) }}">{{ __('Cancel') }}</a>
            <button class="btn btn-primary" type="submit"><x-admin.icon name="check" class="i-sm" />{{ $editing ? __('Save') : __('Create') }}</button>
        </x-slot:footer>
    </x-admin.card>
</x-admin.form>
@endsection
