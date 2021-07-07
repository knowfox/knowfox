@extends('knowfox::layouts.app')

@section('content')

    <main class="container">

        <section class="page-header">

            <ol class="breadcrumb">
                <li class="active">Tags</li>
            </ol>

            <h1>{{$page_title}} <small>{!! $sub_title !!}</small></h1>

            @include('knowfox::partials.messages')

        </section>

        @if ($tags->count() == 0)
            <p>Nothing here.</p>
        @else

            <table class="table">
                <thead>
                    <tr>
                        <th><a href="?order=count&dir={{$other_dir}}">Count
                                @if ($order == 'count')
                                    <i class="glyphicon glyphicon-{{$dir_icon}}"></i>
                                @endif
                            </a></th>
                        <th><a href="?order=name&dir={{$other_dir}}">Name
                                @if ($order == 'name')
                                    <i class="glyphicon glyphicon-{{$dir_icon}}"></i>
                                @endif
                            </a></th>
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
