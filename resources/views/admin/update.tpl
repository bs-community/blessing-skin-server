@extends('admin.master')

@section('title', trans('general.check-update'))

@section('style')
<style type="text/css">
.description { margin: 7px 0 0 0; color: #555; }
.description a { color: #3c8dbc; }
</style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.check-update') }}
            <small>Check Update</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.update.update-info') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        @if ($info['new_version_available'])
                        <div class="callout callout-info">{{ trans('admin.update.update-available') }}</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">{{ trans('admin.update.latest-version') }}</td>
                                    <td class="value">
                                        v{{ $info['latest_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">{{ trans('admin.update.current-version') }}</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">{{ trans('admin.update.release-time') }}</td>
                                    <td class="value">
                                        {{ Utils::getTimeFormatted($info['release_time']) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">{{ trans('admin.update.change-log') }}</td>
                                    <td class="value">
                                        {!! nl2br($info['release_note']) ?: "{{ trans('admin.update.no-log') }}" !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">{{ trans('admin.update.download-link') }}</td>
                                    <td class="value">
                                    <a href="{!! $info['release_url'] !!}">{{ trans('admin.update.download-full') }}</a>
                                    </td>
                                </tr>

                                @if($info['pre_release'])
                                <div class="callout callout-warning">{{ trans('admin.update.pre-release-warning') }}</div>
                                @endif

                            </tbody>
                        </table>
                        @else
                        <div class="callout callout-success">{{ trans('admin.update.latest-now') }}</div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <td class="key">{{ trans('admin.update.current-version') }}</td>
                                    <td class="value">
                                        v{{ $info['current_version'] }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="key">{{ trans('admin.update.release-time') }}</td>
                                    <td class="value">
                                        @if (isset($info['release_time']))
                                        {{ Utils::getTimeFormatted($info['release_time']) }}
                                        @else
                                        {{ trans('admin.update.pre-release') }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <a class="btn btn-primary" id="update-button" {!! !$info['new_version_available'] ? 'disabled="disabled"' : 'href="javascript:downloadUpdates();"' !!}>{{ trans('admin.update.button') }}</a>
                        <a href="{{ trans('admin.update.forum-url') }}" style="float: right;" class="btn btn-default">{{ trans('admin.update.check-forum') }}</a>
                    </div>
                </div>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.update.caution') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <p>{{ trans('admin.update.choose-source') }}</p>
                        <p>{{ trans('admin.update.choose-wrong') }}</p>
                    </div><!-- /.box-body -->
                </div>
            </div>

            <div class="col-md-6">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.update.update-options') }}</h3>
                    </div><!-- /.box-header -->
                    <form method="post">
                        <div class="box-body">
                            <?php
                            if (isset($_POST['submit'])) {
                                $_POST['check_update'] = isset($_POST['check_update']) ? $_POST['check_update'] : "0";

                                foreach ($_POST as $key => $value) {
                                    if ($key != "option" && $key != "submit")
                                        Option::set($key, $value);
                                }

                                echo '<div class="callout callout-success">{{ trans('admin.update.config-saved') }}</div>';
                            }

                            try {
                                $response = file_get_contents(option('update_source'));
                            } catch (Exception $e) {
                                echo '<div class="callout callout-danger">{{ trans('admin.update.connection-error') }}'.$e->getMessage().'</div>';
                            }

                            ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td class="key">{{ trans('admin.update.check-update') }}</td>
                                        <td class="value">
                                            <label for="check_update">
                                                <input {{ (option('check_update') == '1') ? 'checked="true"' : '' }} type="checkbox" id="check_update" name="check_update" value="1"> {{ trans('admin.update.auto-check') }}
                                            </label>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="key">{{ trans('admin.update.source') }}</td>
                                        <td class="value">
                                            <input type="text" class="form-control" name="update_source" value="{{ option('update_source') }}">

                                            <p class="description">{{ trans('admin.update.source-list') }}<a href="https://github.com/printempw/blessing-skin-server/wiki/%E6%9B%B4%E6%96%B0%E6%BA%90%E5%88%97%E8%A1%A8">@GitHub Wiki</a></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" name="submit" class="btn btn-primary">{{ trans('general.submit') }}</button>
                        </div>
                    </form>
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
                <h4 class="modal-title">{{ trans('admin.update.downloading') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('admin.update.size') }}<span id="file-size">0</span> Bytes</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="imported-progress">0</span>%
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
