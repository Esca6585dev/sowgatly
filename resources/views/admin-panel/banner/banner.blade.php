@extends('layouts.admin-page')

@section('page-title'){{ __('Banners') }}@endsection

@section('breadcrumb')
<a href="{{ route('banner.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Banners') }}</a>
@endsection

@section('content')
<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title"><h3 class="card-label">{{ __('Banners') }}</h3></div>
        <div class="card-toolbar">
            <a href="{{ route('banner.create', [ app()->getlocale() ]) }}" class="btn btn-primary font-weight-bolder">{{ __('Create') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="form-inline mb-5">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control mr-2 mb-2" placeholder="{{ __('Title') }}">
            <select name="pagination" class="form-control mr-2 mb-2">
                @foreach([10, 25, 50, 100] as $number)
                <option value="{{ $number }}" {{ $pagination == $number ? 'selected' : '' }}>{{ $number }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2">{{ __('Search') }}</button>
        </form>
        @include('layouts.alert')
        @include('admin-panel.banner.banner-table')
    </div>
</div>
@endsection
