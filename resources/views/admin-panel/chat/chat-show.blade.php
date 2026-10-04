@extends('layouts.admin-page')

@section('page-title'){{ __('Chat') }} #{{ $thread->id }}@endsection

@section('breadcrumb')
<a href="{{ route('chat.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Chats') }}</a>
<li class="breadcrumb-item text-muted">#{{ $thread->id }}</li>
@endsection

@section('content')
<div class="card card-custom">
    <div class="card-header">
        <h3 class="card-title">
            {{ optional($thread->user)->name }} (+993 {{ optional($thread->user)->phone_number }})
            &nbsp;↔&nbsp; {{ optional($thread->shop)->name }}
            @if($thread->order_id) &nbsp;·&nbsp; <a href="{{ route('order.show', [ app()->getlocale(), $thread->order_id ]) }}">{{ __('Order') }} #{{ $thread->order_id }}</a>@endif
        </h3>
    </div>
    <div class="card-body" style="max-height:70vh;overflow-y:auto">
        @forelse($messages as $message)
        <div class="d-flex {{ $message->sender_type === 'shop' ? 'justify-content-end' : 'justify-content-start' }} mb-3">
            <div class="p-3 rounded {{ $message->sender_type === 'shop' ? 'bg-light-primary' : 'bg-light' }}" style="max-width:70%">
                <div class="font-size-sm text-muted mb-1">
                    {{ $message->sender_type === 'shop' ? optional($thread->shop)->name : optional($thread->user)->name }}
                    · {{ $message->created_at->format('d.m.Y H:i') }}
                    @if($message->read_at) · {{ __('read') }} @endif
                </div>
                <div>{!! nl2br(e($message->body)) !!}</div>
            </div>
        </div>
        @empty
        <p class="text-muted text-center mb-0">{{ __('No messages yet') }}</p>
        @endforelse
    </div>
</div>
@endsection
