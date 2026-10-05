@extends('layouts.admin-page')

@section('page-title'){{ __('Chats') }}@endsection
@section('breadcrumb')<span>{{ __('Chats') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Chats')" :subtitle="__('Conversations between customers and shops (read only)')" />

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Customer or shop')" :per-page="$pagination">
        <select name="unread" class="select" style="width:auto" aria-label="{{ __('Unread') }}">
            <option value="">{{ __('All chats') }}</option>
            <option value="1" @selected(request('unread') === '1')>{{ __('Waiting for the shop') }}</option>
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.chat.chat-table')</div>
</x-admin.card>
@endsection
