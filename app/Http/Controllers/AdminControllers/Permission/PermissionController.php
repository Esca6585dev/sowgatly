<?php

namespace App\Http\Controllers\AdminControllers\Permission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\PermissionRequest;
use Str;

class PermissionController extends Controller
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

        $permissions = Permission::orderByDesc('id')->paginate($pagination);
        
        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));
                
                $requestData = ['name', 'guard_name'];
    
                $permissions = Permission::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }
            
            return view('admin-panel.permission.permission-table', compact('permissions', 'pagination'))->render();
        }

        return view('admin-panel.permission.permission', compact('permissions', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Permission $permission)
    {
        return view('admin-panel.permission.permission-form', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        $permission = new Permission;
        
        $permission->name = $request->name;
        $permission->guard_name = $request->guard_name;
        
        $permission->save();

        return redirect()->route('permission.index', [ app()->getlocale() ])->with('success-create', 'The resource was created!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show($lang, Permission $permission)
    {
        return view('admin-panel.permission.permission-show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Permission $permission)
    {
        return view('admin-panel.permission.permission-form', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update($lang, PermissionRequest $request, Permission $permission)
    {
        $permission->name = $request->name;
        $permission->guard_name = $request->guard_name;

        $permission->update();
        
        return redirect()->route('permission.index', [ app()->getlocale() ])->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Permission $permission)
    {
        $permission->delete();
    
        return redirect()->route('permission.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }
}
