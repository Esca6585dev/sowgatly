<!--begin: Datatable-->
<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Username') }}</th>
                <th>{{ __('Phone number') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Message') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($messages as $message)
            <tr id="datatable">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $message->username }}</td>
                <td>{{ $message->phone_number }}</td>
                <td>{{ $message->email }}</td>
                <td>{{ $message->message }}</td>
                <td>
                    <span class="badge badge-secondary">{{ \Carbon::parse($message->created_at)->locale(config('app.faker_locales.' . app()->getlocale() ))->isoFormat('LLLL') }}</span>
                </td>
                <td>@include('admin-panel.message.message-action', [ $message ])</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        <div>
            {{ $messages->links('layouts.pagination') }}
        </div>
    </div>
</div>
<!--end: Datatable-->
