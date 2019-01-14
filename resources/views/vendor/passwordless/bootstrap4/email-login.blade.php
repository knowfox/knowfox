@extends('passwordless::email-layout')

@section('content')
    Hello {{$user->name}},

    click on the button to log into {{config('app.name')}}:

    <a class="btn btn-default" href="{{ $url }}">Log into {{config('app.name')}}</a>

    If this button does not work, please try again or contact us at hello{{ '@' . env('MAIL_DOMAIN') }}.

    Cheers,
    -- The {{config('app.name')}} Messenger
@endsection
