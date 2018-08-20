<div class="box box-{{ $type }}">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $title }} {!! $hint or '' !!}</h3>
    </div><!-- /.box-header -->
    <form method="post">
        @csrf
        <input type="hidden" name="option" value="{{ $id }}">
        <div class="box-body">

            @foreach($messages as $msg)
            {!! $msg !!}
            @endforeach

            @if ($renderWithOutTable)
                @each('common.option-form.item', $items, 'item')
            @else
            <table class="table">
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        @unless ($renderInputTagsOnly)
                        <td class="key">{{ $item->name }} {!! $item->hint or '' !!}</td>
                        @endunless

                        <td class="value">
                            @include('common.option-form.item', compact('item'))
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

        </div><!-- /.box-body -->
        <div class="box-footer">
            @foreach($buttons as $button)
            {!! $button !!}
            @endforeach
        </div>
    </form>
</div>
