@extends('admin.master')

@section('title', '检查更新')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            检查更新
            <small>Check Update</small>
        </h1>
    </section>

    <?php $updater = new Updater(Application::getVersion()); ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新信息</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        @if ($updater->newVersionAvailable())
                        <div class="callout callout-info">有更新可用。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">最新版本：</td>
                                    <td class="value">
                                        v{{ $updater->latest_version }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v{{ $updater->current_version }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        {{ $updater->update_time }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">更新日志：</td>
                                    <td class="value">
                                        {{ nl2br($updater->getUpdateInfo()['releases'][$updater->latest_version]['release_note']) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">下载地址：</td>
                                    <td class="value">
                                    <a href="{{ $updater->getUpdateInfo()['releases'][$updater->latest_version]['release_url'] }}">@GitHub</a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                        @else
                        <div class="callout callout-success">已更新至最新版本。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v{{ $updater->current_version }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        {{ date('Y-m-d H:i:s', $updater->getUpdateInfo()['releases'][$updater->current_version]['release_time']) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <a <?php if (!$updater->newVersionAvailable()) echo "disabled='disabled'"; ?> href="update.php?action=download" class="btn btn-primary">马上升级</a>
                        <a href="http://www.mcbbs.net/thread-552877-1-1.html" style="float: right;" class="btn btn-default">查看 MCBBS 发布贴</a>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">注意事项</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>下载更新需要连接 GitHub 服务器，国内主机可能会长时间无响应。</p>
                    </div><!-- /.box-body -->
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新选项</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <form method="post" action="../admin/update">
                            <div class="box-body">
                                <?php
                                if (isset($_POST['submit'])) {
                                    $_POST['check_update'] = isset($_POST['check_update']) ? $_POST['check_update'] : "0";

                                    foreach ($_POST as $key => $value) {
                                        if ($key != "option" && $key != "submit")
                                            Option::set($key, $value);
                                    }
                                    echo '<div class="callout callout-success">设置已保存。</div>';
                                } ?>
                                <table class="table">
                                    <tbody>


                                        <tr>
                                            <td class="key">检查更新</td>
                                            <td class="value">
                                                <label for="check_update">
                                                    <input {{ (Option::get('check_update') == '1') ? 'checked="true"' : '' }} type="checkbox" id="check_update" name="check_update" value="1"> 自动检查更新并提示
                                                </label>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="key">更新源</td>
                                            <td class="value">
                                                <select class="form-control" name="update_source">
                                                    @foreach ($updater->getUpdateSources() as $key => $value)
                                                    <option {{ (Option::get('update_source') == $key) ? 'selected="selected"' : '' }} value="{{ $key }}">{{ $value['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div><!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" name="submit" class="btn btn-primary">提交</button>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div>
            </div>

        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
