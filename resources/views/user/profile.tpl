@extends('user.master')

@section('title', '个人资料')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            个人资料
            <small>User Profile</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改头像？</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>请在衣柜中任意皮肤的右下角「<i class="fa fa-cog"></i>」处选择「设为头像」，将会自动截取该皮肤的头部作为头像哦~</p>
                    </div><!-- /.box-body -->
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改密码</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group">
                            <label for="password">旧的密码</label>
                            <input type="password" class="form-control" id="password" value="">
                        </div>

                        <div class="form-group">
                            <label for="new-passwd">新密码</label>
                            <input type="password" class="form-control" id="new-passwd" value="">
                        </div>

                        <div class="form-group">
                            <label for="confirm-pwd">确认密码</label>
                            <input type="password" class="form-control" id="confirm-pwd" value="">
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changePassword()" class="btn btn-primary">修改密码</button>
                    </div>
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改昵称</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <div class="form-group has-feedback">
                            <input id="new-nickname" type="text" class="form-control" placeholder="{{ ($user->getNickName() == '') ? '当前未设置昵称，' : '' }}可使用除一些特殊符号外的任意字符">
                            <span class="glyphicon glyphicon-user form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changeNickName()" class="btn btn-primary">提交</button>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">更改邮箱</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <div class="form-group has-feedback">
                            <input id="new-email" type="email" class="form-control" placeholder="新邮箱">
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback" style="display: none;">
                            <input id="current-password" type="password" class="form-control" placeholder="当前密码">
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changeEmail()" class="btn btn-warning">修改邮箱</button>
                    </div>
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">删除账号</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        @if (!$user->is_admin)
                        <p>确定要删除你在 {{ Option::get('site_name') }} 上的账号吗？</p>
                        <button id="delete" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-account">删除我的账户</button>
                        @else
                        <p>管理员账号不能被删除哟</p>
                        <button class="btn btn-danger" disabled="disabled">删除我的账户</button>
                        @endif
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-delete-account" class="modal modal-danger fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">这是危险操作，输入密码以继续</h4>
            </div>
            <div class="modal-body">
                <p>此操作不可恢复！</p>
                <p>你所上传至皮肤库的材质仍会被保留，但你的角色将被永久删除。</p>
                <p>我们不提供任何备份，或者神奇的撤销按钮。</p>
                <p>我们警告过你了，确定要这样做吗？</p>
                <br />
                <input type="password" class="form-control" id="password" placeholder="当前密码">
                <br />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">关闭</button>
                <a href="javascript:deleteAccount();" class="btn btn-outline">提交</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
