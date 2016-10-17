@extends('admin.master')

@section('title', trans('general.plugin-manage'))

@section('style')
<style>
.btn {
    margin-right: 4px;
}
</style>
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.plugin-manage') }}
            <small>Plugin Manage</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>名称</th>
                            <th>描述</th>
                            <th>作者</th>
                            <th>版本</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($installed as $plugin)
                        <tr id="plugin-{{ $plugin->name }}">
                            <td>{{ $plugin->title }}</td>
                            <td id="description">{{ $plugin->description }}</td>
                            <td id="author">{{ $plugin->author }}</td>
                            <td id="version">{{ $plugin->version }}</td>
                            <td id="status">
                                @if ($plugin->isEnabled())
                                已启用
                                @else
                                已禁用
                                @endif
                            </td>

                            <td>
                                @if ($plugin->isEnabled())
                                <a class="btn btn-warning btn-sm" href="?action=disable&id={{ $plugin->name }}">禁用插件</a>
                                @else
                                <a class="btn btn-primary btn-sm" href="?action=enable&id={{ $plugin->name }}">启用插件</a>
                                @endif

                                @if ($plugin->isEnabled() && $plugin->hasConfigView())
                                <a class="btn btn-default btn-sm" href="?action=config&id={{ $plugin->name }}">插件配置</a>
                                @else
                                <a class="btn btn-default btn-sm" disabled="disabled" title="插件已被禁用或无配置页" data-toggle="tooltip" data-placement="top">插件配置</a>
                                @endif

                                <a class="btn btn-danger btn-sm" href="javascript:deletePlugin('{{ $plugin->name }}');">删除插件</a>

                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td>0</td>
                            <td>无结果</td>
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

@section('style')
<style>
    @media (max-width: 767px) {
        .content-header > h1 > small {
            display: none;
        }
    }
</style>
@endsection

@section('script')
<script type="text/javascript">
$(document).ready(function() {
    $('.box-body').css('min-height', $('.content-wrapper').height() - $('.content-header').outerHeight() - 120);
});

function deletePlugin(name) {
    swal({
        text: '真的要删除这个插件吗？',
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
