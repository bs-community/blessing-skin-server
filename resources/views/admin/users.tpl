@extends('admin.master')

@section('title', '用户管理')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @if (isset($_GET['q']))
            搜索结果：{{ $_GET['q'] }}
            @else
            用户管理
            @endif
            <small>User Management</small>
            <form method="get" action="" class="user-search-form">
                <input type="text" name="q" class="form-control user-search-input" placeholder="输入，回车搜索。" value="{{ $q }}">
                <select name="filter" class="form-control pull-right user-search-input">
                    <option value='email' {{ $filter == 'email' ? 'selected="selected"' : '' }}>搜索邮箱</option>
                    <option value='nickname' {{ $filter == 'nickname' ? 'selected="selected"' : '' }}>搜索昵称</option>
                </select>
            </form>

        </h1>
    </section>

    <?php $current_user = new App\Models\User(session('uid')); ?>

    <!-- Main content -->
    <section class="content">
        <div class="box">
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>邮箱</th>
                            <th>昵称</th>
                            <th>积分</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                        <tr id="{{ $user->uid }}">
                            <td>{{ $user->uid }}</td>
                            <td id="email">{{ $user->email }}</td>
                            <td id="nickname">{{ $user->nickname }}</td>
                            <td><input type="text" class="form-control score" value="{{ $user->score }}" title="输入修改后的积分，回车提交" data-placement="top"></td>
                            <td id="permission">
                                @if ($user->permission == "0")
                                正常
                                @elseif ($user->permission == "-1")
                                封禁
                                @elseif ($user->permission == "1")
                                管理员
                                @elseif ($user->permission == "2")
                                超级管理员
                                @endif
                            </td>
                            <td>{{ $user->register_at }}</td>

                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        更多操作 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:changeUserEmail('{{ $user->uid }}');">修改邮箱</a></li>
                                        <li><a href="javascript:changeUserNickName('{{ $user->uid }}');">修改昵称</a></li>
                                        <li><a href="javascript:changeUserPwd('{{ $user->uid }}');">更改密码</a></li>
                                        <li class="divider"></li>
                                        <li><a href="../admin/players?filter=uid&q={{ $user->uid }}">查看该用户拥有的角色</a></li>
                                        <li class="divider"></li>

                                        {{-- If current user is super admin --}}
                                        @if ($current_user->getPermission() == "2")

                                            @if ($user->permission == "1")
                                            <li><a id="admin" href="javascript:changeAdminStatus('{{ $user->uid }}');">解除管理员</a></li>
                                            @elseif ($user->permission == "2")
                                            <li><a href="javascript:;">无法解除超级管理员</a></li>
                                            @else
                                            <li><a id="admin" href="javascript:changeAdminStatus('{{ $user->uid }}');">设为管理员</a></li>
                                            @endif

                                            <li class="divider"></li>

                                            @if ($user->permission == "2")
                                            <li><a href="javascript:;">无法封禁超级管理员</a></li>
                                            @elseif ($user->permission == "-1")
                                            <li><a id="ban" href="javascript:changeBanStatus('{{ $user->uid }}');">解封</a></li>
                                            @else
                                            <li><a id="ban" href="javascript:changeBanStatus('{{ $user->uid }}');">封禁</a></li>
                                            @endif

                                        {{-- If current user is ordinary admin --}}
                                        @else

                                            @if ($user->permission == "1" || $user->permission == "2")
                                            <li><a href="javascript:;">无法封禁管理员</a></li>
                                            @elseif ($user->permission == "0")
                                            <li><a id="ban" href="javascript:changeBanStatus('{{ $user->uid }}');">封禁</a></li>
                                            @else
                                            <li><a id="ban" href="javascript:changeBanStatus('{{ $user->uid }}');">解封</a></li>
                                            @endif

                                        @endif
                                    </ul>
                                </div>

                                {{-- If current user is super admin --}}
                                @if ($current_user->getPermission() == "2")

                                    @if ($user->permission == "2")
                                    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="超级管理员账号不能被这样删除的啦">删除用户</a>
                                    @else
                                    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $user->uid }}');">删除用户</a>
                                    @endif

                                @else
                                    @if ($user->permission == "1" || $user->permission == "2")
                                    <a class="btn btn-danger btn-sm" disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="你不能删除管理员账号哦">删除用户</a>
                                    @else
                                    <a class="btn btn-danger btn-sm" href="javascript:deleteUserAccount('{{ $user->uid }}');">删除用户</a>
                                    @endif

                                @endif
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
            <div class="box-footer">
                <!-- Pagination -->
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?php $base_url = ($filter != "" && $q != "") ? "?filter=$filter&q=$q&" : "?"; ?>
                    <li><a href="?page=1">«</a></li>

                    @if ($page != 1)
                    <li><a href="{{ $base_url }}page={{ $page-1 }}">{{ $page - 1 }}</a></li>
                    @endif

                    <li><a href="{{ $base_url }}page={{ $page }}" class="active">{{ $page }}</a></li>

                    @if ($total_pages > $page)
                    <li><a href="{{ $base_url }}page={{ $page+1 }}">{{ $page+1 }}</a></li>
                    @endif

                    <li><a href="{{ $base_url }}page={{ $total_pages }}">»</a></li>
                </ul>

                <select id="page-select" class="pull-right">
                @for ($i = 1; $i <= $total_pages; $i++)

                    @if ($i == $page)
                    <option value='{{ $i }}' selected="selected">{{ $i }}</option>
                    @else
                    <option value='{{ $i }}'>{{ $i }}</option>
                    @endif

                @endfor
                </select>

                <p class="pull-right">第 {{ $page }} 页，共 {{ $total_pages }} 页</p>

            </div>
        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script type="text/javascript">
$(document).ready(function() {
    $('.box-body').css('min-height', $('.content-wrapper').height() - $('.content-header').outerHeight() - 120);
});
</script>
@endsection
