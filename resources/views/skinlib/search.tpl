@extends('skinlib.master')

@section('title', '搜索结果')

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                搜索结果：{{ $_GET['q'] or "未指定关键字" }}
                <small>Skin Library</small>
            </h1>

        </section>

        <!-- Main content -->
        <section class="content">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">当前正显示：
                        <small>过滤器：
                            @if ($filter == "skin")
                            皮肤（任意模型）
                            @elseif ($filter == "steve")
                            皮肤（Steve 模型）
                            @elseif ($filter == "alex")
                            皮肤（Alex 模型）
                            @elseif ($filter == "cape")
                            披风
                            @endif
                        </small>

                        <small>，排序：
                            @if ($sort == "time")
                            最新上传
                            @elseif ($sort == "likes")
                            最多收藏
                            @endif
                        </small>
                    </h3>
                </div><!-- /.box-header -->
                <div class="box-body">
                    @forelse ($textures as $texture)
                    <a href="../skinlib/show?tid={{ $texture['tid'] }}">
                        <div class="item" tid="{{ $texture['tid'] }}">
                            <div class="item-body">
                                <img src="../preview/{{ $texture['tid'] }}.png">
                            </div>
                            <div class="item-footer">
                                <span>{{ $texture['name'] }} <small>({{ $texture['type'] }})</small></span>
                                @if (isset($_SESSION['email']))

                                @if ($user->closet->has($texture['tid']))
                                <a href="javascript:removeFromCloset({{ $texture['tid'] }});" class="more like liked" tid="{{ $texture['tid'] }}" title="从衣柜中移除" data-placement="top" data-toggle="tooltip">
                                @else
                                <a href="javascript:addToCloset({{ $texture['tid'] }});" class="more like" tid="{{ $texture['tid'] }}" title="添加至衣柜" data-placement="top" data-toggle="tooltip">
                                @endif

                                @else
                                <a href="javascript:;" class="more like" title="请先登录" data-placement="top" data-toggle="tooltip">
                                @endif
                                    <i class="fa fa-heart"></i>
                                </a>

                                @if($texture->public == "0")
                                <small class="more" tid="{{ $texture['tid'] }}">私密</small>
                                @endif

                            </div>
                        </div>
                    </a>
                    @empty
                    <p style="text-align: center; margin: 30px 0;">无结果</p>
                    @endforelse
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
