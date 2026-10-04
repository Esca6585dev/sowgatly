@extends('layouts.admin-page')

@section('page-title'){{ __('Order') }} № {{ $order->number }}@endsection

@section('breadcrumb')
<a href="{{ route('order.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Orders') }}</a>
<li class="breadcrumb-item text-muted">№ {{ $order->number }}</li>
@endsection

@section('content')
@include('layouts.alert')
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom mb-5">
            <div class="card-header"><h3 class="card-title">{{ __('Items') }}</h3></div>
            <div class="card-body">
                <table class="table">
                    <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Quantity') }}</th><th>{{ __('Price') }}</th><th>{{ __('Sum') }}</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                @if($item->product && $item->product->images->first())
                                <img src="{{ asset($item->product->images->first()->url) }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px" class="mr-2">
                                @endif
                                {{ optional($item->product)->name_ru ?? optional($item->product)->name_tm ?? '#' . $item->product_id }}
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-right">{{ __('Items total') }}</td><td>{{ number_format($order->items_total ?? $order->items->sum(fn($i) => $i->price * $i->quantity), 2) }} TMT</td></tr>
                        <tr><td colspan="3" class="text-right">{{ __('Delivery fee') }}</td><td>{{ number_format($order->delivery_fee, 2) }} TMT</td></tr>
                        <tr class="font-weight-bold"><td colspan="3" class="text-right">{{ __('Total') }}</td><td>{{ number_format($order->total_amount, 2) }} TMT</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header"><h3 class="card-title">{{ __('Delivery') }}</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('Type') }}</dt><dd class="col-sm-8">{{ __(ucfirst($order->fulfillment)) }}, {{ $order->delivery_type === 'scheduled' ? __('Scheduled') : __('As soon as possible') }}</dd>
                    @if($order->scheduled_at)<dt class="col-sm-4">{{ __('Scheduled at') }}</dt><dd class="col-sm-8">{{ $order->scheduled_at->format('d.m.Y H:i') }}</dd>@endif
                    <dt class="col-sm-4">{{ __('Recipient') }}</dt><dd class="col-sm-8">{{ $order->recipient_name }} +993 {{ $order->recipient_phone }}</dd>
                    @if($order->delivery_address)<dt class="col-sm-4">{{ __('Address') }}</dt><dd class="col-sm-8">{{ $order->delivery_address }}</dd>@endif
                    @if($order->note)<dt class="col-sm-4">{{ __('Comment') }}</dt><dd class="col-sm-8">{{ $order->note }}</dd>@endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom mb-5">
            <div class="card-header"><h3 class="card-title">{{ __('Customer') }}</h3></div>
            <div class="card-body">
                <p class="mb-1"><strong>{{ optional($order->user)->name }}</strong></p>
                <p class="mb-1">+993 {{ optional($order->user)->phone_number }}</p>
                <p class="mb-0 text-muted">{{ __('Shop') }}: {{ optional($order->shop)->name }}</p>
                @if($order->chatThread)
                <a href="{{ route('chat.show', [ app()->getlocale(), $order->chatThread->id ]) }}" class="btn btn-sm btn-light mt-3">{{ __('Open chat') }}</a>
                @endif
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-header"><h3 class="card-title">{{ __('Status') }}</h3></div>
            <div class="card-body">
                <form method="post" action="{{ route('order.update', [ app()->getlocale(), $order->id ]) }}">
                    @csrf
                    @method('put')
                    <div class="form-group">
                        <label>{{ __('Order status') }}</label>
                        <select name="status" class="form-control">
                            <option value="{{ $order->status }}" selected>{{ __(ucfirst($order->status)) }} ({{ __('current') }})</option>
                            @foreach(\App\Models\Order::TRANSITIONS[$order->status] ?? [] as $next)
                            <option value="{{ $next }}">{{ __(ucfirst($next)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Payment') }}: {{ __(ucfirst($order->payment_method)) }}
                            @if($order->payment_bank) — {{ $banks[$order->payment_bank]['name'][app()->getLocale()] ?? $order->payment_bank }} @endif
                        </label>
                        <select name="payment_status" class="form-control">
                            @foreach(['unpaid', 'paid', 'refunded'] as $ps)
                            <option value="{{ $ps }}" {{ $order->payment_status === $ps ? 'selected' : '' }}>{{ __(ucfirst($ps)) }}</option>
                            @endforeach
                        </select>
                        @if($order->paid_at)<small class="text-muted">{{ __('Paid at') }} {{ $order->paid_at->format('d.m.Y H:i') }}</small>@endif
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Save') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
