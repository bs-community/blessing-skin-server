@extends('skinlib.master')

@section('title', trans('skinlib.upload.title'))

@section('style')
<style>
label[for="type-skin"],
label[for="type-cape"] {
    margin-top: 5px;
}
</style>
@endsection

@section('content')
<!-- Full Width Column -->
<div class="content-wrapper">
    <div class="container">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ trans('skinlib.upload.title') }}
            </h1>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-primary">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="name">{{ trans('skinlib.upload.texture-name') }}</label>
                                <input id="name" class="form-control" type="text" placeholder="{{ trans('skinlib.upload.name-rule') }}" />
                            </div>

                            <div class="form-group">
                                <label>{{ trans('skinlib.upload.texture-type') }}</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-xs-4">
                                                <label for="type-skin">
                                                    <input type="radio" name="type" id="type-skin"> {{ trans('general.skin') }}
                                                </label>
                                            </div>
                                            <div class="col-xs-8">
                                                <select class="form-control" id="skin-type" style="display: none;">
                                                    <option value="steve">{{ trans('skinlib.filter.steve-model') }}</option>
                                                    <option value="alex">{{ trans('skinlib.filter.alex-model') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="type-cape">
                                            <input type="radio" name="type" id="type-cape"> {{ trans('general.cape') }}
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="file">{{ trans('skinlib.upload.select-file') }}</label>
                                <input id="file" type="file" data-show-upload="false" data-language="{{ config('app.locale') }}" class="file" accept="image/png" />
                            </div>

                            <div class="callout callout-info" id="msg" style="display: none;">
                                <p>{{ trans('skinlib.upload.private-score-notice', ['score' => option('private_score_per_storage')]) }}</p>
                            </div>
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <label for="private" class="pull-right" title="{{ trans('skinlib.upload.privacy-notice') }}" data-placement="top" data-toggle="tooltip">
                                <input id="private" type="checkbox"> {{ trans('skinlib.upload.set-as-private') }}
                            </label>
                            <button id="upload-button" onclick="upload()" class="btn btn-primary">{{ trans('skinlib.upload.button') }}</button>
                        </div>
                    </div><!-- /.box -->
                </div>
                <div class="col-md-6">
                    <div class="box box-default">
                        @include('common.texture-preview')
                    </div>
                </div>
            </div>

        </section><!-- /.content -->
    </div><!-- /.container -->
</div><!-- /.content-wrapper -->

@endsection

@section('script')
<script>
    $(document).ready(function() {
        TexturePreview.init3dPreview();

        $('[for="private"]').tooltip();
    });

    // Auto resize canvas to fit responsive design
    $(window).resize(TexturePreview.init3dPreview);
</script>
@endsection
