{{-- Search + page size + optional filters. Drives #datatable via admin.js. --}}
@props(['search' => true, 'placeholder' => null, 'perPage' => null, 'action' => null])
<form class="toolbar" data-table-form method="get" action="{{ $action ?? url()->current() }}">
    @if($search)
    <label class="search">
        <x-admin.icon name="search" class="i-sm" />
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder ?? __('Search') . '…' }}" autocomplete="off">
    </label>
    @endif
    {{ $slot }}
    @if($perPage !== false)
    <div class="right">
        <select name="pagination" class="select" style="width:auto" aria-label="{{ __('Per page') }}">
            @foreach([10, 25, 50, 100] as $n)
            <option value="{{ $n }}" @selected((int) ($perPage ?? request('pagination', 10)) === $n)>{{ $n }}</option>
            @endforeach
        </select>
    </div>
    @endif
</form>
