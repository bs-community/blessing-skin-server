<select class="form-control" name="{{ $id }}">

@foreach ((array) $items as $item)
    <?php list($id, $name) = $item; ?>
    <option {!! $selected == $id ? 'selected="selected"' : '' !!} value="{{ $id }}">{{ $name }}</option>";
@endforeach

</select>
