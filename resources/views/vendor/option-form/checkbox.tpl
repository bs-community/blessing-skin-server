<label for="{{ $id }}">
    <input {!! $value ? 'checked="true"' : '' !!} type="checkbox" id="{{ $id }}" name="{{ $id }}" {{ $disabled or '' }} value="true"> {{ $label }}
</label>
