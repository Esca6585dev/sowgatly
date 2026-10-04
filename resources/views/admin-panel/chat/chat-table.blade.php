<div id="datatable">
    <table class="table table-separate table-head-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Order') }}</th>
                <th>{{ __('Last message') }}</th>
                <th>{{ __('Unread') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($threads as $thread)
            <tr>
                <td>{{ $thread->id }}</td>
                <td>{{ optional($thread->user)->name }}<br><small class="text-muted">+993 {{ optional($thread->user)->phone_number }}</small></td>
                <td>{{ optional($thread->shop)->name }}</td>
                <td>@if($thread->order_id)<a href="{{ route('order.show', [ app()->getlocale(), $thread->order_id ]) }}">#{{ $thread->order_id }}</a>@endif</td>
                <td>
                    @if($thread->lastMessage)
                    <span class="badge badge-light">{{ $thread->lastMessage->sender_type }}</span>
                    {{ \Illuminate\Support\Str::limit($thread->lastMessage->body, 60) }}<br>
                    <small class="text-muted">{{ $thread->lastMessage->created_at->format('d.m.Y H:i') }}</small>
                    @endif
                </td>
                <td>
                    <span class="badge badge-secondary" title="{{ __('Customer') }}">{{ $thread->user_unread }}</span>
                    <span class="badge badge-secondary" title="{{ __('Shop') }}">{{ $thread->shop_unread }}</span>
                </td>
                <td><a href="{{ route('chat.show', [ app()->getlocale(), $thread->id ]) }}" class="btn btn-sm btn-light-primary">{{ __('View') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted">{{ __('No chats yet') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-end"><div>{{ $threads->links('layouts.pagination') }}</div></div>
</div>
