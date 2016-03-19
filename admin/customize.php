<?
/**
 * @Author: printempw
 * @Date:   2016-03-19 14:34:21
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-19 15:06:36
 */
require "../includes/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
$data['style'] = <<< 'EOT'
<link rel="stylesheet" href="../libs/AdminLTE/dist/css/skins/_all-skins.min.css">
<style>
.callout {
    margin: 15px 0;
}
</style>
EOT;
$data['page_title'] = "个性化";
View::show('admin/header', $data);
$db = new Database\Database();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            个性化
            <small>Customize</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改配色</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table id="layout-skins-list" class="table table-striped bring-up nth-2-center">
                            <tbody>
                                <tr>
                                    <td>蓝色主题（默认）</td>
                                    <td><a href="#" data-skin="skin-blue" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>蓝色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-blue-light" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>黄色主题</td>
                                    <td><a href="#" data-skin="skin-yellow" class="btn btn-warning btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>黄色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-yellow-light" class="btn btn-warning btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>绿色主题</td>
                                    <td><a href="#" data-skin="skin-green" class="btn btn-success btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>绿色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-green-light" class="btn btn-success btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>基佬紫</td>
                                    <td><a href="#" data-skin="skin-purple" class="btn bg-purple btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>紫色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-purple-light" class="btn bg-purple btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>喜庆红（笑）</td>
                                    <td><a href="#" data-skin="skin-red" class="btn btn-danger btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>红色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-red-light" class="btn btn-danger btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>高端黑</td>
                                    <td><a href="#" data-skin="skin-black" class="btn bg-black btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                                <tr>
                                    <td>黑色主题-白色侧边栏</td>
                                    <td><a href="#" data-skin="skin-black-light" class="btn bg-black btn-xs"><i class="fa fa-eye"></i></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button id="color-submit" class="btn btn-primary">提交</button>
                        <div id="msg" class="callout callout-info hide"></div>
                    </div>
                </div><!-- /.box -->
            </div>

            <div class="col-md-6">
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript">
// Skin switcher
var current_skin = "skin-blue";
$('#layout-skins-list [data-skin]').click(function(e) {
    e.preventDefault();
    var skin_name = $(this).data('skin');
    $('body').removeClass(current_skin);
    $('body').addClass(skin_name);
    current_skin = skin_name;
});
$('#color-submit').click(function() {
    $.ajax({
        type: "POST",
        url: "admin_ajax.php?action=color",
        dataType: "json",
        data: { "color_scheme": current_skin },
        beforeSend: function() {
            showCallout('alert-info', '提交中。。');
        },
        success: function(json) {
            console.log(json);
            if (json.errno == 0) {
                showCallout('alert-info', '设置已保存。');
            } else {
                showCallout('alert-warning', json.msg);
            }
        }
    });
});
</script>
EOT;
View::show('footer', $data); ?>
