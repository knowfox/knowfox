@extends('passwordless::email.layout')

@section('content')
    <p>@lang('passwordless::email.greeting', ['user' => $user->name])</p>

    <p>@lang('passwordless::email.login_top', ['app' => config('app.name')])</p>

    <p class="button"><a class="btn btn-default" href="{{ $url }}">
        {{ __('passwordless::email.login_button', ['app' => config('app.name')]) }}</a></p>

    <p>@lang('passwordless::email.login_bottom', [
        'app' => config('app.name'), 
        'domain' => env('MAIL_DOMAIN')
    ])</p>

    <p>@lang('passwordless::email.signature', ['app' => config('app.name')])</p>
@endsection
