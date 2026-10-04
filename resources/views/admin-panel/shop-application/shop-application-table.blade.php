<div id="datatable">
    <table class="table table-separate table-head-custom">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Shop name') }}</th>
                <th>{{ __('Phone number') }}</th>
                <th>{{ __('Region') }}</th>
                <th>{{ __('User') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($applications as $application)
            <tr>
                <td>{{ $application->id }}</td>
                <td>{{ $application->name }}</td>
                <td><a href="tel:+993{{ $application->phone }}">+993 {{ $application->phone }}</a></td>
                <td>{{ optional($application->region)->name }}</td>
                <td>{{ optional($application->user)->name }}</td>
                <td>
                    @php $colors = ['new' => 'warning', 'contacted' => 'info', 'approved' => 'success', 'rejected' => 'danger']; @endphp
                    <span class="badge badge-{{ $colors[$application->status] ?? 'secondary' }}">{{ __(ucfirst($application->status)) }}</span>
                </td>
                <td><span class="badge badge-secondary">{{ $application->created_at->format('d.m.Y H:i') }}</span></td>
                <td><a href="{{ route('shop-application.show', [ app()->getlocale(), $application->id ]) }}" class="btn btn-sm btn-light-primary">{{ __('View') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted">{{ __('No applications yet') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-end"><div>{{ $applications->links('layouts.pagination') }}</div></div>
</div>
