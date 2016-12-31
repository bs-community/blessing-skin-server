@extends('admin.master')

@section('title', trans('general.player-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.player-manage') }}
            <small>Player Management</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table id="player-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>PID</th>
                            <th>拥有者</th>
                            <th>角色名</th>
                            <th>优先模型</th>
                            <th>预览材质</th>
                            <th>修改时间</th>
                            <th>操作</th>
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

$('#player-table').DataTable({
    language: trans('vendor.datatables'),
    responsive: true,
    autoWidth: false,
    processing: true,
    serverSide: true,
    ajax: '{{ url("admin/player-data") }}',
    createdRow: function (row, data, index) {
        $('td', row).eq(2).attr('id', 'player-name');
    },
    columns: [
        {data: 'pid', 'width': '1%'},
        {data: 'uid'},
        {data: 'player_name'},
        {data: 'preference'},
        {data: 'previews', searchable: false, orderable: false},
        {data: 'last_modified'},
        {data: 'operations', searchable: false, orderable: false}
    ]
});
</script>
@endsection
