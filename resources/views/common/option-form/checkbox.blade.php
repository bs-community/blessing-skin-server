<label for="{{ $id }}">
    <input {!! $value ? 'checked="true"' : '' !!} type="checkbox" id="{{ $id }}" name="{{ $id }}" {{ $disabled ?? '' }} value="true"> {{ $label }}
</label>
