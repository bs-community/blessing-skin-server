@extends('admin.master')

@section('title', '站点配置')

@section('style')
<style type="text/css">
.box-body > textarea { height: 200px; }
.description { margin: 7px 0 0 0; color: #555; }
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
                    <form method="post">
                        <input type="hidden" name="option" value="general">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "general")) {
                                // pre-set user_can_register because it will not be posted if not checked
                                $_POST['user_can_register'] = isset($_POST['user_can_register']) ? $_POST['user_can_register'] : "0";
                                $_POST['allow_chinese_playername'] = isset($_POST['allow_chinese_playername']) ? $_POST['allow_chinese_playername'] : "0";
                                $_POST['avatar_query_string'] = isset($_POST['avatar_query_string']) ? $_POST['avatar_query_string'] : "0";
                                $_POST['auto_del_invalid_texture'] = isset($_POST['auto_del_invalid_texture']) ? $_POST['auto_del_invalid_texture'] : "0";

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
                                           <input type="text" class="form-control" name="site_name" value="{{ option('site_name') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">站点描述</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_description" value="{{ option('site_description') }}">
                                        </td>
                                    </tr>

                                    <tr title="以 http(s):// 开头，不要以 / 结尾" data-toggle="tooltip" data-placement="top">
                                        <td class="key">站点地址（URL）</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="site_url" value="{{ option('site_url') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">开放注册</td>
                                        <td class="value">
                                            <label for="user_can_register">
                                                <input {{ (option('user_can_register') == '1') ? 'checked="true"' : '' }} type="checkbox" id="user_can_register" name="user_can_register" value="1"> 任何人都可以注册
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">每个 IP 限制注册数</td>
                                        <td class="value">
                                           <input type="text" class="form-control" name="regs_per_ip" value="{{ option('regs_per_ip') }}">
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">角色名</td>
                                        <td class="value">
                                            <label for="allow_chinese_playername">
                                                <input {{ (option('allow_chinese_playername') == '1') ? 'checked="true"' : '' }} type="checkbox" id="allow_chinese_playername" name="allow_chinese_playername" value="1"> 允许中文角色名
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">首选 JSON API</td>
                                        <td class="value">
                                            <select class="form-control" name="api_type">
                                                <option {{ (option('api_type') == '0') ? 'selected="selected"' : '' }} value="0">CustomSkinLoader API</option>
                                                <option {{ (option('api_type') == '1') ? 'selected="selected"' : '' }} value="1">UniversalSkinAPI</option>
                                            </select>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">头像缓存
                                            <i class="fa fa-question-circle" title="如果对头像启用了 CDN 缓存请开启此项" data-toggle="tooltip" data-placement="top"></i>
                                        </td>
                                        <td class="value">
                                            <label for="avatar_query_string">
                                                <input {{ (option('avatar_query_string') == '1') ? 'checked="true"' : '' }} type="checkbox" id="avatar_query_string" name="avatar_query_string" value="1"> 为头像添加 Query String
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">失效材质
                                            <i class="fa fa-question-circle" title="自动从皮肤库中删除文件不存在的材质记录" data-toggle="tooltip" data-placement="top"></i>
                                        </td>
                                        <td class="value">
                                            <label for="auto_del_invalid_texture">
                                                <input {{ (option('auto_del_invalid_texture') == '1') ? 'checked="true"' : '' }} type="checkbox" id="auto_del_invalid_texture" name="auto_del_invalid_texture" value="1"> 自动删除失效材质
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">评论代码
                                            <i class="fa fa-question-circle" title="就是 Disqus，多说，畅言等评论服务提供的代码。留空以停用评论功能" data-toggle="tooltip" data-placement="top"></i>
                                        </td>
                                        <td class="value">
                                            <textarea class="form-control" rows="6" name="comment_script">{{ option('comment_script') }}</textarea>
                                            <p class="description">评论代码内可使用占位符，<code>{tid}</code> 将会被自动替换为材质的 id，<code>{name}</code> 会被替换为材质名称，<code>{url}</code> 会被替换为当前页面地址。</p>
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
                    <form method="post">
                        <input type="hidden" name="option" value="announcement">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "announcement")) {
                                Option::set('announcement', $_POST['announcement']);
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>

                            <textarea name="announcement" class="form-control" rows="3">{{ option('announcement') }}</textarea>
                            <p class="description">站点公告内容不会被转义，因此您可以使用 HTML 进行排版</p>

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
                    <form method="post">
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
