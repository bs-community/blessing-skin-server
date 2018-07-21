@extends('setup.master')

@section('content')
<h1>@lang('setup.wizard.database.title')
@include('setup.wizard.language')
</h1>

<p>@lang('setup.wizard.database.text')</p>

<form id="setup" method="post" action="{{ url('setup/database') }}" novalidate="novalidate">
    <table class="form-table">
        <tr class="form-field form-required">
            <th scope="row"><label for="type">@lang('setup.wizard.database.type')</label></th>
            <td>
                <input name="type" type="radio" value="mysql" id="type-mysql" checked />
                <label for="type-mysql">MySQL / MariaDB</label>
                <input name="type" type="radio" value="pgsql" id="type-pgsql" />
                <label for="type-pgsql">PostgreSQL</label>
                <input name="type" type="radio" value="sqlite" id="type-sqlite" />
                <label for="type-sqlite">SQLite</label>
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="host">@lang('setup.wizard.database.host')</label></th>
            <td>
                <input name="host" type="text" id="host" size="25" value="127.0.0.1" />
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="port">@lang('setup.wizard.database.port')</label></th>
            <td>
                <input name="port" type="text" id="port" size="25" value="3306" />
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="username">@lang('setup.wizard.database.username')</label></th>
            <td>
                <input name="username" type="text" id="username" size="25" value="" />
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="password">@lang('setup.wizard.database.password')</label></th>
            <td>
                <input type="password" name="password" id="password" class="regular-text" autocomplete="off" />
            </td>
        </tr>
        <tr class="form-field form-required">
            <th scope="row"><label for="db">@lang('setup.wizard.database.db')</label></th>
            <td>
                <input name="db" type="text" id="db" size="25" value="" />
                <p>@lang('setup.wizard.database.db-notice')</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="prefix">@lang('setup.wizard.database.prefix')</label></th>
            <td>
                <input name="prefix" type="text" id="prefix" size="25" value="" />
                <p>@lang('setup.wizard.database.prefix-notice')</p>
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
        <input type="submit" name="submit" id="submit" class="button button-large" value="@lang('setup.wizard.welcome.button')"  />
    </p>
</form>
@endsection

@section('script')
<script>
    var port = document.getElementById('port')
    document.getElementById('type-mysql').onchange = function () {
        port.value = 3306
    }
    document.getElementById('type-pgsql').onchange = function () {
        port.value = 5432
    }
</script>
@endsection
