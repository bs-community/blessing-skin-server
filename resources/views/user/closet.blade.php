@extends('user.master')

@section('title', trans('general.my-closet'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.my-closet')
        </h1>
        <div class="breadcrumb">
            <a href="{{ url('skinlib/upload') }}"><i class="fas fa-file-upload"></i> @lang('user.closet.upload')</a>
            <a href="{{ url('skinlib') }}"><i class="fas fa-search"></i> @lang('user.closet.search')</a>
        </div>
    </section>

    <!-- Main content -->
    <section class="content"></section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
Object.defineProperty(blessing, 'extra', {
    get: () => Object.freeze(@json($extra))
})
</script>
@endsection
