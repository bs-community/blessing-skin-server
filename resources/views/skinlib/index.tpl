@extends('skinlib.master')

@section('title', '皮肤库')

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                皮肤库
                <small>Skin Library</small>
            </h1>
            <ol class="breadcrumb">
                <li><i class="fa fa-tags"></i> 当前正显示</li>
                <li>
                    @if ($filter == "skin")
                        皮肤<small>（任意模型）</small>
                    @elseif ($filter == "steve")
                        皮肤<small>（Steve 模型）</small>
                    @elseif ($filter == "alex")
                        皮肤<small>（Alex 模型）</small>
                    @elseif ($filter == "cape")
                        披风
                    @elseif ($filter == "user")
                        用户（uid.{{ $_GET['uid'] or 0 }}）上传
                    @endif
                </li>
                <li class="active">
                    @if ($sort == "time")
                        最新上传
                    @elseif ($sort == "likes")
                        最多收藏
                    @endif
                </li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="box box-default">
                <div class="box-body">
                    @forelse ($textures as $texture)
                    <a href="./skinlib/show?tid={{ $texture['tid'] }}">
                        <div class="item" tid="{{ $texture['tid'] }}">
                            <div class="item-body">
                                <img src="./preview/{{ $texture['tid'] }}.png">
                            </div>
                            <div class="item-footer">
                                <span>{{ $texture['name'] }} <small>({{ $texture['type'] }})</small></span>
                                @if (isset($_SESSION['email']))

                                    @if ($user->closet->has($texture['tid']))
                                    <a title="从衣柜中移除" class="more like liked" tid="{{ $texture['tid'] }}" href="javascript:removeFromCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
                                    @else
                                    <a title="添加至衣柜" class="more like" tid="{{ $texture['tid'] }}" href="javascript:addToCloset({{ $texture['tid'] }});" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
                                    @endif

                                @else
                                <a title="请先登录" class="more like" href="javascript:;" data-placement="top" data-toggle="tooltip"><i class="fa fa-heart"></i></a>
                                @endif

                                @if ($texture['public'] == "0")
                                <small class="more" tid="{{ $texture['tid'] }}">私密</small>
                                @endif

                            </div>
                        </div>
                    </a>
                    @empty
                    <p style="text-align: center; margin: 30px 0;">无结果</p>
                    @endforelse
                </div><!-- /.box-body -->
                <div class="box-footer">
                    <!-- Pagination -->
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
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
