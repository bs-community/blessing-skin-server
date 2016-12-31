<div class="btn-group">
    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ trans('admin.players.operations.title') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="javascript:changeTexture('{{ $pid }}');">{{ trans('admin.players.textures.change') }}</a></li>
        <li><a href="javascript:changeOwner('{{ $pid }}');">{{ trans('admin.players.owner.change') }}</a></li>
    </ul>
</div>

<a class="btn btn-danger btn-sm" href="javascript:deletePlayer('{{ $pid }}');">{{ trans('admin.players.delete.delete') }}</a>
