@extends('layouts.admin-page')

@php
    $l = app()->getLocale();
    $current = request('status');
    $statuses = \App\Models\ShopApplication::STATUSES;
    $chipUrl = fn ($status) => route('shop-application.index', array_merge([$l], request()->except(['status', 'page']), $status ? ['status' => $status] : []));
@endphp
@section('page-title'){{ __('Shop applications') }}@endsection
@section('breadcrumb')<span>{{ __('Shop applications') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Shop applications')" :subtitle="__('Requests from people who want to open a shop on Sowgatly')" />

<x-admin.card flush>
    <div class="chips" style="padding:16px 20px 14px">
        <a class="chip {{ in_array($current, $statuses, true) ? '' : 'on' }}" href="{{ $chipUrl(null) }}">{{ __('All') }} <span class="num">{{ $statusCounts->sum() }}</span></a>
        @foreach($statuses as $status)
        <a class="chip {{ $current === $status ? 'on' : '' }}" href="{{ $chipUrl($status) }}">{{ __(ucfirst($status)) }} <span class="num">{{ $statusCounts[$status] ?? 0 }}</span></a>
        @endforeach
    </div>
    <x-admin.toolbar :placeholder="__('Name or phone')" :per-page="$pagination">
        @if(in_array($current, $statuses, true))<input type="hidden" name="status" value="{{ $current }}">@endif
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.shop-application.shop-application-table')</div>
</x-admin.card>
@endsection
