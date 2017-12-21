@extends('layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li><a href="{{route('concept.index')}}">Concepts</a></li>

                @foreach ($concept->ancestors()->get() as $ancestor)
                    <li><a href="{{route('concept.show', ['concept' => $ancestor])}}">
                            {{$ancestor->title}}
                        </a>
                    </li>
                @endforeach

                <li><a href="{{ route('concept.show', [$concept]) }}">{{$concept->title}}</a></li>
                <li class="active">Attachments</li>
            </ol>

            <h1>{{$page_title}} <small>{{$sub_title}}</small></h1>

            @include('partials.messages')

        </section>

        @if ($attachments->count() == 0)
            <p>Nothing here.</p>
        @else

            <table class="table">
                <thead>
                <tr>
                    <th style="width:10%">Default</th>
                    <th style="width:20%">Thumbnail</th>
                    <th style="width:60%">Name</th>
                    <th style="width:10%">Operations</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($attachments as $attachment)
                @endforeach
                </tbody>
            </table>

            <div class="text-center">{{ $attachments }}</div>
        @endif
    </main>

@endsection