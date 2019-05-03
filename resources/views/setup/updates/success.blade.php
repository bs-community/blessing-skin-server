@extends('setup.updates.master')

@section('content')
<h1>
@lang('setup.updates.success.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.updates.success.text', ['version' => config('app.version')])</p>

{{-- if any tip is given --}}
@if (is_array($tips))
<p><b>@lang('setup.updates.success.tips')</b></p>
<ul>
    @foreach ($tips as $tip)
    <li><p>{!! $tip !!}</p></li>
    @endforeach
</ul>
@endif

<p class="step">
    <a href="{{ url('/') }}" class="button button-large">@lang('general.index')</a>
</p>
@endsection
