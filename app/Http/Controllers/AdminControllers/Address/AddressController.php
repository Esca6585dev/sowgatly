<?php

namespace App\Http\Controllers\AdminControllers\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Shop addresses: one row per shop (addresses.shop_id is unique).
 */
class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $addresses = Address::with('shop:id,name,image,status')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('address_name', 'like', "%{$search}%")
                ->orWhere('postal_code', 'like', "%{$search}%")
                ->orWhereHas('shop', fn ($s) => $s->where('name', 'like', "%{$search}%"))))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.address.address-table', compact('addresses', 'pagination'));
        }

        return view('admin-panel.address.address', compact('addresses', 'pagination'));
    }

    public function create(Request $request, $lang)
    {
        return $this->form(new Address(['shop_id' => $request->integer('shop_id') ?: null]));
    }

    public function store($lang, AddressRequest $request)
    {
        $this->validateOnePerShop($request);

        $address = Address::create($request->validated());

        return redirect()->route('address.show', [app()->getLocale(), $address->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Address $address)
    {
        $address->load(['shop.user:id,name,phone_number', 'shop.region:id,name']);

        return view('admin-panel.address.address-show', compact('address'));
    }

    public function edit($lang, Address $address)
    {
        return $this->form($address);
    }

    public function update($lang, AddressRequest $request, Address $address)
    {
        $this->validateOnePerShop($request, $address);

        $address->update($request->validated());

        return redirect()->route('address.show', [app()->getLocale(), $address->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Address $address)
    {
        $address->delete();

        return redirect()->route('address.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Address $address)
    {
        // Shops without an address, plus the one this address belongs to.
        $shops = Shop::orderBy('name')
            ->where(fn ($q) => $q->whereDoesntHave('address')->orWhere('id', $address->shop_id ?? 0))
            ->get(['id', 'name']);

        return view('admin-panel.address.address-form', compact('address', 'shops'));
    }

    /** Admin-only check (the API request is shared and left as it is): the column is unique. */
    private function validateOnePerShop(Request $request, ?Address $address = null): void
    {
        $request->validate([
            'shop_id' => [Rule::unique('addresses', 'shop_id')->ignore(optional($address)->id)],
        ], [
            'shop_id.unique' => __('This shop already has an address.'),
        ]);
    }
}
