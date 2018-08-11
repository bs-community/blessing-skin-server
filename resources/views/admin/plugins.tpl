@extends('admin.master')

@section('title', trans('general.plugin-manage'))

@section('style')
<style>
/* Fix datatable column width issue caused by AdminLTE.layout.activate() */
html { height: auto; }
.content-wrapper { min-height: 0%; }
</style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.plugin-manage') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (session()->has('message'))
            <div class="callout callout-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        <div class="box">
            <div class="box-body table-bordered">
                <table id="plugin-table" class="table table-hover"></table>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection
