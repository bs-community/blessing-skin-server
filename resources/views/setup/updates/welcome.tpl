@extends('setup.updates.master')

@section('content')
<h1>{{ trans('setup.updates.welcome.title') }}</h1>

<p>{{ trans('setup.updates.welcome.tip-welcome') }} {{ config('app.version') }}</p>
<p>{{ trans('setup.updates.welcome.tip-next') }}</p>

<form method="post" action="" novalidate="novalidate">
    <p class="step">
        <input type="submit" name="submit" class="button button-large" value="{{ trans('setup.updates.welcome.button') }}"  />
    </p>
</form>
@endsection
