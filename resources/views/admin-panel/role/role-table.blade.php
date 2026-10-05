@if($roles->isEmpty())
    <x-admin.empty icon="shield" :text="__('No roles found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Name') }}</th><th class="right">{{ __('Permissions') }}</th><th class="right">{{ __('Admins') }}</th><th>{{ __('Created') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($roles as $role)
            <tr>
                <td>
                    <a class="who" href="{{ route('role.show', [app()->getLocale(), $role->id]) }}">
                        <span class="thumb" style="width:34px;height:34px"><x-admin.icon name="shield" class="i-sm" /></span>
                        <span style="font-weight:600">{{ $role->name }}</span>
                    </a>
                </td>
                <td class="right num">{{ $role->permissions_count }}</td>
                <td class="right num">{{ $adminCounts[$role->id] ?? 0 }}</td>
                <td class="muted nowrap">{{ optional($role->created_at)->format('d.m.Y') }}</td>
                <td class="right"><x-admin.row-actions route="role" :model="$role->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $roles->links('layouts.pagination') }}
@endif
