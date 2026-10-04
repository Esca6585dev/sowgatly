<div id="datatable">
    <table class="table table-separate table-head-custom">
        <thead>
            <tr>
                <th>№</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Items') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Delivery') }}</th>
                <th>{{ __('Payment') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
            <tr>
                <td><a href="{{ route('order.show', [ app()->getlocale(), $order->id ]) }}">{{ $order->number }}</a></td>
                <td>
                    {{ optional($order->user)->name }}<br>
                    <small class="text-muted">+993 {{ optional($order->user)->phone_number }}</small>
                </td>
                <td>{{ optional($order->shop)->name }}</td>
                <td>{{ $order->items->sum('quantity') }}</td>
                <td>{{ number_format($order->total_amount, 2) }} TMT</td>
                <td>
                    <span class="badge badge-light">{{ __(ucfirst($order->fulfillment)) }}</span>
                    @if($order->delivery_type === 'scheduled' && $order->scheduled_at)
                    <br><small>{{ $order->scheduled_at->format('d.m.Y H:i') }}</small>
                    @endif
                </td>
                <td>
                    {{ __(ucfirst($order->payment_method)) }}@if($order->payment_bank) ({{ $order->payment_bank }})@endif<br>
                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'refunded' ? 'warning' : 'secondary') }}">{{ __(ucfirst($order->payment_status)) }}</span>
                </td>
                <td>
                    @php $colors = ['pending' => 'warning', 'processing' => 'info', 'delivering' => 'primary', 'completed' => 'success', 'cancelled' => 'danger']; @endphp
                    <span class="badge badge-{{ $colors[$order->status] ?? 'secondary' }}">{{ __(ucfirst($order->status)) }}</span>
                </td>
                <td><span class="badge badge-secondary">{{ $order->created_at->format('d.m.Y H:i') }}</span></td>
                <td>
                    <a href="{{ route('order.show', [ app()->getlocale(), $order->id ]) }}" class="btn btn-sm btn-light-primary">{{ __('View') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center text-muted">{{ __('No orders yet') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        <div>{{ $orders->links('layouts.pagination') }}</div>
    </div>
</div>
