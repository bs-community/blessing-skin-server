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
blessing.extra = {
    rule: "{{ option('texture_name_regexp') ? trans('skinlib.upload.name-rule-regexp', compact('regexp')) : trans('skinlib.upload.name-rule') }}",
    privacyNotice: "@lang('skinlib.upload.private-score-notice', ['score' => option('private_score_per_storage')])",
    scorePublic: {{ option('score_per_storage') }},
    scorePrivate: {{ option('private_score_per_storage') }},
    award: {{ option('score_award_per_texture') }},
}
</script>
@endsection
