@extends('layouts.email')

@section('content')
Hello {{$user->name}},

Your Evernote notebook "{{$notebook_name}}" has been imported:

    {{$info}}

Cheers,
-- The Knowfox Messenger
@endsection
