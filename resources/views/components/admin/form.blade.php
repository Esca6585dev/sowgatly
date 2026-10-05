{{-- Create/update form wrapper: picks POST or PUT, adds CSRF and multipart. --}}
@props(['action', 'method' => 'post', 'files' => false])
<form method="post" action="{{ $action }}" @if($files) enctype="multipart/form-data" @endif {{ $attributes }}>
    @csrf
    @if(strtolower($method) !== 'post') @method($method) @endif
    {{ $slot }}
</form>
