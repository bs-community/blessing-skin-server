<!-- User Account Menu -->
<li class="dropdown user user-menu">
    <!-- Menu Toggle Button -->
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        @if (app('request')->is('skinlib*') || app('request')->is('/'))
        <!-- The user image in the navbar-->
        <img src="{{ avatar($user, 25) }}" class="user-image" alt="User Image">
        @else
        <i class="fa fa-user"></i>
        @endif

        <!-- hidden-xs hides the username on small devices so only the image appears. -->
        <span class="hidden-xs nickname">{{ bs_nickname($user) }}</span>
    </a>
    <ul class="dropdown-menu">
        <!-- The user image in the menu -->
        <li class="user-header">
            <img src="{{ avatar($user, 128) }}" alt="User Image">
            <p>{{ $user->email }}</p>
        </li>
        @if ($user->isAdmin())
        <!-- Menu Body -->
        <li class="user-body">
            <div class="row">
                <div class="col-xs-4 text-center">
                    <a href="{{ url('admin') }}">管理面板</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="{{ url('admin/users') }}">用户管理</a>
                </div>
                <div class="col-xs-4 text-center">
                    <a href="{{ url('admin/options') }}">站点配置</a>
                </div>
            </div>
        </li>
        <style>li.user-header { height: initial !important; }</style>
        @endif
        <!-- Menu Footer-->
        <li class="user-footer">
            <div class="pull-left">
                <a href="{{ url('user') }}" class="btn btn-default btn-flat">{{ trans('general.user-center') }}</a>
            </div>
            <div class="pull-right">
                <button id="logout-button" class="btn btn-default btn-flat">{{ trans('general.logout') }}</button>
            </div>
        </li>
    </ul>
</li>
