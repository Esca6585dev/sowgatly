<?php

namespace App\Http\Controllers\AdminControllers\Attribute;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute;
use App\Models\Category;
use App\Http\Requests\AttributeRequest;

class AttributeController extends Controller
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

        $attributes = Attribute::orderByDesc('id')->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = Attribute::fillableData();
    
                $attributes = Attribute::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.attribute.attribute-table', compact('attributes', 'pagination'))->render();
        }

        return view('admin-panel.attribute.attribute', compact('attributes', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Attribute $attribute)
    {
        $parentCategories = Category::parentCategory();

        return view('admin-panel.attribute.attribute-form', compact('attribute', 'parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, AttributeRequest $request)
    {   
        $attribute = new Attribute;

        $attribute->type = $request->type;
        $attribute->value = $request->value;
        $attribute->category_id = $request->category_id;

        $attribute->save();

        return redirect()->route('attribute.index', app()->getlocale() )->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Attribute $attribute
     * @return \Illuminate\Http\Response
     */
    public function show($lang, Attribute $attribute)
    {
        $parentCategories = Category::parentCategory();

        return view('admin-panel.attribute.attribute-show', compact('attribute', 'parentCategories'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Attribute $attribute
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Attribute $attribute)
    {
        $parentCategories = Category::parentCategory();

        return view('admin-panel.attribute.attribute-form', compact('attribute', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Attribute $attribute
     * @return \Illuminate\Http\Response
     */
    public function update($lang, AttributeRequest $request, Attribute $attribute)
    {
        $attribute->type = $request->type;
        $attribute->value = $request->value;
        $attribute->category_id = $request->category_id;

        $attribute->update();

        return redirect()->route('attribute.index', app()->getlocale() )->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Attribute $attribute
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('attribute.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }
}
