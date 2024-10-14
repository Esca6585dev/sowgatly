<!--begin: Datatable-->
<div id="datatable">
    <table class="table table-separate table-head-custom table-checkable">
        <thead>
            <tr>
                <th>ID</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Value') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Created time') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attributes as $attribute)
            <tr id="datatable">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $attribute->type }}</td>
                <td>{{ $attribute->value }}</td>
                <td>
                    <span class="badge badge-primary">{{ $attribute->category->{ 'name_' . app()->getlocale() } }}</span>
                </td>
                <td>
                    <span
                        class="badge badge-secondary">{{ \Carbon::parse($attribute->created_at)->locale(config('app.faker_locales.' . app()->getlocale() ))->isoFormat('LLLL') }}</span>
                </td>
                <td>@include('admin-panel.attribute.attribute-action', [ $attribute ])</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        <div>
            {{ $attributes->links('layouts.pagination') }}
        </div>
    </div>
</div>
<!--end: Datatable-->
