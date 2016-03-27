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
                        $page_total = round($db->getRecordNum()/30);
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
                            </td>
                            <td>
                                <a href="manage.php?show=<?php echo $row['uid']; ?>" class="btn btn-default btn-sm">材质预览</a>
                                <a href="javascript:deleteTexture('<?php echo $row['username'] ?>');" class="btn btn-warning btn-sm">删除材质</a>
                                <a href="javascript:changePasswd('<?php echo $row['username'] ?>');" class="btn btn-default btn-sm">更改密码</a>
                                <a class="btn btn-danger btn-sm"
                                   <?php if ($row['uid'] == 1)
                                        echo 'disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="少年，不要作死哦"';
                                    else
                                        echo 'href="javascript:deleteAccount(\''.$row['username'].'\');"';
                                    ?>>
                                    删除用户
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <ul class="pager">
            <?php if ($page_now != 1 && $page_now >= 5): ?>
            <li>
                <a href="manage.php?page=1">1...</a>
            </li>
            <?php endif;

            if ($page_now > $page_total - 2) {
                $from = $page_total - 4;
                $to = $page_total;
            } elseif ($page_now > 2) {
                $from = $page_now - 2;
                $to = $page_now + 2;
            } else {
                $from = 1;
                $to = 6;
            }

            for ($i = $from; $i <= $to; $i++) {
                if ($i == $page_now) {
                    echo '<li class="active"><a href="#">'.(string)$i.'</a></li>';
                } else {
                    echo '<li><a href="manage.php?page='.$i.'">'.(string)$i.'</a></li>';
                }
            } ?>

            <select id="page-select">
                <?php for ($i = 1; $i <= $page_total; $i++) {
                    echo "<option value='$i'>$i</option>";
                } ?>
            </select>
         </ul>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
