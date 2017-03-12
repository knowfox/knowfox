@extends('website.tralla_dev.layout')

@inject('picture', 'Knowfox\Services\PictureService')

@section('header')

    <div class="blog-header">
        <h1 class="blog-title">{{$config->title}}</h1>
        @if ($config->subtitle)
            <p class="lead blog-description">{{$config->subtitle}}.</p>
        @endif
    </div>

@endsection

@section('content')

    @foreach ($concepts as $concept)
        {!! $concept->rendered !!}
    @endforeach

    @if ($prev_page || $next_page)
        <nav>
            <ul class="pager">
            @if ($prev_page)
                <li><a href="{{$prev_page}}">Previous</a></li>
            @endif
            @if ($next_page)
                <li><a href="{{$next_page}}">Next</a></li>
            @endif
            </ul>
        </nav>
    @endif
@endsection
