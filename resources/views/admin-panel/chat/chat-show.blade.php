@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
    $customer = optional($thread->user)->name ?? __('Customer');
    $shopName = optional($thread->shop)->name ?? __('Shop');
@endphp
@section('page-title'){{ __('Chat') }} #{{ $thread->id }}@endsection
@section('breadcrumb')<a href="{{ route('chat.index', $l) }}">{{ __('Chats') }}</a><span class="sep">/</span><span>#{{ $thread->id }}</span>@endsection

@section('content')
<x-admin.page-header :title="$customer . ' ↔ ' . $shopName" :subtitle="__('Read only: admins can see the conversation but cannot write in it.')">
    <x-slot:actions>
        @if($thread->order_id)
        <a class="btn" href="{{ route('order.show', [$l, $thread->order_id]) }}"><x-admin.icon name="orders" class="i-sm" />{{ __('Order') }} № {{ str_pad((string) $thread->order_id, 7, '0', STR_PAD_LEFT) }}</a>
        @endif
        <a class="btn btn-ghost" href="{{ route('chat.index', $l) }}"><x-admin.icon name="left" class="i-sm" />{{ __('All chats') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<section class="split">
    <x-admin.card style="min-width:0" :title="__('Conversation')" :subtitle="__('Total') . ': ' . $thread->messages_count" flush>
        @if($messages->isEmpty())
            <x-admin.empty icon="chat" :text="__('No messages yet')" />
        @else
        <div class="bubbles">
            @foreach($messages as $message)
            @php $mine = $message->sender_type === 'shop'; @endphp
            <div class="bubble {{ $mine ? 'mine' : '' }}">
                <div class="meta">
                    {{ $mine ? $shopName : $customer }} · {{ $message->created_at->locale($faker)->isoFormat('D MMM, HH:mm') }}
                    @if($message->read_at) · {{ __('read') }}@endif
                </div>
                <div style="word-break:break-word">{!! nl2br(e($message->body)) !!}</div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    <div class="stack">
        <x-admin.card :title="__('Customer')">
            <div class="who">
                <span class="avatar lg">{{ $initials(optional($thread->user)->name) }}</span>
                <div>
                    @if($thread->user)
                        <a href="{{ route('user.show', [$l, $thread->user_id]) }}" style="font-weight:600">{{ $customer }}</a>
                        @if($thread->user->phone_number)<small><a href="tel:+993{{ $thread->user->phone_number }}">+993 {{ $thread->user->phone_number }}</a></small>@endif
                    @else
                        <span class="muted">{{ __('Deleted user') }}</span>
                    @endif
                </div>
            </div>
        </x-admin.card>

        <x-admin.card :title="__('Details')">
            <dl class="dl" style="grid-template-columns:130px minmax(0,1fr)">
                <dt>{{ __('Shop') }}</dt>
                <dd>@if($thread->shop)<a class="text-brand" href="{{ route('shop.show', [$l, $thread->shop_id]) }}">{{ $shopName }}</a>@else — @endif</dd>
                <dt>{{ __('Order') }}</dt>
                <dd>
                    @if($thread->order)
                        <a class="text-brand" href="{{ route('order.show', [$l, $thread->order_id]) }}">№ {{ $thread->order->number }}</a>
                        <x-admin.status :value="$thread->order->status" />
                    @else — @endif
                </dd>
                <dt>{{ __('Unread by the shop') }}</dt><dd class="num">{{ $thread->shop_unread }}</dd>
                <dt>{{ __('Unread by the customer') }}</dt><dd class="num">{{ $thread->user_unread }}</dd>
                <dt>{{ __('Last message') }}</dt>
                <dd>{{ optional($thread->last_message_at)->locale($faker)->isoFormat('D MMM YYYY, HH:mm') ?? '—' }}</dd>
            </dl>
        </x-admin.card>
    </div>
</section>
@endsection
