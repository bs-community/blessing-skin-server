@extends('admin.master')

@section('title', trans('general.i18n'))

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>@lang('general.i18n')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-8">
                <div id="table"></div>
            </div>
            <div class="col-lg-4">
                <form action="{{ url('/admin/i18n') }}" method="post">
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">@lang('admin.i18n.add')</h3>
                        </div>
                        <div class="box-body">
                            @if (session()->pull('success'))
                                <div class="callout callout-success">@lang('admin.i18n.added')</div>
                            @endif
                            @if ($errors->any())
                                <div class="callout callout-danger">{{ $errors->first() }}</div>
                            @endif
                            @csrf
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>@lang('admin.i18n.group')</td>
                                        <td>
                                            <input type="text" class="form-control" name="group" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('admin.i18n.key')</td>
                                        <td>
                                            <input type="text" class="form-control" name="key" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>@lang('admin.i18n.text')</td>
                                        <td>
                                            <input type="text" class="form-control" name="text" required>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="box-footer">
                            <input type="submit" value="@lang('general.submit')" class="el-button el-button--primary">
                        </div>
                    </div>
                </form>
                <div class="callout callout-info">
                    <a href="https://blessing.netlify.com/ui-text.html" target="_blank">
                        @lang('admin.i18n.tip')
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
