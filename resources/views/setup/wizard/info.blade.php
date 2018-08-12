@extends('setup.master')

@section('content')
<h1>{{ trans('setup.wizard.info.title') }}
@include('setup.wizard.language')
</h1>

<p>{{ trans('setup.wizard.info.text') }}</p>

<form id="setup" method="post" action="{{ url('setup/finish') }}" novalidate="novalidate">
    <table class="form-table">
        <tr>
            <th scope="row"><label for="email">{{ trans('setup.wizard.info.admin-email') }}</label></th>
            <td>
                <input name="email" type="email" id="email" size="25" value="" />
                <p>{!! trans('setup.wizard.info.admin-notice') !!}</p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password">{{ trans('setup.wizard.info.password') }}</label></th>
            <td>
                <input type="password" name="password" id="password" class="regular-text" autocomplete="off" />
                <p>{!! trans('setup.wizard.info.pwd-notice') !!}</p>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password_confirmation">{{ trans('setup.wizard.info.confirm-pwd') }}</label></th>
            <td>
                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="off" />
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="site_name">{{ trans('setup.wizard.info.site-name') }}</label></th>
            <td>
                <input name="site_name" type="text" id="site_name" size="25" value="{{ config('options.site_name') }}" />
                <p>{{ trans('setup.wizard.info.site-name-notice') }}</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="generate_random">{{ trans('setup.wizard.info.secure') }}</label></th>
            <td>
                <label for="generate_random">
                    <input name="generate_random" type="checkbox" checked="checked" id="generate_random" size="25" value="on" />
                    {{ trans('setup.wizard.info.secure-notice') }}
                </label>
            </td>
        </tr>
    </table>

    @if (count($errors) > 0)
        <div class="alert alert-warning" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="step">
        <input type="submit" name="submit" id="submit" class="button button-large" value="{{ trans('setup.wizard.info.button') }}"  />
    </p>
</form>
@endsection
