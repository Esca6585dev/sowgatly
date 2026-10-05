@php $l = app()->getLocale(); @endphp
@if($addresses->isEmpty())
    <x-admin.empty icon="pin" :text="__('No addresses found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Shop') }}</th><th>{{ __('Address') }}</th><th>{{ __('Postal code') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($addresses as $address)
            @php $logo = \App\Http\Controllers\AdminControllers\Shop\ShopController::imageUrl(optional($address->shop)->image); @endphp
            <tr>
                <td>
                    @if($address->shop)
                    <a class="who" href="{{ route('shop.show', [$l, $address->shop->id]) }}">
                        @if($logo)<img class="thumb" src="{{ $logo }}" alt="" loading="lazy">@else<span class="thumb"><x-admin.icon name="shop" /></span>@endif
                        <b>{{ $address->shop->name }}</b>
                    </a>
                    @else<span class="muted">—</span>@endif
                </td>
                <td><a href="{{ route('address.show', [$l, $address->id]) }}">{{ $address->address_name ?: '—' }}</a></td>
                <td class="num nowrap">{{ $address->postal_code ?: '—' }}</td>
                <td class="right"><x-admin.row-actions route="address" :model="$address->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $addresses->links('layouts.pagination') }}
@endif
