@extends('user.master')

@section('title', trans('general.my-closet'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.my-closet') }}
        </h1>
        <div class="breadcrumb">
            <a href="{{ url('skinlib/upload') }}"><i class="fa fa-upload"></i> {{ trans('user.closet.upload') }}</a>
            <a href="{{ url('skinlib') }}"><i class="fa fa-search"></i> {{ trans('user.closet.search') }}</a>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Left col -->
            <div class="col-md-8">
                <!-- Custom tabs -->
                <div class="nav-tabs-custom" id="closet-container">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#skin-category" class="category-switch" data-toggle="tab">{{ trans('general.skin') }}</a></li>
                        <li><a href="#cape-category" class="category-switch" data-toggle="tab">{{ trans('general.cape') }}</a></li>

                        <li class="pull-right" style="padding: 7px;">
                            <div class="has-feedback pull-right">
                                <div class="user-search-form">
                                    <input type="text" name="q" class="form-control input-sm" placeholder="{{ trans('user.closet.type-to-search') }}">
                                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="tab-content no-padding">
                        <div class="tab-pane active box-body" id="skin-category"></div>
                        <div class="tab-pane box-body" id="cape-category"></div>
                    </div>
                    <div class="box-footer">
                        <div class="pull-right" id="closet-paginator" last-skin-page="1" last-cape-page="1"></div>
                    </div>
                </div><!-- /.nav-tabs-custom -->

            </div>

            <!-- Right col -->
            <div class="col-md-4">

                <div class="box box-default">
                    @include('common.texture-preview')

                    <div class="box-footer">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#modal-use-as">{{ trans('user.closet.use-as.button') }}</button>
                    </div><!-- /.box-footer -->
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-use-as" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('user.closet.use-as.title') }}</h4>
            </div>
            <div class="modal-body">
                @forelse($user->players as $player)
                <label class="model-label" for="{{ $player->pid }}">
                    <input type="radio" id="{{ $player->pid }}" name="player" /> {{ $player->player_name }}
                </label><br />
                @empty
                <p>{{ trans('user.closet.use-as.empty') }}</p>
                @endforelse
            </div>
            <div class="modal-footer">
                <a href="./player" class="btn btn-default pull-left">{{ trans('user.closet.use-as.add') }}</a>
                <a onclick="setTexture();" class="btn btn-primary">{{ trans('general.submit') }}</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('script')
<script>
    $(document).ready(function () {
        $.msp.config.skinUrl = defaultSteveSkin;
        initSkinViewer();
        registerAnimationController();
        registerWindowResizeHandler();
    });
</script>
@endsection
