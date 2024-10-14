<?php

namespace App\Http\Controllers\AdminControllers\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Http\Requests\CartRequest;

class CartController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $lang, $pagination = 10)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        $carts = Cart::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Cart::fillableData();
    
                $carts = Cart::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.cart.cart-table', compact('carts', 'pagination'))->render();
        }

        return view('admin-panel.cart.cart', compact('carts', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect()->route('cart.index', [ app()->getlocale() ])->with('warning', 'Something went wrong.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return redirect()->route('cart.index', [ app()->getlocale() ])->with('warning', 'Something went wrong.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function show($lang, $id)
    {
        $cart = Cart::withTrashed()->find($id);

        return view('admin-panel.cart.cart-show', compact('cart'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect()->route('cart.index', [ app()->getlocale() ])->with('warning', 'Something went wrong.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('cart.index', [ app()->getlocale() ])->with('warning', 'Something went wrong.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, $id)
    {
        $cart = Cart::withTrashed()->find($id);
        
        $cart->forceDelete();

        return redirect()->route('cart.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }
}
