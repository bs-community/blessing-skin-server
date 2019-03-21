@extends('admin.master')

@section('title', trans('general.res-options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.res-options')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-warning">@lang('options.res-warning')</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                {!! $forms['resources']->render() !!}

                {!! $forms['redis']->render() !!}
            </div>

            <div class="col-md-6">
                {!! $forms['cache']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
