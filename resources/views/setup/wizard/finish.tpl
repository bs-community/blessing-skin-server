@extends('setup.wizard.master')

@section('content')
<h1>{{ trans('setup.wizard.finish.title') }}</h1>
<p>{{ trans('setup.wizard.finish.text') }}</p>

<table class="form-table install-success">
    <tr>
        <th>{{ trans('auth.email') }}</th>
        <td>{{ $email }}</td>
    </tr>
    <tr>
        <th>{{ trans('auth.password') }}</th>
        <td><p><em>{{ $password }}</em></p></td>
    </tr>
</table>

<p class="step">
    <a href="../" class="button button-large">{{ trans('general.index') }}</a>
</p>
@endsection
