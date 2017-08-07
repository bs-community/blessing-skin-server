<div class="box-header with-border">
    <h3 class="box-title" style="width: 100%;">{{ trans('general.texture-preview') }}
        <span id="textures-indicator" data-toggle="tooltip" title="" class="badge bg-light-blue"></span>
        <div class="operations">
            <i data-toggle="tooltip" data-placement="bottom" title="{{ trans('general.walk') }}" class="fa fa-pause"></i>
            <i data-toggle="tooltip" data-placement="bottom" title="{{ trans('general.run') }}" class="fa fa-forward"></i>
            <i data-toggle="tooltip" data-placement="bottom" title="{{ trans('general.rotation') }}" class="fa fa-repeat"></i>
        </div>
    </h3>
</div><!-- /.box-header -->
<div class="box-body">
    <div id="skinpreview">
        <!-- Container for 3D Preview -->
    </div>
</div><!-- /.box-body -->

<script type="text/javascript" src="{{ assets('js/three.min.js') }}"></script>
<script type="text/javascript" src="{{ assets('js/three.msp.js') }}"></script>

<script>
    var dskin = "data:image/png;base64,{{ App\Http\Controllers\TextureController::getDefaultSkin() }}";
    MSP.changeSkin(dskin);
    console.log('[3D Preview] Default skin rendered.');
</script>
