@extends('admin.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.dashboard') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="{{ url('admin/users') }}">
                                <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('admin.index.total-users') }}</span>
                                    <span class="info-box-number">{{ App\Models\User::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="{{ url('admin/players') }}">
                                <span class="info-box-icon bg-green"><i class="fa fa-gamepad"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ trans('admin.index.total-players') }}</span>
                                    <span class="info-box-number">{{ App\Models\Player::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-aqua" style="background-color: #605ca8 !important;"><i class="fa fa-files-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.total-textures') }}</span>
                        <span class="info-box-number">{{ App\Models\Texture::count() }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-hdd-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('admin.index.disk-usage') }}</span>
                        <?php $size = DB::table('textures')->sum('size') ?: 0; ?>
                        <span class="info-box-number">{{ $size > 1024 ? round($size / 1024, 1)."MB" : $size."KB" }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.index.overview') }}</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="chart">
                            {!! $chart->render() !!}
                        </div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript" src="{{ assets('js/chart.js') }}"></script>
@endsection
