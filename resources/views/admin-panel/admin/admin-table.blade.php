@php
    $l = app()->getLocale();
    $me = auth('admin')->id();
@endphp
@if($admins->isEmpty())
    <x-admin.empty icon="user" :text="__('No admins found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Username') }}</th><th>{{ __('Email') }}</th><th>{{ __('Roles') }}</th><th>{{ __('Added') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($admins as $admin)
            <tr>
                <td>
                    <a class="who" href="{{ route('admin.show', [$l, $admin->id]) }}">
                        <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$admin->id % 4] }}">{{ mb_strtoupper(mb_substr($admin->first_name, 0, 1) . mb_substr($admin->last_name, 0, 1)) }}</span>
                        <div style="font-weight:600">{{ $admin->first_name }} {{ $admin->last_name }}@if($admin->id === $me) <x-admin.pill tone="info">{{ __('You') }}</x-admin.pill>@endif</div>
                    </a>
                </td>
                <td class="muted">{{ '@' . $admin->username }}</td>
                <td>{{ $admin->email }}</td>
                <td>
                    @forelse($admin->roles as $role)
                        <x-admin.pill tone="violet">{{ $role->name }}</x-admin.pill>
                    @empty
                        <span class="muted">—</span>
                    @endforelse
                </td>
                <td class="muted nowrap">{{ optional($admin->created_at)->format('d.m.Y') }}</td>
                <td class="right"><x-admin.row-actions route="admin" :model="$admin->id" :delete="$admin->id !== $me" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $admins->links('layouts.pagination') }}
@endif
