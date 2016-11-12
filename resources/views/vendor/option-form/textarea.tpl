<textarea class="form-control" rows="{{ $rows }}" name="{{ $id }}">{{ $value }}</textarea>

@if ($description != "")
<p class="description">{!! $description !!}</p>
@endif
