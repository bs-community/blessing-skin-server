<div class="card card-{{ $type }}">
    <div class="card-header">
        <h3 class="card-title">{{ $title }} {!! $hint ?? '' !!}</h3>
    </div>
    <form method="post">
        @csrf
        <input type="hidden" name="option" value="{{ $id }}">
        <div class="card-body">

            @foreach($messages as $msg)
            {!! $msg !!}
            @endforeach

            @if ($renderWithoutTable)
                @each('common.option-form.item', $items, 'item')
            @else
            <table class="table">
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        @unless ($renderInputTagsOnly)
                        <td class="key">{{ $item->name }} {!! $item->hint ?? '' !!}</td>
                        @endunless

                        <td class="value">
                            @include('common.option-form.item', compact('item'))
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

        </div>
        <div class="card-footer">
            @foreach($buttons as $button)
            {!! $button !!}
            @endforeach
        </div>
    </form>
</div>
