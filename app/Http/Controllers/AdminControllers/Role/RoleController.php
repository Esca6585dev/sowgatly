<?php

namespace App\Http\Controllers\AdminControllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** Roles of admin panel users (spatie/laravel-permission, guard "admin"). */
class RoleController extends Controller
{
    public const GUARD = 'admin';

    public function __construct()
    {
        $this->middleware(['auth:admin']);
    }

    public function index(Request $request, $lang)
    {
        $pagination = (int) $request->input('pagination', 10) ?: 10;
        $search = trim((string) $request->input('search'));

        $roles = Role::withCount('permissions')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($pagination)
            ->withQueryString();

        $adminCounts = self::adminCounts($roles->pluck('id')->all());

        if ($request->ajax()) {
            return view('admin-panel.role.role-table', compact('roles', 'pagination', 'adminCounts'));
        }

        return view('admin-panel.role.role', compact('roles', 'pagination', 'adminCounts'));
    }

    public function create($lang)
    {
        return $this->form(new Role(['guard_name' => self::GUARD]));
    }

    public function store($lang, RoleRequest $request)
    {
        $role = Role::create(['name' => $request->validated()['name'], 'guard_name' => self::GUARD]);
        $role->syncPermissions($request->validated()['permissions'] ?? []);

        return redirect()->route('role.show', [app()->getLocale(), $role->id])->with('success-create', 'The resource was created!');
    }

    public function show($lang, Role $role)
    {
        $groups = self::groupPermissions($role->permissions);
        $admins = Admin::role($role)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'username', 'email']);

        return view('admin-panel.role.role-show', compact('role', 'groups', 'admins'));
    }

    public function edit($lang, Role $role)
    {
        return $this->form($role);
    }

    public function update($lang, RoleRequest $request, Role $role)
    {
        $role->update(['name' => $request->validated()['name']]);
        $role->syncPermissions($request->validated()['permissions'] ?? []);

        return redirect()->route('role.show', [app()->getLocale(), $role->id])->with('success-update', 'The resource was updated!');
    }

    public function destroy($lang, Role $role)
    {
        $role->delete();

        return redirect()->route('role.index', app()->getLocale())->with('success-delete', 'The resource was deleted!');
    }

    private function form(Role $role)
    {
        $groups = self::groupPermissions(Permission::where('guard_name', $role->guard_name ?: self::GUARD)->get());
        $selected = $role->exists ? $role->permissions->pluck('name')->all() : [];

        return view('admin-panel.role.role-form', compact('role', 'groups', 'selected'));
    }

    /**
     * Group permissions by the part before the last separator:
     * "banner-list", "banner-create" -> "banner"; "shop-application-edit" -> "shop-application".
     */
    public static function groupPermissions(Collection $permissions): Collection
    {
        return $permissions
            ->sortBy('name')
            ->groupBy(fn ($p) => self::groupOf($p->name))
            ->sortKeys();
    }

    public static function groupOf(string $name): string
    {
        foreach (['-', '.', ' ', '_'] as $sep) {
            if (Str::contains($name, $sep)) {
                return Str::beforeLast($name, $sep);
            }
        }

        return $name;
    }

    /** [role_id => number of admins with that role] */
    public static function adminCounts(array $roleIds): array
    {
        return DB::table(config('permission.table_names.model_has_roles'))
            ->whereIn('role_id', $roleIds ?: [0])
            ->where('model_type', Admin::class)
            ->selectRaw('role_id, count(*) as aggregate')
            ->groupBy('role_id')
            ->pluck('aggregate', 'role_id')
            ->all();
    }
}
