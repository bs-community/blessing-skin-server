<?php
/**
 * @Author: printempw
 * @Date:   2016-03-19 21:00:58
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 07:55:53
 */
require "../libraries/session.inc.php";
if (!$user->is_admin) Utils::redirect('../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "添加用户"));
$db = new Database\Database('users');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            批量添加用户
            <small>Add Users</small>
        </h1>
    </section>
    <style>
        input {
            margin: 0;
        }
        a > i, button > i {
            padding-right: 5px;
        }
    </style>
    <!-- Main content -->
    <section class="content">
        <?php
        function showCallout($type, $id, $msg) {
            echo "<div class='callout callout-".$type."'>$id"."：".$_POST['username-'.$id]." $msg"."</div>";
        }
        if (isset($_POST['submit'])) {
            for ($i = 1; $i <= (int)$_POST['submit']; $i++) {
                if (User::checkValidUname($_POST['username-'.$i])) {
                    $password = ($_POST['password-'.$i] == "") ? '123456' : $_POST['password-'.$i];
                    if (strlen($password) < 16 && strlen($password) > 5) {
                        $user = new User($_POST['username-'.$i]);
                        if (!$user->is_registered) {
                            if ($user->register($password, 'added by admin')) {
                                if ($_FILES['skin-'.$i]['name'] != "") {
                                    if ($user->setTexture('steve', $_FILES['skin-'.$i])) {
                                        showCallout('success', $i, "皮肤上传成功！");
                                    } else {
                                        showCallout('danger', $i, "出现了奇怪的错误。。请联系作者 :(");
                                    }
                                } else {
                                    showCallout('success', $i, "注册成功！密码 $password");
                                }
                            } else {
                                showCallout('danger', $i, "注册失败.");
                            }
                        } else {
                            showCallout('danger', $i, "用户名已被注册。");
                        }
                    } else {
                        showCallout('danger', $i, "无效的密码。密码长度应该大于 6 并小于 15。");
                    }
                } else {
                    showCallout('danger', $i, "无效的用户名。用户名只能包含数字，字母以及下划线。");
                }
            }
        } ?>
        <form method="post" action="adduser.php" enctype="multipart/form-data">
            <div class="box">
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>用户名</th>
                                <th>密码（默认 123456）</th>
                                <th>上传皮肤</th>
                            </tr>
                        </thead>

                        <tbody id="users">
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="javascript:add();" style="float: right; margin-left: 10px;" class="btn btn-primary"><i class="fa fa-plus"></i>添加一个用户</a>
            <button type="submit" name="submit" style="float: right;" class="btn btn-primary"><i class="fa fa-upload"></i>提交</button>
        </form>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/libs/bootstrap-fileinput/js/fileinput.min.js"></script>
<script type="text/javascript" src="../assets/libs/bootstrap-fileinput/js/fileinput_locale_zh.js"></script>
<script type="text/javascript">
var user_count = 1;
$(document).ready(function() {
    add();
});
function add() {
    var dom =  '<tr id="user-'+user_count+'">'+
                    '<td>'+user_count+'</td>'+
                    '<td><input type="text" class="form-control" name="username-'+user_count+'"></td>'+
                    '<td><input type="password" class="form-control" name="password-'+user_count+'"></td>'+
                    '<td><input type="file" class="form-control" name="skin-'+user_count+'" data-show-preview="false" name="site_name" accept="image/png" ></td>'+
                '</tr>';
    $('#users').append($(dom));
    $('input[type=file]').fileinput({showCaption: false, 'showUpload':false, 'language': 'zh'});
    $('button[type=submit]').prop('value', user_count);
    user_count++;
}
</script>
EOT;
View::show('footer', $data); ?>
