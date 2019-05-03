@extends('setup.updates.master')

@section('content')
<h1>
@lang('setup.updates.welcome.title')
@include('setup.wizard.language')
</h1>

<p>{!! nl2br(trans('setup.updates.welcome.text', ['version' => config('app.version')])) !!}</p>

<p class="step">
    <a href="{{ url('/setup/exec-update') }}" class="button button-large">
    @lang('setup.updates.welcome.button')
    </a>
</p>
@endsection
