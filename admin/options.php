<?php
/**
 * @Author: printempw
 * @Date:   2016-03-18 22:50:25
 * @Last Modified by:   printempw
 * @Last Modified time: 2016-04-03 07:55:54
 */
require "../libraries/session.inc.php";
if (!$user->is_admin) Utils::redirect('../index.php?msg=看起来你并不是管理员');
View::show('admin/header', array('page_title' => "站点配置"));
$db = new Database\Database('users');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            站点配置
            <small>Options</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">常规选项</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="options.php">
                        <input type="hidden" name="option" value="general">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "general")) {
                                // pre-set user_can_register because it will not be posted if not checked
                                if (!isset($_POST['user_can_register'])) $_POST['user_can_register'] = '0';
                                foreach ($_POST as $key => $value) {
                                    if ($key != "option" && $key != "submit") {
                                        Option::set($key, $value);
                                        // echo $key."=".$value."<br />";
                                    }
                                }
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">站点标题</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_name" value="<?php echo Option::get('site_name'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">站点描述</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_description" value="<?php echo Option::get('site_description'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">站点地址（URL）</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_url" value="<?php echo Option::get('site_url'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">开放注册</td>
                                        <td class="value">
                                            <label for="user_can_register">
                                                <input <?php echo (Option::get('user_can_register') == '1') ? 'checked="true"' : ''; ?> type="checkbox" id="user_can_register" name="user_can_register" value="1">任何人都可以注册
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">每个 IP 限制注册数</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="regs_per_ip" value="<?php echo Option::get('regs_per_ip'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">首选 JSON API</td>
                                        <td class="value">
                                           <select class="form-control" name="api_type">selected="selected"
                                                <option <?php echo (Option::get('api_type') == '0') ? 'selected="selected"' : ''; ?> value="0">CustomSkinLoader API</option>
                                                <option <?php echo (Option::get('api_type') == '1') ? 'selected="selected"' : ''; ?> value="1">UniversalSkinAPI</option>
                                           </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">站点公告</td>
                                        <td class="value">
                                           <textarea name="announcement" class="form-control" rows="3"><?php echo Option::get('announcement'); ?></textarea>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">数据对接配置</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="options.php">
                        <input type="hidden" name="option" value="adapter">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "adapter")) {
                                foreach ($_POST as $key => $value) {
                                    if ($key != "option" && $key != "submit") {
                                        Option::set($key, $value);
                                        //echo $key."=".$value."<br />";
                                    }
                                }
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } else {
                                echo '<div class="callout callout-warning">如果你不知道下面这些是干什么的，请不要继续编辑。</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">数据对接适配器</td>
                                        <td class="value">
                                           <select class="form-control" name="data_adapter">
                                                <option <?php echo (Option::get('data_adapter') == '') ? 'selected="selected"' : ''; ?> value="">不进行数据对接</option>
                                                <option <?php echo (Option::get('data_adapter') == 'Authme') ? 'selected="selected"' : ''; ?> value="Authme">Authme</option>
                                                <option <?php echo (Option::get('data_adapter') == 'Crazy') ? 'selected="selected"' : ''; ?> value="Crazy">CrazyLogin</option>
                                                <option <?php echo (Option::get('data_adapter') == 'Discuz') ? 'selected="selected"' : ''; ?> value="Discuz">Discuz</option>
                                           </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">对接数据表名</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="data_table_name" value="<?php echo Option::get('data_table_name'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">对接数据表用户名字段</td>
                                        <td class="value">
                                           <input data-toggle="tooltip" data-placement="bottom" title="如果你没有修改插件配置的话，请保持默认。CrazyLogin 的话请将此字段改为 `name`。" type="text" class="form-control" name="data_column_uname" value="<?php echo Option::get('data_column_uname'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">对接数据表密码字段</td>
                                        <td class="value">
                                           <input data-toggle="tooltip" data-placement="bottom" title="同上，不要瞎球改。" type="text" class="form-control" name="data_column_passwd" value="<?php echo Option::get('data_column_passwd'); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">对接数据表 IP 字段</td>
                                        <td class="value">
                                           <input data-toggle="tooltip" data-placement="bottom" title="CrazyLogin 的话请将此字段改为 `ips`，Discuz 请改为 `regip`。" type="text" class="form-control" name="data_column_ip" value="<?php echo Option::get('data_column_ip'); ?>">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<?php
View::show('footer'); ?>
