@extends('layouts.admin-page')

@section('page-title'){{ __('Shop applications') }}@endsection

@section('breadcrumb')
<a href="{{ route('shop-application.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Shop applications') }}</a>
@endsection

@section('content')
<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title"><h3 class="card-label">{{ __('Shop applications') }}</h3></div>
    </div>
    <div class="card-body">
        <form method="get" class="form-inline mb-5">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control mr-2 mb-2" placeholder="{{ __('Name or phone') }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\ShopApplication::STATUSES as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
            <select name="pagination" class="form-control mr-2 mb-2">
                @foreach([10, 25, 50, 100] as $number)
                <option value="{{ $number }}" {{ $pagination == $number ? 'selected' : '' }}>{{ $number }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2">{{ __('Search') }}</button>
        </form>
        @include('layouts.alert')
        @include('admin-panel.shop-application.shop-application-table')
    </div>
</div>
@endsection
