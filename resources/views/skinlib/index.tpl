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
                        @include('skinlib.item')
                    </a>
                    @empty
                    <p style="text-align: center; margin: 30px 0;">无结果</p>
                    @endforelse
                </div><!-- /.box-body -->
                <div class="box-footer">
                    <!-- Pagination -->
                    <ul class="pagination pagination-sm no-margin pull-right">
                        <?php
                            $base_url = ($filter != "" && $sort != "") ? "?filter=$filter&sort=$sort&" : "?";
                            if (isset($_GET['uid'])) $base_url.= "uid=".$_GET['uid']."&";
                        ?>
                        <li><a href="{{ $base_url }}page=1">«</a></li>

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
                        <option value='{{ $i }}' {{ ($i == $page) ? 'selected="selected"' : '' }}>{{ $i }}</option>
                    @endfor
                    </select>

                    <p class="pull-right">第 {{ $page }} 页，共 {{ $total_pages }} 页</p>
                </div>
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
