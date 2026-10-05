@extends('layouts.admin-page')

@section('page-title'){{ __('Messages') }}@endsection
@section('breadcrumb')<span>{{ __('Messages') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Messages')" :subtitle="__('Messages sent from the contact form on the website')" />

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Name, email, phone or text')" :per-page="$pagination" />
    <div id="datatable">@include('admin-panel.message.message-table')</div>
</x-admin.card>
@endsection
