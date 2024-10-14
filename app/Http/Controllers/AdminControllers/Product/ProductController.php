<?php

namespace App\Http\Controllers\AdminControllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Image;
use App\Models\Shop;
use Str;

class ProductController extends Controller
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

        $products = Product::orderByDesc('id')->paginate($pagination);
        
        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Product::fillableData();
    
                $products = Product::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.product.product-table', compact('products', 'pagination'))->render();
        }

        return view('admin-panel.product.product', compact('products', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Product $product)
    {
        $parentCategories = Category::parentCategory();
        $shops = Shop::all();

        return view('admin-panel.product.product-form', compact('product','parentCategories', 'shops'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, ProductRequest $request)
    {   
        $product = new Product;

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->discount = $request->discount;
        $product->category_id = $request->category_id;
        $product->status = $request->status;

        $product->attributes = [
            'color' => $request->color_name,
            'size' => $request->size,
            'weight' => $request->weight,
        ];

        $product->code = Str::random(6);

        $product->save();

        $this->uploadImages($product->id, $request);
        
        return redirect()->route('product.index', app()->getlocale() )->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($lang, Product $product)
    {
        $parentCategories = Category::parentCategory();
        $shops = Shop::all();

        return view('admin-panel.product.product-show', compact('product', 'parentCategories', 'shops'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Product $product)
    {
        $parentCategories = Category::parentCategory();
        $shops = Shop::all();

        return view('admin-panel.product.product-form', compact('product','parentCategories', 'shops'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update($lang, ProductRequest $request, Product $product)
    {
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->discount = $request->discount;
        $product->category_id = $request->category_id;
        $product->status = $request->status;

        $product->attributes = [
            'color' => $request->color_name,
            'size' => $request->size,
            'weight' => $request->weight,
        ];

        $product->update();

        $this->uploadImages($product->id, $request);
        
        return redirect()->route('product.index', [ app()->getlocale() ])->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Product $product)
    {
        $this->deleteFolder($product->id);

        $product->delete();

        return redirect()->route('product.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }

    public function deleteFolder($product_id)
    {
        $image = Image::where('product_id', $product_id)->first();
        $images = Image::where('product_id', $product_id)->get();
        
        if($image){
            $folder = explode('/', $image->image);
            
            if($folder[1] != 'product-seeder'){
                \File::deleteDirectory($folder[0] . '/' . $folder[1]);
            }

            $images->each->delete();
        }
    }

    public function uploadImages($product_id, $request)
    {
        if($request->file('images')){
            $this->deleteFolder($product_id);

            $images = $request->file('images');

            foreach($images as $image){
                $date = date("d-m-Y-H-i-s");

                $fileRandName = Str::random(10);
                $fileExt = $image->getClientOriginalExtension();
    
                $fileName = $fileRandName . '.' . $fileExt;
                
                $path = 'product/' . Str::slug($request->name . '-' . $date ) . '/';
    
                $image->move($path, $fileName);
                
                $originalImage = $path . $fileName;

                $image = new Image;

                $image->image = $originalImage;
                $image->product_id = $product_id;

                $image->save();
            }
        }
    }
}
