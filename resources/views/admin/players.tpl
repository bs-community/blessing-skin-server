@extends('admin.master')

@section('title', '角色管理')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @if (isset($_GET['q']))
            搜索结果：{{ $_GET['q'] }}
            @else
            角色管理
            @endif
            <small>Player Management</small>
            <form method="get" action="" class="user-search-form">
                <input type="text" name="q" class="form-control user-search-input" placeholder="输入，回车搜索。" value="{{ $q }}">
                <select name="filter" class="form-control pull-right user-search-input">
                    <option value='player_name' selected="{{ $filter == 'email' ? 'selected' : '' }}">搜索角色名</option>
                    <option value='uid' selected="{{ $filter == 'nickname' ? 'selected' : '' }}">根据角色拥有者搜索</option>
                </select>
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
                            <th>PID</th>
                            <th>拥有者 UID</th>
                            <th>角色名</th>
                            <th>优先模型</th>
                            <th>预览材质</th>
                            <th>修改时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($players as $player)
                        <tr id="{{ $player->pid }}">
                            <td>{{ $player->pid }}</td>
                            <td><a href="?filter=uid&q={{ $player->uid }}">{{ $player->uid }}</a></td>
                            <td id="player-name">{{ $player->player_name }}</td>
                            <td>
                                 <select class="form-control" id="preference">
                                     <option {{ ($player->preference == "default") ? 'selected=selected' : '' }} value="default">Default</option>
                                     <option {{ ($player->preference == "slim") ? 'selected=selected' : '' }} value="slim">Slim</option>
                                </select>
                            </td>
                            <td>
                                @if ($player->tid_steve == '0')
                                <img id="{{ $player->pid }}-steve" width="64" />
                                @else
                                <a href="../skinlib/show?tid={{ $player->tid_steve }}">
                                    <img id="{{ $player->pid }}-steve" width="64" src="../preview/64/{{ $player->tid_steve }}.png" />
                                </a>
                                @endif

                                @if ($player->tid_alex == '0')
                                <img id="{{ $player->pid }}-alex" width="64" />
                                @else
                                <a href="../skinlib/show?tid={{ $player->tid_alex }}">
                                    <img id="{{ $player->pid }}-alex" width="64" src="../preview/64/{{ $player->tid_alex }}.png" />
                                </a>
                                @endif

                                @if ($player->tid_cape == '0')
                                <img id="{{ $player->pid }}-cape" width="64" />
                                @else
                                <a href="../skinlib/show?tid={{ $player->tid_cape }}">
                                    <img id="{{ $player->pid }}-cape" width="64" src="../preview/64/{{ $player->tid_cape }}.png" />
                                </a>
                                @endif
                            </td>
                            <td>{{ $player->last_modified }}</td>

                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        更多操作 <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a href="javascript:changeTexture('{{ $player->pid }}');">更换材质</a></li>
                                        <li><a href="javascript:changeOwner('{{ $player->pid }}');">更换角色拥有者</a></li>
                                    </ul>
                                </div>

                                <a class="btn btn-danger btn-sm" href="javascript:deletePlayer('{{ $player->pid }}');">删除角色</a>
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

<div id="modal-change-texture" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">更改 Player 的材质</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <a href="./player" class="btn btn-default pull-left">添加角色</a>
                <a href="javascript:setTexture();" class="btn btn-primary">提交</a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection
