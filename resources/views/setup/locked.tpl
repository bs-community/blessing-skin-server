@extends('setup.wizard.master')

@section('content')
<h1>{{ trans('setup.locked.title') }}</h1>

<p>{{ trans('setup.locked.text') }}</p>
<p class="step">
    <a href="{{ url('/') }}" class="button button-large">{{ trans('setup.locked.button') }}</a>
</p>
@endsection
