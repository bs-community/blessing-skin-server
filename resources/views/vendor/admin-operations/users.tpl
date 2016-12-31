<div class="btn-group">
    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        更多操作 <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="javascript:changeUserEmail('{{ $uid }}');">修改邮箱</a></li>
        <li><a href="javascript:changeUserNickName('{{ $uid }}');">修改昵称</a></li>
        <li><a href="javascript:changeUserPwd('{{ $uid }}');">更改密码</a></li>
        <li class="divider"></li>
        <li><a href="{{ url('admin/players?filter=uid&q='.$uid) }}">查看该用户拥有的角色</a></li>
        <li class="divider"></li>

        {{-- If current user is super admin --}}
        @if (app('user.current')->getPermission() == App\Models\User::SUPER_ADMIN)

            @if ($permission == "1")
            <li><a id="admin" href="javascript:changeAdminStatus('{{ $uid }}');">解除管理员</a></li>
            @elseif ($permission == "2")
            <li><a href="javascript:;">无法解除超级管理员</a></li>
            @else
            <li><a id="admin" href="javascript:changeAdminStatus('{{ $uid }}');">设为管理员</a></li>
            @endif

            <li class="divider"></li>

            @if ($permission == "2")
            <li><a href="javascript:;">无法封禁超级管理员</a></li>
            @elseif ($permission == "-1")
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">解封</a></li>
            @else
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">封禁</a></li>
            @endif

        {{-- If current user is ordinary admin --}}
        @else

            @if ($permission == "1" || $permission == "2")
            <li><a href="javascript:;">无法封禁管理员</a></li>
            @elseif ($permission == "0")
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">封禁</a></li>
            @else
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">解封</a></li>
            @endif

        @endif
    </ul>
</div>

{{-- If current user is super admin --}}
@if (app('user.current')->getPermission() == App\Models\User::SUPER_ADMIN)

    @if ($permission == "2")
    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="超级管理员账号不能被这样删除的啦">删除用户</a>
    @else
    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $uid }}');">删除用户</a>
    @endif

@else
    @if ($permission == "1" || $permission == "2")
    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="你不能删除管理员账号哦">删除用户</a>
    @else
    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $uid }}');">删除用户</a>
    @endif

@endif
