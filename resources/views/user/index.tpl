@extends('user.master')

@section('title', '仪表盘')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            仪表盘
            <small>Dashboard</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">

        </div><!-- /.row -->

        <div class="row">
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">使用情况</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="progress-group">
                                    <span class="progress-text">角色数量</span>
                                    <?php
                                        $players_available = count($user->getPlayers()) + floor($user->getScore() / Option::get('score_per_player'));
                                        $percent = ($players_available == 0) ? 0 : count($user->getPlayers()) / $players_available * 100
                                    ?>
                                    <span class="progress-number"><b>{{ count($user->getPlayers()) }}</b>/{{ $players_available }}</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">存储空间</span>
                                    <?php $rate = Option::get('score_per_storage'); ?>
                                    @if ($user->getStorageUsed() > 1024)
                                    <span class="progress-number">
                                        <b>{{ round($user->getStorageUsed() / 1024, 1) }}</b>/
                                        {{ round(($user->getStorageUsed() + $user->getScore() / $rate) / 1024, 1) }} MB
                                    </span>
                                    @else
                                    <span class="progress-number">
                                        <b>{{ $user->getStorageUsed() }}</b>/
                                        {{ $user->getStorageUsed() + $user->getScore() / $rate }} KB
                                    </span>
                                    @endif

                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" style="width: {{ $user->getStorageUsed() / ($user->getStorageUsed() + $user->getScore() / $rate) * 100 }}%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                            </div><!-- /.col -->
                            <div class="col-md-4">
                                <p class="text-center">
                                    <strong>当前积分</strong>
                                </p>
                                <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
                                    {{ $user->getScore() }}
                                </p>
                                <p class="text-center" style="font-size: smaller; margin-top: 20px;">点击积分查看说明</p>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                    <div class="box-footer">
                        @if ($user->canSign())
                        <button id="sign-button" class="btn btn-primary pull-left" onclick="sign()">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;每日签到
                        </button>
                        @else
                        <button class="btn btn-primary pull-left" title="上次签到于 {{ $user->getLastSignTime() }}" disabled="disabled">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;{{ $user->canSign(true) }} 小时后可签到
                        </button>
                        @endif
                    </div><!-- /.box-footer -->
                </div><!-- /.box -->
            </div><!-- /.col -->

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">公告</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! nl2br(Option::get('announcement')) !!}
                    </div><!-- /.box-body -->
                </div>
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div id="modal-score-instruction" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">积分是个啥？</h4>
            </div>
            <div class="modal-body">
                <p>「既然你诚心诚意地发问了！」</p>
                <p>「那我们就大发慈悲地告诉你！」</p>
                <p>「为了守护皮肤站的和平」</p>
                <p>「为了防止皮肤站被破坏」</p>
                <p>「贯彻爱与真实的。。呸！」上面只是卖下萌~</p>

                <hr />

                <p>为了不出现用户一个劲上传材质导致存储空间爆满，我们决定启用积分系统。</p>
                <p>添加角色以及上传材质都会消耗积分，而删除已经添加的角色和已上传的材质时积分将会被返还。</p>
                <?php $sign_score = explode(',', Option::get('sign_score')); ?>
                <p>本站用户初始积分为 {{ \Option::get('user_initial_score') }}，每日签到可以随机获得 {{ $sign_score[0] }} ~ {{ $sign_score[1] }} 积分</p>
                <p>添加皮肤库里的材质到衣柜不消耗积分。</p>

                <hr />

                <div class="row">
                    <div class="col-md-6">
                        <p class="text-center">{{ Option::get('score_per_storage') }} 积分 = 1 KB 存储空间</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-center">{{ Option::get('score_per_player') }} 积分 = 1 个角色</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
