<?php

namespace App\Http\Controllers\AdminControllers\Attribute;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Admin CRUD for category attributes: a type ("Colour", "Size") with a value,
 * optionally tied to a category. Columns: type, value, category_id.
 */
class AttributeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        // Named $attrs: $attributes is reserved inside Blade components.
        $attrs = Attribute::with('category.parent')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('type', 'like', "%{$search}%")
                ->orWhere('value', 'like', "%{$search}%")))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', (int) $request->input('category_id')))
            ->orderBy('type')->orderBy('value')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.attribute.attribute-table', compact('attrs', 'pagination'));
        }

        $categories = $this->categoryOptions();

        return view('admin-panel.attribute.attribute', compact('attrs', 'pagination', 'categories'));
    }

    public function create($lang)
    {
        return $this->form(new Attribute());
    }

    public function store($lang, AttributeRequest $request)
    {
        $attribute = Attribute::create($request->validated());

        return redirect()->route('attribute.show', [app()->getLocale(), $attribute->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Attribute $attribute)
    {
        $attribute->load('category.parent');
        $siblings = Attribute::where('type', $attribute->type)
            ->where('id', '!=', $attribute->id)
            ->with('category')
            ->orderBy('value')
            ->take(20)
            ->get();

        return view('admin-panel.attribute.attribute-show', compact('attribute', 'siblings'));
    }

    public function edit($lang, Attribute $attribute)
    {
        return $this->form($attribute);
    }

    public function update($lang, AttributeRequest $request, Attribute $attribute)
    {
        $attribute->update($request->validated());

        return redirect()->route('attribute.show', [app()->getLocale(), $attribute->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('attribute.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Attribute $attribute)
    {
        $categories = $this->categoryOptions();
        $types = Attribute::query()->distinct()->orderBy('type')->pluck('type');

        return view('admin-panel.attribute.attribute-form', compact('attribute', 'categories', 'types'));
    }

    /** [id => "Parent › Child"] for every category, parents first. */
    private function categoryOptions()
    {
        $key = 'name_' . (in_array(app()->getLocale(), ['tm', 'en', 'ru'], true) ? app()->getLocale() : 'tm');

        $options = [];
        foreach (Category::whereNull('category_id')->with('categories')->orderBy($key)->get() as $parent) {
            $options[$parent->id] = $parent->$key;
            foreach ($parent->categories->sortBy($key) as $child) {
                $options[$child->id] = $parent->$key . ' › ' . $child->$key;
            }
        }

        return $options;
    }
}
