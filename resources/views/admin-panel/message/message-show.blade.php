@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
@section('page-title'){{ __('Message') }} #{{ $message->id }}@endsection
@section('breadcrumb')<a href="{{ route('message.index', $l) }}">{{ __('Messages') }}</a><span class="sep">/</span><span>#{{ $message->id }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Message') . ' #' . $message->id" :subtitle="optional($message->created_at)->locale($faker)->isoFormat('D MMMM YYYY, HH:mm')">
    <x-slot:actions>
        <form method="post" action="{{ route('message.destroy', [$l, $message->id]) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
            @csrf @method('delete')
            <button class="btn btn-danger" type="submit"><x-admin.icon name="trash" class="i-sm" />{{ __('Delete') }}</button>
        </form>
        @if($message->email)
        <a class="btn btn-primary" href="mailto:{{ $message->email }}"><x-admin.icon name="mail" class="i-sm" />{{ __('Reply by email') }}</a>
        @endif
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card style="min-width:0" :title="__('Message')">
        <div style="white-space:pre-line;word-break:break-word;line-height:1.6">{{ $message->message ?: '—' }}</div>
    </x-admin.card>

    <x-admin.card :title="__('Sender')">
        <div class="who" style="margin-bottom:16px">
            <span class="avatar lg">{{ $initials($message->username) }}</span>
            <div><b>{{ $message->username ?: __('No name') }}</b>@if($sender)<small>{{ __('Registered user') }}</small>@endif</div>
        </div>
        <dl class="dl" style="grid-template-columns:110px minmax(0,1fr)">
            <dt>{{ __('Email') }}</dt>
            <dd>@if($message->email)<a class="text-brand" href="mailto:{{ $message->email }}">{{ $message->email }}</a>@else — @endif</dd>
            <dt>{{ __('Phone number') }}</dt>
            <dd>@if($message->phone_number)<a class="text-brand" href="tel:{{ $message->phone_number }}">{{ $message->phone_number }}</a>@else — @endif</dd>
            @if($sender)
            <dt>{{ __('User') }}</dt>
            <dd><a class="text-brand" href="{{ route('user.show', [$l, $sender->id]) }}">{{ $sender->name ?: '#' . $sender->id }}</a></dd>
            @endif
        </dl>
    </x-admin.card>
</section>
@endsection
