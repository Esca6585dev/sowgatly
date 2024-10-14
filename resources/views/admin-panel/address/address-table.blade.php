<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Street') }}</th>
                <th>{{ __('Settlement') }}</th>
                <th>{{ __('District') }}</th>
                <th>{{ __('Province') }}</th>
                <th>{{ __('Region') }}</th>
                <th>{{ __('Country') }}</th>
                <th>{{ __('Postal Code') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($addresses as $address)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $address->street }}</td>
                    <td>{{ $address->settlement }}</td>
                    <td>{{ $address->district }}</td>
                    <td>{{ $address->province }}</td>
                    <td>{{ $address->region }}</td>
                    <td>{{ $address->country }}</td>
                    <td>{{ $address->postal_code }}</td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $address->created_at->locale(config('app.faker_locales.' . app()->getLocale()))->isoFormat('LLLL') }}
                        </span>
                    </td>
                    <td>
                        @include('admin-panel.address.address-action', ['address' => $address])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">{{ __('No addresses found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-end">
        {{ $addresses->links('layouts.pagination') }}
    </div>
</div>