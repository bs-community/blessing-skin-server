@extends('admin.master')

@section('title', trans('general.status'))

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>@lang('general.status')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">@lang('admin.status.info')</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                @foreach ($detail as $category => $info)
                                <tr>
                                    <th colspan="2">@lang("admin.status.$category.name")</th>
                                </tr>
                                @foreach ($info as $key => $value)
                                <tr>
                                    <td>@lang("admin.status.$category.$key")</td>
                                    <td>{{ $value }}</td>
                                </tr>
                                @endforeach
                                @endforeach
                                <tr>
                                    <th colspan="2">@lang('admin.status.plugins', ['amount' => $plugins->count()])</th>
                                </tr>
                                @foreach ($plugins as $plugin)
                                <tr>
                                    <td>{{ $plugin['title'] }}</td>
                                    <td>{{ $plugin['version'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>
    </section>
</div>
@endsection
