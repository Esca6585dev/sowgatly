@if($permissions->isEmpty())
    <x-admin.empty icon="key" :text="__('No permissions found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Section') }}</th><th class="right">{{ __('Roles') }}</th><th>{{ __('Created') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($permissions as $permission)
            <tr>
                <td><a href="{{ route('permission.show', [app()->getLocale(), $permission->id]) }}" style="font-weight:600">{{ $permission->name }}</a></td>
                <td><x-admin.pill>{{ \App\Http\Controllers\AdminControllers\Role\RoleController::groupOf($permission->name) }}</x-admin.pill></td>
                <td class="right num">{{ $permission->roles_count }}</td>
                <td class="muted nowrap">{{ optional($permission->created_at)->format('d.m.Y') }}</td>
                <td class="right"><x-admin.row-actions route="permission" :model="$permission->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $permissions->links('layouts.pagination') }}
@endif
