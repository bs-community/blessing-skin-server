@extends('admin.master')

@section('title', '站点配置')

@section('style')
<style type="text/css">
.box-body > textarea {
    height: 200px;
}
</style>
@endsection

@section('content')

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
                    <form method="post" action="../admin/options">
                        <input type="hidden" name="option" value="general">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "general")) {
                                // pre-set user_can_register because it will not be posted if not checked
                                $_POST['user_can_register'] = isset($_POST['user_can_register']) ? $_POST['user_can_register'] : "0";
                                $_POST['allow_chinese_playername'] = isset($_POST['allow_chinese_playername']) ? $_POST['allow_chinese_playername'] : "0";

                                foreach ($_POST as $key => $value) {
                                    // remove slash if site_url is ended with slash
                                    if ($key == "site_url" && substr($value, -1) == "/")
                                        $value = substr($value, 0, -1);

                                    if ($key != "option" && $key != "submit")
                                        Option::set($key, $value);
                                }
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">站点标题</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_name" value="{{ Option::get('site_name') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">站点描述</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_description" value="{{ Option::get('site_description') }}">
                                        </td>
                                    </tr>

                                    <tr title="以 http:// 开头，不要以 / 结尾" data-toggle="tooltip" data-placement="top">
                                        <td class="key">站点地址（URL）</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_url" value="{{ Option::get('site_url') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">开放注册</td>
                                        <td class="value">
                                            <label for="user_can_register">
                                                <input {{ (Option::get('user_can_register') == '1') ? 'checked="true"' : '' }} type="checkbox" id="user_can_register" name="user_can_register" value="1"> 任何人都可以注册
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">每个 IP 限制注册数</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="regs_per_ip" value="{{ Option::get('regs_per_ip') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">新用户默认积分</td>
                                        <td class="value">
                                            <div class="input-group">
                                            <input type="text" class="form-control" name="user_initial_score" value="{{ Option::get('user_initial_score') }}">
                                          </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">角色名</td>
                                        <td class="value">
                                            <label for="allow_chinese_playername">
                                                <input {{ (Option::get('allow_chinese_playername') == '1') ? 'checked="true"' : '' }} type="checkbox" id="allow_chinese_playername" name="allow_chinese_playername" value="1"> 允许中文角色名
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">首选 JSON API</td>
                                        <td class="value">
                                           <select class="form-control" name="api_type">
                                                <option {{ (Option::get('api_type') == '0') ? 'selected="selected"' : '' }} value="0">CustomSkinLoader API</option>
                                                <option {{ (Option::get('api_type') == '1') ? 'selected="selected"' : '' }} value="1">UniversalSkinAPI</option>
                                           </select>
                                        </td>
                                    </tr>

                                    <tr title="留空以停用评论功能" data-toggle="tooltip" data-placement="top">
                                        <td class="key">Disqus 短域名</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="disqus-shortname" value="{{ Option::get('disqus-shortname') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">签到间隔时间</td>
                                        <td class="value">
                                            <div class="input-group">
                                            <input type="text" class="form-control" name="sign_gap_time" value="{{ Option::get('sign_gap_time') }}">
                                            <span class="input-group-addon">小时</span>
                                          </div>

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
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">站点公告</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="../admin/options">
                        <input type="hidden" name="option" value="announcement">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "announcement")) {
                                Option::set('announcement', $_POST['announcement']);
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>

                            <textarea name="announcement" class="form-control" rows="3">{{ Option::get('announcement') }}</textarea>

                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">数据对接配置</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="../admin/options">
                        <input type="hidden" name="option" value="adapter">
                        <div class="box-body">
                            <p>当前版本数据对接不可用。</p>
                        </div><!-- /.box-body -->
                    </form>
                </div>
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
