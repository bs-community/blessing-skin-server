<select id="language-chooser" onchange="refreshWithLangPrefer()">
    @foreach(config('locales') as $code => $langInfo)
    <option value="{{ $code }}" {!! $code == config('app.locale') ? 'selected="selected"' : '' !!}>{{ $langInfo['short_name'] }} - {{ $langInfo['name'] }}</option>
    @endforeach
</select>
