<?php

namespace App\Http\Controllers\AdminControllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Admin CRUD for categories. URLs carry a {categoryType} segment
 * (all | parent | sub) that filters the list and shapes the form.
 */
class CategoryController extends Controller
{
    public const TYPES = ['all', 'parent', 'sub'];

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang, $categoryType)
    {
        $this->checkType($categoryType);

        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $categories = Category::with('parent:id,name_tm,name_en,name_ru')
            ->withCount('categories')
            ->addSelect(['products_count' => Product::selectRaw('count(*)')->whereColumn('products.category_id', 'categories.id')])
            ->when($categoryType === 'parent', fn ($q) => $q->whereNull('category_id'))
            ->when($categoryType === 'sub', fn ($q) => $q->whereNotNull('category_id'))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name_tm', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('name_ru', 'like', "%{$search}%")))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.category.category-table', compact('categories', 'categoryType', 'pagination'));
        }

        return view('admin-panel.category.category', compact('categories', 'categoryType', 'pagination'));
    }

    public function create(Request $request, $lang, $categoryType)
    {
        $this->checkType($categoryType);

        return $this->form(new Category(['category_id' => $request->query('parent')]), $categoryType);
    }

    public function store($lang, $categoryType, CategoryRequest $request)
    {
        $this->checkType($categoryType);

        $category = new Category($request->safe()->only(['name_tm', 'name_en', 'name_ru']));
        $category->category_id = $request->validated('category_id');
        if ($request->hasFile('image')) {
            $category->image = ImageUploader::store($request->file('image'), 'categories');
        }
        $category->save();

        return redirect()->route('category.show', [app()->getLocale(), $categoryType, $category->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, $categoryType, Category $category)
    {
        $this->checkType($categoryType);

        $category->load('parent')->loadCount('categories');
        $subcategories = $category->categories()
            ->addSelect(['products_count' => Product::selectRaw('count(*)')->whereColumn('products.category_id', 'categories.id')])
            ->orderBy('name_' . $this->locale())
            ->get();
        // A parent category shows the products of its subcategories too (as the product list filter does).
        $ids = $subcategories->pluck('id')->push($category->id);
        $productsCount = Product::whereIn('category_id', $ids)->count();
        $products = Product::with('images')->whereIn('category_id', $ids)->latest('id')->take(6)->get();

        return view('admin-panel.category.category-show', compact('category', 'categoryType', 'subcategories', 'products', 'productsCount'));
    }

    public function edit($lang, $categoryType, Category $category)
    {
        $this->checkType($categoryType);

        return $this->form($category, $categoryType);
    }

    public function update($lang, $categoryType, CategoryRequest $request, Category $category)
    {
        $this->checkType($categoryType);

        $category->fill($request->safe()->only(['name_tm', 'name_en', 'name_ru']));
        // The "parent" form has no parent select: a parent category stays a parent.
        if ($request->has('category_id') || $categoryType !== 'parent') {
            $category->category_id = $request->validated('category_id');
        }
        if ($request->hasFile('image')) {
            $this->deleteImage($category->image);
            $category->image = ImageUploader::store($request->file('image'), 'categories');
        }
        $category->save();

        return redirect()->route('category.show', [app()->getLocale(), $categoryType, $category->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, $categoryType, Category $category)
    {
        $this->checkType($categoryType);

        $ids = $category->categories()->pluck('id')->push($category->id);
        if (Product::whereIn('category_id', $ids)->exists()) {
            return back()->with('error', 'This category still has products. Move or delete them first.');
        }

        foreach ($category->categories as $child) {
            $this->deleteImage($child->image);
        }
        $this->deleteImage($category->image);
        // Subcategories and attributes are removed by the foreign keys (on delete cascade).
        $category->delete();

        return redirect()->route('category.index', [app()->getLocale(), $categoryType])->with('success-delete', 'The resource was deleted!');
    }

    private function form(Category $category, string $categoryType)
    {
        $parents = Category::whereNull('category_id')
            ->where('id', '!=', $category->id ?? 0)
            ->orderBy('name_' . $this->locale())
            ->get(['id', 'name_tm', 'name_en', 'name_ru']);

        return view('admin-panel.category.category-form', compact('category', 'categoryType', 'parents'));
    }

    private function checkType($categoryType): void
    {
        abort_unless(in_array($categoryType, self::TYPES, true), 404);
    }

    private function locale(): string
    {
        return in_array(app()->getLocale(), ['tm', 'en', 'ru'], true) ? app()->getLocale() : 'tm';
    }

    /**
     * New images live on the public disk (storage/categories/...). Older uploads were
     * moved into public/category/<folder>/; seeded ones (category/category-seeder) stay.
     */
    private function deleteImage(?string $path): void
    {
        if (! $path) {
            return;
        }
        if (Str::startsWith($path, 'storage/')) {
            ImageUploader::delete($path);
        } elseif (Str::startsWith($path, 'category/') && ! Str::startsWith($path, 'category/category-seeder/') && substr_count($path, '/') === 2) {
            File::deleteDirectory(public_path(dirname($path)));
        }
    }
}
