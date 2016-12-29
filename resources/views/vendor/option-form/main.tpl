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


                    @unless ($renderWithOutTable)
                    <tr>
                        @unless ($renderInputTagsOnly)
                        <td class="key">{{ $item->name }} {!! $item->hint or '' !!}</td>
                        @endunless

                        <td class="value">
                    @endunless

                            {!! $item->render() !!}

                            @if ($item->description)
                            <p class="description">{!! $item->description !!}</p>
                            @endif

                    @unless ($renderWithOutTable)
                        </td>
                    </tr>
                    @endunless

                    @endforeach
                </tbody>
            </table>
        </div><!-- /.box-body -->
        <div class="box-footer">
            <button type="submit" name="submit" class="btn btn-primary">{{ trans('general.submit') }}</button>
        </div>
    </form>
</div>
