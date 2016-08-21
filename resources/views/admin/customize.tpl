@extends('admin.master')

@section('title', '个性化')

@section('style')
<link rel="stylesheet" href="../assets/libs/skins/_all-skins.min.css">
@endsection

@section('content')

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
            <div class="col-md-3">
                <div class="box box-primary">
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
                    </div>
                </div><!-- /.box -->


            </div>

            <div class="col-md-9">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">首页配置</h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="../admin/customize">
                        <input type="hidden" name="option" value="adapter">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['home_pic_url'])) {
                                if (!isset($_POST['show_footer_copyright'])) $_POST['show_footer_copyright'] = '0';

                                Option::set('home_pic_url', $_POST['home_pic_url']);
                                Option::set('show_footer_copyright', $_POST['show_footer_copyright']);
                                Option::set('copyright_text', $_POST['copyright_text']);
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">首页图片地址</td>
                                        <td class="value">
                                           <input type="text" title="相对于首页的路径或者完整的 URL" data-toggle="tooltip" data-placement="top" class="form-control" name="home_pic_url" value="{{ Option::get('home_pic_url') }}">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">版权信息
                                            <i class="fa fa-question-circle" title="推荐开启，求扩散 qwq" data-toggle="tooltip" data-placement="top"></i>
                                        </td>
                                        <td class="value">
                                            <label for="show_footer_copyright">
                                                <input {{ (Option::get('show_footer_copyright') == '1') ? 'checked="true"' : '' }} type="checkbox" id="show_footer_copyright" name="show_footer_copyright" value="1"> 显示页面右下角的版权信息
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">版权文字</td>
                                        <td class="value">
                                            <textarea class="form-control" rows="4" name="copyright_text">{{ Option::get('copyright_text') }}</textarea>
                                            <p class="description">自定义版权文字内可使用占位符，<code>{site_name}</code> 将会被自动替换为站点名称，<code>{site_url}</code> 会被替换为站点地址。</p>
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

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">自定义 CSS/JavaScript
                            <i class="fa fa-question-circle" title="字符串将不会被转义，请小心" data-toggle="tooltip" data-placement="bottom"></i>
                        </h3>
                    </div><!-- /.box-header -->
                    <form method="post" action="../admin/customize">
                        <input type="hidden" name="option" value="adapter">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['custom_css']) && isset($_POST['custom_js'])) {
                                Option::set('custom_css', $_POST['custom_css']);
                                Option::set('custom_js', $_POST['custom_js']);
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } else {
                                echo '<div class="callout callout-info">内容将会被追加至每个页面的 &lt;style&gt; 和 &lt;script&gt; 标签中</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">CSS</td>
                                        <td class="value">
                                           <textarea name="custom_css" class="form-control" rows="6">{{ Option::get('custom_css') }}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">JavaScript</td>
                                        <td class="value">
                                           <textarea name="custom_js" class="form-control" rows="6">{{ Option::get('custom_js') }}</textarea>
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

<script type="text/javascript">
var current_skin = "{{ Option::get('color_scheme') }}";
</script>

@endsection


