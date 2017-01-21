@extends('admin.master')

@section('title', trans('general.customize'))

@section('style')
<link rel="stylesheet" href="{{ assets('styles/skins/_all-skins.min.css') }}">
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{ trans('general.customize') }}
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ trans('admin.customize.change-color.title') }}</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body no-padding">
                        <table id="layout-skins-list" class="table table-striped bring-up nth-2-center">
                            <tbody>
                                @foreach(['blue', 'yellow', 'green', 'purple', 'red', 'black'] as $color)
                                    @foreach([$color, "$color-light"] as $innerColor)
                                    <tr>
                                        <td>{{ trans("admin.customize.colors.$innerColor") }}</td>
                                        <td><a href="#" data-skin="skin-{{ $innerColor }}" class="btn bg-{{ $color }} btn-xs"><i class="fa fa-eye"></i></a></td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                    <div class="box-footer">
                        <button id="color-submit" class="btn btn-primary">{{ trans('general.submit') }}</button>
                    </div>
                </div><!-- /.box -->

            </div>

            <div class="col-md-9">
                {!! $forms['homepage']->render() !!}

                {!! $forms['customJsCss']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
var current_skin = "{{ option('color_scheme') }}";
</script>

@endsection


