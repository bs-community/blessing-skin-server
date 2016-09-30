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

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title" title="{{ trans('user.closet.switch-category') }}" data-toggle="tooltip" data-placement="bottom">
                            <a href="?category=skin" {{ ($category == "skin") ? 'class=selected' : "" }}>{{ trans('general.skin') }}</a>
                            /
                            <a href="?category=cape" {{ ($category == "cape") ? 'class=selected' : "" }}>{{ trans('general.cape') }}</a>
                        </h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        @forelse ($items as $item)
                        <div class="item" tid="{{ $item->tid }}">
                            <div class="item-body">
                                <img src="{{ url('preview/'.$item->tid) }}.png">
                            </div>
                            <div class="item-footer">
                                <p class="texture-name">
                                    <span title="{{ $item->name }}">{{ $item->name }} <small>({{ $item->type }})</small></span>
                                </p>

                                <a href="{{ url('skinlib/show?tid='.$item->tid) }}" title="{{ trans('user.closet.view') }}" class="more" data-toggle="tooltip" data-placement="bottom"><i class="fa fa-share"></i></a>
                                <span title="{{ trans('user.closet.more') }}" class="more" data-toggle="dropdown" aria-haspopup="true" id="share-button"><i class="fa fa-cog"></i></span>

                                <ul class="dropdown-menu" aria-labelledby="share-button">
                                    <li><a href="javascript:renameClosetItem({{ $item->tid }});">{{ trans('user.closet.rename.title') }}</a></li>
                                    <li><a href="javascript:removeFromCloset({{ $item->tid }});">{{ trans('user.closet.remove.title') }}</a></li>
                                    <li><a href="javascript:setAsAvatar({{ $item->tid }});">{{ trans('user.closet.set-avatar') }}</a></li>
                                </ul>
                            </div>
                        </div>
                        @empty
                        <div class="empty-msg">
                            {!! trans('user.closet.empty-msg', ['url' => url('skinlib')]) !!}
                        </div>
                        @endforelse


                    </div>
                    <div class="box-footer">
                        <ul class="pagination pagination-sm no-margin pull-right">
                            <li><a href="?page=1">«</a></li>
                            @if ($page != 1)
                            <li><a href="?page={{ $page-1 }}">{{ $page - 1 }}</a></li>
                            @endif

                            <li><a href="?page={{ $page }}" class="active">{{ $page }}</a></li>

                            @if ($total_pages > $page)
                            <li><a href="?page={{ $page+1 }}">{{ $page+1 }}</a></li>
                            @endif

                            <li><a href="?page={{ $total_pages }}">»</a></li>
                        </ul>
                        <p class="pull-right">{{ trans('general.pagination', ['page' => $page, 'total' => $total_pages]) }}</p>
                    </div>
                </div>
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
                @forelse($user->getPlayers() as $player)
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
