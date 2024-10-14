<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Username') }}</th>
                <th>{{ __('Region Name') }}</th>
                <th>{{ __('Parent Region') }}</th>
                <th>{{ __('Address') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($regions as $region)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $region->name }}</td>
                    <td>{{ $region->parent->name ?? __('N/A') }}</td>
                    <td>
                        @if($region->address)
                            {{ $region->address->street }},
                            {{ $region->address->city }},
                            {{ $region->address->state }},
                            {{ $region->address->country }},
                            {{ $region->address->postal_code }}
                        @else
                            {{ __('No address available') }}
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $region->created_at->locale(config('app.faker_locales.' . app()->getLocale()))->isoFormat('LLLL') }}
                        </span>
                    </td>
                    <td>
                        @include('admin-panel.region.region-action', ['region' => $region])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ __('No regions found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-end">
        {{ $regions->links('layouts.pagination') }}
    </div>
</div>