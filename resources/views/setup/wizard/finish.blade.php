@extends('setup.master')

@section('content')
<h1>@lang('setup.wizard.finish.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.wizard.finish.text')</p>

<table class="form-table install-success">
    <tr>
        <th>@lang('auth.email')</th>
        <td>{{ $email }}</td>
    </tr>
    <tr>
        <th>@lang('auth.password')</th>
        <td><p><em>{{ $password }}</em></p></td>
    </tr>
</table>

<p class="step">
    <a href="../" class="button button-large">@lang('general.index')</a>
</p>
@endsection
