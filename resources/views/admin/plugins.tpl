@extends('admin.master')

@section('title', trans('general.plugin-manage'))

@section('style')
<style> .btn { margin-right: 4px; } </style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.plugin-manage') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (session()->has('message'))
            <div class="callout callout-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        <div class="box">
            <div class="box-body table-bordered">
                <table id="plugin-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('admin.plugins.name') }}</th>
                            <th>{{ trans('admin.plugins.description') }}</th>
                            <th>{{ trans('admin.plugins.author') }}</th>
                            <th>{{ trans('admin.plugins.version') }}</th>
                            <th>{{ trans('admin.plugins.status.title') }}</th>
                            <th>{{ trans('admin.plugins.operations.title') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript">

var table = $('#plugin-table').DataTable({
    language: trans('vendor.datatables'),
    scrollX: true,
    pageLength: 25,
    autoWidth: false,
    processing: true,
    serverSide: true,
    ajax: '{{ url("admin/plugins/data") }}',
    createdRow: function (row, data, index) {
        $('td', row).eq(1).attr('id', 'description');
        $('td', row).eq(2).attr('id', 'author');
        $('td', row).eq(3).attr('id', 'version');
        $('td', row).eq(4).attr('id', 'status');
    },
    columns: [
        {data: 'title'},
        {data: 'description', 'width': '35%'},
        {data: 'author'},
        {data: 'version'},
        {data: 'status'},
        {data: 'operations', searchable: false, orderable: false}
    ]
});
</script>
@endsection
