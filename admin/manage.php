<?php
/**
 * @Author: printempw
 * @Date:   2016-03-06 14:19:20
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 20:58:13
 */
require "../libraries/session.inc.php";
if (!$user->is_admin) Utils::redirect('../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "用户管理"));
$db = new Database\Database('users');

if (isset($_GET['show'])) {
    View::show('admin/show', ['uid' => (int)$_GET['show']]);
} else {
    View::show('admin/list');
}

$data['script'] = <<< 'EOT'
<script type="text/javascript" src="../assets/js/admin.utils.js"></script>
<script>
$('#page-select').on('change', function() {
    window.location = "manage.php?page="+$(this).val();
});
</script>
EOT;
if (isset($_GET['show']))
    $data['script'] .= '<script type="text/javascript" src="../assets/js/preview.utils.js"></script>';
View::show('admin/footer', $data); ?>
