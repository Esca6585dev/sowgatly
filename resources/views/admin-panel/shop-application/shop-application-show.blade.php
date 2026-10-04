@extends('layouts.admin-page')

@section('page-title'){{ __('Shop application') }} #{{ $application->id }}@endsection

@section('breadcrumb')
<a href="{{ route('shop-application.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Shop applications') }}</a>
<li class="breadcrumb-item text-muted">#{{ $application->id }}</li>
@endsection

@section('content')
@include('layouts.alert')
<div class="row">
    <div class="col-lg-7">
        <div class="card card-custom">
            <div class="card-header"><h3 class="card-title">{{ $application->name }}</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('Phone number') }}</dt><dd class="col-sm-8"><a href="tel:+993{{ $application->phone }}">+993 {{ $application->phone }}</a></dd>
                    <dt class="col-sm-4">{{ __('Region') }}</dt><dd class="col-sm-8">{{ optional($application->region)->name ?? '—' }}</dd>
                    <dt class="col-sm-4">{{ __('User') }}</dt><dd class="col-sm-8">{{ optional($application->user)->name ?? __('Guest') }}</dd>
                    <dt class="col-sm-4">{{ __('Description') }}</dt><dd class="col-sm-8">{!! nl2br(e($application->description)) ?: '—' !!}</dd>
                    <dt class="col-sm-4">{{ __('Created time') }}</dt><dd class="col-sm-8">{{ $application->created_at->format('d.m.Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-custom">
            <div class="card-header"><h3 class="card-title">{{ __('Decision') }}</h3></div>
            <div class="card-body">
                <form method="post" action="{{ route('shop-application.update', [ app()->getlocale(), $application->id ]) }}">
                    @csrf
                    @method('put')
                    <div class="form-group">
                        <label>{{ __('Status') }}</label>
                        <select name="status" class="form-control">
                            @foreach(\App\Models\ShopApplication::STATUSES as $status)
                            <option value="{{ $status }}" {{ $application->status === $status ? 'selected' : '' }}>{{ __(ucfirst($status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Admin note') }}</label>
                        <textarea name="admin_note" rows="4" class="form-control">{{ old('admin_note', $application->admin_note) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">{{ __('Save') }}</button>
                </form>
                @if($application->status === 'approved')
                <a href="{{ route('shop.create', [ app()->getlocale() ]) }}" class="btn btn-light-success btn-block mt-3">{{ __('Create the shop') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
