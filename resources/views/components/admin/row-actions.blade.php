{{-- View / edit / delete buttons for a resource row. Pass route name base and model. --}}
@props(['route', 'model', 'show' => true, 'edit' => true, 'delete' => true, 'params' => []])
@php $p = array_merge([app()->getLocale()], $params, [$model]); @endphp
<span class="row-actions">
    @if($show)<a class="icon-act" href="{{ route($route . '.show', $p) }}" title="{{ __('View') }}"><x-admin.icon name="eye" class="i-sm" /></a>@endif
    @if($edit)<a class="icon-act edit" href="{{ route($route . '.edit', $p) }}" title="{{ __('Edit') }}"><x-admin.icon name="edit" class="i-sm" /></a>@endif
    @if($delete)
    <form method="post" action="{{ route($route . '.destroy', $p) }}" data-confirm="{{ __('Are you sure you want to delete this resource?') }}">
        @csrf @method('delete')
        <button class="icon-act danger" type="submit" title="{{ __('Delete') }}"><x-admin.icon name="trash" class="i-sm" /></button>
    </form>
    @endif
    {{ $slot }}
</span>
