<?php use App\Models\User; ?>
<div class="btn-group">
    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ trans('admin.users.operations.title') }} <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="javascript:changeUserEmail('{{ $uid }}');">{{ trans('admin.users.operations.email.change') }}</a></li>
        <li><a href="javascript:changeUserNickName('{{ $uid }}');">{{ trans('admin.users.operations.nickname.change') }}</a></li>
        <li><a href="javascript:changeUserPwd('{{ $uid }}');">{{ trans('admin.users.operations.password.change') }}</a></li>

        @unless ($permission == User::SUPER_ADMIN)
        <li class="divider"></li>
            {{-- If current user is super admin --}}
            @if (app('user.current')->getPermission() == User::SUPER_ADMIN)
                @if ($permission == User::ADMIN)
                <li><a id="admin" href="javascript:changeAdminStatus('{{ $uid }}');">{{ trans('admin.users.operations.admin.unset.text') }}</a></li>
                @else
                <li><a id="admin" href="javascript:changeAdminStatus('{{ $uid }}');">{{ trans('admin.users.operations.admin.set.text') }}</a></li>
                @endif
            @endif

            <li class="divider"></li>

            @if ($permission == User::BANNED)
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">{{ trans('admin.users.operations.ban.unban.text') }}</a></li>
            @else
            <li><a id="ban" href="javascript:changeBanStatus('{{ $uid }}');">{{ trans('admin.users.operations.ban.ban.text') }}</a></li>
            @endif

        @endunless
    </ul>
</div>

{{-- If current user is super admin --}}
@if (app('user.current')->getPermission() == User::SUPER_ADMIN)

    @if ($permission == "2")
    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="{{ trans('admin.users.operations.delete.cant-super-admin') }}">{{ trans('admin.users.operations.delete.delete') }}</a>
    @else
    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $uid }}');">{{ trans('admin.users.operations.delete.delete') }}</a>
    @endif

@else
    @if ($permission == "1" || $permission == "2")
    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="{{ trans('admin.users.operations.delete-cant-admin') }}">{{ trans('admin.users.operations.delete.delete') }}</a>
    @else
    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $uid }}');">{{ trans('admin.users.operations.delete.delete') }}</a>
    @endif

@endif
