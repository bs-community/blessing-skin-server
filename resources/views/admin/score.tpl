@extends('admin.master')

@section('title', trans('general.score-options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.score-options') }}
            <small>Score Options</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                {!! $rate->render() !!}
            </div>

            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">签到配置</h3>
                    </div><!-- /.box-header -->
                    <form method="post">
                        <input type="hidden" name="option" value="sign">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['option']) && ($_POST['option'] == "sign")) {
                                $sign_score = $_POST['sign_score_from'].','.$_POST['sign_score_to'];
                                Option::set('sign_score', $sign_score);
                                Option::set('sign_gap_time', $_POST['sign_gap_time']);
                                Option::set('sign_after_zero', isset($_POST['sign_after_zero']) ? '1' : '0');
                                echo '<div class="callout callout-success">设置已保存。</div>';
                            } ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">签到获得积分</td>
                                        <td class="value">
                                            <div class="input-group">
                                            <input type="text" class="form-control" name="sign_score_from" value="{{ explode(',', option('sign_score'))[0] }}">
                                            <span class="input-group-addon" style="border-right: 0; border-left: 0;">积分 ~ </span>
                                            <input type="text" class="form-control" name="sign_score_to" value="{{ explode(',', option('sign_score'))[1] }}">
                                            <span class="input-group-addon">积分</span>
                                          </div>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">签到间隔时间</td>
                                        <td class="value">
                                            <div class="input-group">
                                            <input type="text" class="form-control" name="sign_gap_time" value="{{ option('sign_gap_time') }}">
                                            <span class="input-group-addon">小时</span>
                                          </div>

                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">签到时间
                                            <i class="fa fa-question-circle" title="勾选后将无视上一条，每天零时后均可签到" data-toggle="tooltip" data-placement="top"></i>
                                        </td>
                                        <td class="value">
                                            <label for="sign_after_zero">
                                                <input {{ (option('sign_after_zero') == '1') ? 'checked="true"' : '' }} type="checkbox" id="sign_after_zero" name="sign_after_zero" value="1"> 每天零点后可签到
                                            </label>
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

@endsection
