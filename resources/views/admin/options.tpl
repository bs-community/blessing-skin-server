@extends('admin.master')

@section('title', trans('general.options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.options') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                @if (! App\Http\Controllers\SetupController::checkNewColumnsExist())
                <div class="callout callout-danger">
                    <h4><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ trans('setup.integrity-check.title') }}</h4>
                    <p>{!! trans('setup.integrity-check.description') !!}</p>
                </div>
                @endif

                {!! $forms['general']->render() !!}
            </div>

            <div class="col-md-6">
                {!! $forms['announ']->render() !!}

                {!! $forms['resources']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
