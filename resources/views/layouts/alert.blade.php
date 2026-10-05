@php
$flash = [
    'success-create' => ['ok', 'check-circle'], 'success-update' => ['ok', 'check-circle'], 'success' => ['ok', 'check-circle'],
    'success-delete' => ['bad', 'trash'], 'success-restore' => ['info', 'refresh'],
    'warning' => ['warn', 'alert'], 'error' => ['bad', 'alert'], 'info' => ['info', 'info'],
];
@endphp
<div class="toasts" aria-live="polite">
    @foreach($flash as $key => [$tone, $icon])
        @if(session()->has($key))
        <div class="toast {{ $tone }}" role="status"><x-admin.icon :name="$icon" /><div>{{ __(session($key)) }}</div><button type="button" data-dismiss-toast aria-label="{{ __('Close') }}"><x-admin.icon name="x" class="i-sm" /></button></div>
        @endif
    @endforeach
    @if(isset($errors) && $errors->any())
        <div class="toast bad" role="alert"><x-admin.icon name="alert" /><div>{{ __('Please check the highlighted fields.') }}</div><button type="button" data-dismiss-toast aria-label="{{ __('Close') }}"><x-admin.icon name="x" class="i-sm" /></button></div>
    @endif
</div>
