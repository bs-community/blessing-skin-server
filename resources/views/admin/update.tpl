@extends('admin.master')

@section('title', trans('general.check-update'))

@section('style')
<style type="text/css">
.description { margin: 7px 0 0 0; color: #555; }
.description a { color: #3c8dbc; }
</style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.check-update') }}
            <small>Check Update</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新信息</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        @if ($info['new_version_available'])
                        <div class="callout callout-info">有更新可用。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">最新版本：</td>
                                    <td class="value">
                                        v{{ $info['latest_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        {{ Utils::getTimeFormatted($info['release_time']) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">更新日志：</td>
                                    <td class="value">
                                        {!! nl2br($info['release_note']) ?: "无内容" !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">下载地址：</td>
                                    <td class="value">
                                    <a href="{!! $info['release_url'] !!}">点击下载完整安装包</a>
                                    </td>
                                </tr>

                                @if($info['pre_release'])
                                <div class="callout callout-warning">本次更新为预发布版，请谨慎选择是否更新。</div>
                                @endif

                            </tbody>
                        </table>
                        @else
                        <div class="callout callout-success">已更新至最新版本。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        @if (isset($info['release_time']))
                                        {{ Utils::getTimeFormatted($info['release_time']) }}
                                        @else
                                        当前版本为未发布测试版
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <a class="btn btn-primary" id="update-button" {!! !$info['new_version_available'] ? 'disabled="disabled"' : 'href="javascript:downloadUpdates();"' !!}>马上升级</a>
                        <a href="http://www.mcbbs.net/thread-552877-1-1.html" style="float: right;" class="btn btn-default">查看 MCBBS 发布贴</a>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">注意事项</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>请根据你的主机所在位置（国内/国外）选择更新源。</p>
                        <p>如错选至相对于你的主机速度较慢的源，可能会造成检查/下载更新页面长时间无响应。</p>
                    </div><!-- /.box-body -->
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新选项</h3>
                    </div><!-- /.box-header -->
                    <form method="post">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['submit'])) {
                                $_POST['check_update'] = isset($_POST['check_update']) ? $_POST['check_update'] : "0";

                                foreach ($_POST as $key => $value) {
                                    if ($key != "option" && $key != "submit")
                                        Option::set($key, $value);
                                }

                                echo '<div class="callout callout-success">设置已保存。</div>';
                            }

                            try {
                                $response = file_get_contents(option('update_source'));
                            } catch (Exception $e) {
                                echo '<div class="callout callout-danger">无法访问当前更新源。详细信息：'.$e->getMessage().'</div>';
                            }

                            ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">检查更新</td>
                                        <td class="value">
                                            <label for="check_update">
                                                <input {{ (option('check_update') == '1') ? 'checked="true"' : '' }} type="checkbox" id="check_update" name="check_update" value="1"> 自动检查更新并提示
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">更新源</td>
                                        <td class="value">
                                            <input type="text" class="form-control" name="update_source" value="{{ option('update_source') }}">

                                            <p class="description">可用的更新源列表可以在这里查看：<a href="https://github.com/printempw/blessing-skin-server/wiki/%E6%9B%B4%E6%96%B0%E6%BA%90%E5%88%97%E8%A1%A8">@GitHub Wiki</a></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-start-download" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">正在下载更新包</h4>
            </div>
            <div class="modal-body">
                <p>更新包大小：<span id="file-size">0</span> Bytes</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="imported-progress">0</span>%
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
