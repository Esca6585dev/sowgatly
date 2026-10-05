@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
@endphp
@section('page-title'){{ __('Shop application') }} #{{ $application->id }}@endsection
@section('breadcrumb')<a href="{{ route('shop-application.index', $l) }}">{{ __('Shop applications') }}</a><span class="sep">/</span><span>{{ $application->name }}</span>@endsection

@section('content')
<x-admin.page-header :title="$application->name" :subtitle="__('Shop application') . ' #' . $application->id . ' · ' . $application->created_at->locale($faker)->isoFormat('D MMMM YYYY, HH:mm')">
    <x-slot:actions>
        @if($application->status === 'approved')
        <a class="btn btn-primary" href="{{ route('shop.create', $l) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('Create the shop') }}</a>
        @endif
        <a class="btn btn-ghost" href="{{ route('shop-application.index', $l) }}"><x-admin.icon name="left" class="i-sm" />{{ __('All applications') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card style="min-width:0" :title="__('Applicant')">
        <x-slot:right><x-admin.status :value="$application->status" /></x-slot:right>
        <dl class="dl">
            <dt>{{ __('Shop name') }}</dt><dd style="font-weight:600">{{ $application->name }}</dd>
            <dt>{{ __('Phone number') }}</dt><dd><a class="text-brand" href="tel:+993{{ $application->phone }}">+993 {{ $application->phone }}</a></dd>
            <dt>{{ __('Region') }}</dt><dd>{{ optional($application->region)->name ?? '—' }}</dd>
            <dt>{{ __('User') }}</dt>
            <dd>
                @if($application->user)
                    <a class="text-brand" href="{{ route('user.show', [$l, $application->user_id]) }}">{{ $application->user->name ?: __('No name') }}</a>
                    @if($application->user->phone_number)<span class="muted"> · +993 {{ $application->user->phone_number }}</span>@endif
                @else
                    <span class="muted">{{ __('Guest') }}</span>
                @endif
            </dd>
            <dt>{{ __('Description') }}</dt><dd>{!! $application->description ? nl2br(e($application->description)) : '—' !!}</dd>
            <dt>{{ __('Created time') }}</dt><dd>{{ $application->created_at->locale($faker)->isoFormat('D MMMM YYYY, HH:mm') }}</dd>
            <dt>{{ __('Updated time') }}</dt><dd>{{ $application->updated_at->locale($faker)->isoFormat('D MMMM YYYY, HH:mm') }}</dd>
        </dl>
    </x-admin.card>

    <x-admin.card :title="__('Decision')" :subtitle="__('Approving or rejecting notifies the applicant in the app.')">
        <x-admin.form :action="route('shop-application.update', [$l, $application->id])" method="put">
            <div class="form-grid">
                <x-admin.select name="status" :label="__('Status')" :value="$application->status" col="col-12"
                    :options="collect(\App\Models\ShopApplication::STATUSES)->mapWithKeys(fn ($s) => [$s => __(ucfirst($s))])" />
                <x-admin.textarea name="admin_note" :label="__('Admin note')" :value="$application->admin_note" rows="4" col="col-12" />
            </div>
            <button class="btn btn-primary btn-block" type="submit" style="margin-top:16px"><x-admin.icon name="check" class="i-sm" />{{ __('Save') }}</button>
        </x-admin.form>
        @if($application->status === 'approved')
        <a class="btn btn-soft btn-block" style="margin-top:10px" href="{{ route('shop.create', $l) }}"><x-admin.icon name="shop" class="i-sm" />{{ __('Create the shop') }}</a>
        @endif
    </x-admin.card>
</section>
@endsection
