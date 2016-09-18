@extends('skinlib.master')

@section('title', trans('skinlib.search.title'))

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ trans('skinlib.search.title') }}:{{ $_GET['q'] or "{{ trans('skinlib.search.no-given-keywords') }}" }}
                <small>Skin Library</small>
            </h1>

        </section>

        <!-- Main content -->
        <section class="content">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('skinlib.index.now-showing') }}:
                        <small>{{ trans('skinlib.master.filter') }}:
                            @if ($filter == "skin")
                            {{ trans('general.skin').trans('skinlib.index.any-model') }}
                            @elseif ($filter == "steve")
                            {{ trans('general.skin').'('.trans('skinlib.index.steve-model').')' }}
                            @elseif ($filter == "alex")
                            {{ trans('general.skin').'('.trans('skinlib.index.alex-model').')' }}
                            @elseif ($filter == "cape")
                            {{ trans('general.cape') }}
                            @endif
                        </small>

                        <small>,{{ trans('skinlib.master.sort') }}:
                            @if ($sort == "time")
                            {{ trans('skinlib.index.newest-uploaded') }}
                            @elseif ($sort == "likes")
                            {{ trans('skinlib.index.most-likes') }}
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
                    <p style="text-align: center; margin: 30px 0;">{{ trans('skinlib.search.no-result') }}</p>
                    @endforelse
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
