@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">
            <h1>Edit circle "{{$circle->name}}"</h1>

            @include('partials.messages')

        </section>

        <form action="{{route('circle.store', [$circle])}}" method="POST">
            {{csrf_field()}}

            <button type="submit" class="btn btn-primary">Save</button>
        </form>

    </main>

@endsection
