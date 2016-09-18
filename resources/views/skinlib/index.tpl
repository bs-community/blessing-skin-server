@extends('skinlib.master')

@section('title', trans('general.skinlib'))

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ trans('general.skinlib') }}
                <small>Skin Library</small>
            </h1>
            <ol class="breadcrumb">
                <li><i class="fa fa-tags"></i> {{ trans('skinlib.index.now-showing') }}</li>
                <li>
                    @if ($filter == "skin")
                        {{ trans('general.skin') }}<small>{{ trans('skinlib.index.any-model') }}</small>
                    @elseif ($filter == "steve")
                        {{ trans('skinlib.index.any-model') }}<small>({{ trans('skinlib.index.steve-model') }})</small>
                    @elseif ($filter == "alex")
                        {{ trans('general.skin') }}<small>{{ trans('skinlib.index.alex-model') }}</small>
                    @elseif ($filter == "cape")
                        {{ trans('general.cape') }}
                    @elseif ($filter == "user")
                        {{ trans('skinlib.index.uploader', ['name' => (new App\Models\User($_GET['uid']))->getNickName()]) }}
                    @endif
                </li>
                <li class="active">
                    @if ($sort == "time")
                        {{ trans('skinlib.index.newest-uploaded') }}
                    @elseif ($sort == "likes")
                        {{ trans('skinlib.index.most-likes') }}
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
                    <p style="text-align: center; margin: 30px 0;">{{ trans('skinlib.index.no-result') }}</p>
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

                    <p class="pull-right">{{ trans('general.pagination', ['page' => $page , 'total' => $total_pages]) }}</p>
                </div>
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
