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
                
                $products = Product::where(function($q) use($searchQuery) {
                                        foreach (['name_tm', 'name_en', 'name_ru', 'description_tm', 'description_en', 'description_ru'] as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->orderByDesc('id')->paginate($pagination);
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
        $product = Product::create($this->productData($request));

        $this->uploadImages($product, $request);

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
        $product->update($this->productData($request));

        $this->uploadImages($product, $request);

        return redirect()->route('product.index', [ app()->getlocale() ])->with('success-update', 'The resource was updated!');
    }

    /**
     * Columns of the products table that the form may set.
     */
    private function productData(ProductRequest $request): array
    {
        return $request->only([
            'name_tm', 'name_en', 'name_ru',
            'description_tm', 'description_en', 'description_ru',
            'price', 'discount', 'stock', 'production_time', 'min_order',
            'shop_id', 'category_id', 'status', 'seller_status',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Product $product)
    {
        $this->deleteImages($product);
        $product->delete();

        return redirect()->route('product.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }

    /**
     * Remove the product's uploaded images (files and rows). Seeder images
     * under product/product-seeder are shared and never deleted.
     */
    public function deleteImages(Product $product)
    {
        foreach ($product->images as $image) {
            $path = (string) $image->url;
            if ($path !== '' && !str_starts_with($path, 'http') && !str_contains($path, 'product-seeder')) {
                \File::delete(public_path($path));
            }
            $image->delete();
        }
    }

    public function uploadImages(Product $product, $request)
    {
        if (!$request->hasFile('images')) {
            return;
        }

        $this->deleteImages($product);

        $folder = 'product/' . Str::slug($product->name_en . '-' . date('d-m-Y-H-i-s')) . '/';

        foreach ($request->file('images') as $file) {
            $fileName = Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($folder), $fileName);

            Image::create([
                'product_id' => $product->id,
                'url' => $folder . $fileName,
            ]);
        }
    }
}
