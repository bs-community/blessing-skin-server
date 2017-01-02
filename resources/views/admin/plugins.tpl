@extends('admin.master')

@section('title', trans('general.plugin-manage'))

@section('style')
<style>
.btn { margin-right: 4px; }
td#description { width: 35%; }
@media (max-width: 767px) { .content-header > h1 > small { display: none; } }
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
        <div class="breadcrumb"></div>
    </section>

    <!-- Main content -->
    <section class="content">

        @if (session()->has('message'))
            <div class="callout callout-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        <div class="box">
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('admin.plugins.name') }}</th>
                            <th>{{ trans('admin.plugins.description') }}</th>
                            <th>{{ trans('admin.plugins.author') }}</th>
                            <th>{{ trans('admin.plugins.version') }}</th>
                            <th>{{ trans('admin.plugins.status.title') }}</th>
                            <th>{{ trans('admin.plugins.operations.title') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($installed as $plugin)
                        <tr id="plugin-{{ $plugin->name }}">
                            <td>{!! trans($plugin->title) !!}</td>
                            <td id="description">{!! trans($plugin->description) !!}</td>
                            <td id="author">{{ $plugin->author }}</td>
                            <td id="version">{{ $plugin->version }}</td>
                            <td id="status">
                                @if ($plugin->isEnabled())
                                {{ trans('admin.plugins.status.enabled') }}
                                @else
                                {{ trans('admin.plugins.status.disabled') }}
                                @endif
                            </td>

                            <td>
                                @if ($plugin->isEnabled())
                                <a class="btn btn-warning btn-sm" href="?action=disable&id={{ $plugin->name }}">{{ trans('admin.plugins.operations.disable') }}</a>
                                @else
                                <a class="btn btn-primary btn-sm" href="?action=enable&id={{ $plugin->name }}">{{ trans('admin.plugins.operations.enable') }}</a>
                                @endif

                                @if ($plugin->isEnabled() && $plugin->hasConfigView())
                                <a class="btn btn-default btn-sm" href="?action=config&id={{ $plugin->name }}">{{ trans('admin.plugins.operations.configure') }}</a>
                                @else
                                <a class="btn btn-default btn-sm" disabled="disabled" title="{{ trans('admin.plugins.operations.no-config-notice') }}" data-toggle="tooltip" data-placement="top">{{ trans('admin.plugins.operations.configure') }}</a>
                                @endif

                                <a class="btn btn-danger btn-sm" href="javascript:deletePlugin('{{ $plugin->name }}');">{{ trans('admin.plugins.operations.delete') }}</a>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td>0</td>
                            <td>{{ trans('admin.plugins.empty') }}</td>
                            <td>(´・ω・`)</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript">

function deletePlugin(name) {
    swal({
        text: trans('admin.confirmDeletion'),
        type: 'warning',
        showCancelButton: true
    }).then(function() {
        $.ajax({
            type: "POST",
            url: "?action=delete&id=" + name,
            dataType: "json",
            success: function(json) {
                if (json.errno == 0) {
                    toastr.success(json.msg);

                    $('tr[id=plugin-'+name+']').remove();
                } else {
                    toastr.warning(json.msg);
                }
            },
            error: showAjaxError
        });
    });
}
</script>
@endsection
