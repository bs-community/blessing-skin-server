@extends('setup.wizard.master')

@section('content')
<h1>{{ trans('setup.wizard.welcome.title') }}</h1>

<p>{{ trans('setup.wizard.welcome.welcome') }} v{{ config('app.version') }}</p>
<p>{{ trans('setup.wizard.database.connection-success', ['server' => $server]) }}</p>

<p class="step">
    <a href="{{ url('setup/info') }}" class="button button-large">{{ trans('setup.wizard.welcome.button') }}</a>
</p>
@endsection
