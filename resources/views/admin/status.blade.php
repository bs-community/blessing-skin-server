@extends('admin.master')

@section('title', trans('general.status'))

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>@lang('general.status')</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header"></div>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </section>
</div>
@endsection
