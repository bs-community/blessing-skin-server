@extends('setup.updates.master')

@section('content')
<h1>
@lang('setup.updates.changelog.title')
@include('setup.wizard.language')
</h1>

{!! app('parsedown')->text(
    @file_get_contents(resource_path('misc/changelogs/'.app()->getLocale().'/'.config('app.version').'.md'))
) !!}

<p class="step">
    <a href="{{ url('/') }}" class="button button-large">@lang('general.index')</a>
</p>
@endsection
