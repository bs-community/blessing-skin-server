@extends('user.master')

@section('title', trans('general.profile'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.profile')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content"></section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
Object.defineProperty(blessing, 'extra', {
    configurable: false,
    get: () => Object.freeze(@json($extra)),
})
</script>
@endsection
