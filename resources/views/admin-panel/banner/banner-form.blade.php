@extends('layouts.admin-page')

@section('page-title'){{ __('Banners') }} — {{ $banner->id ? __('Edit') : __('Create') }}@endsection

@section('breadcrumb')
<a href="{{ route('banner.index', [ app()->getlocale() ]) }}" class="text-muted">{{ __('Banners') }}</a>
<li class="breadcrumb-item text-muted">{{ $banner->id ? '#' . $banner->id : __('Create') }}</li>
@endsection

@section('content')
@include('layouts.alert')
<div class="card card-custom">
    <div class="card-header"><h3 class="card-title">{{ $banner->id ? $banner->title_tm : __('New banner') }}</h3></div>
    <form method="post" enctype="multipart/form-data"
        action="{{ $banner->id ? route('banner.update', [ app()->getlocale(), $banner->id ]) : route('banner.store', [ app()->getlocale() ]) }}">
        @csrf
        @if($banner->id) @method('put') @endif
        <div class="card-body">
            <div class="row">
                @foreach(['tm' => 'Türkmençe', 'ru' => 'Русский', 'en' => 'English'] as $code => $label)
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{ __('Title') }} ({{ $label }}) @if($code === 'tm')<span class="text-danger">*</span>@endif</label>
                        <input type="text" name="title_{{ $code }}" class="form-control @error('title_' . $code) is-invalid @enderror"
                            value="{{ old('title_' . $code, $banner->{'title_' . $code}) }}">
                        @error('title_' . $code)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label>{{ __('Subtitle') }} ({{ $label }})</label>
                        <input type="text" name="subtitle_{{ $code }}" class="form-control @error('subtitle_' . $code) is-invalid @enderror"
                            value="{{ old('subtitle_' . $code, $banner->{'subtitle_' . $code}) }}">
                        @error('subtitle_' . $code)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                @endforeach
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>{{ __('Image') }} (1125×441 px)</label>
                        <input type="file" name="image" accept="image/*" class="form-control-file @error('image') is-invalid @enderror">
                        @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @if($banner->image)<img src="{{ asset($banner->image) }}" alt="" class="mt-2" style="max-height:80px;border-radius:4px">@endif
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>{{ __('Link type') }}</label>
                        <select name="link_type" class="form-control @error('link_type') is-invalid @enderror">
                            @foreach(\App\Models\Banner::LINK_TYPES as $type)
                            <option value="{{ $type }}" {{ old('link_type', $banner->link_type) === $type ? 'selected' : '' }}>{{ __(ucfirst($type)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ __('Link value') }} <small class="text-muted">(id or URL)</small></label>
                        <input type="text" name="link_value" class="form-control @error('link_value') is-invalid @enderror" value="{{ old('link_value', $banner->link_value) }}">
                        @error('link_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ __('Region') }}</label>
                        <select name="region_id" class="form-control">
                            <option value="">{{ __('All cities') }}</option>
                            @foreach($regions as $region)
                            <option value="{{ $region->id }}" {{ (string) old('region_id', $banner->region_id) === (string) $region->id ? 'selected' : '' }}>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>{{ __('Position') }}</label>
                        <input type="number" name="position" min="0" class="form-control" value="{{ old('position', $banner->position ?? 0) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ __('Starts at') }}</label>
                        <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', optional($banner->starts_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>{{ __('Ends at') }}</label>
                        <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" value="{{ old('ends_at', optional($banner->ends_at)->format('Y-m-d\TH:i')) }}">
                        @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="d-block">{{ __('Status') }}</label>
                        <label class="checkbox checkbox-lg">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                            <span></span>&nbsp;{{ __('Active') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary mr-2">{{ __('Save') }}</button>
            <a href="{{ route('banner.index', [ app()->getlocale() ]) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
