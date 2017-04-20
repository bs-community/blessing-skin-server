@if ($tid_steve == '0')
<img id="{{ $pid }}-steve" width="64" />
@else
<a href="{{ url('skinlib/show/'.$tid_steve) }}">
    <img id="{{ $pid }}-steve" width="64" src="{{ url('preview/64/'.$tid_steve) }}.png" />
</a>
@endif

@if ($tid_alex == '0')
<img id="{{ $pid }}-alex" width="64" />
@else
<a href="{{ url('skinlib/show/'.$tid_alex) }}">
    <img id="{{ $pid }}-alex" width="64" src="{{ url('preview/64/'.$tid_alex) }}.png" />
</a>
@endif

@if ($tid_cape == '0')
<img id="{{ $pid }}-cape" width="64" />
@else
<a href="{{ url('skinlib/show/'.$tid_cape) }}">
    <img id="{{ $pid }}-cape" width="64" src="{{ url('preview/64/'.$tid_cape) }}.png" />
</a>
@endif
