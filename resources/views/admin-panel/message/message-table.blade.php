@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
@if($messages->isEmpty())
    <x-admin.empty icon="mail" :text="__('No messages found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Sender') }}</th><th>{{ __('Contacts') }}</th><th>{{ __('Message') }}</th><th>{{ __('Created time') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($messages as $i => $message)
            <tr>
                <td>
                    <a class="who" href="{{ route('message.show', [$l, $message->id]) }}">
                        <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials($message->username) }}</span>
                        <div><b>{{ $message->username ?: __('No name') }}</b>@if($message->user_id)<small>{{ __('Registered user') }}</small>@endif</div>
                    </a>
                </td>
                <td class="nowrap small">
                    @if($message->email)<div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>@endif
                    @if($message->phone_number)<div class="muted">{{ $message->phone_number }}</div>@endif
                    @if(!$message->email && !$message->phone_number)<span class="muted">—</span>@endif
                </td>
                <td style="max-width:420px"><div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ \Illuminate\Support\Str::limit($message->message, 140) }}</div></td>
                <td class="nowrap muted small">{{ optional($message->created_at)->locale($faker)->isoFormat('D MMM YYYY, HH:mm') }}</td>
                <td class="right"><x-admin.row-actions route="message" :model="$message->id" :edit="false" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $messages->links('layouts.pagination') }}
@endif
