@extends('setup.updates.master')

@section('content')
<h1>
@lang('setup.updates.success.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.updates.success.text', ['version' => config('app.version')])</p>

<p class="step">
    <a href="{{ url('/setup/changelog') }}" class="button button-large">@lang('setup.updates.welcome.button')</a>
</p>
@endsection
