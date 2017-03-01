@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li class="active">Circles</li>
            </ol>

            <h1>{{$page_title}} <small>{{$sub_title}}</small></h1>

            @include('partials.messages')

        </section>

        <table class="table">
            <thead>
            <tr>
                <th style="width:5%">Id</th>
                <th style="width:50%">Name</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($circles as $circle)
                <tr>
                    <td>{{$circle->id}}</td>
                    <td><a href="{{route('circle.edit', [$circle])}}">{{$circle->name}}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="text-center">{{ $circles }}</div>
    </main>

@endsection
