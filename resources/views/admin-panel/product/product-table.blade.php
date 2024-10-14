<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Price') }}</th>
                <th>{{ __('Discount') }}</th>
                <th>{{ __('Discount') }} {{ __('Price') }}</th>
                <th>{{ __('Shop name') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr id="datatable">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product->name }}</td>
                <td>{!! Str::limit($product->description, 10) !!}</td>
                <td>{{ $product->price }} TMT</td>
                <td>{{ $product->discount }}%</td>
                <td>{{ $product->getDiscountedPrice() }} TMT</td>
                <td><a href="{{ route('product.show', [app()->getlocale(), $product->shop->id]) }}">{{ $product->shop->name }}</a></td>
                <td>
                    <span
                        class="badge badge-primary">{{ $product->category->{ 'name_' . app()->getlocale() } }}</span>
                </td>
                <td>
                    <span
                        class="badge badge-warning">{{ $product->code }}</span>
                </td>
                <td>
                    @if($product->status)
                    <span class="badge badge-success">{{ __('Active') }}</span>
                    @else
                    <span class="badge badge-danger">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td>
                    <span
                        class="badge badge-secondary">{{ \Carbon::parse($product->created_at)->locale(config('app.faker_locales.' . app()->getlocale() ))->isoFormat('LLLL') }}</span>
                </td>
                <td>@include('admin-panel.product.product-action', [ $product ])</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-end">
        <div>
            {{ $products->links('layouts.pagination') }}
        </div>
    </div>
</div>
