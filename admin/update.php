<?php
/**
 * @Author: printempw
 * @Date:   2016-03-27 15:03:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-02 18:33:02
 */
require "../libraries/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "检查更新"));
$db = new Database\Database('users');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            检查更新
            <small>Check Updates</small>
        </h1>
    </section>
    <?php
        $updater = new Updater(Option::get('current_version'));
    ?>
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
                        <a href="http://www.mcbbs.net/thread-552877-1-1.html" class="btn btn-primary">查看 MCBBS 发布贴</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">更新流程</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>1. 下载新版源码</p>
                        <p>2. <strong>完全覆盖</strong>旧版目录</p>
                        <p>3. 运行 /setup/update-from-2.3.3-to-2.3.4.php</p>
                        <p>4. 升级完成</p>
                    </div><!-- /.box-body -->
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">关于</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>自动升级正在开发中，开发者现在忙于应考，可能要过几个星期才能发布。</p>
                    </div><!-- /.box-body -->
                </div>
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
View::show('footer'); ?>
