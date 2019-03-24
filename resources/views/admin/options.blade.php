@extends('admin.master')

@section('title', trans('general.options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.options')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                {!! $forms['general']->render() !!}
            </div>

            <div class="col-md-6">
                {!! $forms['announ']->render() !!}

                {!! $forms['meta']->render() !!}

                {!! $forms['recaptcha']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
