@extends('setup.master')

@section('content')
<h1>@lang('setup.wizard.welcome.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.wizard.welcome.text', ['version' => config('app.version')])</p>
<p>@lang('setup.database.connection-success', ['server' => $server, 'type' => $type])</p>

<p class="step">
    <a href="{{ url('setup/info') }}" class="button button-large">@lang('setup.wizard.welcome.button')</a>
</p>
@endsection
