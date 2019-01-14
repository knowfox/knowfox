@extends('passwordless::email.layout')

@section('content')
    <p>@lang('passwordless::email.greeting', ['user' => $user->name])</p>

    <p>@lang('passwordless::email.register_text', ['app' => config('app.name')])</p>

    <p>@lang('passwordless::email.signature', ['app' => config('app.name')])</p>
@endsection

@section('cancel')
    <p>@lang('passwordless::email.cancel', [
        'app' => config('app.name'),
        'cancel' => route('cancel', ['what' => 'all', 'email' => $user->email])
    ])</p>
@endsection