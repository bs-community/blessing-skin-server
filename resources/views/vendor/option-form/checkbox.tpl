<label for="{{ $id }}">
    <input {!! $value ? 'checked="true"' : '' !!} type="checkbox" id="{{ $id }}" name="{{ $id }}" value="on"> {{ $label }}
</label>
