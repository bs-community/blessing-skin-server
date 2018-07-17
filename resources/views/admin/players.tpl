@extends('admin.master')

@section('title', trans('general.player-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.player-manage')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-bordered">
                <table id="player-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>@lang('general.player.pid')</th>
                            <th>@lang('general.player.owner')</th>
                            <th>@lang('general.player.player-name')</th>
                            <th>@lang('general.player.preference')</th>
                            <th>@lang('general.player.previews')</th>
                            <th>@lang('general.player.last-modified')</th>
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
    $(document).ready(function () {
        $('.box-body').css(
            'min-height',
            $('.content-wrapper').height() - $('.content-header').outerHeight() - 120
        );
    });
</script>
@endsection
