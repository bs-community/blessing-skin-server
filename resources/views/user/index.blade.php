@extends('user.master')

@section('title', trans('general.dashboard'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.dashboard')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-7">
                <div class="box" id="usage-box"></div><!-- /.box -->
            </div><!-- /.col -->

            <div class="col-md-5">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('user.announcement')</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! $announcement !!}
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
                <h4 class="modal-title">@lang('user.score-intro.title')</h4>
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
                    <div class="col-md-4">
                        <p class="text-center">@lang('user.score-intro.rates.storage', ['score' => option('score_per_storage')])</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-center">@lang('user.score-intro.rates.player', ['score' => option('score_per_player')])</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-center">@lang('user.score-intro.rates.closet', ['score' => option('score_per_closet_item')])</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('general.close')</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
Object.defineProperty(blessing, 'extra', {
    get: () => Object.freeze(@json($extra))
})
</script>
@endsection
