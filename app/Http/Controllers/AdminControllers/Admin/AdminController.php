<?php

namespace App\Http\Controllers\AdminControllers\Admin;

use App\Http\Controllers\AdminControllers\Role\RoleController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateRequest;
use App\Http\Requests\AdminEditRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));
        $role = (string) $request->input('role');

        $admins = Admin::with('roles:id,name')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                foreach (['first_name', 'last_name', 'username', 'email'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            }))
            ->when($role !== '', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->orderByDesc('id')
            ->paginate($pagination)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin-panel.admin.admin-table', compact('admins', 'pagination'));
        }

        $roles = $this->roles();

        return view('admin-panel.admin.admin', compact('admins', 'pagination', 'roles'));
    }

    public function create($lang)
    {
        return view('admin-panel.admin.admin-form', ['admin' => new Admin, 'roles' => $this->roles(), 'selected' => []]);
    }

    public function store($lang, AdminCreateRequest $request)
    {
        $data = $request->validated();

        $admin = Admin::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $admin->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.show', [app()->getLocale(), $admin->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Admin $admin)
    {
        $admin->load('roles.permissions');
        $groups = RoleController::groupPermissions($admin->getAllPermissions());

        return view('admin-panel.admin.admin-show', compact('admin', 'groups'));
    }

    public function edit($lang, Admin $admin)
    {
        return view('admin-panel.admin.admin-form', [
            'admin' => $admin,
            'roles' => $this->roles(),
            'selected' => $admin->roles->pluck('name')->all(),
        ]);
    }

    public function update($lang, AdminEditRequest $request, Admin $admin)
    {
        $data = $request->validated();

        $admin->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);
        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }
        $admin->save();
        $admin->syncRoles($data['roles'] ?? []);

        return redirect()->route('admin.show', [app()->getLocale(), $admin->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Admin $admin)
    {
        if ($admin->is(auth('admin')->user())) {
            return redirect()->route('admin.show', [app()->getLocale(), $admin->id])->with('warning', 'You cannot delete your own account.');
        }

        $admin->syncRoles([]);
        $admin->forceDelete();

        return redirect()->route('admin.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function roles()
    {
        return Role::where('guard_name', 'admin')->withCount('permissions')->orderBy('name')->get(['id', 'name']);
    }
}
