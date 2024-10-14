<?php

namespace App\Http\Controllers\AdminControllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Seller;
use App\Http\Requests\ShopRequest;
use Str;

class ShopController extends Controller
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

        $shops = Shop::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Shop::fillableData();
    
                $shops = Shop::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.shop.shop-table', compact('shops', 'pagination'))->render();
        }

        return view('admin-panel.shop.shop', compact('shops', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Shop $shop)
    {
        $sellers = Seller::all();

        return view('admin-panel.shop.shop-form', compact('shop', 'sellers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, ShopRequest $request)
    {   
        $shop = new Shop;

        $this->uploadImage($shop, $request);
        
        $shop->name = $request->name;
        $shop->email = $request->email;
        $shop->address = $request->address;
        $shop->mon_fri_open = $request->mon_fri_open;
        $shop->mon_fri_close = $request->mon_fri_close;
        $shop->sat_sun_open = $request->sat_sun_open;
        $shop->sat_sun_close = $request->sat_sun_close;
        $shop->seller_id = $request->seller_id;

        $shop->save();

        return redirect()->route('shop.index', app()->getlocale() )->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show($lang, Shop $Shop)
    {
        $sellers = Seller::all();

        return view('admin-panel.shop.shop-form', compact('shop', 'sellers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Shop $shop)
    {
        $sellers = Seller::all();

        return view('admin-panel.shop.shop-form', compact('shop', 'sellers'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update($lang, ShopRequest $request, Shop $shop)
    {
        $this->uploadImage($shop, $request);
        
        $shop->name = $request->name;
        $shop->email = $request->email;
        $shop->address = $request->address;
        $shop->mon_fri_open = $request->mon_fri_open;
        $shop->mon_fri_close = $request->mon_fri_close;
        $shop->sat_sun_open = $request->sat_sun_open;
        $shop->sat_sun_close = $request->sat_sun_close;
        $shop->seller_id = $request->seller_id;

        $shop->update();

        return redirect()->route('shop.index', app()->getlocale() )->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Shop $shop)
    {
        $this->deleteFolder($shop);

        $shop->delete();

        return redirect()->route('shop.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }

    public function deleteFolder($shop)
    {
        if($shop->image){
            $folder = explode('/', $shop->image);

            if($folder[1] != 'shop-seeder'){
                \File::deleteDirectory($folder[0] . '/' . $folder[1]);
            }
        }
    }

    public function uploadImage($shop, $request)
    {
        if($request->file('image')){
            $this->deleteFolder($shop);

            $image = $request->file('image');

            $date = date("d-m-Y H-i-s");
            
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'shop/' . Str::slug($request->name . '-' . $date ) . '/';

            $image->move($path, $fileName);
            
            $originalImage = $path . $fileName;

            $shop->image = $originalImage;
        }
    }
}
