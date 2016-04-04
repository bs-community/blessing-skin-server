<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            检查更新
            <small>Check Updates</small>
        </h1>
    </section>
    <?php $updater = new Updater(Option::get('current_version')); ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新信息</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <?php if ($updater->newVersionAvailable()): ?>
                        <div class="callout callout-info">有更新可用。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">最新版本：</td>
                                    <td class="value">
                                        v<?php echo $updater->latest_version; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v<?php echo $updater->current_version; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        <?php echo $updater->update_time; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">更新日志：</td>
                                    <td class="value">
                                        <?php echo nl2br($updater->getUpdateInfo()['releases'][$updater->latest_version]['release_note']); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">下载地址：</td>
                                    <td class="value">
                                    <a href="<?php echo $updater->getUpdateInfo()['releases'][$updater->latest_version]['release_url']; ?>">@GitHub</a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="callout callout-success">已更新至最新版本。</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">当前版本：</td>
                                    <td class="value">
                                        v<?php echo $updater->current_version; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">发布时间：</td>
                                    <td class="value">
                                        <?php echo date('Y-m-d H:i:s', $updater->getUpdateInfo()['releases'][$updater->current_version]['release_time']); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <?php endif; ?>
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

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
