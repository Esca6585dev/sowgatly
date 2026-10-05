@if ($paginator->hasPages())
<nav class="pager" aria-label="{{ __('Pagination') }}">
    <span class="muted small">{{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</span>
    <div class="pages">
        <a href="{{ $paginator->previousPageUrl() }}" class="{{ $paginator->onFirstPage() ? 'off' : '' }}" aria-label="{{ __('Previous') }}"><x-admin.icon name="left" class="i-sm" /></a>
        @foreach ($elements as $element)
            @if (is_string($element))<span class="pg">…</span>@endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())<span class="pg on">{{ $page }}</span>
                    @else<a href="{{ $url }}">{{ $page }}</a>@endif
                @endforeach
            @endif
        @endforeach
        <a href="{{ $paginator->nextPageUrl() }}" class="{{ $paginator->hasMorePages() ? '' : 'off' }}" aria-label="{{ __('Next') }}"><x-admin.icon name="chevron" class="i-sm" /></a>
    </div>
</nav>
@elseif($paginator->total() > 0)
<nav class="pager"><span class="muted small">{{ __('Showing :from–:to of :total', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</span></nav>
@endif
