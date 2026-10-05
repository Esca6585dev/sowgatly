@php $l = app()->getLocale(); @endphp
@if($users->isEmpty())
    <x-admin.empty icon="users" :text="__('No users found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Shop') }}</th><th class="right">{{ __('Orders') }}</th><th>{{ __('Status') }}</th><th>{{ __('Joined') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>
                    <a class="who" href="{{ route('user.show', [$l, $user->id]) }}">
                        @include('admin-panel.user.user-avatar', ['u' => $user])
                        <div class="nowrap"><b>{{ $user->name }}</b>@if($user->email)<small>{{ $user->email }}</small>@endif</div>
                    </a>
                </td>
                <td class="nowrap num">+993 {{ $user->phone_number }}</td>
                <td>
                    @if($user->shop)
                        <a href="{{ route('shop.show', [$l, $user->shop->id]) }}"><x-admin.pill tone="brand" :title="$user->shop->name">{{ \Illuminate\Support\Str::limit($user->shop->name, 20) }}</x-admin.pill></a>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td class="right num">{{ $user->orders_count }}</td>
                <td><x-admin.status :value="(bool) $user->status" /></td>
                <td class="muted nowrap">{{ optional($user->created_at)->format('d.m.Y') }}</td>
                <td class="right"><x-admin.row-actions route="user" :model="$user->id" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $users->links('layouts.pagination') }}
@endif
