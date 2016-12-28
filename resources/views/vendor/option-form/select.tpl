<select class="form-control" name="{{ $id }}">

@foreach ((array) $options as $option)
    <option {!! $selected == $option['value'] ? 'selected="selected"' : '' !!} value="{{ $option['value'] }}">{{ $option['name'] }}</option>
@endforeach

</select>
