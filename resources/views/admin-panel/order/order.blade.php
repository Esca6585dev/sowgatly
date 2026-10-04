@extends('layouts.admin-page')

@section('page-title'){{ __('Orders') }}@endsection

@section('breadcrumb')
<a href="{{ route('order.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Orders') }}</a>
@endsection

@section('content')
<div class="card card-custom">
    <div class="card-header flex-wrap py-5">
        <div class="card-title">
            <h3 class="card-label">{{ __('Orders') }}</h3>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="form-inline mb-5">
            <input type="search" name="search" value="{{ request('search') }}" class="form-control mr-2 mb-2" placeholder="{{ __('Order number or product') }}">
            <select name="status" class="form-control mr-2 mb-2">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
            <select name="payment_status" class="form-control mr-2 mb-2">
                <option value="">{{ __('All payments') }}</option>
                @foreach(['unpaid', 'paid', 'refunded'] as $ps)
                <option value="{{ $ps }}" {{ request('payment_status') === $ps ? 'selected' : '' }}>{{ __(ucfirst($ps)) }}</option>
                @endforeach
            </select>
            <select name="fulfillment" class="form-control mr-2 mb-2">
                <option value="">{{ __('Delivery and pickup') }}</option>
                <option value="delivery" {{ request('fulfillment') === 'delivery' ? 'selected' : '' }}>{{ __('Delivery') }}</option>
                <option value="pickup" {{ request('fulfillment') === 'pickup' ? 'selected' : '' }}>{{ __('Pickup') }}</option>
            </select>
            <select name="pagination" class="form-control mr-2 mb-2">
                @foreach([10, 25, 50, 100] as $number)
                <option value="{{ $number }}" {{ $pagination == $number ? 'selected' : '' }}>{{ $number }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary mb-2">{{ __('Search') }}</button>
        </form>

        @include('layouts.alert')

        @include('admin-panel.order.order-table')
    </div>
</div>
@endsection
