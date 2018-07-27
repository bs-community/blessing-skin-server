@extends('user.master')

@section('title', trans('general.profile'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.profile') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (option('require_verification') && !$user->verified)
            @include('common.email-verification')
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.profile.avatar.title') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! trans('user.profile.avatar.notice') !!}
                    </div><!-- /.box-body -->
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.profile.password.title') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="form-group">
                            <label for="password">{{ trans('user.profile.password.old') }}</label>
                            <input type="password" class="form-control" id="password" value="">
                        </div>

                        <div class="form-group">
                            <label for="new-passwd">{{ trans('user.profile.password.new') }}</label>
                            <input type="password" class="form-control" id="new-passwd" value="">
                        </div>

                        <div class="form-group">
                            <label for="confirm-pwd">{{ trans('user.profile.password.confirm') }}</label>
                            <input type="password" class="form-control" id="confirm-pwd" value="">
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changePassword()" class="btn btn-primary">{{ trans('user.profile.password.button') }}</button>
                    </div>
                </div><!-- /.box -->
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.profile.nickname.title') }}</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <div class="form-group has-feedback">
                            <input id="new-nickname" type="text" class="form-control" placeholder="{{ ($user->getNickName() == '') ? trans('user.profile.nickname.empty') : '' . trans('user.profile.nickname.rule') }}">
                            <span class="glyphicon glyphicon-user form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changeNickName()" class="btn btn-primary">{{ trans('general.submit') }}</button>
                    </div>
                </div>

                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.profile.email.title') }}</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        <div class="form-group has-feedback">
                            <input id="new-email" type="email" class="form-control" placeholder="{{ trans('user.profile.email.new') }}">
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback" style="display: none;">
                            <input id="current-password" type="password" class="form-control" placeholder="{{ trans('user.profile.email.password') }}">
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button onclick="changeEmail()" class="btn btn-warning">{{ trans('user.profile.email.button') }}</button>
                    </div>
                </div>

                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.profile.delete.title') }}</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                        @if (!$user->isAdmin())
                        <p>{{ trans('user.profile.delete.notice', ['site' => option_localized('site_name')]) }}</p>
                        <button id="delete" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-account">{{ trans('user.profile.delete.button') }}</button>
                        @else
                        <p>{{ trans('user.profile.delete.admin') }}</p>
                        <button class="btn btn-danger" disabled="disabled">{{ trans('user.profile.delete.button') }}</button>
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
                <h4 class="modal-title">{{ trans('user.profile.delete.modal-title') }}</h4>
            </div>
            <div class="modal-body">
                {!! nl2br(trans('user.profile.delete.modal-notice')) !!}
                <br />
                <input type="password" class="form-control" id="password" placeholder="{{ trans('user.profile.delete.password') }}">
                <br />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">{{ trans('general.close') }}</button>
                <a onclick="deleteAccount();" class="btn btn-outline">{{ trans('general.submit') }}</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
