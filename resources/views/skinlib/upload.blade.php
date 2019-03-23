@extends('skinlib.master')

@section('title', trans('skinlib.upload.title'))

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                @lang('skinlib.upload.title')
            </h1>
        </section>

        <!-- Main content -->
        <section class="content"></section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->

<script>
blessing.extra = @json($extra)
</script>
@endsection
