@extends('admin.master')

@section('title', trans('general.customize'))

@section('style')
<link rel="stylesheet" href="{{ webpack_assets('skins/_all-skins.min.css') }}">
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.customize')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-3">
                <div id="change-color"></div><!-- /.box -->
            </div>

            <div class="col-md-9">
                {!! $forms['homepage']->render() !!}

                {!! $forms['customJsCss']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
blessing.extra = @json($extra);
</script>

@endsection
