@extends('admin.master')

@section('title', trans('general.score-options'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.score-options')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                {!! $forms['rate']->render() !!}

                {!! $forms['report']->render() !!}
            </div>

            <div class="col-md-6">
                {!! $forms['sign']->render() !!}

                {!! $forms['sharing']->render() !!}
            </div>

        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
