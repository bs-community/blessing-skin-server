<?php
/**
 * @Author: printempw
 * @Date:   2016-03-06 14:19:20
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-03-18 22:49:28
 */
require "../includes/session.inc.php";
if (!$user->is_admin) header('Location: ../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "用户管理"));
$db = new Database\Database();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            用户管理
            <small>User Manage</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>用户名</th>
                            <th>预览材质</th>
                            <th>更改材质</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $page_now = isset($_GET['page']) ? $_GET['page'] : 1;
                        $db = new Database\Database();
                        $result = $db->query("SELECT * FROM users ORDER BY `uid` LIMIT ".(string)(($page_now-1)*30).", 30");
                        $page_total = $db->getRecordNum()/30;
                        while ($row = $result->fetch_array()) { ?>
                        <tr>
                            <td><?php echo $row['uid']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <img width="64" <?php if ($row['hash_steve']): ?>src="../skin/<?php echo $row['username']; ?>-steve.png"<?php endif; ?> />
                                <img width="64" <?php if ($row['hash_alex']): ?>src="../skin/<?php echo $row['username']; ?>-alex.png"<?php endif; ?> />
                                <img width="64" <?php if ($row['hash_cape']): ?>src="../cape/<?php echo $row['username']; ?>.png"<?php endif; ?> />
                            </td>
                            <td>
                                <a href="javascript:uploadSkin('<?php echo $row['username']; ?>');" class="btn btn-primary btn-sm">皮肤</a>
                                <a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'cape');" class="btn btn-primary btn-sm">披风</a>
                                <a href="javascript:changeModel('<?php echo $row['username']; ?>');" class="btn btn-default btn-sm">优先模型</a>
                                <span>(<?php echo $row['preference']; ?>)</span>
                            </td>
                            <td>
                                <a href="javascript:deleteTexture('<?php echo $row['username'] ?>');" class="btn btn-warning btn-sm">删除材质</a>
                                <a href="javascript:changePasswd('<?php echo $row['username'] ?>');" class="btn btn-default btn-sm">更改密码</a>
                                <a href="javascript:deleteAccount('<?php echo $row['username'] ?>');" class="btn btn-danger btn-sm">删除用户</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <ul class="pagination">
            <?php if ($page_now == 1): ?>
            <li class="disabled">
                <a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
            </li>
            <?php else: ?>
            <li>
                <a href="manage.php?page=<?php echo $page_now-1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php endif;

            for ($i = 1; $i <= $page_total; $i++) {
                if ($i == $page_now) {
                    echo '<li class="active"><a href="#">'.(string)$i.'</a></li>';
                } else {
                    echo '<li><a href="manage.php?page='.$i.'">'.(string)$i.'</a></li>';
                }
            }

            if ($page_now == $page_total): ?>
            <li class="disabled">
                <a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
            </li>
            <?php else: ?>
            <li>
                <a href="manage.php?page=<?php echo $page_now+1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php endif; ?>
         </ul>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/js/admin.utils.js"></script>
EOT;
View::show('footer', $data); ?>
