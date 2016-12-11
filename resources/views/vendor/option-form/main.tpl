<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $title }} {!! $hint or '' !!}</h3>
    </div><!-- /.box-header -->
    <form method="post">
        <input type="hidden" name="option" value="{{ $id }}">
        <div class="box-body">
            @if (session("$id.status") == 'success')
            <div class="callout callout-success">设置已保存。</div>
            @endif

            @if (!empty($messages))
                @foreach($messages as $msg)
                {!! $msg !!}
                @endforeach
            @endif

            <table class="table">
                <tbody>
                    @foreach($items as $item)
                    {!! $item->render() !!}
                    @endforeach
                </tbody>
            </table>
        </div><!-- /.box-body -->
        <div class="box-footer">
            <button type="submit" name="submit" class="btn btn-primary">{{ trans('general.submit') }}</button>
        </div>
    </form>
</div>
