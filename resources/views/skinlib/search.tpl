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
                    <a href="{{ url('skinlib/show?tid='.$texture['tid']) }}">
                        @include('skinlib.item')
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
