@extends('user.master')

@section('title', trans('general.player-manage'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.player-manage')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content"></section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
var __bs_data__ = {
    rule: "@lang('user.player.player-name-rule.'.option('player_name_rule'))",
    length: "@lang('user.player.player-name-length', ['min' => option('player_name_length_min'), 'max' => option('player_name_length_max')])"
}
</script>

@endsection
