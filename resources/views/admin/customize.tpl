@extends('admin.master')

@section('title', trans('general.customize'))

@section('style')
<link rel="stylesheet" href="{{ assets('vendor/skins/_all-skins.min.css') }}">
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.customize') }}
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
                {!! $homepage->render() !!}

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">自定义 CSS/JavaScript
                            <i class="fa fa-question-circle" title="字符串将不会被转义，请小心" data-toggle="tooltip" data-placement="bottom"></i>
                        </h3>
                    </div><!-- /.box-header -->
                    <form method="post">
                        <input type="hidden" name="option" value="adapter">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['custom_css']) && isset($_POST['custom_js'])) {
                                Option::set('custom_css', $_POST['custom_css']);
                                Option::set('custom_js', $_POST['custom_js']);
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } else { ?>
                                <div class="callout callout-info">
                                    内容将会被追加至每个页面的 &lt;style&gt; 和 &lt;script&gt; 标签中。<br>
                                    - 这里有一些有用的示例：<a href="https://github.com/printempw/blessing-skin-server/wiki/%E3%80%8C%E8%87%AA%E5%AE%9A%E4%B9%89-CSS-JavaScript%E3%80%8D%E5%8A%9F%E8%83%BD%E7%9A%84%E4%B8%80%E4%BA%9B%E5%AE%9E%E4%BE%8B">「自定义 CSS JavaScript」功能的一些实例@GitHub WiKi</a>
                                </div>
                            <?php } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">CSS</td>
                                        <td class="value">
                                           <textarea name="custom_css" class="form-control" rows="6">{{ option('custom_css') }}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="key">JavaScript</td>
                                        <td class="value">
                                           <textarea name="custom_js" class="form-control" rows="6">{{ option('custom_js') }}</textarea>
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
var current_skin = "{{ option('color_scheme') }}";
</script>

@endsection


