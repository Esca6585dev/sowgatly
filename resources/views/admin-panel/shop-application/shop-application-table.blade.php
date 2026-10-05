@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
@endphp
@if($applications->isEmpty())
    <x-admin.empty icon="inbox" :text="__('No applications found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Shop name') }}</th><th>{{ __('Phone number') }}</th><th>{{ __('Region') }}</th><th>{{ __('User') }}</th><th>{{ __('Status') }}</th><th>{{ __('Created time') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($applications as $application)
            <tr>
                <td>
                    <a class="who" href="{{ route('shop-application.show', [$l, $application->id]) }}">
                        <span class="thumb"><x-admin.icon name="shop" /></span>
                        <div><b>{{ $application->name }}</b>@if($application->description)<small style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $application->description }}</small>@endif</div>
                    </a>
                </td>
                <td class="nowrap"><a href="tel:+993{{ $application->phone }}">+993 {{ $application->phone }}</a></td>
                <td>{{ optional($application->region)->name ?? '—' }}</td>
                <td>{{ optional($application->user)->name ?? __('Guest') }}</td>
                <td><x-admin.status :value="$application->status" /></td>
                <td class="nowrap muted small">{{ $application->created_at->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                <td class="right"><x-admin.row-actions route="shop-application" :model="$application->id" :edit="false" :delete="false" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $applications->links('layouts.pagination') }}
@endif
