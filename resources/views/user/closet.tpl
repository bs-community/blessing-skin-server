@extends('user.master')

@section('title', trans('general.my-closet'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.my-closet') }}
            <small>Closet</small>
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
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#skin-category" data-toggle="tab">{{ trans('general.skin') }}</a></li>
                        <li><a href="#cape-category" data-toggle="tab">{{ trans('general.cape') }}</a></li>

                        <div style="padding: 7px;">
                            <div class="has-feedback pull-right">
                                <form method="get" action="" class="user-search-form">
                                    <input type="text" name="q" class="form-control input-sm" placeholder="输入，回车搜索" value="{{ $q }}">
                                    <span class="glyphicon glyphicon-search form-control-feedback"></span>
                                </form>
                            </div>
                        </div>
                    </ul>
                    <div class="tab-content no-padding">
                        <div class="tab-pane active box-body" id="skin-category">
                            @include('vendor.closet-items', ['items' => $items['skin']])
                        </div>

                        <div class="tab-pane box-body" id="cape-category">
                            @include('vendor.closet-items', ['items' => $items['cape']])
                        </div>
                    </div>
                    <div class="box-footer">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <?php $base_url = $q ? "?q=$q&" : "?"; ?>

                            <li><a href="{{ $base_url }}page=1">«</a></li>
                            @if ($page != 1)
                            <li><a href="{{ $base_url }}page={{ $page-1 }}">{{ $page - 1 }}</a></li>
                            @endif

                            <li><a href="{{ $base_url }}page={{ $page }}" class="active">{{ $page }}</a></li>

                            @if ($total_pages > $page)
                            <li><a href="{{ $base_url }}page={{ $page+1 }}">{{ $page+1 }}</a></li>
                            @endif

                            <li><a href="{{ $base_url }}page={{ $total_pages }}">»</a></li>
                        </ul>
                        <p class="pull-right">{{ trans('general.pagination', ['page' => $page, 'total' => $total_pages]) }}</p>
                    </div>
                </div><!-- /.nav-tabs-custom -->

            </div>

            <!-- Left col -->
            <div class="col-md-4">

                <div class="box box-default">
                    @include('vendor.texture-preview')

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
                <a href="javascript:setTexture();" class="btn btn-primary">{{ trans('general.submit') }}</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('script')
<script>
    $(document).ready(init3dCanvas);
    // Auto resize canvas to fit responsive design
    $(window).resize(init3dCanvas);
</script>
@endsection
