@extends('website.tralla_dev.layout')

@inject('picture', 'Knowfox\Services\PictureService')

@section('header')

    <div class="blog-header">
        @if (!empty($concept->image))
            <img src="{{ url($picture->asset($concept->image, 'text')) }}">
        @endif

        <h1 class="blog-title">{{$concept->title}}</h1>
        @if ($concept->summary)
            <p class="lead blog-description">{{$concept->summary}}</p>
        @endif
    </div>

@endsection

@section('content')

    <p class="blog-post-meta">{{ strftime('%Y-%m-%d', strtotime($concept->created_at)) }} by {{$concept->owner->name}}</p>

    @if ($concept->rendered_body)
        <section class="body" data-uuid="{{$concept->uuid}}">
            {!! $concept->rendered_body !!}
        </section>
    @endif
@endsection
