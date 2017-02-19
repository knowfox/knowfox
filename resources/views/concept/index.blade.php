@extends('layouts.app')

@section('content')

    <main class="container">

        <ol class="breadcrumb">
            <li class="active">Concepts</li>
        </ol>

        <table class="table">
            <thead>
            <tr>
                <th style="width:5%">Id</th>
                <th style="width:75%">Title</th>
                <th style="width:10%">Updated</th>
                <th style="width:10%">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($concepts as $concept)
                <tr>
                    <td>{{$concept->id}}</td>
                    <td>
                        @if ($concept->depth == 0)
                            <a href="{{route('concept.show', ['concept' => $concept])}}">
                                <strong>{{$concept->title}}</strong>
                            </a>
                        @else
                            @foreach ($concept->ancestors()->get() as $ancestor)
                            {{$ancestor->title}} &raquo;
                                @endforeach
                            <br>
                            <a href="{{route('concept.show', ['concept' => $concept])}}">
                                {{$concept->title}}
                            </a>
                        @endif
                    </td>
                    <td>{{$concept->updated_at}}</td>
                    <td><a href="#edit" data-id="{{$concept->id}}"><i class="glyphicon glyphicon-edit"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $concepts }}
    </main>

@endsection