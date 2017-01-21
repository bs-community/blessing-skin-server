@extends('admin.master')

@section('title', trans('general.user-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.user-manage') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table id="user-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('general.user.uid') }}</th>
                            <th>{{ trans('general.user.email') }}</th>
                            <th>{{ trans('general.user.nickname') }}</th>
                            <th>{{ trans('general.user.score') }}</th>
                            <th>{{ trans('admin.users.status.title') }}</th>
                            <th>{{ trans('general.user.register-at') }}</th>
                            <th>{{ trans('general.operations') }}</th>
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
$(document).ready(function() {
    $('.box-body').css('min-height', $('.content-wrapper').height() - $('.content-header').outerHeight() - 120);
});

$('#user-table').DataTable({
    language: trans('vendor.datatables'),
    scrollX: true,
    autoWidth: false,
    processing: true,
    serverSide: true,
    ajax: '{{ url("admin/user-data") }}',
    createdRow: function (row, data, index) {
        $('td', row).eq(1).attr('id', 'email');
        $('td', row).eq(2).attr('id', 'nickname');
        $('td', row).eq(4).attr('id', 'permission');
    },
    columns: [
        {data: 'uid', 'width': '1%'},
        {data: 'email'},
        {data: 'nickname'},
        {data: 'score'},
        {data: 'permission'},
        {data: 'register_at'},
        {data: 'operations', searchable: false, orderable: false}
    ]
});
</script>
@endsection
