@extends('admin.master')

@section('title', trans('general.dashboard'))

@section('content')
<style>.info-box > a { color: #333; }</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.dashboard')
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
                                <span class="info-box-icon bg-aqua"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('admin.index.total-users')</span>
                                    <span class="info-box-number">{{ App\Models\User::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>

                    <div class="col-md-6">
                        <div class="info-box">
                            <a href="{{ url('admin/players') }}">
                                <span class="info-box-icon bg-green"><i class="fas fa-gamepad"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">@lang('admin.index.total-players')</span>
                                    <span class="info-box-number">{{ App\Models\Player::count() }}</span>
                                </div><!-- /.info-box-content -->
                            </a>
                        </div><!-- /.info-box -->
                    </div>
                </div>

                <div class="info-box">
                    <span class="info-box-icon bg-aqua" style="background-color: #605ca8 !important;"><i class="far fa-file"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('admin.index.total-textures')</span>
                        <span class="info-box-number">{{ App\Models\Texture::count() }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="far fa-hdd"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">@lang('admin.index.disk-usage')</span>
                        @php
                            $size = DB::table('textures')->sum('size') ?: 0;
                        @endphp
                        <span class="info-box-number">{{ $size > 1024 ? round($size / 1024, 1)."MB" : $size."KB" }}</span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin.index.overview')</h3>
                    </div>
                    <div class="box-body">
                        <div id="chart"></div>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection
