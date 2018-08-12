@extends('setup.master')

@section('content')
<h1>{{ trans('setup.wizard.welcome.title') }}
@include('setup.wizard.language')
</h1>

<p>{{ trans('setup.wizard.welcome.text', ['version' => config('app.version')]) }}</p>
<p>{{ trans('setup.database.connection-success', ['server' => $server, 'type' => $type]) }}</p>

<p class="step">
    <a href="{{ url('setup/info') }}" class="button button-large">{{ trans('setup.wizard.welcome.button') }}</a>
</p>
@endsection
