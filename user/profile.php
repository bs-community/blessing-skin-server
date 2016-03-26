<?php
/**
 * @Author: printempw
 * @Date:   2016-02-03 16:12:45
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-26 21:59:57
 */
require "../libraries/session.inc.php";
$data['style'] = <<< 'EOT'
<link rel="stylesheet" href="../assets/css/user.style.css">
EOT;
$data['user'] = $user;
$data['page_title'] = "个人资料";
View::show('header', $data);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            个人资料
            <small>User Profile</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改密码</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group">
                            <label for="title">旧的密码</label>
                            <input type="password" class="form-control" id="passwd" value="">
                        </div>

                        <div class="form-group">
                            <label for="server">新密码</label>
                            <input type="password" class="form-control" id="new-passwd" value="">
                        </div>

                        <div class="form-group">
                            <label for="method">确认密码</label>
                            <input type="password" class="form-control" id="confirm-pwd" value="">
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button id="change" class="btn btn-primary">修改密码</button>
                    </div>
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">重置账号</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <p>这将会删除所有你上传的内容。我们不提供任何备份，确定？</p>
                        <button id="reset" class="btn btn-warning">重置我的账户</button>
                    </div><!-- /.box-body -->
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">删除账号</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <?php if (!$user->is_admin): ?>
                        <p>确定要删除你在 <?php echo Config::get('site_name'); ?> 上的账号吗？</p>
                        <p>此操作不可恢复！我们不提供任何备份，或者神奇的撤销按钮。</p>
                        <p>我们警告过你了，确定要这样做吗？</p>
                        <button id="delete" class="btn btn-danger">删除我的账户</button>
                        <?php else: ?>
                        <p>管理员账号不能被删除。</p>
                        <button class="btn btn-danger" disabled="disabled">删除我的账户</button>
                        <?php endif; ?>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/js/profile.utils.js"></script>
EOT;
View::show('footer', $data); ?>
