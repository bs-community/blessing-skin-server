@extends('user.master')

@section('title', trans('general.player-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.player-manage') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>PID</th>
                                    <th>{{ trans('user.player.player-name') }}</th>
                                    <th>{{ trans('user.player.preference.title') }}</th>
                                    <th>{{ trans('user.player.edit') }}</th>
                                    <th>{{ trans('user.player.operation') }}</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($players as $player)
                                <tr class="player" id="{{ $player['pid'] }}">
                                    <td class="pid">{{ $player['pid'] }}</td>
                                    <td class="player-name">{{ $player['player_name'] }}</td>
                                    <td>
                                        <select class="form-control" id="preference" pid="{{ $player['pid'] }}">
                                            <option {{ ($player['preference'] == "default") ? 'selected="selected"' : '' }} value="default">Default</option>
                                            <option {{ ($player['preference'] == "slim") ? 'selected="selected"' : '' }} value="slim">Slim</option>
                                       </select>
                                    </td>
                                    <td>
                                        <a class="btn btn-default btn-sm" onclick="changePlayerName('{{ $player['pid'] }}', '{{ $player['player_name'] }}')">{{ trans('user.player.edit-pname') }}</a>
                                    </td>
                                    <td>
                                        <a class="btn btn-warning btn-sm" onclick="clearTexture('{{ $player['pid'] }}');">{{ trans('user.player.delete-texture') }}</a>
                                        <a class="btn btn-danger btn-sm" onclick="deletePlayer('{{ $player['pid'] }}');">{{ trans('user.player.delete-player') }}</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer clearfix">
                        <button class="btn btn-primary pull-left" data-toggle="modal" data-target="#modal-add-player">
                            <i class="fa fa-plus" aria-hidden="true"></i> &nbsp;{{ trans('user.player.add-player') }}
                        </button>
                    </div>
                </div>

                <div class="box box-default collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('general.notice') }}</h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>{{ trans('user.player.login-notice') }}</p>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box">
                    <!-- 3D skin preview -->
                    @include('common.texture-preview', ['title' => trans('user.player.player-info') ])
                    <!-- 2D skin preview -->
                    <div class="box-body">
                        <div id="preview-2d-container" style="display: none;">
                            <p>{{ trans('user.player.textures.steve') }}<a href=""><img id="steve" class="skin2d" /></a>
                                <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
                            </p>

                            <p>{{ trans('user.player.textures.alex') }}<a href=""><img id="alex" class="skin2d" /></a>
                                <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
                            </p>

                            <p>{{ trans('user.player.textures.cape') }}<a href=""><img id="cape" class="skin2d" /></a>
                                <span class="skin2d">{{ trans('user.player.textures.empty') }}</span>
                            </p>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button id="preview-switch" class="btn btn-default">{{ trans('general.switch-2d-preview') }}</button>
                    </div>
                </div><!-- /.box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-add-player" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ trans('user.player.add-player') }}</h4>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <style> td { border-top: 0 !important; } </style>
                            <td>{{ trans('user.player.player-name') }}</td>
                            <td>
                               <input type="text" class="form-control" id="player_name"
                                    placeholder="{{ option('allow_chinese_playername') ? trans('user.player.pname-rule-chinese') : trans('user.player.pname-rule') }}" value="">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
                <a onclick="addNewPlayer();" class="btn btn-primary">{{ trans('general.submit') }}</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('script')
<script>
    $(document).ready(function () {
        $.msp.config.skinUrl = defaultSkin;
        initSkinViewer();
        registerAnimationController();
        registerWindowResizeHandler();
        $.msp.handles.walk.paused = $.msp.handles.rotate.paused = false;
    });
</script>
@endsection
