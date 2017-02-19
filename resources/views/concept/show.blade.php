@extends('layouts.app')

@section('content')

    <main class="container">

        <ol class="breadcrumb">
            <li><a href="{{route('concept.index')}}">Concepts</a></li>

            @foreach ($concept->ancestors()->get() as $ancestor)
                <li><a href="{{route('concept.show', ['concept' => $ancestor])}}">
                        {{$ancestor->title}}
                    </a>
                </li>
            @endforeach

            <li class="active">{{$concept->title}}</li>
        </ol>

        <section class="meta">

            <h2>Meta Data</h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th>Created</th>
                        <td>{{$concept->created_at}}</td>
                    </tr>
                    <tr>
                        <th>Updated</th>
                        <td>{{$concept->updated_at}}</td>
                    </tr>
                    <tr>
                        <th>Language</th>
                        <td>{{$concept->language}}</td>
                    </tr>
                    <tr>
                        <th>Source</th>
                        <td><a href="{{$concept->source_url}}">{{parse_url($concept->source_url, PHP_URL_HOST)}}</a></td>
                    </tr>
                </tbody>
            </table>

            @if ($concept->getSiblings()->count())

                <h2>Siblings</h2>

                <?php $sep = ''; ?>
                @foreach ($concept->getSiblings() as $sibling)
                    {{$sep}}<a href="{{route('concept.show', ['concept' => $sibling])}}">
                        {{$sibling->title}}
                    </a><?php $sep = ', '; ?>
                @endforeach

            @endif

            @if ($concept->children()->count())

                <h2>Children</h2>

                <?php $sep = ''; ?>
                @foreach ($concept->children()->get() as $child)
                    {{$sep}}<a href="{{route('concept.show', ['concept' => $child])}}">
                        {{$child->title}}
                    </a><?php $sep = ', '; ?>
                @endforeach

            @endif

        </section>

        <h1>{{$concept->title}}</h1>

        @if ($concept->summary)
            <p class="summary">{{$concept->summary}}</p>
        @endif

        @if ($concept->body)
            <section>
                {!! $concept->body !!}
            </section>
        @endif

    </main>

@endsection