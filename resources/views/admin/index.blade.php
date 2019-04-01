@extends('admin.master')

@section('title', trans('general.dashboard'))

@section('content')
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
                        <div class="small-box bg-aqua">
                            <div class="inner">
                                <h3>{{ App\Models\User::count() }}</h3>
                                <p>@lang('admin.index.total-users')</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <a href="{{ url('admin/users') }}" class="small-box-footer">
                                @lang('general.user-manage') <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ App\Models\Player::count() }}</h3>
                                <p>@lang('admin.index.total-players')</p>
                            </div>
                            <div class="icon"><i class="fas fa-gamepad"></i></div>
                            <a href="{{ url('admin/players') }}" class="small-box-footer">
                                @lang('general.player-manage') <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="small-box bg-purple">
                            <div class="inner">
                                <h3>{{ App\Models\Texture::count() }}</h3>
                                <p>@lang('admin.index.total-textures')</p>
                            </div>
                            <div class="icon"><i class="fas fa-file"></i></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="small-box bg-yellow">
                            <div class="inner">
                                @php
                                    $size = DB::table('textures')->sum('size') ?: 0;
                                @endphp
                                <h3>{{ $size > 1024 ? round($size / 1024, 1).'MB' : $size.'KB' }}</h3>
                                <p>@lang('admin.index.disk-usage')</p>
                            </div>
                            <div class="icon"><i class="fas fa-hdd"></i></div>
                        </div>
                    </div>
                </div>
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
