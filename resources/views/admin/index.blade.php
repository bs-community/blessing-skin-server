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

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin.notifications.send.title')</h3>
                    </div>
                    <form method="post" action="{{ url('/admin/notifications/send') }}">
                        @csrf
                        <div class="box-body">
                            @if ($errors->any())
                                <div class="callout callout-danger">{{ $errors->first() }}</div>
                            @endif
                            @if ($sentResult = Session::pull('sentResult'))
                                <div class="callout callout-success">{{ $sentResult }}</div>
                            @endif
                            <div class="form-group">
                                <label>@lang('admin.notifications.receiver.title')</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="receiver" value="all" required>
                                        @lang('admin.notifications.receiver.all')
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="receiver" value="normal" required>
                                        @lang('admin.notifications.receiver.normal')
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="receiver" value="uid" required>
                                        @lang('admin.notifications.receiver.uid') &nbsp;
                                        <input type="number" name="uid" class="form-control">
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="receiver" value="email" required>
                                        @lang('admin.notifications.receiver.email') &nbsp;
                                        <input type="email" name="email" class="form-control">
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin.notifications.title')</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>@lang('admin.notifications.content')</label>
                                <textarea name="content" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <input type="submit" value="@lang('general.submit')" class="el-button el-button--primary">
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin.index.overview')</h3>
                    </div>
                    <div class="box-body">
                        <div id="chart"></div>
                    </div>
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection
