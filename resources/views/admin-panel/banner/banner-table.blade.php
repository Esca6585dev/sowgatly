<div id="datatable">
    <table class="table table-separate table-head-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Image') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Link') }}</th>
                <th>{{ __('Region') }}</th>
                <th>{{ __('Position') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Period') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($banners as $banner)
            <tr>
                <td>{{ $banner->id }}</td>
                <td>@if($banner->image)<img src="{{ asset($banner->image) }}" alt="" style="height:40px;border-radius:4px">@else — @endif</td>
                <td>{{ $banner->title_tm }}<br><small class="text-muted">{{ $banner->subtitle_tm }}</small></td>
                <td>{{ $banner->link_type === 'none' ? '—' : $banner->link_type . ': ' . $banner->link_value }}</td>
                <td>{{ optional($banner->region)->name ?? __('All cities') }}</td>
                <td>{{ $banner->position }}</td>
                <td><span class="badge badge-{{ $banner->is_active ? 'success' : 'secondary' }}">{{ $banner->is_active ? __('Active') : __('Inactive') }}</span></td>
                <td><small>{{ optional($banner->starts_at)->format('d.m.Y') ?? '…' }} – {{ optional($banner->ends_at)->format('d.m.Y') ?? '…' }}</small></td>
                <td class="d-flex">
                    <a href="{{ route('banner.edit', [ app()->getlocale(), $banner->id ]) }}" class="btn btn-sm btn-light-primary mr-2">{{ __('Edit') }}</a>
                    <form method="post" action="{{ route('banner.destroy', [ app()->getlocale(), $banner->id ]) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this resource?') }}')">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-sm btn-light-danger">{{ __('Delete') }}</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center text-muted">{{ __('No banners yet') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-end"><div>{{ $banners->links('layouts.pagination') }}</div></div>
</div>
