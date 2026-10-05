<?php

namespace App\Http\Controllers\AdminControllers\Permission;

use App\Http\Controllers\AdminControllers\Role\RoleController;
use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/** Permissions of admin panel users (spatie/laravel-permission, guard "admin"). */
class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));
        $group = (string) $request->input('group');

        $permissions = Permission::withCount('roles')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($group !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "{$group}-%")->orWhere('name', 'like', "{$group}.%")->orWhere('name', $group)))
            ->orderBy('name')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.permission.permission-table', compact('permissions', 'pagination'));
        }

        $groups = Permission::pluck('name')->map(fn ($n) => RoleController::groupOf($n))->unique()->sort()->values();

        return view('admin-panel.permission.permission', compact('permissions', 'pagination', 'groups'));
    }

    public function create($lang)
    {
        return view('admin-panel.permission.permission-form', ['permission' => new Permission(['guard_name' => RoleController::GUARD])]);
    }

    public function store($lang, PermissionRequest $request)
    {
        $permission = Permission::create(['name' => $request->validated()['name'], 'guard_name' => RoleController::GUARD]);

        return redirect()->route('permission.show', [app()->getLocale(), $permission->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Permission $permission)
    {
        $permission->load(['roles' => fn ($q) => $q->withCount('permissions')->orderBy('name')]);
        $admins = Admin::permission($permission)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'username']);

        return view('admin-panel.permission.permission-show', compact('permission', 'admins'));
    }

    public function edit($lang, Permission $permission)
    {
        return view('admin-panel.permission.permission-form', compact('permission'));
    }

    public function update($lang, PermissionRequest $request, Permission $permission)
    {
        $permission->update(['name' => $request->validated()['name']]);

        return redirect()->route('permission.show', [app()->getLocale(), $permission->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permission.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }
}
