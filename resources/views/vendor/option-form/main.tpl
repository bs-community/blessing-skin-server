<div class="box box-{{ $type }}">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $title }} {!! $hint or '' !!}</h3>
    </div><!-- /.box-header -->
    <form method="post">
        <input type="hidden" name="option" value="{{ $id }}">
        <div class="box-body">

            @if (!empty($messages))
                @foreach($messages as $msg)
                {!! $msg !!}
                @endforeach
            @endif

            <table class="table">
                <tbody>
                    @foreach($items as $item)
                        @include('vendor.option-form.item', compact('item'))
                    @endforeach
                </tbody>
            </table>
        </div><!-- /.box-body -->
        <div class="box-footer">
            <button type="submit" name="submit" class="btn btn-primary">{{ trans('general.submit') }}</button>
        </div>
    </form>
</div>
