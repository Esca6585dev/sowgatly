<?php

namespace App\Http\Controllers\AdminControllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminEditRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Auth;

class AdminController extends Controller
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

        $admins = Admin::orderByDesc('id')->with('roles')->withTrashed()->paginate($pagination);

        if(request()->ajax()){
            if($request->search) {
                $searchQuery = trim($request->query('search'));

                $requestData = Admin::fillableData();

                $admins = Admin::where(function($q) use($requestData, $searchQuery) {
                                        foreach ($requestData as $field)
                                        $q->orWhere($field, 'like', "%{$searchQuery}%");
                                })->paginate($pagination);
            }

            return view('admin-panel.admin.admin-table', compact('admins', 'pagination'))->render();
        }

        return view('admin-panel.admin.admin', compact('admins', 'pagination'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($lang, Admin $admin)
    {
        $roles = Role::where('guard_name','admin')->pluck('name','name')->all();

        return view('admin-panel.admin.admin-form', compact('admin', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($lang, AdminCreateRequest $request)
    {
        if(Admin::where('email', $request->email)->exists()) {
            return redirect()->route('admin.index', [ app()->getlocale() ])->with('warning', 'This Email Address already is exist!');
        } else {

            $admin = new Admin;

            $admin->first_name = $request->first_name;
            $admin->last_name = $request->last_name;
            $admin->username = $request->username;
            $admin->email = $request->email;
            $admin->password = Hash::make($request->password);

            $admin->save();

            $admin->assignRole($request->roles);

            return redirect()->route('admin.index', [ app()->getlocale() ])->with('success-create', 'The resource was created!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show($lang, $id)
    {
        $admin = Admin::withTrashed()->find($id);
        $roles = Role::where('guard_name','admin')->pluck('name','name')->all();

        return view('admin-panel.admin.admin-show', compact('admin', 'roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($lang, Admin $admin)
    {
        $roles = Role::where('guard_name','admin')->pluck('name','name')->all();

        $adminRole = $admin->roles->pluck('name','name')->all();

        return view('admin-panel.admin.admin-form', compact('admin', 'roles', 'adminRole'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($lang, AdminEditRequest $request, Admin $admin)
    {
        if($admin->email != $request->email && Admin::where('email', '=', $request->email)->count() == 0){
            $admin->email = $request->email;
        }

        $admin->first_name = $request->first_name;
        $admin->last_name = $request->last_name;
        $admin->username = $request->username;

        if($request->password){
            $admin->password = Hash::make($request->password);
        }

        $admin->update();

        DB::table('model_has_roles')->where('model_id',$admin->id)->delete();

        $admin->assignRole($request->roles);

        return redirect()->route('admin.index', [ app()->getlocale() ])->with('success-update', 'The resource was updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy($lang, Admin $admin)
    {
        $admin->forceDelete();

        return redirect()->route('admin.index', [ app()->getlocale() ])->with('success-delete', 'The resource was deleted!');
    }
}
