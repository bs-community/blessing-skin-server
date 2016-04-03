<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            用户预览
            <small>User Preview</small>
        </h1>
    </section>
    <?php $db = new Database\Database('users');
        $user = new User($db->select('uid', $data['uid'])['username']);
    ?>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">修改用户配置</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="">
                        <input type="hidden" name="option" value="general">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['submit'])) {
                                if (User::checkValidUname($_POST['username']) &&
                                    ($_POST['model_preference'] == 'default' || $_POST['model_preference'] == 'slim'))
                                {
                                    $db->update('username', $_POST['username'], ['where' => "username='$user->uname'"]);
                                    $db->update('preference', $_POST['model_preference'], ['where' => "username='$user->uname'"]);
                                    echo '<div class="callout callout-success">设置已保存。</div>';
                                } else {
                                    echo '<div class="callout callout-danger">无效的用户名或优先模型。</div>';
                                }

                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">用户名</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="username" value="<?php echo $user->uname; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">优先模型</td>
                                        <td class="value">
                                           <select class="form-control" name="model_preference">
                                                <option <?php echo ($user->getPreference() == 'default') ? 'selected="selected"' : ''; ?> value="default">Default (steve)</option>
                                                <option <?php echo ($user->getPreference() == 'slim') ? 'selected="selected"' : ''; ?> value="slim">Slim (alex)</option>
                                           </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                            <a href="javascript:history.back();" style="float: right;" class="btn btn-default">返回</a>
                        </div>
                    </form>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">预览控制</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            选择要预览的模型：
                            <a class="btn btn-primary" href="javascript:MSP.changeSkin('../textures/<?php echo $user->getTexture('alex');?>');" style="float: right;">Alex</a>
                            <a class="btn btn-primary" href="javascript:MSP.changeSkin('../textures/<?php echo $user->getTexture('steve');?>');" style="float: right; margin-right: 10px;">Steve</a>
                    </div><!-- /.box-body -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="box">
                    <div class="box-body table-responsive no-padding">
                        <div class="box-header with-border">
                            <h3 class="box-title">皮肤预览
                                <small><a id="preview" href="javascript:show2dPreview();">切换 2D 预览</a></small>
                                <div class="operations">
                                    <i data-toggle="tooltip" data-placement="bottom" title="Movements" class="fa fa-pause"></i>
                                    <i data-toggle="tooltip" data-placement="bottom" title="Running" class="fa fa-forward"></i>
                                    <i data-toggle="tooltip" data-placement="bottom" title="Rotation" class="fa fa-repeat"></i>
                                </div>
                            </h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <?php View::show('preview', array('user'=>$user)); ?>
                        </div><!-- /.box-body -->
                    </div>
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
