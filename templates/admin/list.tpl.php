<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            用户管理
            <small>User Manage</small>
            <form method="post" action="manage.php" class="user-search-form">
                <input type="text" name="search-username" class="form-control user-search-input" placeholder="输入用户名，回车搜索。" value="<?php echo Utils::getValue('search-username', $_POST); ?>">
            </form>
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
                        $db = new Database\Database('users');

                        if (isset($_POST['search-username'])) {
                            $result = $db->select(null, null, [
                                'where' => "`username` LIKE '%".$_POST['search-username']."%'",
                                'order' => 'uid',
                                'limit' => (string)(($page_now-1)*30).", 30"
                            ]);
                            $page_total = floor($db->query("SELECT * FROM ".DB_PREFIX."users WHERE `username` LIKE '%".$_POST['search-username']."%'")->num_rows/30) + 1;
                        } else {
                            $result = $db->select(null, null, [
                                'where' => '',
                                'order' => 'uid',
                                'limit' => (string)(($page_now-1)*30).", 30"
                            ], null, true);
                            $page_total = floor($db->getRecordNum()/30) + 1;
                        }

                        while ($row = $result->fetch_array()) { ?>
                        <tr>
                            <td><?php echo $row['uid']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <img id="<?php echo $row['username']; ?>-steve" width="64" <?php if ($row['hash_steve']): ?>src="../skin/<?php echo $row['username']; ?>-steve.png"<?php endif; ?> />
                                <img id="<?php echo $row['username']; ?>-alex"  width="64" <?php if ($row['hash_alex']): ?>src="../skin/<?php echo $row['username']; ?>-alex.png"<?php endif; ?> />
                                <img id="<?php echo $row['username']; ?>-cape"  width="64" <?php if ($row['hash_cape']): ?>src="../cape/<?php echo $row['username']; ?>.png"<?php endif; ?> />
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        上传材质 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'steve');">皮肤（Steve 模型）</a></li>
                                        <li><a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'alex' );">皮肤（Alex 模型）</a></li>
                                        <li><a href="javascript:uploadTexture('<?php echo $row['username']; ?>', 'cape' );">披风</a></li>
                                    </ul>
                                </div>
                                <a href="javascript:deleteTexture('<?php echo $row['username'] ?>');" class="btn btn-warning btn-sm">删除材质</a>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        更多操作 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="manage.php?show=<?php echo $row['uid']; ?>">修改用户名</a></li>
                                        <li><a href="javascript:changePasswd('<?php echo $row['username'] ?>');">更改密码</a></li>
                                        <li><a href="manage.php?show=<?php echo $row['uid']; ?>">修改优先模型</a></li>
                                        <li role="separator" class="divider"></li>
                                        <li><a href="manage.php?show=<?php echo $row['uid']; ?>">材质预览</a></li>
                                    </ul>
                                </div>
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

        <?php if ($page_total != 0): ?>
        <ul class="pager">
            <?php if ($page_now != 1 && $page_now >= 5): ?>
            <li><a href="manage.php?page=1">1...</a></li>
            <?php endif;

            // calculate page numbers to show
            if ($page_total < 5) {
                $from = 1;
                $to = $page_total;
            } elseif ($page_now > $page_total - 2) {
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
         <?php endif; ?>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
