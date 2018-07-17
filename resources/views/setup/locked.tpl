@extends('setup.master')

@section('content')
<h1>
@lang('setup.locked.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.locked.text')</p>
<p class="step">
    <a href="{{ url('/') }}" class="button button-large">@lang('setup.locked.button')</a>
</p>
@endsection
