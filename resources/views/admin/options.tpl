@extends('admin.master')

@section('title', trans('general.options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.options') }}
            <small>Options</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                {!! $forms['general']->render() !!}
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
                            <p class="description">可使用 Markdown 进行排版</p>

                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>

                {!! $forms['cache']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('style')
<style type="text/css">
.box-body > textarea { height: 200px; }
.description { margin: 7px 0 0 0; color: #555; }
</style>
@endsection
