@extends('layouts.admin-page')

@section('page-title'){{ __('Attributes') }}@endsection
@section('breadcrumb')<span>{{ __('Attributes') }}</span>@endsection

@section('content')
<x-admin.page-header :title="__('Attributes')" :subtitle="__('Characteristics such as colour or size, grouped by category')">
    <x-slot:actions>
        <a class="btn btn-primary" href="{{ route('attribute.create', app()->getLocale()) }}"><x-admin.icon name="plus" class="i-sm" />{{ __('New attribute') }}</a>
    </x-slot:actions>
</x-admin.page-header>

<x-admin.card flush>
    <div style="height:16px"></div>
    <x-admin.toolbar :placeholder="__('Type or value')" :per-page="$pagination">
        <select name="category_id" class="select" style="width:auto;max-width:260px" aria-label="{{ __('Category') }}">
            <option value="">{{ __('All categories') }}</option>
            @foreach($categories as $id => $label)
            <option value="{{ $id }}" @selected((string) request('category_id') === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
    </x-admin.toolbar>
    <div id="datatable">@include('admin-panel.attribute.attribute-table')</div>
</x-admin.card>
@endsection
