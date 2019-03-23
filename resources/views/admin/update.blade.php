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
                                <tr>
                                    <td class="key">@lang('admin.update.info.release-time')</td>
                                    <td class="value">
                                        {{ get_datetime_string($info['release_time']) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">@lang('admin.update.info.change-log.text')</td>
                                    <td class="value">
                                    {!! nl2p($info['release_note']) ?: trans('admin.update.info.change-log.empty') !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">@lang('admin.update.info.downloads.text')</td>
                                    <td class="value">
                                    <a href="{!! $info['release_url'] !!}">@lang('admin.update.info.downloads.link')</a>
                                    </td>
                                </tr>

                                @if($info['pre_release'])
                                <div class="callout callout-warning">@lang('admin.update.info.pre-release-warning')</div>
                                @endif

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
                                <tr>
                                    <td class="key">@lang('admin.update.info.release-time')</td>
                                    <td class="value">
                                        @if ($info['release_time'])
                                        {{ get_datetime_string($info['release_time']) }}
                                        @else
                                        @lang('admin.update.info.pre-release')
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <span id="update-button"></span>
                        {!! trans('admin.update.info.check-github', ['url' => 'https://github.com/printempw/blessing-skin-server/releases']) !!}
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

<div id="modal-start-download" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('admin.update.download.downloading')</h4>
            </div>
            <div class="modal-body">
                <p>@lang('admin.update.download.size')<span id="file-size">0</span> Bytes</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="download-progress">0</span>%
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
blessing.extra = @json($extra)
</script>

@endsection
