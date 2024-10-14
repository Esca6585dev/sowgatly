<?php

namespace App\Http\Controllers\AdminControllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Image;
use Str;

class CategoryController extends Controller
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
    public function index(Request $request, $lang, $categoryType, $pagination = 10)
    {
        if($request->pagination) {
            $pagination = (int)$request->pagination;
        }

        if($categoryType == 'all'){
            $categories = Category::orderByDesc('id')->paginate($pagination);
        } else if($categoryType == 'parent'){
            $categories = Category::whereNull('category_id')->orderByDesc('id')->paginate($pagination);
        } else if($categoryType == 'sub'){
            $categories = Category::whereNotNull('category_id')->orderByDesc('id')->paginate($pagination);
        } 
        
        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Category::fillableData();
    
                $categories = Category::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.category.category-table', compact('categories', 'categoryType', 'pagination'))->render();
        }

        return view('admin-panel.category.category', compact('categories', 'categoryType', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, $categoryType, Category $category)
    {
        $parentCategories = Category::all();

        return view('admin-panel.category.category-form', compact('category', 'categoryType', 'parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, $categoryType, CategoryRequest $request)
    {
        if($request->file('image')){
            $image = $request->file('image');
            
            $date = date("d-m-Y H-i-s");
            
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'category/' . Str::slug($request->name_tm . '-' . $date ) . '/';

            $image->move($path, $fileName);
            
            $originalImage = $path . $fileName;
        }
        
        $category = new Category;
        
        $category->name_tm = $request->name_tm;
        $category->name_en = $request->name_en;
        $category->name_ru = $request->name_ru;
        $category->category_id = $request->category_id;

        $category->image = $originalImage ?? null;

        $category->save();
        
        return redirect()->route('category.index', [ app()->getlocale(), $categoryType ])->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($lang, $categoryType, Category $category)
    {
        return view('admin-panel.category.category-show', compact('category', 'categoryType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, $categoryType, Category $category)
    {
        $parentCategories = Category::all();

        return view('admin-panel.category.category-form', compact('category', 'categoryType', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update($lang, $categoryType, CategoryRequest $request, Category $category)
    {
        if($request->file('image')){
            $this->deleteFolder($category);

            $image = $request->file('image');
            
            $date = date("d-m-Y H-i-s");
            
            $fileRandName = Str::random(10);
            $fileExt = $image->getClientOriginalExtension();

            $fileName = $fileRandName . '.' . $fileExt;
            
            $path = 'category/' . Str::slug($request->name_tm . '-' . $date ) . '/';

            $image->move($path, $fileName);
            
            $originalImage = $path . $fileName;

            $category->image = $originalImage;
        }

        $category->name_tm = $request->name_tm;
        $category->name_en = $request->name_en;
        $category->name_ru = $request->name_ru;
        $category->category_id = $request->category_id;
        
        $category->update();
        
        return redirect()->route('category.index', [ app()->getlocale(), $categoryType ])->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, $categoryType, Category $category)
    {
        $this->deleteFolder($category);

        $category->delete();

        return redirect()->route('category.index', [ app()->getlocale(), $categoryType ])->with('success-delete', 'The resource was deleted!');
    }

    public function deleteFolder($category)
    {
        if($category->image){
            $folder = explode('/', $category->image);

            if($folder[1] != 'category-seeder'){
                \File::deleteDirectory($folder[0] . '/' . $folder[1]);
            }
        }
    }
}
