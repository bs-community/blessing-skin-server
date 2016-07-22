@extends('admin.master')

@section('title', '用户管理')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @if (isset($_GET['filter']))
            搜索结果：{{ $_GET['filter'] }}
            @else
            用户管理
            @endif
            <small>User Management</small>
            <form method="get" action="" class="user-search-form">
                <input type="text" name="filter" class="form-control user-search-input" placeholder="输入昵称，回车搜索。" value="<?php echo Utils::getValue('search-username', $_POST); ?>">
            </form>
        </h1>
    </section>

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
                                    </ul>
                                </div>

                                <a class="btn btn-danger btn-sm"
                                    @if ($user->permission == "1")
                                        disabled="disabled" data-toggle="tooltip" data-placement="bottom" title="少年，不要作死哦"
                                    @else
                                        href="javascript:deleteUserAccount('{{ $user->uid }}');"
                                    @endif>
                                    删除用户
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td>0</td>
                            <td>无结果</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <li><a href="?page=1">«</a></li>
                    @if ($page != 1)
                    <li><a href="?page={{ $page-1 }}">{{ $page - 1 }}</a></li>
                    @endif

                    <li><a href="?page={{ $page }}" class="active">{{ $page }}</a></li>

                    @if ($total_pages > $page)
                    <li><a href="?page={{ $page+1 }}">{{ $page+1 }}</a></li>
                    @endif

                    <li><a href="?page={{ $total_pages }}">»</a></li>
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
