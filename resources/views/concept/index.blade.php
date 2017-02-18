@extends('layouts.app')

@section('content')

    <main class="container">
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
                        @foreach ($concept->getAncestors(['title']) as $ancestor)
                            {{$ancestor->title}} &raquo;
                        @endforeach
                        <br>
                        {{$concept->title}}
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