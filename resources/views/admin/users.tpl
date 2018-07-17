@extends('admin.master')

@section('title', trans('general.user-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.user-manage')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table id="user-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>@lang('general.user.uid')</th>
                            <th>@lang('general.user.email')</th>
                            <th>@lang('general.user.nickname')</th>
                            <th>@lang('general.user.score')</th>
                            <th>@lang('admin.users.players-count.title')</th>
                            <th>@lang('admin.users.status.title')</th>
                            <th>@lang('general.user.register-at')</th>
                            <th>@lang('general.operations')</th>
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
        $('.box-body').css(
            'min-height',
            $('.content-wrapper').height() - $('.content-header').outerHeight() - 120
        );
    });
</script>
@endsection
