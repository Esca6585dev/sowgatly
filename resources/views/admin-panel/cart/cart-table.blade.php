<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('First Name') }}</th>
                <th>{{ __('Last Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Phone number') }}</th>
                <th>{{ __('IP Address') }}</th>
                <th>TDS №</th>
                <th>{{ __('Accepted') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($carts as $cart)
            <tr id="datatable">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $cart->user->first_name }}</td>
                <td>{{ $cart->user->last_name }}</td>
                <td>{{ $cart->user->email }}</td>
                <td>+993 {{ $cart->user->phone_number }}</td>
                <td>{{ $cart->ip_address }}</td>
                <td>{{ $cart->standart->number }}</td>
                <td>
                    @if($cart->deleted_at)
                    <span class="badge badge-primary">Arza ugratdy</span>
                    @else
                    <span class="badge badge-success">Sebede goşuldy</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-secondary">{{ \Carbon::parse($cart->created_at)->locale(config('app.faker_locales.' . app()->getlocale() ))->isoFormat('LLLL') }}</span>
                </td>
                <td>@include('admin-panel.cart.cart-action', [ $cart ])</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-end">
        <div>
            {{ $carts->links('layouts.pagination') }}
        </div>
    </div>                                
</div>
