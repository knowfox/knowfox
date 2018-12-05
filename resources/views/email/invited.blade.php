@extends('layouts.email')

@section('content')
Hi,

{{ $owner->name }} has shared a document with you.
To access this document, please click on this link:

<a class="btn btn-default" href="{{ $url }}">{{ $concept->title }}</a>

If this button does not work, please try again or contact <a href="mailto:{{$owner->email}}">{{$owner->name}}</a>.

Cheers,
-- The Knowfox Messenger
@endsection
