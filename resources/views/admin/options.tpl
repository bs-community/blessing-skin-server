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
        <div class="breadcrumb"></div>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-6">
                {!! $forms['general']->render() !!}
            </div>

            <div class="col-md-6">
                {!! $forms['announ']->render() !!}

                {!! $forms['cache']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('style')
<style type="text/css">
.box-body > textarea { height: 200px; }
.description { margin: 7px 0 0 0; color: #555; }
</style>
@endsection
