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
            @include('vendor.breadcrumb')
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
                    <p style="text-align: center; margin: 30px 0;">{{ trans('skinlib.general.no-result') }}</p>
                    @endforelse
                </div><!-- /.box-body -->

                <div class="box-footer">
                    @include('vendor.pagination')
                </div>
            </div><!-- /.box -->
        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->
@endsection
