@php $l = app()->getLocale(); @endphp
@if($shops->isEmpty())
    <x-admin.empty icon="shop" :text="__('No shops found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Shop') }}</th><th>{{ __('Owner') }}</th><th>{{ __('Region') }}</th><th class="right">{{ __('Products') }}</th><th class="right">{{ __('Orders') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($shops as $shop)
            @php $logo = \App\Http\Controllers\AdminControllers\Shop\ShopController::imageUrl($shop->image); @endphp
            <tr>
                <td>
                    <a class="who" href="{{ route('shop.show', [$l, $shop->id]) }}">
                        @if($logo)<img class="thumb" src="{{ $logo }}" alt="" loading="lazy">@else<span class="thumb"><x-admin.icon name="shop" /></span>@endif
                        <div><b>{{ $shop->name }}</b>@if($shop->phone)<small class="nowrap">+993 {{ $shop->phone }}</small>@elseif($shop->email)<small>{{ $shop->email }}</small>@endif</div>
                    </a>
                </td>
                <td>
                    @if($shop->user)
                    <div>{{ $shop->user->name }}<small class="muted nowrap" style="display:block;font-size:12px">+993 {{ $shop->user->phone_number }}</small></div>
                    @else<span class="muted">—</span>@endif
                </td>
                <td class="muted">{{ $shop->region->name ?? '—' }}</td>
                <td class="right num">{{ $shop->products_count }}</td>
                <td class="right num">{{ $shop->orders_count }}</td>
                <td><x-admin.status :value="$shop->status ?? 'approved'" /></td>
                <td class="right"><x-admin.row-actions route="shop" :model="$shop->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $shops->links('layouts.pagination') }}
@endif
