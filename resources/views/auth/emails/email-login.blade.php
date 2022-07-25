@extends('knowfox::layouts.email')

@section('content')
Hello {{$user->name}},

click on the button to log into Knowfox:

<a class="btn btn-default" href="{{ $url }}">Log into Knowfox</a>

If this button does not work, please try again or contact us at hello@post.knowfox.com.

Cheers,
-- The Knowfox Messenger
@endsection
