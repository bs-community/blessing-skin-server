<?php
/**
 * @Author: printempw
 * @Date:   2016-01-21 13:56:40
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 11:50:47
 */
require "../libraries/session.inc.php";
$data['style'] = <<< 'EOT'
<link rel="stylesheet" href="../assets/css/user.style.css">
EOT;
$data['user'] = $user;
$data['page_title'] = "用户中心";
View::show('header', $data);
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            用户中心
            <small>User Center</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">公告</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <?php echo nl2br(Option::get('announcement')); ?>
                    </div><!-- /.box-body -->
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">修改优先模型</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <p>你现在的优先皮肤模型是 <b><?php echo $user->getPreference(); ?></b>。
                            <a class="btn btn-primary" href="javascript:changeModel();">更改</a>
                        </p>
                    </div><!-- /.box-body -->
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">皮肤预览
                            <small><a id="preview" href="javascript:show2dPreview();">切换 2D 预览</a></small>
                            <div class="operations">
                                <i data-toggle="tooltip" data-placement="bottom" title="行走" class="fa fa-pause"></i>
                                <i data-toggle="tooltip" data-placement="bottom" title="奔跑" class="fa fa-forward"></i>
                                <i data-toggle="tooltip" data-placement="bottom" title="旋转" class="fa fa-repeat"></i>
                            </div>
                        </h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <?php View::show('preview', array('user'=>$user)); ?>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/js/preview.utils.js"></script>
<script type="text/javascript" src="../assets/js/user.utils.js"></script>
EOT;
View::show('footer', $data); ?>
