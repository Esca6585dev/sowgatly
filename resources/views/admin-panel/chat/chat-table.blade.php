@php
    $l = app()->getLocale();
    $faker = config('app.faker_locales.' . $l, 'en_US');
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/u', trim((string) $name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: '?';
@endphp
@if($threads->isEmpty())
    <x-admin.empty icon="chat" :text="__('No chats found')" />
@else
<div class="table-wrap">
    <table class="tbl">
        <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Shop') }}</th><th>{{ __('Last message') }}</th><th>{{ __('Order') }}</th><th class="right">{{ __('Unread') }}</th><th></th></tr></thead>
        <tbody>
        @foreach($threads as $i => $thread)
            @php $last = $thread->lastMessage; @endphp
            <tr>
                <td>
                    <a class="who" href="{{ route('chat.show', [$l, $thread->id]) }}">
                        <span class="avatar {{ ['', 'av-2', 'av-3', 'av-4'][$i % 4] }}">{{ $initials(optional($thread->user)->name) }}</span>
                        <div><b>{{ optional($thread->user)->name ?? '—' }}</b>@if(optional($thread->user)->phone_number)<small class="nowrap">+993 {{ $thread->user->phone_number }}</small>@endif</div>
                    </a>
                </td>
                <td>{{ optional($thread->shop)->name ?? '—' }}</td>
                <td style="max-width:360px">
                    @if($last)
                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <span class="muted">{{ $last->sender_type === 'shop' ? __('Shop') : __('Customer') }}:</span>
                        {{ \Illuminate\Support\Str::limit($last->body, 80) }}
                    </div>
                    <small class="muted">{{ $last->created_at->locale($faker)->isoFormat('D MMM, HH:mm') }}</small>
                    @else
                    <span class="muted">{{ __('No messages yet') }}</span>
                    @endif
                </td>
                <td class="nowrap">
                    @if($thread->order_id)
                    <a class="text-brand" style="font-weight:600" href="{{ route('order.show', [$l, $thread->order_id]) }}">№ {{ str_pad((string) $thread->order_id, 7, '0', STR_PAD_LEFT) }}</a>
                    @else
                    <span class="muted">—</span>
                    @endif
                </td>
                <td class="right nowrap small">
                    @if($thread->shop_unread > 0)<div title="{{ __('Unread by the shop') }}"><span class="muted">{{ __('Shop') }}</span> <i class="badge-n">{{ $thread->shop_unread }}</i></div>@endif
                    @if($thread->user_unread > 0)<div title="{{ __('Unread by the customer') }}" style="margin-top:4px"><span class="muted">{{ __('Customer') }}</span> <i class="badge-n" style="background:var(--muted)">{{ $thread->user_unread }}</i></div>@endif
                    @if($thread->shop_unread == 0 && $thread->user_unread == 0)<span class="muted">—</span>@endif
                </td>
                <td class="right"><x-admin.row-actions route="chat" :model="$thread->id" :edit="false" :delete="false" /></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $threads->links('layouts.pagination') }}
@endif
