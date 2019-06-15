@extends('admin.master')

@section('title', trans('general.customize'))

@section('style')
<link rel="stylesheet" href="{{ webpack_assets('skins/_all-skins.min.css') }}">
@endsection

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('general.customize')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="row">
            <div class="col-md-3">
                <form class="box box-primary" method="post" action="{{ url('/admin/customize?action=color') }}">
                    @csrf
                    <div class="box-header with-border">
                        <h3 v-t="'admin.change-color.title'" class="box-title">
                            @lang('admin.customize.change-color.title')
                        </h3>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped bring-up nth-2-center" id="change-color">
                            @php
                                $colors = ['blue', 'yellow', 'green', 'purple', 'red', 'black'];
                            @endphp
                            <tbody>
                                @foreach ($colors as $color)
                                <tr>
                                    <td>@lang('admin.customize.colors.'.$color)</td>
                                    <td>
                                        <label>
                                            <input type="radio" name="color" value="skin-{{ $color }}" style="display: none;">
                                            <span class="btn bg-{{ $color }} btn-xs">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>@lang('admin.customize.colors.'.$color.'-light')</td>
                                    <td>
                                        <label>
                                            <input type="radio" name="color" value="skin-{{ $color }}-light" style="display: none;">
                                            <span class="btn bg-{{ $color }} btn-xs">
                                                <i class="far fa-eye"></i>
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="box-footer">
                        <input type="submit" class="el-button el-button--primary" value="@lang('general.submit')" name="submit_color">
                    </div>
                </form>
            </div>

            <div class="col-md-9">
                {!! $forms['homepage']->render() !!}

                {!! $forms['customJsCss']->render() !!}
            </div>

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
@endsection
