@extends('auth.master')

@section('title', trans('auth.oauth.authorization.title'))

@section('content')
<div class="login-box">
    <div class="login-logo">
        <a href="{{ url('/') }}">{{ option_localized('site_name') }}</a>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">
        @lang('auth.oauth.authorization.introduction', ['name' => $client->name])
        </p>

        <div class="row">
            <div class="col-xs-6">
                <form method="post" action="{{ route('passport.authorizations.approve') }}">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <button type="submit" class="btn btn-success btn-block btn-flat">
                        @lang('auth.oauth.authorization.button')
                    </button>
                </form>
            </div>
            <div class="col-xs-6">
                <form method="post" action="{{ route('passport.authorizations.deny') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <button class="btn btn-default btn-block btn-flat">@lang('general.cancel')</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
