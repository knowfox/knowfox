@extends('passwordless::email-layout')

@section('content')
    Hello {{$user->name}},

    thank you for registering with {{config('app.name')}}.

    Cheers,
    -- The {{config('app.name')}} Messenger
@endsection

@section('cancel')
    You no longer want to receive mail from {{config('app.name')}}?<br>
    <a href="{{ route('cancel', ['what' => 'all', 'email' => $user->email]) }}">Cancel all mailings with a single click.</a>
@endsection