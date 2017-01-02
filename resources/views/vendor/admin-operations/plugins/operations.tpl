@if ($plugin->isEnabled())
<a class="btn btn-warning btn-sm" href="javascript:disablePlugin('{{ $plugin->name }}');">{{ trans('admin.plugins.operations.disable') }}</a>
@else
<a class="btn btn-primary btn-sm" href="javascript:enablePlugin('{{ $plugin->name }}');">{{ trans('admin.plugins.operations.enable') }}</a>
@endif

@if ($plugin->isEnabled() && $plugin->hasConfigView())
<a class="btn btn-default btn-sm" href="?action=config&id={{ $plugin->name }}">{{ trans('admin.plugins.operations.configure') }}</a>
@else
<a class="btn btn-default btn-sm" disabled="disabled" title="{{ trans('admin.plugins.operations.no-config-notice') }}" data-toggle="tooltip" data-placement="top">{{ trans('admin.plugins.operations.configure') }}</a>
@endif

<a class="btn btn-danger btn-sm" href="javascript:deletePlugin('{{ $plugin->name }}');">{{ trans('admin.plugins.operations.delete') }}</a>
