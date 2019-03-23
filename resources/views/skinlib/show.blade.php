@extends('skinlib.master')

@section('title', $texture->name)

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                @lang('skinlib.show.title')
            </h1>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row"></div>

            <div class="row">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('skinlib.show.comment')</h3>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            @if (option('comment_script') != "")
                            <!-- Comment Start -->
                            {!! get_string_replaced(option('comment_script'), [
                                '{tid}' => $texture->tid,
                                '{name}' => $texture->name,
                                '{url}' => get_base_url().$_SERVER['REQUEST_URI']
                            ]) !!}
                            <!-- Comment End -->
                            @else
                            <p style="text-align: center; margin: 30px 0;">@lang('skinlib.show.comment-not-available')</p>
                            @endif
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div>
            </div>

        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->

<script>
Object.defineProperty(blessing, 'extra', {
    configurable: false,
    get: () => Object.freeze(@json($extra))
})
</script>

@endsection
