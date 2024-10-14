<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Username') }}</th>
                <th>{{ __('Shop name') }}</th>
                <th>{{ __('Contact') }}</th>
                <th>{{ __('Address') }}</th>
                <th>{{ __('Image') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($shops as $shop)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $shop->user->name }}</td>
                    <td>{{ $shop->name }}</td>
                    <td>
                        <a href="mailto:{{ $shop->email }}">{{ $shop->email }}</a><br>
                        <a href="tel:+993{{ $shop->user->phone_number }}">
                            +993 {{ $shop->user->phone_number }}
                        </a>
                    </td>
                    <td>{{ $shop->address->address_name }} - {{ $shop->address->postal_code }}</td>
                    <td>
                        <img src="{{ asset($shop->image) }}" alt="{{ $shop->name }}" class="logo-circle" loading="lazy">
                    </td>
                    <td>
                        @if($shop->user->status)
                            <span class="badge badge-success">{{ __('Active') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('Inactive') }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $shop->created_at->locale(config('app.faker_locales.' . app()->getLocale()))->isoFormat('LLLL') }}
                        </span>
                    </td>
                    <td>
                        @include('admin-panel.shop.shop-action', ['shop' => $shop])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('No shops found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="d-flex justify-content-end">
        {{ $shops->links('layouts.pagination') }}
    </div>
</div>