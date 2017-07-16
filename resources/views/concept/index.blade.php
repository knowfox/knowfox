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
                @include('partials.table-header')
                <tbody>
                @foreach ($concepts as $concept)
                    @include('partials.table-row')
                @endforeach
                </tbody>
            </table>

            <div class="text-center">{{ $concepts }}</div>
        @endif
    </main>

@endsection