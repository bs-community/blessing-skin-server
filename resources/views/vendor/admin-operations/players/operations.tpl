<div class="btn-group">
    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        更多操作 <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="javascript:changeTexture('{{ $pid }}');">更换材质</a></li>
        <li><a href="javascript:changeOwner('{{ $pid }}');">更换角色拥有者</a></li>
    </ul>
</div>

<a class="btn btn-danger btn-sm" href="javascript:deletePlayer('{{ $pid }}');">删除角色</a>
