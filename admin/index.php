<?php
/**
 * @Author: printempw
 * @Date:   2016-02-03 14:39:50
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 10:08:11
 */
require "../includes/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "仪表盘"));
$db = new Database\Database();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            仪表盘
            <small>Dashboard</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">注册用户</span>
                        <span class="info-box-number"><?php echo $db->getRecordNum();?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-files-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">上传材质总数</span>
                        <span class="info-box-number"><?php echo count(scandir("../textures/"))-2;?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->

                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-hdd-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">占用空间大小</span>
                        <span class="info-box-number"><?php echo floor(Utils::getDirSize("../textures/")/1024)."KB";?></span>
                    </div><!-- /.info-box-content -->
                </div><!-- /.info-box -->
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
View::show('footer'); ?>
