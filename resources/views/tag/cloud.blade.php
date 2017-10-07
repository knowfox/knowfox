@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li class="active">Tags</li>
            </ol>

            <h1>{{$page_title}} <small>{{$sub_title}}</small></h1>

            @include('partials.messages')

        </section>

        @if ($tags->count() == 0)
            <p>Nothing here.</p>
        @else

            <table class="table">
                <thead>
                    <tr>
                        <th><a href="?order=count">Count</a></th>
                        <th><a href="?order=name">Name</a></th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($tags as $tag)
                    <tr>
                        <td>{{$tag->count}}</td>
                        <td><a href="{{ route('concept.index', ['tag' => $tag->slug]) }}">{{$tag->name}}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="text-center">{{ $tags }}</div>
        @endif
    </main>

@endsection
