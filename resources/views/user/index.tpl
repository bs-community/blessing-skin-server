@extends('user.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.dashboard') }}
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
                                    <span class="progress-number"><b>{{ $statistics['players']['used'] }}</b>/ {{ $statistics['players']['total'] }}</span>
                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-aqua" style="width: {{ $statistics['players']['percentage'] }}%"></div>
                                    </div>
                                </div><!-- /.progress-group -->
                                <div class="progress-group">
                                    <span class="progress-text">{{ trans('user.used.storage') }}</span>

                                    <?php
                                        $used  = $statistics['storage']['used'];
                                        $total = $statistics['storage']['total'];
                                    ?>

                                    <span class="progress-number" id="user-storage">
                                        @if ($used > 1024)
                                            <b>{{ round($used / 1024, 1) }}</b>/ {{ is_string($total) ? $total : round($total / 1024, 1) }} MB
                                        @else
                                            <b>{{ $used }}</b>/ {{ $total }} KB
                                        @endif
                                    </span>

                                    <div class="progress sm">
                                        <div class="progress-bar progress-bar-yellow" id="user-storage-bar" style="width: {{ $statistics['storage']['percentage'] }}%"></div>
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
                        @if ($user->canSign())
                        <button id="sign-button" class="btn btn-primary pull-left" onclick="sign()">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;{{ trans('user.sign') }}
                        </button>
                        @else
                        <button class="btn btn-primary pull-left" title="{{ trans('user.last-sign', ['time' => $user->getLastSignTime()]) }}" disabled="disabled">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> &nbsp;
                            <?php $hours = $user->getSignRemainingTime() / 3600; ?>
                            @if ($hours >= 1)
                                {{ trans('user.sign-remain-time', ['time' => round($hours), 'unit' => trans('user.time-unit-hour')]) }}
                            @else
                                {{ trans('user.sign-remain-time', ['time' => round($hours * 60), 'unit' => trans('user.time-unit-min')]) }}
                            @endif
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
                        {!! bs_announcement() !!}
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
                    'score-to'      => $to,
                    'return-score'  => option('return_score') ? trans('user.score-intro.will-return-score') : trans('user.score-intro.no-return-score')
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
