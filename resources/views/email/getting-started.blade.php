@extends('layouts.email')

@section('content')
    Hello {{$user->name}},

    thank you for registering with Knowfox, the Personal Knowledge Management app.

    Cheers,

    -- The Knowfox Messenger
@endsection

@section('cancel')
    You no longer want to receive mail from Knowfox?<br>
    <a href="{{ route('cancel', ['what' => 'all', 'email' => $user->email]) }}">Cancel all mailings with a single click.</a>
@endsection