<?php

namespace App\Http\Controllers\AdminControllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Customers fill their carts from the app; the admin panel only lists,
 * inspects and deletes them. create/store/edit/update go back to the list.
 */
class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));
        $state = (string) $request->input('state');

        $carts = Cart::with('user:id,name,phone_number,email')
            ->withCount('items')
            ->withSum('items as items_quantity', 'quantity')
            ->withSum('items as items_total', DB::raw('quantity * price'))
            ->withMax('items as items_updated_at', 'updated_at')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    if (ctype_digit($search)) {
                        $q->orWhere('id', (int) $search);
                    }
                    $q->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($state === 'filled', fn ($q) => $q->has('items'))
            ->when($state === 'empty', fn ($q) => $q->doesntHave('items'))
            ->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.cart.cart-table', compact('carts', 'pagination'));
        }

        return view('admin-panel.cart.cart', compact('carts', 'pagination'));
    }

    public function create($lang)
    {
        return redirect()->route('cart.index', $lang);
    }

    public function store($lang)
    {
        return redirect()->route('cart.index', $lang);
    }

    public function show($lang, Cart $cart)
    {
        $cart->load(['user', 'items' => fn ($q) => $q->orderBy('id'), 'items.product.images', 'items.product.shop:id,name']);

        return view('admin-panel.cart.cart-show', compact('cart'));
    }

    public function edit($lang, Cart $cart)
    {
        return redirect()->route('cart.show', [$lang, $cart->id]);
    }

    public function update($lang, Cart $cart)
    {
        return redirect()->route('cart.show', [$lang, $cart->id]);
    }

    public function destroy($lang, Cart $cart)
    {
        // cart_items are removed by the foreign key's ON DELETE CASCADE; delete
        // them explicitly too so SQLite without FK enforcement stays clean.
        $cart->items()->delete();
        $cart->delete();

        return redirect()->route('cart.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }
}
