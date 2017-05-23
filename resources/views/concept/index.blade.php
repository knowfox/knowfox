@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li class="active">Concepts</li>
            </ol>

            <a class="btn btn-default pull-right" href="{{route('concept.create')}}"><i class="glyphicon glyphicon-plus-sign"></i> New concept</a>
            <h1>{{$page_title}} <small>{{$sub_title}}</small></h1>

            @include('partials.messages')

        </section>

        @if ($concepts->count() == 0)
            <p>Nothing here.</p>
        @else

            <table class="table">
                <thead>
                <tr>
                    <th style="width:5%">Id</th>
                    <th style="width:50%">Title</th>
                    <th style="width:20%">Tags</th>
                    <th style="width:5%">Viewed</th>
                    <th style="width:20%">Viewed / Updated</th>
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
                            @if ($concept->is_flagged)
                                <i class="glyphicon glyphicon-heart"></i>
                            @endif
                        </td>
                        <td>
                            @if ($concept->type != 'concept')
                                <span class="label label-info">{{ucfirst($concept->type)}}</span>
                            @endif
                            @foreach ($concept->tags as $tag)
                                <a href="{{route('concept.index', ['tag' => $tag->slug])}}" class="label label-default">{{$tag->name}}</a>
                            @endforeach
                        </td>
                        <td>{{$concept->viewed_count}}</td>
                        <td>{{$concept->viewed_at or $concept->updated_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="text-center">{{ $concepts }}</div>
        @endif
    </main>

@endsection