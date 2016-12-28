<tr>
    <td class="key">{{ $item->name }} {!! $item->hint or '' !!}</td>
    <td class="value">
        {!! $item->render() !!}

        @if ($item->description != "")
        <p class="description">{!! $item->description !!}</p>
        @endif
    </td>
</tr>
