@extends('setup.updates.master')

@section('content')
<h1>
{{ trans('setup.updates.success.title') }}
@include('setup.wizard.language')
</h1>

<p>{{ trans('setup.updates.success.text', ['version' => config('app.version')]) }}</p>

{{-- if any tip is given --}}
@if (!empty($tips))
<p><b>{{ trans('setup.updates.success.tips') }}</b></p>
<ul>
    @foreach ($tips as $tip)
    <li><p>{{ $tip }}</p></li>
    @endforeach
</ul>
@endif

<p class="step">
    <a href="{{ url('/') }}" class="button button-large">{{ trans('general.index') }}</a>
</p>
@endsection
