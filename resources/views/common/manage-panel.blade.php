<div class="box box-danger">
    {{-- Texture Manage Panel --}}
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('skinlib.show.manage-panel.title') }}</h3>
    </div><!-- /.box-header -->
    <div class="box-body">
        <p>{{ trans('skinlib.show.manage-panel.notice') }}</p>
    </div><!-- /.box-body -->

    <div class="box-footer">
        @if ($texture->public == "1")
        <a onclick="changePrivacy({{ $texture->tid }});" class="btn btn-warning">{{ trans('skinlib.privacy.set-as-private') }}</a>
        @else
        <a onclick="changePrivacy({{ $texture->tid }});" class="btn btn-warning">{{ trans('skinlib.privacy.set-as-public') }}</a>
        @endif
        <a onclick="deleteTexture({{ $texture->tid }});" class="btn btn-danger pull-right">{{ trans('skinlib.show.delete-texture') }}</a>
    </div><!-- /.box-footer -->
</div><!-- /.box -->
