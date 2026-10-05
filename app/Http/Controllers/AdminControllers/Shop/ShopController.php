<?php

namespace App\Http\Controllers\AdminControllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Models\Region;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    /** Columns the admin form writes (image is handled separately). */
    private const FIELDS = [
        'name', 'email', 'phone', 'mon_fri_open', 'mon_fri_close', 'sat_sun_open', 'sat_sun_close',
        'user_id', 'region_id', 'delivery_fee', 'pickup_available', 'min_order_amount',
        'description_tm', 'description_ru', 'description_en', 'status',
    ];

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    /**
     * Public URL of a stored shop/product image. Handles both formats in use:
     * legacy paths relative to public/ (seeders, old admin uploads: "shop/…")
     * and paths on the public disk (API and new admin uploads: "shops/…").
     */
    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/') || is_file(public_path($path))) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $regionId = (int) $request->input('region_id');

        $shops = Shop::with(['user:id,name,phone_number', 'region:id,name'])
            ->withCount(['products', 'orders'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('phone_number', 'like', "%{$search}%"))))
            ->when(in_array($status, Shop::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($regionId > 0, fn ($q) => $q->where('region_id', $regionId))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.shop.shop-table', compact('shops', 'pagination'));
        }

        $regions = $this->regions();

        return view('admin-panel.shop.shop', compact('shops', 'pagination', 'regions'));
    }

    public function create($lang)
    {
        return $this->form(new Shop([
            'status' => 'approved', 'delivery_fee' => 20, 'pickup_available' => false,
            'mon_fri_open' => '09:00', 'mon_fri_close' => '18:00', 'sat_sun_open' => '10:00', 'sat_sun_close' => '16:00',
        ]));
    }

    public function store($lang, ShopRequest $request)
    {
        $this->validateOwner($request);

        $shop = new Shop;
        $this->fill($shop, $request);
        $this->uploadImage($shop, $request);
        $shop->save();

        return redirect()->route('shop.show', [app()->getLocale(), $shop->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Shop $shop)
    {
        $shop->load(['user:id,name,phone_number,email', 'region.parent', 'address'])->loadCount(['products', 'orders']);

        $products = $shop->products()->with('images')->latest('id')->take(6)->get();
        $orders = $shop->orders()->with('user:id,name,phone_number')->latest('id')->take(6)->get();
        $revenue = $shop->orders()->where('status', '!=', 'cancelled')->sum('total_amount');

        return view('admin-panel.shop.shop-show', compact('shop', 'products', 'orders', 'revenue'));
    }

    public function edit($lang, Shop $shop)
    {
        return $this->form($shop);
    }

    /**
     * Full form save, or a quick status change from the show page (PUT with only `status`).
     */
    public function update($lang, ShopRequest $request, Shop $shop)
    {
        $this->validateOwner($request, $shop);

        $this->fill($shop, $request);
        $this->uploadImage($shop, $request);
        $shop->save();

        return redirect()->route('shop.show', [app()->getLocale(), $shop->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Shop $shop)
    {
        $this->deleteImage($shop->image);

        $shop->delete();

        return redirect()->route('shop.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Shop $shop)
    {
        // One shop per account: offer users without a shop plus the current owner.
        $sellers = User::orderBy('name')
            ->where(fn ($q) => $q->whereDoesntHave('shop')->orWhere('id', $shop->user_id ?? 0))
            ->get(['id', 'name', 'phone_number']);
        $regions = $this->regions();

        return view('admin-panel.shop.shop-form', compact('shop', 'sellers', 'regions'));
    }

    private function regions()
    {
        return Region::orderBy('name')->get(['id', 'name', 'type']);
    }

    /** Only the fields present in the request are written, so a status-only PUT changes nothing else. */
    private function fill(Shop $shop, ShopRequest $request): void
    {
        $data = collect($request->validated())->only(self::FIELDS);

        if ($data->has('delivery_fee') && $data['delivery_fee'] === null) {
            $data['delivery_fee'] = 0;
        }
        if ($data->has('pickup_available')) {
            $data['pickup_available'] = $request->boolean('pickup_available');
        }
        if (! $shop->exists && ! $data->has('status')) {
            $data['status'] = 'approved';
        }

        $shop->fill($data->all());
    }

    /** A user can own one shop (User::shop is hasOne). Admin-only rule; the API does not send user_id. */
    private function validateOwner(Request $request, ?Shop $shop = null): void
    {
        $request->validate([
            'user_id' => [Rule::unique('shops', 'user_id')->ignore(optional($shop)->id)],
        ], [
            'user_id.unique' => __('This user already owns another shop.'),
        ]);
    }

    /** New uploads go to the public disk (`shops/…`), the same place the API reads them from. */
    private function uploadImage(Shop $shop, ShopRequest $request): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $old = $shop->image;
        $shop->image = $request->file('image')->store('shops', 'public');
        $this->deleteImage($old);
    }

    private function deleteImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, 'shop/shop-seeder/')) {
            return; // seeded images are shared between shops
        }

        if (is_file(public_path($path))) {
            // Legacy admin upload: public/shop/{slug-date}/{file}, one folder per upload.
            $dir = dirname($path);
            Str::startsWith($path, 'shop/') && $dir !== 'shop' ? File::deleteDirectory(public_path($dir)) : File::delete(public_path($path));

            return;
        }

        Storage::disk('public')->delete($path);
    }
}
