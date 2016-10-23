@extends('user.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.dashboard') }}
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
                        <h3 class="box-title">{{ trans('user.used.title') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="progress-group">
                                    <span class="progress-text">{{ trans('user.used.players') }}</span>
                                    <?php
                                        $players_available = $user->players->count() + floor($user->getScore() / option('score_per_player'));
                                        $percent = ($players_available == 0) ? 0 : $user->players->count() / $players_available * 100
                                    ?>
                                    <span class="progress-number"><b>{{ $user->players->count() }}</b>/{{ $players_available }}</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">{{ trans('user.used.storage') }}</span>
                                    <?php $rate = option('score_per_storage'); ?>
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
                                    <strong>{{ trans('user.cur-score') }}</strong>
                                </p>
                                <p id="score" data-toggle="modal" data-target="#modal-score-instruction">
                                    {{ $user->getScore() }}
                                </p>
                                <p class="text-center" style="font-size: smaller; margin-top: 20px;">{{ trans('user.score-notice') }}</p>
                            </div><!-- /.col -->
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                    <div class="box-footer">
                        @if ($user->canCheckIn())
                        <button id="checkin-button" class="btn btn-primary pull-left" onclick="checkin()">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;{{ trans('user.checkin') }}
                        </button>
                        @else
                        <button class="btn btn-primary pull-left" title="{{ trans('user.last-checkin', ['time' => $user->getLastSignTime()]) }}" disabled="disabled">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;{{ trans('user.checkin-remain-time', ['time' => $user->canCheckIn(true)]) }}
                        </button>
                        @endif
                    </div><!-- /.box-footer -->
                </div><!-- /.box -->
            </div><!-- /.col -->

            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('user.announcement') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! nl2br(option('announcement')) !!}
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
                <h4 class="modal-title">{{ trans('user.score-intro.title') }}</h4>
            </div>
            <div class="modal-body">
                <?php list($from, $to) = explode(',', Option::get('sign_score')); ?>
                {!! nl2br(trans('user.score-intro.introduction', [
                    'initial_score' => option('user_initial_score'),
                    'score-from'    => $from,
                    'score-to'      => $to
                ])) !!}

                <hr />

                <div class="row">
                    <div class="col-md-6">
                        <p class="text-center">{{ trans('user.score-intro.rates.storage', ['score' => option('score_per_storage')]) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-center">{{ trans('user.score-intro.rates.player', ['score' => option('score_per_player')]) }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('general.close') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
