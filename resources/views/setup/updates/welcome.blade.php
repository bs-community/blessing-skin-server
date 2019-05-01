@extends('setup.updates.master')

@section('content')
<h1>
@lang('setup.updates.welcome.title')
@include('setup.wizard.language')
</h1>

<p>{!! nl2br(trans('setup.updates.welcome.text', ['version' => config('app.version')])) !!}</p>

<form method="post" action="{{ url('/setup/update') }}" novalidate="novalidate">
    @csrf
    <p class="step">
        <input type="submit" name="submit" class="button button-large" value="@lang('setup.updates.welcome.button')"  />
    </p>
</form>
@endsection
