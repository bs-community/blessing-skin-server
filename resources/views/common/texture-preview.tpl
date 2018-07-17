<div class="box-header with-border">
    <h3 class="box-title" style="width: 100%;">{!! $title or trans('general.texture-preview') !!}
        <span id="textures-indicator" data-toggle="tooltip" title="" class="badge bg-light-blue"></span>
        <div class="operations">
            <i data-toggle="tooltip" data-placement="bottom" title="@lang('general.walk').' / '.trans('general.run')" class="fa fa-forward"></i>
            <i data-toggle="tooltip" data-placement="bottom" title="@lang('general.rotation')" class="fa fa-repeat"></i>
            <i data-toggle="tooltip" data-placement="bottom" title="@lang('general.pause')" class="fa fa-pause"></i>
            <i data-toggle="tooltip" data-placement="bottom" title="@lang('general.reset')" class="fa fa-stop"></i>
        </div>
    </h3>
</div><!-- /.box-header -->
<div class="box-body">
    <div id="preview-3d-container">
        <!-- Container for 3D Preview -->
    </div>
</div><!-- /.box-body -->

<script type="text/javascript" src="{{ assets('js/skinview3d.js') }}"></script>
<script type="text/javascript">
    var defaultAlexSkin  = "data:image/png;base64,{{ App\Http\Controllers\TextureController::getDefaultAlexSkin()  }}";
    var defaultSteveSkin = "data:image/png;base64,{{ App\Http\Controllers\TextureController::getDefaultSteveSkin() }}";
</script>
