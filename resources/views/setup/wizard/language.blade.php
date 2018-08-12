<select id="language-chooser" onchange="refreshWithLangPrefer()">
    @foreach(config('locales') as $code => $langInfo)
        @if (!isset($langInfo['alias']))
            <option value="{{ $code }}" {!! $code == config('app.locale') ? 'selected="selected"' : '' !!}>{{ $langInfo['short_name'] }} - {{ $langInfo['name'] }}</option>
        @endif
    @endforeach
</select>
