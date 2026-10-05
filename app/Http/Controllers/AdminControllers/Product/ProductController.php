<?php

namespace App\Http\Controllers\AdminControllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));
        $status = (string) $request->input('status');

        $products = Product::with([
                'shop:id,name',
                'category:id,name_tm,name_en,name_ru',
                'images' => fn ($q) => $q->orderBy('id'),
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    if (ctype_digit($search)) {
                        $q->orWhere('id', (int) $search);
                    }
                    foreach (['name_tm', 'name_en', 'name_ru', 'description_tm', 'description_en', 'description_ru'] as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            })
            ->when($request->filled('shop_id'), fn ($q) => $q->where('shop_id', (int) $request->input('shop_id')))
            ->when($request->filled('category_id'), function ($q) use ($request) {
                // A parent category also matches the products of its subcategories.
                $id = (int) $request->input('category_id');
                $ids = Category::where('category_id', $id)->pluck('id')->push($id);
                $q->whereIn('category_id', $ids);
            })
            ->when($status === 'active', fn ($q) => $q->where('status', true))
            ->when($status === 'inactive', fn ($q) => $q->where('status', false))
            ->when($status === 'hidden', fn ($q) => $q->where('seller_status', false))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.product.product-table', compact('products', 'pagination'));
        }

        $shops = Shop::orderBy('name')->pluck('name', 'id');
        $parentCategories = $this->categoryTree();

        return view('admin-panel.product.product', compact('products', 'pagination', 'shops', 'parentCategories'));
    }

    public function create($lang)
    {
        return $this->form(new Product(['status' => true, 'seller_status' => true]));
    }

    public function store($lang, ProductRequest $request)
    {
        $product = Product::create($this->productData($request));

        $this->uploadImages($product, $request);

        return redirect()->route('product.index', app()->getLocale())->with('success-create', 'The resource was created!');
    }

    public function show($lang, Product $product)
    {
        $product->load(['shop:id,name,image', 'category.parent', 'images' => fn ($q) => $q->orderBy('id'), 'attributes', 'brands', 'compositions'])
            ->loadAvg('reviews as reviews_avg', 'rating')
            ->loadCount(['reviews', 'orderItems']);

        return view('admin-panel.product.product-show', compact('product'));
    }

    public function edit($lang, Product $product)
    {
        $product->load(['images' => fn ($q) => $q->orderBy('id')]);

        return $this->form($product);
    }

    public function update($lang, ProductRequest $request, Product $product)
    {
        $product->update($this->productData($request));

        $this->uploadImages($product, $request);

        return redirect()->route('product.index', app()->getLocale())->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Product $product)
    {
        $this->deleteImages($product);
        $product->delete();

        return redirect()->route('product.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
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

    private function form(Product $product)
    {
        $shops = Shop::orderBy('name')->pluck('name', 'id');
        $parentCategories = $this->categoryTree();

        return view('admin-panel.product.product-form', compact('product', 'shops', 'parentCategories'));
    }

    /** Parent categories with their subcategories, for grouped selects. */
    private function categoryTree()
    {
        return Category::whereNull('category_id')
            ->with(['categories' => fn ($q) => $q->orderBy('name_' . app()->getLocale())])
            ->orderBy('name_' . app()->getLocale())
            ->get();
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

    /**
     * New uploads replace the product's current images. Files live under
     * public/product/{slug}/ and `images.url` keeps the relative path.
     */
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
