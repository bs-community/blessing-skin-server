@extends('admin.master')

@section('title', trans('general.check-update'))

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.check-update')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin.update.info.title')</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        @if ($info['new_version_available'])
                        <div class="callout callout-info">@lang('admin.update.info.available')</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">@lang('admin.update.info.versions.latest')</td>
                                    <td class="value">
                                        v{{ $info['latest_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">@lang('admin.update.info.versions.current')</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                        @else

                            @if ($connectivity === true)
                            <div class="callout callout-success">{{ trans('admin.update.info.up-to-date') }}</div>
                            @else
                            <div class="callout callout-danger">{{ trans('admin.update.errors.connection', ['error' => $connectivity]) }}</div>
                            @endif

                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">@lang('admin.update.info.versions.current')</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <span id="update-button"></span>
                        {!! trans('admin.update.info.check-github', ['url' => 'https://github.com/bs-community/blessing-skin-server/releases']) !!}
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('admin.update.cautions.title')</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        {!! nl2p(trans('admin.update.cautions.text')) !!}
                    </div><!-- /.box-body -->
                </div>
            </div>

        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
blessing.extra = @json($extra)
</script>
@endsection
